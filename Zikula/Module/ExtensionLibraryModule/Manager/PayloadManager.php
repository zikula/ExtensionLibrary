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

namespace Zikula\Module\ExtensionLibraryModule\Manager;

use Zikula\Module\ExtensionLibraryModule\Util;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PayloadManager {
    /**
     * @var string
     */
    private $payload;
    /**
     * @var \Zikula_Request_Http
     */
    private $request;
    /**
     * @var json
     */
    private $jsonPayload;

    /**
     * Constructor
     *
     * @param \Zikula_Request_Http $request
     *
     * @throws NotFoundHttpException
     */
    public function __construct(\Zikula_Request_Http $request)
    {
        $payload = $request->request->get('payload', null);

        // github is guaranteed to send via POST and param is 'payload'
        if (!isset($payload)) {
            Util::log('ExtensionLibraryModule::payload was null.');
            throw new NotFoundHttpException('Sorry! Page not found.', null, 404);
        }

        $this->payload = $payload;
        $this->request = $request;

        // check to make sure IP address is from github
        if (!$this->ipCIDRCheck()) {
            Util::log('ExtensionLibraryModule::IP was invalid.');
            throw new NotFoundHttpException('Sorry! Page not found.', null, 404);
        }

        // payload is valid
        Util::log($payload);
        try {
            $this->jsonPayload = json_decode($payload);
        } catch (\Exception $e) {
            Util::log('ExtensionLibraryModule::unable to decode json payload.');
            throw new \InvalidArgumentException();
        }

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
     * @return json
     */
    public function getJsonPayload()
    {
        return $this->jsonPayload;
    }
} 