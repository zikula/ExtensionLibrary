{elGetChosenCore assign='coreVersion'}
{if !empty($coreVersion)}
    <p class="pull-right">
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