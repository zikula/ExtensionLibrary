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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Zikula\Core\Response\PlainResponse;
use Zikula\Module\ExtensionLibraryModule\Util;
use Zikula\Module\ExtensionLibraryModule\Manager\ManifestManager;
use Zikula\Module\ExtensionLibraryModule\Manager\ImageManager;
use Zikula\Module\ExtensionLibraryModule\Manager\ComposerManager;

/**
 * UI operations executable by general users.
 */
class TestController extends \Zikula_AbstractController
{
    /**
     * @Route("/test")
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

        return $this->response($this->view->fetch('Test/view.tpl'));
    }

    /**
     * test postreceive-hook
     *
     * @Route("/test/hook/{type}", requirements={"type" = "0|10|11"})
     */
    public function postReceiveHookAction($type = 0)
    {
        $tagPayload10 = '{"ref":"refs/tags/0.0.10","after":"bd5d8c7c43d86e4f72c520a3b794f52edb915cd4","before":"0000000000000000000000000000000000000000","created":true,"deleted":false,"forced":true,"base_ref":"refs/heads/master","compare":"https://github.com/craigh/Nutin/compare/0.0.10","commits":[],"head_commit":{"id":"bd5d8c7c43d86e4f72c520a3b794f52edb915cd4","distinct":true,"message":"10","timestamp":"2014-01-27T14:14:15-08:00","url":"https://github.com/craigh/Nutin/commit/bd5d8c7c43d86e4f72c520a3b794f52edb915cd4","author":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"committer":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"added":[],"removed":[],"modified":["file1.txt","zikula.manifest.json"]},"repository":{"id":16236813,"name":"Nutin","url":"https://github.com/craigh/Nutin","description":"This is nutin","watchers":0,"stargazers":0,"forks":0,"fork":false,"size":0,"owner":{"name":"craigh","email":"craigh@mac.com"},"private":false,"open_issues":0,"has_issues":true,"has_downloads":true,"has_wiki":true,"created_at":1390673890,"pushed_at":1390860910,"master_branch":"master"},"pusher":{"name":"craigh","email":"craigh@mac.com"}}';
        $tagPayload11 = '{"ref":"refs/tags/0.0.11","after":"b3f68de9b63c16127a14c3716c5b32636db6be76","before":"0000000000000000000000000000000000000000","created":true,"deleted":false,"forced":true,"base_ref":"refs/heads/master","compare":"https://github.com/craigh/Nutin/compare/0.0.11","commits":[],"head_commit":{"id":"b3f68de9b63c16127a14c3716c5b32636db6be76","distinct":true,"message":"11","timestamp":"2014-01-28T08:19:51-08:00","url":"https://github.com/craigh/Nutin/commit/b3f68de9b63c16127a14c3716c5b32636db6be76","author":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"committer":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"added":[],"removed":[],"modified":["file1.txt","zikula.manifest.json"]},"repository":{"id":16236813,"name":"Nutin","url":"https://github.com/craigh/Nutin","description":"This is nutin","watchers":0,"stargazers":0,"forks":0,"fork":false,"size":0,"owner":{"name":"craigh","email":"craigh@mac.com"},"private":false,"open_issues":0,"has_issues":true,"has_downloads":true,"has_wiki":true,"created_at":1390673890,"pushed_at":1390926013,"master_branch":"master"},"pusher":{"name":"craigh","email":"craigh@mac.com"}}';
        $nonTagPayload = '{"ref":"refs/heads/master","after":"5a794a194bd1d7b52b04a9254421a1e2a207af7b","before":"ea5cbe141d9ad4eae9f6ceb5fbb1f0d4b666d289","created":false,"deleted":false,"forced":false,"compare":"https://github.com/craigh/Nutin/compare/ea5cbe141d9a...5a794a194bd1","commits":[{"id":"5a794a194bd1d7b52b04a9254421a1e2a207af7b","distinct":true,"message":"commit C","timestamp":"2014-01-25T13:59:41-08:00","url":"https://github.com/craigh/Nutin/commit/5a794a194bd1d7b52b04a9254421a1e2a207af7b","author":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"committer":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"added":[],"removed":[],"modified":["file1.txt"]}],"head_commit":{"id":"5a794a194bd1d7b52b04a9254421a1e2a207af7b","distinct":true,"message":"commit C","timestamp":"2014-01-25T13:59:41-08:00","url":"https://github.com/craigh/Nutin/commit/5a794a194bd1d7b52b04a9254421a1e2a207af7b","author":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"committer":{"name":"Craig Heydenburg","email":"craigh@mac.com","username":"craigh"},"added":[],"removed":[],"modified":["file1.txt"]},"repository":{"id":16236813,"name":"Nutin","url":"https://github.com/craigh/Nutin","description":"This is nutin","watchers":0,"stargazers":0,"forks":0,"fork":false,"size":0,"owner":{"name":"craigh","email":"craigh@mac.com"},"private":false,"open_issues":0,"has_issues":true,"has_downloads":true,"has_wiki":true,"created_at":1390673890,"pushed_at":1390687184,"master_branch":"master"},"pusher":{"name":"craigh","email":"craigh@mac.com"}}';
        $url = $this->request->getUriForPath('/library/postreceive-hook');
        switch ($type) {
            case 10:
                $payload = $tagPayload10;
                break;
            case 11:
                $payload = $tagPayload11;
                break;
            default:
                $payload = $nonTagPayload;
        }
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
        Util::log("The result of the curl_exec() was $result");

        echo "test complete, check the el.log file for results";

        return $this->response('');
    }

