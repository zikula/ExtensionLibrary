{pageaddvarblock name='footer'}
{if isset($modalRelease)}
    <div class="modal fade" id="{$id}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                    <h4 class="modal-title"><strong>{$modalRelease->getNameI18n()|safetext}</strong></h4>
                </div>
                <div class="modal-body">
                    {$modalRelease->getState()|elReleaseStateToAlert}
                    {$modalRelease->getDescriptionI18n()}
                </div>
                <div class="modal-footer">
                    {foreach from=$modalRelease.assets item='asset'}
                        <a href="{$asset.download_url}" class="btn btn-sm btn-success">{$asset.name}</a>
                    {foreachelse}
                        <div class="alert alert-warning">{gt text='Direct download links not yet available!'}</div>
                    {/foreach}
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
{elseif isset($modalReleases)}
    <div class="modal fade" id="{$id}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                    <h4 class="modal-title">{gt text='Download the Zikula Core'}</h4>
                </div>
                <div class="modal-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        {foreach from=$modalReleases item='release' name='releases'}
                            <li{if $smarty.foreach.releases.first} class="active"{/if}>
                                <a href="#{$id}-tab-{$smarty.foreach.releases.iteration}" role="tab" data-toggle="tab">{$release->getNameI18n()|safetext}</a>
                            </li>
                        {/foreach}
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        {foreach from=$modalReleases item='release' name='releases'}
                            <div class="tab-pane fade{if $smarty.foreach.releases.first} in active{/if}" id="{$id}-tab-{$smarty.foreach.releases.iteration}">
                                {$release->getState()|elReleaseStateToAlert}
                                {$release->getDescriptionI18n()}
                                <hr />
                                <div class="pull-right">
                                    {foreach from=$release.assets item='asset'}
                                        <a href="{$asset.download_url}" class="btn btn-sm btn-success">{$asset.name}</a>
                                    {foreachelse}
                                        <div class="alert alert-warning">{gt text='Direct download links not yet available!'}</div>
                                    {/foreach}
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        {/foreach}
                    </div>
                </div>
                <!-- div class="modal-footer">
                </div -->
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
{/if}
{/pageaddvarblock}
