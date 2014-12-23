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

class ComposerManager extends RemoteJsonManager {
    /**
     * Constructor
     *
     * @param string $owner
     * @param string $repo
     * @param string $ref
     * @param string $path
     */
    public function __construct($owner, $repo, $ref, $path)
    {
        $this->schema = 'schema.composer.json';
        parent::__construct($owner, $repo, $ref, $path);
    }
}