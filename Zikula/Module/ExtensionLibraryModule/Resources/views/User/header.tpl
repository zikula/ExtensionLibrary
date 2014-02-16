<div class="row">
    {if $displayFilter|default:false}
        <div class="pull-right">
            <div class="btn-group">
                <button type="button" name="typeFilter" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-filter fa-lg"></i>&nbsp;{gt text="Type"}: {$typeFilter|default:"All"}&nbsp;<span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="">{gt text='All Types'}</a></li>
                    <li role="presentation"  class="divider"></li>
                    <li><a href="">{gt text='Modules'}</a></li>
                    <li><a href="">{gt text='Themes'}</a></li>
                    <li><a href="">{gt text='Plugins'}</a></li>
                </ul>
            </div>
            {elGetChosenCore assign='coreVersion'}
            <div class="btn-group">
                <button type="button" name="coreFilter" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-filter fa-lg"></i>&nbsp;{gt text="Core"}: {$coreVersion}&nbsp;<span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="">{gt text='All versions'}</a></li>
                    <li role="presentation"  class="divider"></li>
                    <li role="presentation" class="dropdown-header">Current versions</li>
                    {foreach from=$coreVersions.supported key="version" item="foo"}
                        <li><a href="">{$version}</a></li>
                    {/foreach}
                    <li role="presentation"  class="divider"></li>
                    <li role="presentation" class="dropdown-header">Outdated versions</li>
                    {foreach from=$coreVersions.outdated key="version" item="foo"}
                        <li><a href="">{$version}</a></li>
                    {/foreach}
                    <li role="presentation"  class="divider"></li>
                    <li role="presentation" class="dropdown-header">Developmental versions</li>
                    {foreach from=$coreVersions.dev key="version" item="foo"}
                        <li><a href="">{$version}</a></li>
                    {/foreach}
                </ul>
            </div>
        </div>
    {/if}
    <h1><img src="{modgetimage}" alt="" style="vertical-align: bottom; padding-right:.5em;" /><i>{modgetinfo info='displayname'}</i></h1>
    <hr />
    <ol class="breadcrumb">
        <li><a href="el/">{gt text='Library Home'}</a></li>
        {foreach from=$breadcrumbs item="breadcrumb" name="breadcrumbs"}
            {if $smarty.foreach.breadcrumbs.last}
                <li class="active">{$breadcrumb.title}</li>
            {else}
                <li><a href="{$breadcrumb.route}">{$breadcrumb.title}</a></li>
            {/if}
        {/foreach}
    </ol>
</div>