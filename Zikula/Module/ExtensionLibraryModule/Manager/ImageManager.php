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
class ImageManager
{
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
     * Validation error discovered in the validation method
     * @var array
     */
    protected $validationErrors = array();

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
                $this->addValidationError("Could not validate image extension.");
            }
        } else {
            Util::log("could not validate storage directory.");
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
        if (ini_get('allow_url_fopen')) {
            $r = copy($this->url, self::STORAGE_PATH . $this->name);
        } else {
            $r = $this->curlDownload($this->url, self::STORAGE_PATH . $this->name);
        }

        if ($r) {
            Util::log("file successfully copied to local directory.");
        } else {
            $this->addValidationError("Could not find image file from url.");
            return false;
        }
        // confirm image type
        if (function_exists('exif_imagetype')) {
            $type = exif_imagetype(self::STORAGE_PATH . $this->name);
        } else {
            $type = getimagesize(self::STORAGE_PATH . $this->name);
            $type = isset($type[2]) ? $type[2] : false;
        }

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
        $this->addValidationError($logtext);
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
        if (is_dir(self::STORAGE_PATH) && is_writable(self::STORAGE_PATH)) {
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

    /**
     * Download a file from the given $url and save it to $path.
     *
     * @param string $url
     * @param string $path
     * @return bool|void
     *
     * Taken from here: http://www.w3bees.com/2013/09/download-file-from-remote-server-with.html
     */
    private function curlDownload($url, $path)
    {
        // Check if file exists without downloading it.
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        if ($headers['http_code'] !== 200) {
            return false;
        }

        // Now download the file.

        // open file to write
        $fp = fopen($path, 'w+');
        // start curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // set return transfer to false
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        // write data to local file
        curl_setopt($ch, CURLOPT_FILE, $fp);
        // execute curl
        curl_exec($ch);
        // close curl
        curl_close($ch);
        // close local file
        fclose($fp);

        if (filesize($path) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Add an error
     *
     * @param string $error
     */
    private function addValidationError($error)
    {
        $this->validationErrors[] = $error;
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}