    /**
     * test get manifest
     *
     * @Route("/test/getmanifest")
     */
    public function getManifestAction($owner = 'craigh', $repo = 'Nutin', $refs = 'refs/tags/0.0.9')
    {
        $manifestManager = new ManifestManager($owner, $repo, $refs);
        $content = $manifestManager->getContent();

        if (!empty($content)) {
            Util::log("The manifest was read and decoded.");
        }
        echo "<pre>";
        var_dump($content);

        return new PlainResponse();
    }

    /**
     * Test if image importing works
     *
     * @Route("/test/importimage/{type}", requirements={"type" = "valid|invalidUrl|extension|fake|size"})
     */
    public function importImage($type = 'valid') {
        $fileUrl = array();
        $fileUrl['valid'] = 'https://raw.github.com/zikula-modules/DizkusModule/master/Zikula/Module/DizkusModule/Resources/public/images/admin.png';
        $fileUrl['invalidUrl'] = 'https://raw.github.com/zikula-xxxxxxx/DizkusModule/master/Zikula/Module/DizkusModule/Resources/public/images/admin.png';
        $fileUrl['extension'] = 'https://raw2.github.com/zikula-modules/DizkusModule/master/README.md';
        $fileUrl['fake'] = 'https://raw2.github.com/craigh/Nutin/master/file1.png'; // file actually a txt file
        $fileUrl['size'] = 'https://raw2.github.com/zikula/core/1.3/src/themes/Zikula/Theme/Andreas08Theme/Resources/public/images/preview_large.png';

        $imageManager = new ImageManager($fileUrl[$type]);
        if ($imageManager->import()) {
            $this->view->assign('src', "el/getimage/" . $imageManager->getName());
        } else {
            $this->view->assign('src', "el/getimage");
        }

        return $this->response($this->view->fetch('Test/image.tpl'));
    }

    /**
     * @Route("/test/composer")
     *
     * Test if the schema properly validates the composer file
     * @return PlainResponse
     */
    public function validateComposer() {
        $composerManager = new ComposerManager('craigh', 'Nutin', 'refs/tags/0.0.24', 'composer.json');
        $data = $composerManager->getContent();
        if (!empty($data)) {
            echo "Composer content valid.";
        } else {
            echo "Could not get composer content";
        }
        echo "<pre>";
        var_dump($data);

        return new PlainResponse();
    }

}
