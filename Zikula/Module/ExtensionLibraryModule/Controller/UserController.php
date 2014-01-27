<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Module\ExtensionLibraryModule\Controller;

use SecurityUtil;
use ModUtil;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula\Core\Response\PlainResponse;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionVersionEntity;
use Zikula\Module\ExtensionLibraryModule\Entity\VendorEntity;
use Zikula\Module\ExtensionLibraryModule\Entity\ExtensionEntity;

/**
 * UI operations executable by general users.
 */
class UserController extends \Zikula_AbstractController
{
    /**
     * @Route("")
     * The default entry point.
     *
     * @return string
     */
    public function indexAction()
    {
        // Security check
        if (!SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            throw new AccessDeniedException();
        }

        return $this->response($this->view->fetch('User/view.tpl'));
    }

    /**
     * @Route("/postreceive-hook")
     */
    public function processInboundAction()
    {
        // log that the method was called
        $this->log('ExtensionLibraryModule::processInboundAction called.');

        $payload = $this->request->request->get('payload', null);

        // github is guaranteed to send via POST and param is 'payload'
        if (!isset($payload)) {
            $this->log('ExtensionLibraryModule::payload was null.');
            throw new NotFoundHttpException($this->__('Sorry! Page not found.'), null, 404);
        }

        // check to make sure IP address is from github
        if (!$this->ipCIDRCheck()) {
            $this->log('ExtensionLibraryModule::IP was invalid.');
            throw new NotFoundHttpException($this->__('Sorry! Page not found.'), null, 404);
        }

        // payload is valid
        $this->log($payload);
        try {
            $jsonPayload = json_decode($payload);
        } catch (Exception $e) {
            $this->log('ExtensionLibraryModule::unable to decode json payload.');
            throw new \InvalidArgumentException();
        }

        // check 'refs' for tags, if none, then return
        if (!strpos($jsonPayload->ref, 'tags')) {
            return new PlainResponse();
        }

        // check vendor exists, if not create new vendor
        $vendor = $this->entityManager
            ->getRepository('ZikulaExtensionLibraryModule:VendorEntity')
            ->findOneBy(array('owner' => $jsonPayload->repository->owner->name));
        if (!isset($vendor)) {
            // not found, create
            $vendor = new VendorEntity($jsonPayload->repository->owner->name);
            $this->entityManager->persist($vendor);
            $this->log(sprintf('Vendor (%s) created', $jsonPayload->repository->owner->name));
        } else {
            // found
            $this->log(sprintf('Vendor (%s) found', $jsonPayload->repository->owner->name));
        }

        // check extension exists, if not create new extension
        if ($vendor->hasExtensionById($jsonPayload->repository->id)) {
            $this->log(sprintf('Extension (%s) found', $jsonPayload->repository->id));
            $extension = $vendor->getExtensionById($jsonPayload->repository->id);
        } else {
            // not found, create new extension and assign to vendor
            $title = 'title'; // temp
            $type = 'm'; // temp
            $extension = new ExtensionEntity($vendor, (int)$jsonPayload->repository->id, $jsonPayload->repository->name, $title, $type);
            $vendor->addExtension($extension);
            $this->entityManager->persist($extension);
            $this->log(sprintf('Extension (%s) created', $jsonPayload->repository->id));
        }

        // compare version to newest available. If newer, add new version
        list(, , $semver) = explode('/', $jsonPayload->ref);
        $newestVersion = $extension->getNewestVersion();
        if (empty($newestVersion) || (version_compare($semver, $newestVersion->getSemver(), '>'))) {
            // add new version of extension
            $compatibility = '>=1.3.7'; // temp
            $licenses = '{}'; // temp
            $versionEntity = new ExtensionVersionEntity($extension, $semver, $compatibility, $licenses);
            $this->entityManager->persist($versionEntity);
            $extension->addVersion($versionEntity);
            $this->log(sprintf('Version %s added to extension %s', $semver, $jsonPayload->repository->id));
        } else {
            $this->log("The version was not added because it was the same or older than the current version.");
        }

        $this->entityManager->flush();

        return new PlainResponse();
    }

