{include file='User/header.tpl' icon=$extension.icon}

<div class="row">
    <div class="col-md-8">
        <div style="min-height: 100px;">
            <img class="media-object img-thumbnail pull-left" style='margin: 0 1em 1em 0' src="{$extension.icon}" alt="" width="90" height="90" />
            <h3 style="margin-top: 0">{$extension.title|safetext}&nbsp;&nbsp;<small>{$extension.type}</small>&nbsp;<i class="fa fa-wrench fa-lg text-info"></i></h3>
            <ul class="list-unstyled">
                {if !empty($extension.url)}<li><i class="fa fa-external-link"></i> <a href="{$extension.url}">{gt text="Extension Website"}</a></li>{/if}
                {if !empty($extension.description)}<li>{$extension.description|safehtml}</li>{/if}
            </ul>
        </div>
        <div>
            {notifydisplayhooks eventname='el.ui_hooks.extension.display_view' id=$extension.id}
        </div>
    </div>
    <div class="well well-sm col-md-4">
        <h3 style="margin-top: 0">{$extension.vendor.title|safetext}&nbsp;<i class="fa fa-wrench fa-lg text-info"></i></h3>
        <ul class="list-unstyled">
            {if !empty($extension.vendor.url)}<li><i class="fa fa-external-link"></i> <a href="{$extension.vendor.url}">{gt text="Vendor Website"}</a></li>{/if}
        </ul>
        <div style="min-height: 90px;">
            <div class="iconStack pull-left">
                <img class="media-object img-thumbnail" src="{$extension.vendor.logo}" alt="" width="90" height="90" />
                {if isset($extension.vendor.ownerEmail)}
                    <img src="http://www.gravatar.com/avatar/{$extension.vendor.ownerEmail|md5}?d=identicon" alt="" class="img-thumbnail vendorIcon">
                {/if}
            </div>

            <ul class="list-unstyled" style="padding-left: 100px">
                {if !empty($extension.vendor.ownerName)}<li>{$extension.vendor.ownerName}</li>{/if}
                {if !empty($extension.vendor.ownerEmail)}<li>{$extension.vendor.ownerEmail}</li>{/if}
                {if !empty($extension.vendor.ownerUrl)}<li><i class="fa fa-external-link"></i> <a href="{$extension.vendor.ownerUrl|default:''}">{gt text="Owner Website"}</a></li>{/if}
            </ul>
        </div>
    </div>
</div>
<br />

<div class="row">
    <div class="panel-group clearfix" id="accordion">
    {assign var='firstMatchingVersion' value=true}
    {foreach from=$extension.versions item="version" name="versionLoop"}
        <div class="panel {if $version->matchesCoreChosen()}{if $firstMatchingVersion}panel-primary{else}panel-default{/if}{else}panel-warning{/if}">
            <div class="panel-heading">
                <em class="pull-right">
                    <span class="label {if $version->matchesCoreChosen()}label-info{else}label-danger{/if} tooltips" title="{gt text='Zikula Core version compatibility'}">{$version.compatibility}</span>&nbsp;
                    {gt text='Released: %s' tag1=$version.created->format('j M Y')}
                </em>
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#version-{$version.id}">
                        <strong>{gt text="Version"}: {$version.semver|safetext}</strong>
                    </a>
                </h4>
            </div>
            <div id="version-{$version.id}" class="panel-collapse collapse{if $version->matchesCoreChosen() && $firstMatchingVersion} in{assign var='firstMatchingVersion' value=false}{/if}">
                <div class="panel-body">
                    <div class="col-md-10">
                        <ul>
                            <li>{gt text="Description"}: {$version.description|safetext}</li>
                            <li>
                                {gt text="License" plural="Licenses" count=$version.licenses|count}:
                                <ul class="list-inline" style="display:inline;">
                                    {foreach from=$version.licenses item="license" name="licenseLoop"}
                                        <li><a href='http://spdx.org/licenses/{$license}#licenseText'>{$license|safetext}</a></li>
                                    {/foreach}
                                </ul>
                            </li>
                            {if !empty($version.dependencies)}
                                <li>{gt text="Dependencies"}
                                    <ul>
                                        {foreach from=$version.dependencies item="dependency"}
                                            <li>{$dependency.name} ({$dependency.version})</li>
                                        {/foreach}
                                    </ul>
                                </li>
                            {/if}
                        </ul>
                        {if isset($version.urls.download)}
                            <a type="button" class="btn btn-success btn-lg" href="{$version.urls.download}"><i class="fa fa-cloud-download fa-lg"></i> Download</a>
                        {else}
                            <a type="button" class="btn btn-success" href="{$version.urls.zipball_url}"><i class="fa fa-github fa-lg"></i> Download Zipball</a>
                            <a type="button" class="btn btn-success" href="{$version.urls.tarball_url}"><i class="fa fa-github fa-lg"></i> Download Tarball</a>
                        {/if}
                    </div>
                    <div class="col-md-2 btn-group-vertical">
                        {if !empty($version.contributors)}<a href="" data-toggle="modal" data-people='{$version.encodedContributors}' data-target="#contributorsModal" type="button" class="btn btn-default btn-info btn-sm">{gt text="Contributors"}</a>{/if}
                        {if !empty($version.urls.version)}<a href="{$version.urls.version}" type="button" class="btn btn-default btn-info btn-sm">{gt text="Version Site"}</a>{/if}
                        {if !empty($version.urls.docs)}<a href="{$version.urls.docs}" type="button" class="btn btn-default btn-info btn-sm">{gt text="Docs"}</a>{/if}
                        {if !empty($version.urls.demo)}<a href="{$version.urls.demo}" type="button" class="btn btn-default btn-info btn-sm">{gt text="Demo"}</a>{/if}
                        {if !empty($version.urls.issues)}<a href="{$version.urls.issues}" type="button" class="btn btn-default btn-info btn-sm">{gt text="Issues"}</a>{/if}
                    </div>
                </div>
            </div>
        </div>
    {/foreach}
    </div>
</div>
{include file='User/footer.tpl'}
<!-- Modal -->
<div class="modal fade" id="contributorsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">{gt text="Contributors"}</h4>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function() {
        jQuery('#contributorsModal').on('show.bs.modal', function (e) {
            var people = jQuery(e.relatedTarget).data('people');
            var content = "<ul>";
            jQuery.each(people, function(k, v) {
               content = content + "<li><strong>" + v.name + "</strong><ul>";
               if (v.role) content = content + "<li><em>" + v.role + "</em></li>";
               if (v.email) content = content + "<li>" + v.email + "</li>";
               if (v. homepage) content = content + "<li><a href='"+ v.homepage+"'>" + v.homepage + "</a></li>";
               content = content + "</ul></li>";
            });
            content = content + "</ul>";
            jQuery(this).find(".modal-body").html(content);
        });
    });
</script>
