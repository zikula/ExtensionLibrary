{foreach from=$extensions item="extension"}
    <h2>{$extension.vendor.title|safetext}</h2>
    <h3>{$extension.title|safetext}</h3>
    <h4>{gt text="Type"}: {$extension.type}</h4>
    <div class="panel-group" id="accordion">
    {foreach from=$extension.versions item="version" name="versionLoop"}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion" href="#version-{$version.id}">
                        Version: {$version.semver|safetext}
                    </a>
                </h4>
            </div>
            <div id="version-{$version.id}" class="panel-collapse collapse{if $smarty.foreach.versionLoop.first} in{/if}">
                <div class="panel-body">
                    <ul>
                        <li>Date: {$version.created->format('j M Y')}</li>
                        <li>Description: {$version.description|safetext}</li>
                    </ul>
                    <a type="button" class="btn btn-success" href="{$version.urls.download}">Download</a>
                </div>
            </div>
        </div>
    {/foreach}
    </div>
{/foreach}