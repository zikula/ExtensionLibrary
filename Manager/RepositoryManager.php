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

namespace Zikula\Module\ExtensionLibraryModule\Manager;


use Github\Client as GitHubClient;
use Github\Exception\RuntimeException;
use Github\HttpClient\Message\ResponseMediator;
use Github\ResultPager;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class RepositoryManager
{
    /**
     * @var GitHubClient
     */
    private $gitHubClient;

    private $username;

    /**
     * @var ResultPager
     */
    private $gitHubClientPaginator;

    public function __construct()
    {

    }

    public function setGitHubClient(GitHubClient $gitHubClient)
    {
        $this->gitHubClient = $gitHubClient;
        $this->gitHubClientPaginator = new ResultPager($gitHubClient);

        $user = $gitHubClient->api('current_user')->show();
        $this->username = $user['login'];
    }

    public function forkRepository($repository)
    {
        $result = $this->gitHubClient->api('repo')->forks()->create($repository['owner']['login'], $repository['name']);

        $forkedRepository = false;
        for ($i = 0; $i < 10; $i++) {
            // Wait until repo is forked. Retry every three seconds.
            sleep(3);
            $forkedRepository = $this->getRepository($result['name']);
            if ($forkedRepository !== false) {
                break;
            }
        }
        if ($forkedRepository === false) {
            throw new ServiceUnavailableHttpException(null, __('Something went wrong while forking your project.'));
        }

        return $forkedRepository;
    }

    public function deleteRepository($repository)
    {
        $this->gitHubClient->getHttpClient()->delete("/repos/{$repository['owner']['login']}/{$repository['name']}");
    }

    public function createPullRequest($repository, $forkedRepository, $fromBranch, $toBranch, $title, $body)
    {
        return ResponseMediator::getContent($this->gitHubClient->getHttpClient()->post(
            "/repos/{$repository['owner']['login']}/{$repository['name']}/pulls",
            json_encode(array (
                'head' => "{$forkedRepository['owner']['login']}:$fromBranch",
                'base' => $toBranch,
                'title' => $title,
                'body' => $body
        ))));
    }

    public function createWebHook($repository, $events, $url)
    {
        return ResponseMediator::getContent($this->gitHubClient->getHttpClient()->post(
            "repos/{$repository['owner']['login']}/{$repository['name']}/hooks",
            json_encode(array(
                'name' => 'web',
                'active' => true,
                'events' => $events,
                'config' => array (
                    'url' => $url,
                    'secret' => '',
                    'content_type' => 'json'
            )
        ))));
    }

    public function addBranch($repository, $baseBranch, $newBranch)
    {
        $refs = ResponseMediator::getContent($this->gitHubClient->getHttpClient()->get(
            "/repos/{$repository['owner']['login']}/{$repository['name']}/git/refs/heads"
        ));

        foreach ($refs as $ref) {
            if ($ref['ref'] == "refs/heads/$baseBranch") {
                $sha = $ref['object']['sha'];
                try {
                    $this->gitHubClient->getHttpClient()->post(
                        "/repos/{$repository['owner']['login']}/{$repository['name']}/git/refs",
                        json_encode(array (
                            'ref' => "refs/heads/$newBranch",
                            'sha' => $sha
                        ))
                    );
                } catch (RuntimeException $e) {
                    // This throws an exception from time to time for some reason, although it worked fine..
                }
                return;
            }
        }

        throw new \RuntimeException("No branch $baseBranch to create a new branch from found.");
    }

    public function getFileInRepository($repository, $branch, $path)
    {
        try {
            $file = ResponseMediator::getContent($this->gitHubClient->getHttpClient()->get(
                "/repos/{$repository['owner']['login']}/{$repository['name']}/contents/$path",
                array ('ref' => $branch)
            ));
        } catch (RuntimeException $e) {
            return false;
        }

        if ($file['type'] == 'file') {
            return $file;
        }

        return false;
    }

    public function createFileInRepository($repository, $branch, $path, $content)
    {
        $this->gitHubClient->getHttpClient()->put(
            "/repos/{$repository['owner']['login']}/{$repository['name']}/contents/$path",
            json_encode(array(
                'message' => "Created $path file for Extension Library.",
                'content' => base64_encode($content),
                'branch' => $branch
            )));
    }

    public function updateFileInRepository($repository, $branch, $sha, $path, $content)
    {
        $this->gitHubClient->getHttpClient()->put(
            "/repos/{$repository['owner']['login']}/{$repository['name']}/contents/$path",
            json_encode(array(
                'message' => "Updated $path file for Extension Library.",
                'content' => base64_encode($content),
                'branch' => $branch,
                'sha' => $sha
            )));
    }

    public function getRepository($name)
    {
        $repos = array_filter($this->getRepositoriesWithPushAccess(), function ($repo) use ($name) {
            return $name === $repo['name'] || $name === $repo['full_name'];
        });

        if (count($repos) == 0) {
            return false;
        }

        return current($repos);
    }

    public function getRepositories()
    {
        return $this->gitHubClientPaginator->fetchAll(
            $this->gitHubClient->api('current_user'),
            'repositories',
            array ('all')
        );
    }

    /**
     * Returns a list of all the organizations the current user has admin access at. Includes the user himself.
     *
     * @return array
     */
    public function getOrgsAndUserWithAdminAccess()
    {
        $vendors = array();

        $user = $this->gitHubClient->currentUser()->show();
        $vendors[$user['id']] = $user;

        $teams = $this->getTeams();
        foreach ($teams as $team) {
            if ($team['permission'] == 'admin') {
                $vendors[$team['organization']['id']] = $team['organization'];
            }
        }

        return $vendors;
    }

    /**
     * Returns a list of all repositories the user has push access to.
     *
     * @return array
     */
    public function getRepositoriesWithPushAccess()
    {
        // Get normal repositories (where the user is owner or member).
        $repositories = $this->getRepositories();

        $teams = $this->getTeams();
        foreach ($teams as $team) {
            if (!in_array($team['permission'], array('admin', 'push'))) {
                continue;
            }

            $repositories = array_merge($repositories, $this->gitHubClientPaginator->fetchAll(
                $this->gitHubClient->api('teams'),
                'repositories',
                array ($team['id'])
            ));
        }

        return $repositories;
    }

    /**
     * Get an array of teams the user is in.
     *
     * @return array
     */
    private function getTeams()
    {
        try {
            return ResponseMediator::getContent($this->gitHubClient->getHttpClient()->get('user/teams'));
        } catch (RuntimeException $e) {
            // We don't have permission.
            return array();
        }
    }
} 
