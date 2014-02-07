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

/**
 * Class ImageManager
 *
 * This class will take a url for an image and attempt to validate that as an actual image in three ways:
 *     1. confirm the extension is .gif, .jpg, .jpeg or .png
 *     2. use exif_imagetype() to check the headers of the file
 *     3. check the size of the image (limits set in class)
 * The class copies the file to a local private directory as defined in a class constant
 * Images can then be retrieved using the class get() method
 *
 * security suggestions taken from http://blog.nic0.me/post/579191344/some-common-php-security-pitfalls
 *     and http://security.stackexchange.com/a/237
 */
class ImageManager {

    /**
     * the private directory all module images are stored in
     * include trailing slash
     */
    const STORAGE_PATH = "../extensionlibrary/images/";

    /**
     * the url of the image's initial location
     * @var string
     */
    private $url;

    /**
     * the local image filename
     * @var string
     */
    private $name = null;

    /**
     * maximum size of image
     * @var array
     */
    private $maxSize = array('height' => 120, 'width' => 120);

    /**
     * @param string $url location of the file
     */
    public function __construct($url)
    {
        if (self::checkStorageDir()) {
            $this->url = $url;
            if ($this->validateExtension($url)) {
                $this->name = uniqid();
            } else {
                Util::log("could not validate image extension.");
            }
        }
    }

    /**
     * copy image from the url to local STORAGE_PATH
     *
     * @return boolean
     */
    public function import()
    {
        if (!isset($this->name)) {
            Util::log("image name is not set. Aborting import.");
            return false;
        }
        // move the file to local directory
        $r = copy($this->url, self::STORAGE_PATH . $this->name);
        if ($r) {
            Util::log("file successfully copied to local directory.");
        } else {
            Util::log("could not find file from url.");
            return false;
        }
        // confirm image type
        $type = exif_imagetype(self::STORAGE_PATH . $this->name);
        if (!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
            $this->removeFile("improper imagetype upload attempted. file removed.");
            return false;
        } else {
            Util::log("valid imagetype.");
        }

        // confirm image size
        $imagesize = getimagesize(self::STORAGE_PATH . $this->name);
        if (!is_array($imagesize)) {
            $this->removeFile("unable to get image size. file removed.");
            return false;
        } else {
            if ($imagesize[0] > $this->maxSize['width'] || $imagesize[1] > $this->maxSize['height']) {
                $this->removeFile("image size exceeds allowed limits. file removed.");
                return false;
            } else {
                Util::log("valid image size");
            }
        }

        return true;
    }

    /**
     * get the images new filename (without path)
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * remove the file
     *
     * @param string $logtext
     */
    private function removeFile($logtext)
    {
        unlink(self::STORAGE_PATH . $this->name);
        unset($this->name);
        Util::log($logtext);
    }

    /**
     * confirm storage directory is available.
     *
     * @param bool $log Whether or not to log an error if the directory does not exist, default true.
     *
     * @return bool
     */
    public static function checkStorageDir($log = true)
    {
        if ($dh = @opendir(self::STORAGE_PATH)) {
            // errors suppressed: only need true/false (without triggering E_WARNING)
            closedir($dh);
            return true;
        } else {
            if ($log) {
                Util::log("unable to find storage directory! You must manually create the directory.");
            }
            return false;
        }
    }

    /**
     * determine if the url has an acceptable extension on the filename
     *
     * @param string $url
     * @return bool
     */
    private function validateExtension($url)
    {
        $parts = explode('/', $url);
        $filename = array_pop($parts);

        // Valid file extensions.
        $validExtensions = array('.jpg', '.jpeg', '.png', '.gif');

        // Get current file extension
        $extension = (strpos($filename, '.') !== false) ? strrchr($filename, '.') : '';

        if (in_array(strtolower($extension), $validExtensions, true)) {
            return true;
        } else {
            return false;
        }
    }
}