<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\ExtensionLibraryModule\OAuth;

use Doctrine\ORM\EntityManagerInterface;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Client\CurlClient;
use OAuth\OAuth2\Service\GitHub as GitHubService;
use OAuth\OAuth2\Token\TokenInterface;
use OAuth\ServiceFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Github\Client as GitHubClient;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;
use Zikula\Module\ExtensionLibraryModule\Manager\RepositoryManager;

class Manager
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Zikula\Module\ExtensionLibraryModule\Manager\RepositoryManager
     */
    private $repositoryManager;

    public function __construct(RequestStack $requestStack, RouterInterface $router, EntityManagerInterface $em, RepositoryManager $repositoryManager)
    {
        $request = $requestStack->getCurrentRequest();
        if (is_null($request)) {
            throw new \RuntimeException('The current request cannot be null.');
        }
        $this->request = $request;
        $this->router = $router;
        $this->em = $em;
        $this->repositoryManager = $repositoryManager;
    }

    /**
     * Authenticate a user via GitHub's OAuth process.
     *
     * @param  string $redirectUrl                The url to redirect the user to after successful authentication.
     *
     * @return bool|RedirectResponse|GitHubClient Returns false on fatal error, a RedirectResponse to authenticate at
     *                                            GitHub if appropriate or an authenticated GitHubClient if everything
     *                                            went ok.
     */
    public function authenticate($redirectUrl)
    {
        $oAuthService = $this->getOAuthService($redirectUrl);
        if (!$oAuthService) {
            return false;
        }

        $hasToken = $oAuthService->getStorage()->hasAccessToken('GitHub');
        if ($hasToken) {
            $accessToken = $oAuthService->getStorage()->retrieveAccessToken('GitHub');
            $endOfLife = $accessToken->getEndOfLife();
            if ($endOfLife != TokenInterface::EOL_NEVER_EXPIRES && $endOfLife >= time() - 60) {
                $accessToken = $oAuthService->refreshAccessToken($accessToken);
            }
            if (!$userGitHubClient = $this->getUserGitHubClient($accessToken)) {
                $hasToken = false;
                $oAuthService->getStorage()->clearToken('GitHub');
                $oAuthService->getStorage()->clearAuthorizationState('GitHub');
                $this->request->query->remove('code');
            }
        }

        if (!$hasToken && !$this->request->query->has('code')) {
            return new RedirectResponse((string)$oAuthService->getAuthorizationUri());
        }

        if (!$hasToken) {
            $accessToken = $oAuthService->requestAccessToken($this->request->query->get('code'));
            $userGitHubClient = $this->getUserGitHubClient($accessToken);
        }

        return $userGitHubClient;
    }

    /**
     * Checks if the currently authenticated user has push access to a specific repository. If the user is not authenticated
     * via GitHub's OAuth yet, no attempt will be made to authenticate him.
     *
     * @param ExtensionEntity $extensionEntity
     *
     * @return bool
     */
    public function hasPushAccess(ExtensionEntity $extensionEntity)
    {
        $userGitHubClient = $this->authenticate($this->router->generate('zikulaextensionlibrarymodule_user_index', array(), RouterInterface::ABSOLUTE_URL));
        if (!$userGitHubClient instanceof GitHubClient) {
            return false;
        }

        $this->repositoryManager->setGitHubClient($userGitHubClient);
        $userRepositoriesWithPushAccess = array_column($this->repositoryManager->getRepositoriesWithPushAccess(), 'full_name');

        if (in_array("{$extensionEntity->getVendor()->getOwner()}/{$extensionEntity->getName()}", $userRepositoriesWithPushAccess)) {
            return true;
        }

        return false;
    }

    /**
     * Get an authenticated GitHub OAuth service.
     *
     * @param  string $redirectUrl The url to redirect the user to after successful authentication.
     *
     * @return bool|GitHubService
     */
    private function getOAuthService($redirectUrl)
    {
        $appId = \ModUtil::getVar('ZikulaExtensionLibraryModule', 'github_app_id', '');
        $appSecret = \ModUtil::getVar('ZikulaExtensionLibraryModule', 'github_app_secret', '');
        if (empty($appId) || empty($appSecret)) {
            return false;
        }

        // Setup the credentials for the requests
        $credentials = new Credentials(
            $appId,
            $appSecret,
            $redirectUrl
        );
        // Session storage
        $storage = new TokenStorage($this->em, $this->request->getSession());
        /** @var $serviceFactory ServiceFactory An OAuth service factory. */
        $serviceFactory = new ServiceFactory();
        $serviceFactory->setHttpClient(new CurlClient());

        // Instantiate the GitHub service using the credentials, http client and storage mechanism for the token
        $service = $serviceFactory->createService('GitHub', $credentials, $storage/*, array('write:repo_hook', 'read:org')*/);

        // Hack to set the scopes. The OAuth lib currently does not know about some of the new scopes and would throw an error.
        $r = new \ReflectionClass($service);
        $r = $r->getProperty('scopes');
        $r->setAccessible(true);
        $r->setValue($service, array('admin:repo_hook', 'read:org'));

        return $service;
    }

    /**
     * Get a GitHub API client authenticated with a user's OAuth token.
     *
     * @param TokenInterface $accessToken
     *
     * @return bool|GitHubClient
     */
    private function getUserGitHubClient(TokenInterface $accessToken)
    {
        $userGitHubClient = new GitHubClient();
        $userGitHubClient->authenticate($accessToken->getAccessToken(), null, GitHubClient::AUTH_HTTP_TOKEN);
        try {
            $userGitHubClient->getHttpClient()->get('rate_limit');
        } catch (\RuntimeException $e) {
            return false;
        }

        return $userGitHubClient;
    }
}
