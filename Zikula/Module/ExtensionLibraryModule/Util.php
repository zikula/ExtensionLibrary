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

namespace Zikula\Module\ExtensionLibraryModule;

class Util {
    /**
     * Log a message to a file
     *
     * @param $msg
     */
    public static function log($msg)
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

    public static function setChosenCore($version)
    {
        \CookieUtil::setCookie('zikulaextensionslibrarymodule_chosenCore', $version, time() + 60*60*24, '/');
    }

    public static function getChosenCore()
    {
        return \CookieUtil::getCookie('zikulaextensionslibrarymodule_chosenCore', true, 'all');
    }
} 