{pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.ExtensionLibrary.User.Display.js'}
{include file='User/header.tpl' icon=$extension.icon}
{checkpermission component=$module|cat:"::" instance=".*" level="ACCESS_ADMIN" assign='isZikulaAdmin'}

<div class="row">
    <div class="col-md-8">
        <div style="min-height: 100px;">
            <img class="media-object img-thumbnail pull-left" style='margin: 0 1em 1em 0' src="{$extension.icon|safetext}" alt="" width="90" height="90" />
            <h3 style="margin-top: 0">{$extension.title|safetext}&nbsp;&nbsp;<small>{$extension.typeForDisplay}</small></h3>
            <ul class="list-unstyled">
                {if !empty($extension.url)}<li><i class="fa fa-external-link"></i> <a href="{$extension.url|safetext}">{gt text="Extension Website"}</a></li>{/if}
                {if !empty($extension.description)}<li>{$extension.description|safetext}</li>{/if}
            </ul>
        </div>
        <div>
            {notifydisplayhooks eventname='el.ui_hooks.extension.display_view' id=$extension.id}
        </div>
    </div>
    <div class="well well-sm col-md-4">
        <h3 style="margin-top: 0">{$extension.vendor.title|safetext}</h3>
        <div class="iconStack pull-left">
            <img class="media-object img-thumbnail" src="{$extension.vendor.logoUrl|safetext}" alt="" width="90" height="90" />
            {if !empty($extension.vendor.email)}
                <img src="http://www.gravatar.com/avatar/{$extension.vendor.email|md5}?d=identicon" alt="" class="img-thumbnail vendorIcon">
            {/if}
        </div>
        <ul class="list-unstyled">
            {if !empty($extension.vendor.url)}<li><i class="fa fa-external-link"></i> <a href="{$extension.vendor.url|safetext}">{gt text="Vendor Website"}</a></li>{/if}
            {if !empty($extension.vendor.email)}<li><i class="fa fa-external-link"></i> <a href="mailto:{$extension.vendor.email|safetext}">{gt text="Vendor Email"}</a></li>{/if}
        </ul>
    </div>
</div>
<br />

<ul class="nav nav-tabs row">
    <li class="active"><a href="#versions" data-toggle="tab"><i class="fa fa-bars"></i> {gt text="Available Versions"}</a></li>
    <li><a href="#community" data-toggle="tab"><i class="fa fa-comments"></i> {gt text="Community Feedback"}</a></li>
    {if $isZikulaAdmin || $isExtensionAdmin}
        <li><a href="#admin" data-toggle="tab"><i class="fa fa-wrench"></i> {gt text="Administrate"}</a></li>
    {else}
        <li><a href="{route name='zikulaextensionlibrarymodule_user_display' extension_slug=$extension.titleSlug authenticate=true}"><i class="fa fa-github"></i> {gt text="Login with GitHub to administrate"}</a></li>
    {/if}
</ul>

<div class="tab-content">
    <div class="tab-pane active row" id="versions">
        <div class="panel-group clearfix" id="accordion">
            {assign var='firstMatchingVersion' value=true}
            {foreach from=$extension.versions item="version" name="versionLoop"}
                <div class="panel {if $version->matchesCoreChosen()}{if $firstMatchingVersion}panel-primary{else}panel-default{/if}{else}panel-warning{/if}">
                    <div class="panel-heading">
                        <em class="pull-right">
                            <span class="label {if $version->matchesCoreChosen()}label-info{else}label-danger{/if} tooltips" title="{gt text='Zikula Core version compatibility'}">{$version.compatibility|safetext}</span>&nbsp;
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
                                                <li><a href='http://spdx.org/licenses/{$license|urlencode}#licenseText'>{$license|safetext}</a></li>
                                            {/foreach}
                                        </ul>
                                    </li>
                                    {if !empty($version.dependencies)}
                                        <li>{gt text="Dependencies"}
                                            <ul>
                                                {foreach from=$version.dependencies key="extensionDependency" item="versionDependency"}
                                                    <li>{if $extensionDependency=='zikula/core'}<mark><strong>{/if}{$extensionDependency|safetext} ({$versionDependency|safetext}){if $extensionDependency=='zikula/core'}</strong></mark>{/if}</li>
                                                {/foreach}
                                            </ul>
                                        </li>
                                    {/if}
                                </ul>
                                {$version.verifiedIcon}
                                {if isset($version.urls.download)}
                                    <a type="button" class="btn btn-success btn-lg" href="{$version.urls.download|safetext}"><i class="fa fa-cloud-download fa-lg"></i> Download</a>
                                {else}
                                    <a type="button" class="btn btn-success" href="{$version.urls.zipball_url|safetext}"><i class="fa fa-github fa-lg"></i> Download Zipball</a>
                                    <a type="button" class="btn btn-success" href="{$version.urls.tarball_url|safetext}"><i class="fa fa-github fa-lg"></i> Download Tarball</a>
                                {/if}
                            </div>
                            <div class="col-md-2 btn-group-vertical">
                                {if !empty($version.contributors)}<a href="" data-toggle="modal" data-people='{$version.encodedContributors|safetext}' data-target="#contributorsModal" type="button" class="btn btn-default btn-info btn-sm">{gt text="Contributors"}</a>{/if}
                                {if !empty($version.urls.version)}<a href="{$version.urls.version|safetext}" type="button" class="btn btn-default btn-info btn-sm">{gt text="Version Site"}</a>{/if}
                                {if !empty($version.urls.docs)}<a href="{$version.urls.docs|safetext}" type="button" class="btn btn-default btn-info btn-sm">{gt text="Docs"}</a>{/if}
                                {if !empty($version.urls.demo)}<a href="{$version.urls.demo|safetext}" type="button" class="btn btn-default btn-info btn-sm">{gt text="Demo"}</a>{/if}
                                {if !empty($version.urls.issues)}<a href="{$version.urls.issues|safetext}" type="button" class="btn btn-default btn-info btn-sm">{gt text="Issues"}</a>{/if}
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
    <div class="tab-pane row" id="community">
        <h3>{gt text="Community Feedback"}</h3>
        <div>
            {notifydisplayhooks eventname='el.ui_hooks.community.display_view' id=$extension.id}
        </div>
    </div>
    {if $isZikulaAdmin || $isExtensionAdmin}
        <div class="tab-pane row" id="admin">
            <h3>{gt text="Administration"}</h3>
            <form role="form">
                <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{gt text="Version"}</th>
                            {if $isZikulaAdmin}<th>{gt text="Verified"}</th>{/if}
                            <th>{gt text="Remove"}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$extension.versions item="version" name="versionLoop2"}
                        <tr id="{$version.semver}" class="{if $version.verified}success{else}warning{/if}">
                            <td>{$version.semver}</td>
                            {if $isZikulaAdmin}
                            <td>
                                <div class="verify-checkbox">
                                    <label><input data-version="{$version.semver}" data-extid="{$extension.id}" class="verify" type="checkbox" {if $version.verified}checked="checked"{/if}></label>
                                    <i class="fa fa-cog fa-spin text-danger" style="display: none;"></i>
                                </div>
                            </td>
                            {/if}
                            <td>
                                <a class='deleteversion' data-version="{$version.semver}" data-extid="{$extension.id}" data-title="{$extension.title|safetext}" href="" data-target="#deleteVersionModal" data-toggle="modal">
                                    <i class="fa fa-trash-o fa-lg text-danger"></i>
                                </a>
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
            </form>
        </div>
    {/if}
</div>


{include file='User/footer.tpl'}
<!-- Contributors Modal -->
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
<!-- Delete Version Modal -->
<div class="modal fade" id="deleteVersionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">{gt text="Delete Version"}</h4>
            </div>
            <div class="modal-body">
                <p class="alert alert-danger">
                    {gt text="Deleting a version of this extension will <em>permenently remove it from the database</em>. Once removed, it <em>cannot be replaced</em> unless it is the most recent version!"}
                </p>
                <h4>{gt text="Delete version %s of extension %s?" tag1="<strong id='version-tag'>1</strong>" tag2="<strong id='extension-title-tag'>name</strong>"}</h4>
                <h3 class="text-center text-danger"><strong class="text-uppercase">{gt text="This is not recommended!"}</strong></h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal"><i class='fa fa-times'></i> {gt text="Cancel"}</button>
                <button id='deleteVersionButton' data-version="" data-extid="" type="button" class="btn btn-danger"><i id="deleteVersionIcon" class='fa fa-trash-o'></i> {gt text="Delete version"}</button>
            </div>
        </div>
    </div>
</div>
