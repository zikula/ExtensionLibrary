{elGetChosenCore assign='coreVersion'}
{if !empty($coreVersion)}
    <p class="pull-right">
        <i class="fa fa-filter"></i>&nbsp;
        {if $coreVersion !== 'all'}
            {gt text='Only showing extensions compatible with'}&nbsp;<span class="label label-info">Zikula Core {$coreVersion}</span>
        {else}
            {gt text='Showing all extensions'}
        {/if}
        - <a href="{modurl modname='ZikulaExtensionLibraryModule' type='user' func='chooseCore'}">{gt text='Change'}</a>
    </p>
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