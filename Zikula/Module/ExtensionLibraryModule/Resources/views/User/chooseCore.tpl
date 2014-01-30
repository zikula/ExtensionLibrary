{include file='User/header.tpl'}
<h2>{gt text='Choose the core to browse extensions for'}</h2>
<h3>{gt text='Show extensions for all core versions'}</h3>
<div class="row">
<div class="col-sm-2 text-center">
    <a class="btn btn-default" href="el/choose-your-core/no-filter">
        {img src='logo32.png'}
        <strong>{gt text='All versions'}</strong>
    </a>
</div>
</div>
<h3>{gt text='Current version'}</h3>
<div class="row">
    {foreach from=$coreVersions.supported item='versionData' key='version'}
        <div class="col-sm-2 text-center">
            <a class="btn btn-success" href="el/choose-your-core/{$version}">
                {img src='logo32.png'}
                <strong>{$version}</strong>
            </a>
        </div>
    {/foreach}
</div>
<h3>{gt text='Outdated versions'}</h3>
<div class="row">
    {foreach from=$coreVersions.outdated item='versionData' key='version'}
    <div class="col-sm-2 text-center">
        <a class="btn btn-info" href="el/choose-your-core/{$version}">
            {img src='logo32.png'}
            <strong>{$version}</strong>
        </a>
    </div>
    {/foreach}
</div>
<h3>{gt text='Experimental development versions'}</h3>
<div class="row">
    {foreach from=$coreVersions.dev item='versionData' key='version'}
    <div class="col-sm-2 text-center">
        <a class="btn btn-default" href="el/choose-your-core/{$version}">
            {img src='logo32.png'}
            <strong class="text-muted">{$version}</strong>
        </a>
    </div>
    {/foreach}
</div>