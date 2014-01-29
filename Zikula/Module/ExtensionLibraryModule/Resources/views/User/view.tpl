{foreach from=$extensions item="extension"}
    <h2>{$extension.vendor.title|safetext}</h2>
    <h3>{$extension.title|safetext}</h3>
    <h4>{gt text="Type"}: {$extension.type}</h4>
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
{/foreach}