    /**
     * @Route("/test/hook/{type}", requirements={"id" = "\d+"})
     */
    public function testPostReceiveHookAction($type = 0)
    {
        $tagPayload = '{"ref":"refs/tags/0.0.6","after":"5a794a194bd1d7b52b04a9254421a1e2a207af7b","before":"0000000000000000000000000000000000000000","created":true,"deleted":false,"forced":true,"base_ref":"refs/heads/master","compare":"https://github.com/craigh/Nutin/compare/0.0.6","commits":[],"head_commit":{"id":"5a794a194bd1d7b52b04a9254421a1e2a207af7b","distinct":true,"message":"commit C","timestamp":"2014-01-25T13:59:41-08:00","url":"https://github.com/craigh/Nutin/commit/5a794a194bd1d7b52b04a9254421a1e2a207af7b","author":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"committer":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"added":[],"removed":[],"modified":["file1.txt"]},"repository":{"id":16236813,"name":"Nutin","url":"https://github.com/craigh/Nutin","description":"This is nutin","watchers":0,"stargazers":0,"forks":0,"fork":false,"size":0,"owner":{"name":"craigh","email":"craigh@mac.com"},"private":false,"open_issues":0,"has_issues":true,"has_downloads":true,"has_wiki":true,"created_at":1390673890,"pushed_at":1390687196,"master_branch":"master"},"pusher":{"name":"craigh","email":"craigh@mac.com"}}';
        $nonTagPayload = '{"ref":"refs/heads/master","after":"5a794a194bd1d7b52b04a9254421a1e2a207af7b","before":"ea5cbe141d9ad4eae9f6ceb5fbb1f0d4b666d289","created":false,"deleted":false,"forced":false,"compare":"https://github.com/craigh/Nutin/compare/ea5cbe141d9a...5a794a194bd1","commits":[{"id":"5a794a194bd1d7b52b04a9254421a1e2a207af7b","distinct":true,"message":"commit C","timestamp":"2014-01-25T13:59:41-08:00","url":"https://github.com/craigh/Nutin/commit/5a794a194bd1d7b52b04a9254421a1e2a207af7b","author":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"committer":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"added":[],"removed":[],"modified":["file1.txt"]}],"head_commit":{"id":"5a794a194bd1d7b52b04a9254421a1e2a207af7b","distinct":true,"message":"commit C","timestamp":"2014-01-25T13:59:41-08:00","url":"https://github.com/craigh/Nutin/commit/5a794a194bd1d7b52b04a9254421a1e2a207af7b","author":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"committer":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"added":[],"removed":[],"modified":["file1.txt"]},"repository":{"id":16236813,"name":"Nutin","url":"https://github.com/craigh/Nutin","description":"This is nutin","watchers":0,"stargazers":0,"forks":0,"fork":false,"size":0,"owner":{"name":"craigh","email":"craigh@mac.com"},"private":false,"open_issues":0,"has_issues":true,"has_downloads":true,"has_wiki":true,"created_at":1390673890,"pushed_at":1390687184,"master_branch":"master"},"pusher":{"name":"craigh","email":"craigh@mac.com"}}';
        $url = 'http://127.0.0.1/core.git/src/el/postreceive-hook';
        $payload = ($type == 1) ? $tagPayload : $nonTagPayload;
        $data = array('payload' => $payload);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        //execute post
        $result = curl_exec($ch); // boolean
        $result = $result ? 'true' : 'false';
        $this->log("The result of the curl_exec() was $result");

        return $this->response('');
    }

    /**
     * @Route("/test/getmanifest")
     */
    public function testGetManifestAction($owner = 'craigh', $repo = 'Nutin', $refs = 'refs/tags/0.0.8')
    {
        $content = $this->getManifestContent($owner, $repo, $refs);

        if (!empty($content)) {
            $this->log("The manifest was read and decoded.");
        }
        echo "<pre>";
        var_dump($content);

        return new PlainResponse();
    }

    /**
     * Determine if provided IP is in the given range of IP addresses
     * @see http://www.php.net/manual/en/ref.network.php
     *
     * @return bool
     */
    private function ipCIDRCheck() {
        $REMOTE_ADDR = $this->request->server->get("REMOTE_ADDR", null);
        $HTTP_X_FORWARDED_FOR = $this->request->server->get("HTTP_X_FORWARDED_FOR", null);
        $HTTP_CLIENT_IP = $this->request->server->get("HTTP_CLIENT_IP", null);
        if (isset($REMOTE_ADDR)) {
            $requestIP = $REMOTE_ADDR;
        } else if (isset($HTTP_X_FORWARDED_FOR)) {
            $requestIP = $HTTP_X_FORWARDED_FOR;
        } else if (isset($HTTP_CLIENT_IP)) {
            $requestIP = $HTTP_CLIENT_IP;
        } else {
            $requestIP = null;
        }

        // allow local use for testing
        if ($requestIP == $this->request->server->get('HTTP_HOST')) {
            return true;
        }
        // IP range taken from https://help.github.com/articles/what-ip-addresses-does-github-use-that-i-should-whitelist#service-hook-ip-addresses
        $CIDR = "192.30.252.0/22";

        // check current IP is in acceptable range
        list ($net, $mask) = explode("/", $CIDR);
        $ip_net = ip2long($net);
        $ip_mask = ~((1 << (32 - $mask)) - 1);
        $ip_ip = ip2long($requestIP);
        $ip_ip_net = $ip_ip & $ip_mask;

        return ($ip_ip_net == $ip_net);
    }

    /**
     * Log a message to a file
     *
     * @param $msg
     */
    private function log($msg)
    {
        // open file
        $fd = fopen('./app/logs/el.log', "a");
        // prepend date/time to message
        $str = "[" . date("Y/m/d h:i:s", time()) . "] " . $msg;
        // write string
        fwrite($fd, $str . "\n");
        // close file
        fclose($fd);
    }

    /**
     * load a manifest file
     *
     * @param $owner
     * @param $repo
     * @param $refs
     *
     * @return string the decoded content of the manifest
     */
    private function getManifestContent($owner, $repo, $refs)
    {
        $module = ModUtil::getModule($this->name);
        require_once $module->getPath() . '/vendor/autoload.php';

        $client = new \Github\Client();
        try {
            $file = $client->api('repo')->contents()->show($owner, $repo, 'zikula.manifest.json', $refs);
        } catch (Exception $e) {
            $this->log("Unable to fetch manifest file");
            throw new \InvalidArgumentException();
        }

        try {
            $content = json_decode(base64_decode($file["content"]));
        } catch (Exception $e) {
            $this->log(sprintf("Unable to decode manifest content (%s)", json_last_error_msg()));
            throw new \InvalidArgumentException();
        }

        return $content;
    }

}
