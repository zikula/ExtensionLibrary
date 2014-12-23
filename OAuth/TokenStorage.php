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
use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\Module\ExtensionLibraryModule\Entity\OAuthEntity;


class TokenStorage implements TokenStorageInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    /**
     * @param string $service
     *
     * @return TokenInterface
     *
     * @throws TokenNotFoundException
     */
    public function retrieveAccessToken($service)
    {
        $oauth = $this->em->getRepository('ZikulaExtensionLibraryModule:OAuthEntity')->findOneBy(array(
            'sessId'  => $this->session->getId(),
            'type'    => OAuthEntity::TYPE_TOKEN,
            'service' => $service
        ));

        if (!$oauth) {
            throw new TokenNotFoundException();
        }

        return $oauth->getValue();
    }

    /**
     * @param string         $service
     * @param TokenInterface $token
     *
     * @return TokenStorageInterface
     */
    public function storeAccessToken($service, TokenInterface $token)
    {
        $oauth = new OAuthEntity($this->session->getId(), OAuthEntity::TYPE_TOKEN, $service, $token);
        $this->em->persist($oauth);
        $this->em->flush();

        return $this;
    }

    /**
     * @param string $service
     *
     * @return bool
     */
    public function hasAccessToken($service)
    {
        try {
            $this->retrieveAccessToken($service);

            return true;
        } catch (TokenNotFoundException $e) {
            return false;
        }
    }

    /**
     * Delete the users token. Aka, log out.
     *
     * @param string $service
     *
     * @return TokenStorageInterface
     */
    public function clearToken($service)
    {
        $oauthEntities = $this->em->getRepository('ZikulaExtensionLibraryModule:OAuthEntity')->findBy(array(
            'sessId'  => $this->session->getId(),
            'type'    => OAuthEntity::TYPE_TOKEN,
            'service' => $service
        ));

        foreach ($oauthEntities as $oauthEntity) {
            $this->em->remove($oauthEntity);
        }

        $this->em->flush();

        return $this;
    }

    /**
     * Delete *ALL* user tokens. Use with care. Most of the time you will likely
     * want to use clearToken() instead.
     *
     * @return TokenStorageInterface
     */
    public function clearAllTokens()
    {
        $oauthEntities = $this->em->getRepository('ZikulaExtensionLibraryModule:OAuthEntity')->findBy(array(
            'sessId'  => $this->session->getId(),
            'type'    => OAuthEntity::TYPE_TOKEN,
        ));

        foreach ($oauthEntities as $oauthEntity) {
            $this->em->remove($oauthEntity);
        }

        $this->em->flush();

        return $this;
    }

    /**
     * Store the authorization state related to a given service
     *
     * @param string $service
     * @param string $state
     *
     * @return TokenStorageInterface
     */
    public function storeAuthorizationState($service, $state)
    {
        $oauth = new OAuthEntity($this->session->getId(), OAuthEntity::TYPE_STATE, $service, $state);
        $this->em->persist($oauth);
        $this->em->flush();

        return $this;
    }

    /**
     * Check if an authorization state for a given service exists
     *
     * @param string $service
     *
     * @return bool
     */
    public function hasAuthorizationState($service)
    {
        try {
            $this->retrieveAuthorizationState($service);

            return true;
        } catch (AuthorizationStateNotFoundException $e) {
            return false;
        }
    }

    /**
     * Retrieve the authorization state for a given service
     *
     * @param string $service
     *
     * @return string
     */
    public function retrieveAuthorizationState($service)
    {
        $oauth = $this->em->getRepository('ZikulaExtensionLibraryModule:OAuthEntity')->findOneBy(array(
            'sessId'  => $this->session->getId(),
            'type'    => OAuthEntity::TYPE_STATE,
            'service' => $service
        ));

        if (!$oauth) {
            throw new AuthorizationStateNotFoundException();
        }

        return $oauth->getValue();
    }

    /**
     * Clear the authorization state of a given service
     *
     * @param string $service
     *
     * @return TokenStorageInterface
     */
    public function clearAuthorizationState($service)
    {
        $oauthEntities = $this->em->getRepository('ZikulaExtensionLibraryModule:OAuthEntity')->findBy(array(
            'sessId'  => $this->session->getId(),
            'type'    => OAuthEntity::TYPE_STATE,
            'service' => $service
        ));

        foreach ($oauthEntities as $oauthEntity) {
            $this->em->remove($oauthEntity);
        }

        $this->em->flush();

        return $this;
    }

    /**
     * Delete *ALL* user authorization states. Use with care. Most of the time you will likely
     * want to use clearAuthorization() instead.
     *
     * @return TokenStorageInterface
     */
    public function clearAllAuthorizationStates()
    {
        $oauthEntities = $this->em->getRepository('ZikulaExtensionLibraryModule:OAuthEntity')->findBy(array(
            'sessId'  => $this->session->getId(),
            'type'    => OAuthEntity::TYPE_STATE,
        ));

        foreach ($oauthEntities as $oauthEntity) {
            $this->em->remove($oauthEntity);
        }

        $this->em->flush();

        return $this;
    }
}
