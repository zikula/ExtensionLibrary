<a class="btn btn-default" role="button" href="#" style="white-space: normal"  data-toggle="modal" data-target="#el-block-jenkins-build-modal-{$id}">
    <i class="fa fa-ban fa-2x pull-left"></i> {gt text='Download nightly'}<br />
    {foreach from=$developmentReleases item='developmentRelease' name='releases'}
        {$developmentRelease->getSemver()}
        {if !$smarty.foreach.releases.last}/{/if}
    {/foreach}
</a>
{include file='User/releasedownloadmodal.tpl' modalReleases=$developmentReleases id="el-block-jenkins-build-modal-`$id`"}
