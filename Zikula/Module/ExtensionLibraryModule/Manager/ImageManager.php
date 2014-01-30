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

class ImageManager {
    /**
     * @var array map IMAGETYPE_* constant to suffix
     */
    private $imageTypeMap = array(
        IMAGETYPE_GIF => '.gif',
        IMAGETYPE_JPEG => '.jpg',
        IMAGETYPE_PNG => '.png',
    );

    /**
     * the url of the image's initial location
     * @var string
     */
    private $url;

    /**
     * the local path/filename without suffix
     * @var string
     */
    private $destination;

    /**
     * @param string $url location of the file
     * @param string $type 'extension'|'vendor'
     * @param integer $id extension or vendor id
     */
    public function __construct($url, $type, $id)
    {
        $this->url = $url;
        $this->destination = "userdata/el/images/{$type}-{$id}"; // temp name without suffix
    }

    /**
     * copy and image from a url to a local directory
     */
    public function import()
    {
        // move the file to local directory
        // @todo IS THIS A SECURITY CONCERN? is there anyway to check the incoming file before copying?
        // The file is immediately deleted if the filetype doesn't match
        // but apparenlty it is possible to insert executable code inside a gif?
        // if so, then need a safe way to handle this.
        // maybe only let verified vendors upload their own images?
        $r = copy($this->url, $this->destination);
        if ($r) {
            Util::log("file successfully copied to local directory.");
        }
        // discover filetype
        $type = exif_imagetype($this->destination);
        if (in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
            $suffix = $this->imageTypeMap[$type];
            // add filetype suffix
            $r = rename($this->destination, $this->destination.$suffix);
            if ($r) {
                Util::log("file successfully renamed.");
            }
        } else {
            // filetype unsupported, delete the file
            unlink($this->destination);
        }
        Util::log("file transfer complete.");
    }

}