{if !isset($icon)}
    <img src="{modgetimage}" alt="" class="pull-left" />
{else}
    <img src="{$icon}" alt="" class="pull-left" />
{/if}
{zikulaextensionlibrarymodulecorefilter assign='coreVersion'}
{if !empty($coreVersion)}
    <p class="pull-right">
        {if $coreVersion !== 'no-filter'}
            {gt text='Only showing extensions compatible with'}&nbsp;<span class="label label-info">Zikula Core {$coreVersion}</span>
        {else}
            {gt text='Showing all extensions'}
        {/if}
        - <a href="{modurl modname='ZikulaExtensionLibraryModule' type='user' func='chooseCore'}">{gt text='Change'}</a>
    </p>
{/if}
<h1><i>{modgetinfo info='displayname'}</i>{if isset($title)} &ndash; {$title}{/if}</h1>
<hr />