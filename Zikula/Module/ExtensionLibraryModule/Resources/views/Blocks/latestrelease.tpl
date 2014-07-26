{if isset($supportedRelease)}
    <a class="btn btn-success btn-lg" role="button" href="#" style="white-space: normal" data-toggle="modal" data-target="#el-block-latest-release-modal-{$id}">
        <i class="fa fa-cloud-download fa-3x pull-left"></i> {gt text='Download Zikula'}<br />{$supportedRelease->getSemver()}
    </a>
{/if}
{if isset($preRelease)}
    <a class="btn btn-warning" role="button" href="#" style="white-space: normal" data-toggle="modal" data-target="#el-block-latest-prerelease-modal-{$id}">
        <i class="fa fa-bug fa-2x pull-left"></i> {gt text='Help testing'}<br />{$preRelease->getSemver()}
    </a>
{/if}
{if isset($developmentRelease)}
    <a class="btn btn-default" role="button" href="#" style="white-space: normal" data-toggle="modal" data-target="#el-block-latest-developmentrelease-modal-{$id}">
        <i class="fa fa-ban fa-2x pull-left"></i> {gt text='Download nightly'}<br />{$developmentRelease->getSemver()}
    </a>
{/if}
{pageaddvarblock name='footer'}
    {if isset($supportedRelease)}
    <div class="modal fade" id="el-block-latest-release-modal-{$id}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                    <h4 class="modal-title"><strong>{$supportedRelease->getNameI18n()|safetext}</strong></h4>
                </div>
                <div class="modal-body">
                    {$supportedRelease->getDescriptionI18n()}
                </div>
                <div class="modal-footer">
                    {foreach from=$supportedRelease.assets item='asset'}
                        <a href="{$asset.download_url}" class="btn btn-success">{$asset.name}</a>
                        {foreachelse}
                        <div class="alert alert-warning">{gt text='Direct download links not yet available!'}</div>
                    {/foreach}
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    {/if}
    {if isset($preRelease)}
        <div class="modal fade" id="el-block-latest-prerelease-modal-{$id}">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                        <h4 class="modal-title">{$preRelease->getNameI18n()|safetext}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger"><strong>{gt text='Danger: Do not use on production sites! Download the latest release instead.'}</strong></div>
                        {$preRelease->getDescriptionI18n()}
                    </div>
                    <div class="modal-footer">
                        {foreach from=$preRelease.assets item='asset'}
                            <a href="{$asset.download_url}" class="btn btn-success">{$asset.name}</a>
                            {foreachelse}
                            <div class="alert alert-warning">{gt text='Direct download links not yet available!'}</div>
                        {/foreach}
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    {/if}
    {if isset($developmentRelease)}
        <div class="modal fade" id="el-block-latest-developmentrelease-modal-{$id}">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                        <h4 class="modal-title">{$developmentRelease->getNameI18n()|safetext}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger"><strong>{gt text='Danger: Do not use on production sites! Download the latest release instead.'}</strong></div>
                        {$developmentRelease->getDescriptionI18n()}
                    </div>
                    <div class="modal-footer">
                        {foreach from=$developmentRelease.assets item='asset'}
                            <a href="{$asset.download_url}" class="btn btn-success">{$asset.name}</a>
                            {foreachelse}
                            <div class="alert alert-warning">{gt text='Direct download links not yet available!'}</div>
                        {/foreach}
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    {/if}
{/pageaddvarblock}
