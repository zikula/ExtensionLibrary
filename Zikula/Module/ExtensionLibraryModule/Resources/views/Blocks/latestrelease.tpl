<script type="text/javascript">
    (function($) {
        $(function(){

        });
    })(jQuery);
</script>
<a class="btn btn-success btn-lg" role="button" href="http://go.zikula.org/download_latest" style="white-space: normal" data-toggle="modal" data-target="#el-block-latest-release-modal-{$id}">
    <i class="fa fa-cloud-download fa-3x pull-left"></i> {gt text='Download Zikula'} {$release->getSemver()}
</a>
<div class="modal fade" id="el-block-latest-release-modal-{$id}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                <h4 class="modal-title">{$release->getName()|safetext}</h4>
            </div>
            <div class="modal-body">
                {$release->getDescription()}
            </div>
            <div class="modal-footer">
                {foreach from=$release.assets item='asset'}
                    <a href="{$asset.download_url}" class="btn btn-success" data-dismiss="modal">{$asset.name}</a>
                {foreachelse}
                    <div class="alert alert-warning">{gt text='Direct download link not yet available!'}</div>
                {/foreach}
                <!-- button type="button" class="btn btn-default" data-dismiss="modal">{gt text='Close'}</button -->
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
