<a class="btn btn-warning btn-lg{if isset($btnBlock) && $btnBlock} btn-block{/if}" role="button" href="#" style="white-space: normal" data-toggle="modal" data-target="#el-block-latest-release-modal-prerelease-{$id}">
    <i class="fa fa-bug fa-3x pull-left"></i> {gt text='Help testing'}<br />{$preRelease->getSemver()}
</a>
{include file='User/releasedownloadmodal.tpl' modalRelease=$preRelease id="el-block-latest-release-modal-prerelease-`$id`"}