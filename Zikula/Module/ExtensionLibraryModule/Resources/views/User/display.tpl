{capture assign='title'}
    {$extension.title|safetext}&nbsp;&nbsp;<small>{$extension.type}</small>
{/capture}

{include file='User/header.tpl' title=$title icon=$extension.icon}
<div  class="pull-right">
    <h3>{$extension.vendor.title|safetext}</h3>
    <div style="min-height: 80px;">
        {if isset($extension.vendor.ownerEmail)}
        <img src="http://www.gravatar.com/avatar/{$extension.vendor.ownerEmail|md5}?d=identicon" alt="" class="img-thumbnail pull-left">
        {/if}
        <ul class="list-unstyled" style="padding-left: 100px">
            <li>{$extension.vendor.ownerName|default:''}</li>
            <li>{$extension.vendor.ownerEmail|default:''}</li>
            <li><i class="fa fa-external-link"></i> <a href="{$extension.vendor.ownerUrl|default:''}">{gt text="Vendor Website"}</a></li>
        </ul>
    </div>
</div>
<div>
    <ul class="list-unstyled">
        <li><i class="fa fa-external-link"></i> <a href="{$extension.url|default:''}">{gt text="Extension Website"}</a></li>
    </ul>
</div>
<div class="clear"></div>
<br />

<div class="panel-group" id="accordion">
{foreach from=$extension.versions item="version" name="versionLoop"}
    <div class="panel panel-default">
        <div class="panel-heading">
            <em class="pull-right">
                <span class="label label-info tooltips" title="{gt text='Zikula Core version compatibility'}">{$version.compatibility}</span>&nbsp;
                Released: {$version.created->format('j M Y')}
            </em>
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#version-{$version.id}">
                    <strong>Version: {$version.semver|safetext}</strong>
                </a>
            </h4>
        </div>
        <div id="version-{$version.id}" class="panel-collapse collapse{if $smarty.foreach.versionLoop.first} in{/if}">
            <div class="panel-body">
                <ul>
                    <li>Description: {$version.description|safetext}</li>
                </ul>
                {if isset($version.urls.download)}
                    <a type="button" class="btn btn-success" href="{$version.urls.download}">Download</a>
                {else}
                    <a type="button" class="btn btn-success" href="{$version.urls.zipball_url}">Download Zipball</a>
                    <a type="button" class="btn btn-success" href="{$version.urls.tarball_url}">Download Tarball</a>
                {/if}
            </div>
        </div>
    </div>
{/foreach}
</div>