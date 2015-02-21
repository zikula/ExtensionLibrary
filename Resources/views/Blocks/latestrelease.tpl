<a class="btn btn-success btn-lg{if isset($btnBlock) && $btnBlock} btn-block{/if}" role="button" href="#" style="white-space: normal" data-toggle="modal" data-target="#el-block-latest-release-modal-supported-{$id}">
    <i class="fa fa-cloud-download fa-3x pull-left"></i> {gt text='Download Zikula'}<br />{$supportedRelease->getSemver()}
</a>
{include file='User/releasedownloadmodal.tpl' modalRelease=$supportedRelease id="el-block-latest-release-modal-supported-`$id`"}
