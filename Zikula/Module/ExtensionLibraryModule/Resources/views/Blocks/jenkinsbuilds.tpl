<a class="btn btn-default" role="button" href="#" style="white-space: normal"  data-toggle="modal" data-target="#el-block-jenkins-build-modal-{$id}">
    <i class="fa fa-ban fa-2x pull-left"></i> {gt text='Download nightly'}<br />
    {foreach from=$developmentReleases item='developmentRelease' name='releases'}
        {$developmentRelease->getSemver()}
        {if !$smarty.foreach.releases.last}/{/if}
    {/foreach}
</a>
{pageaddvarblock name='footer'}
<div class="modal fade" id="el-block-jenkins-build-modal-{$id}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                <h4 class="modal-title">{gt text='Download nightly builds'}</h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger"><strong>{gt text='Danger: Do not use on production sites! Download the latest release instead.'}</strong></div>
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    {foreach from=$developmentReleases item='developmentRelease' name='releases'}
                        <li{if $smarty.foreach.releases.first} class="active"{/if}>
                            <a href="#el-block-jenkins-build-modal-{$id}-tab-{$smarty.foreach.releases.iteration}" role="tab" data-toggle="tab">{$developmentRelease->getNameI18n()|safetext}</a>
                        </li>
                    {/foreach}
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    {foreach from=$developmentReleases item='developmentRelease' name='releases'}
                        <div class="tab-pane fade{if $smarty.foreach.releases.first} in active{/if}" id="el-block-jenkins-build-modal-{$id}-tab-{$smarty.foreach.releases.iteration}">
                            {$developmentRelease->getDescriptionI18n()}
                            <hr />
                            {foreach from=$developmentRelease.assets item='asset'}
                                <a href="{$asset.download_url}" class="btn btn-sm btn-success">{$asset.name}</a>
                            {foreachelse}
                                <div class="alert alert-warning">{gt text='Direct download links not available!'}</div>
                            {/foreach}
                        </div>
                    {/foreach}
                </div>
            </div>
            <!-- div class="modal-footer">
            </div -->
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
{/pageaddvarblock}
