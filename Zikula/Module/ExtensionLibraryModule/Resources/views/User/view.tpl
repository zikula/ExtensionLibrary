{include file='User/header.tpl' displayFilter=true}
<div class="row">
    <div class="col-lg-4 col-md-6 col-xs-12">
        <a href="{route name='zikulaextensionlibrarymodule_user_addextension'}" class="btn btn-success btn-lg btn-block" style="height:90px;">
            <i class="fa fa-github fa-3x pull-left"></i>
            <span style='font-size:1.3em'>{gt text='Add your own<br />extension!'}</span>
        </a>
    </div>
    {foreach from=$extensions item="extension" name='loop'}
        {if ($smarty.foreach.loop.iteration) % 3 == 0}
            <div class="col-xs-12 visible-lg-block" style="margin-bottom:30px"></div>
        {/if}
        {if ($smarty.foreach.loop.iteration) % 2 == 0}
            <div class="col-xs-12 visible-md-block" style="margin-bottom:30px"></div>
        {/if}        
        <div class="col-xs-12 visible-xs-block visible-sm-block" style="margin-bottom:30px"></div>
        <div class="col-lg-4 col-md-6 col-xs-12">
            <div class="media extension-display" data-content="[version {$extension.newestVersion.semver}] {$extension.description|safehtml}">
                <a class="pull-left" href="{route name='zikulaextensionlibrarymodule_user_display' extension_slug=$extension.titleSlug}">
                    <div class="iconStack">
                        <img class="media-object img-thumbnail" src="{$extension.icon}" alt="" width="90" height="90" />
                        <img class="img-thumbnail vendorIcon" src="{$extension.vendor.logo}" alt="">
                    </div>
                </a>
                <div class="media-body">
                    <h4 class="media-heading"><a href="{route name='zikulaextensionlibrarymodule_user_display' extension_slug=$extension.titleSlug}">{$extension.title|safetext}</a></h4>
                    <em class="text-muted">{$extension.typeForDisplay}</em>
                    <ul class="list-unstyled">
                        {if !empty($extension.vendor.title)}<li>{$extension.vendor.title|safetext}</li>{/if}
                        {if !empty($extension.vendor.url)}<li><i class="fa fa-external-link"></i> <a href="{$extension.vendor.url}">{gt text="Vendor Website"}</a></li>{/if}
                    </ul>
                </div>
            </div>
        </div>
    {/foreach}
</div>
{include file='User/footer.tpl'}
<script>
    jQuery(document).ready(function() {
        jQuery(".extension-display").popover({container: "body", trigger: "hover", placement: "top"});
    });
</script>
