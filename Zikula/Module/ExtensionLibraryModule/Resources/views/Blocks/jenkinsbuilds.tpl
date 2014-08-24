<a class="btn btn-default{if isset($btnBlock) && $btnBlock} btn-block{/if}" role="button" href="#" style="white-space: normal"  data-toggle="modal" data-target="#el-block-jenkins-build-modal-{$id}">
    <i class="fa fa-warning fa-2x pull-left"></i> {gt text='Download development builds'}<br />
    <span style="white-space:nowrap; min-width:80px;">{foreach from=$developmentReleases item='developmentRelease' name='releases'}
        {$developmentRelease->getSemver()}
        {if !$smarty.foreach.releases.last}/{/if}
    {/foreach}</span>
</a>
{include file='User/releasedownloadmodal.tpl' modalReleases=$developmentReleases id="el-block-jenkins-build-modal-`$id`"}
