{include file='User/header.tpl'}
<div class="row">
    {foreach from=$extensions item="extension" name='loop'}
        {if ($smarty.foreach.loop.iteration - 1) % 3 == 0 && $smarty.foreach.loop.iteration != 1}
        </div>
        <hr />
        <div class="row">
        {/if}
        <div class="col-sm-4">
            <div class="media extension-display" data-content="{$extension.newestVersion.description|safehtml}">
                <a class="pull-left" href="{modurl modname='ZikulaExtensionLibraryModule' type='user' func='display' id=$extension.id}">
                    <div class="iconStack">
                        <img class="media-object img-thumbnail" src="{$extension.icon}" alt="" width="90" height="90" />
                        <img class="img-thumbnail vendorIcon" src="{$extension.vendor.logo}" alt="">
                    </div>
                </a>
                <div class="media-body">
                    <h4 class="media-heading"><a href="{modurl modname='ZikulaExtensionLibraryModule' type='user' func='display' id=$extension.id}">{$extension.title|safetext}</a></h4>
                    <em class="text-muted">{$extension.type}</em>
                    <ul class="list-unstyled">
                        <li>{$extension.vendor.ownerName|default:''}</li>
                        <li><i class="fa fa-external-link"></i> <a href="{$extension.vendor.ownerUrl|default:''}">{gt text="Vendor Website"}</a></li>
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