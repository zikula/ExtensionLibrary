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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zikula\Module\ExtensionLibraryModule\Exception\ClientException;

class PayloadManager {
    /**
     * @var string
     */
    private $payload;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var \stdClass|array
     */
    private $jsonPayload;

    /**
     * Constructor
     *
     * @param Request $request
     * @param bool    $payloadToArray Whether or not to json_decode into an array or object.
     *
     * @throws NotFoundHttpException
     */
    public function __construct(Request $request, $payloadToArray = false)
    {
        if ($request->headers->get('X-GitHub-Event') == 'ping') {
            throw new ClientException('Ping event received.', Response::HTTP_OK);
        }

        $contentType = $request->headers->get('content-type');
        if ($contentType == 'application/json') {
            $payload = $request->getContent();
        } else if ($contentType == 'application/x-www-form-urlencoded') {
            $payload = $request->request->get('payload', null);
        } else {
            throw new ClientException('"content-type" header must be either "application/json" or "application/x-www-form-urlencoded".', Response::HTTP_BAD_REQUEST);
        }

        if (empty($payload)) {
            throw new ClientException('Payload is missing!', Response::HTTP_BAD_REQUEST);
        }

        $this->payload = $payload;
        $this->request = $request;

        // check to make sure IP address is from github
        if (!$this->ipCIDRCheck()) {
            throw new ClientException('Request IP is invalid.', Response::HTTP_BAD_REQUEST);
        }

        // payload is valid
        try {
            $this->jsonPayload = json_decode($payload, $payloadToArray);
        } catch (\Exception $e) {
            throw new ClientException('Unable to decode json payload.', Response::HTTP_BAD_REQUEST);
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
        if ($requestIP == $this->request->server->get('SERVER_ADDR')) {
            return true;
        }
        // IP range taken from https://help.github.com/articles/what-ip-addresses-does-github-use-that-i-should-whitelist#service-hook-ip-addresses
        // @todo Fetch from GitHub using the "meta" api endpoint.
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
     * @return \stdClass|array
     */
    public function getJsonPayload()
    {
        return $this->jsonPayload;
    }

    /**
     * @return string
     */
    public function getRawPayload()
    {
        return $this->payload;
    }
} 
