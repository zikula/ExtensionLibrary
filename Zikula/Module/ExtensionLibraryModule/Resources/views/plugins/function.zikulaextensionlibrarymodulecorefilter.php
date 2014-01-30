<?php

function smarty_function_zikulaextensionlibrarymodulecorefilter($params, &$view)
{
    $version = \Zikula\Module\ExtensionLibraryModule\Util::getChosenCore();

    if (isset($params['assign']) && !empty($params['assign'])) {
        $view->assign($params['assign'], $version);
    } else {
        return $version;
    }
}
