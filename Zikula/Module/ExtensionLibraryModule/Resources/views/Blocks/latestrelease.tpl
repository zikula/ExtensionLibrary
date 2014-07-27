{if isset($supportedRelease)}
    <a class="btn btn-success btn-lg" role="button" href="#" style="white-space: normal" data-toggle="modal" data-target="#el-block-latest-release-modal-supported-{$id}">
        <i class="fa fa-cloud-download fa-3x pull-left"></i> {gt text='Download Zikula'}<br />{$supportedRelease->getSemver()}
    </a>
    {include file='User/releasedownloadmodal.tpl' modalRelease=$supportedRelease id="el-block-latest-release-modal-supported-`$id`"}
{/if}
{if isset($preRelease)}
    <a class="btn btn-warning btn-lg" role="button" href="#" style="white-space: normal" data-toggle="modal" data-target="#el-block-latest-release-modal-prerelease-{$id}">
        <i class="fa fa-bug fa-3x pull-left"></i> {gt text='Help testing'}<br />{$preRelease->getSemver()}
    </a>
    {include file='User/releasedownloadmodal.tpl' modalRelease=$preRelease id="el-block-latest-release-modal-prerelease-`$id`"}
{/if}
