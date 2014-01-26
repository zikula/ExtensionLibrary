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

/**
 * UI operations executable by general users.
 */

namespace Zikula\Module\ExtensionLibraryModule\Controller;

use SecurityUtil;
use LogUtil;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Zikula\Core\Response\PlainResponse;

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
        LogUtil::log('ExtensionLibraryModule::processInboundAction called.', \Monolog\Logger::INFO);

        $payload = $this->request->request->get('payload', null);

        // github is guaranteed to send via POST and param is 'payload'
        if (!isset($payload)) {
            LogUtil::log('ExtensionLibraryModule::payload was null.', \Monolog\Logger::ERROR);
            throw new NotFoundHttpException($this->__('Sorry! Page not found.'), null, 404);
        }

        // check to make sure IP address is from github
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
        if (!$this->ipCIDRCheck($requestIP)) {
            LogUtil::log('ExtensionLibraryModule::IP was invalid.', \Monolog\Logger::ERROR);
            throw new NotFoundHttpException($this->__('Sorry! Page not found.'), null, 404);
        }

        // payload is valid
        LogUtil::log($payload, \Monolog\Logger::INFO);
        try {
            $jsonPayload = json_decode($payload);
        } catch (Exception $e) {
            LogUtil::log('ExtensionLibraryModule::unable to decode json payload.', \Monolog\Logger::ERROR);
            return;
        }

        // check 'refs' for tags, if none, then return
        if (!strpos($jsonPayload->ref, 'tags')) {
            return;
        }

        $vendor = $jsonPayload->repository->owner->name;
        $extension = $jsonPayload->repository->name;
        list(, , $version) = explode('/', $jsonPayload->ref);
        LogUtil::log(sprintf('ExtensionLibraryModule: %s has update the extension %s to version %s.', $vendor, $extension, $version), \Monolog\Logger::INFO);
        return new PlainResponse();

        // if 'tags' then process new version to extension

        // check for vendor exists, if not create new

        // check for existing extension and either create or add new.

    }

    /**
     * @Route("/test")
     */
    public function testAction()
    {
        $payload = '{"ref":"refs/tags/0.0.6","after":"5a794a194bd1d7b52b04a9254421a1e2a207af7b","before":"0000000000000000000000000000000000000000","created":true,"deleted":false,"forced":true,"base_ref":"refs/heads/master","compare":"https://github.com/craigh/Nutin/compare/0.0.6","commits":[],"head_commit":{"id":"5a794a194bd1d7b52b04a9254421a1e2a207af7b","distinct":true,"message":"commit C","timestamp":"2014-01-25T13:59:41-08:00","url":"https://github.com/craigh/Nutin/commit/5a794a194bd1d7b52b04a9254421a1e2a207af7b","author":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"committer":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"added":[],"removed":[],"modified":["file1.txt"]},"repository":{"id":16236813,"name":"Nutin","url":"https://github.com/craigh/Nutin","description":"This is nutin","watchers":0,"stargazers":0,"forks":0,"fork":false,"size":0,"owner":{"name":"craigh","email":"craigh@mac.com"},"private":false,"open_issues":0,"has_issues":true,"has_downloads":true,"has_wiki":true,"created_at":1390673890,"pushed_at":1390687196,"master_branch":"master"},"pusher":{"name":"craigh","email":"craigh@mac.com"}}';
        $url = 'http://127.0.0.1/core.git/src/el/postreceive-hook';
        $data = array('payload' => $payload);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        //execute post
        $result = curl_exec($ch); // boolean
        return $this->response('');
    }

    /**
     * Determine if provided IP is in the given range of IP addresses
     * @see http://www.php.net/manual/en/ref.network.php
     *
     * @param $IP
     * @return bool
     */
    private function ipCIDRCheck($IP) {

        if (!isset($IP)) {
            return false;
        }
        // allow local use for testing
        if ($IP = $this->request->server->get('HTTP_HOST')) {
            return true;
        }
        // IP range taken from https://help.github.com/articles/what-ip-addresses-does-github-use-that-i-should-whitelist#service-hook-ip-addresses
        $CIDR = "192.30.252.0/22";

        list ($net, $mask) = explode("/", $CIDR);
        $ip_net = ip2long($net);
        $ip_mask = ~((1 << (32 - $mask)) - 1);
        $ip_ip = ip2long($IP);
        $ip_ip_net = $ip_ip & $ip_mask;
        return ($ip_ip_net == $ip_net);
    }

}
