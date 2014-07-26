{checkpermission component='ZikulaExtensionLibraryModule::' instance='::' level=ACCESS_ADMIN assign='admin'}
<div class="row">
    <div class="well well-sm text-center" style="margin-top: 4em;">
        <ul class="list-inline" style="margin-bottom:-.3em">
            <li><a href="{route name='zikulaextensionlibrarymodule_user_index'}">{gt text="Library Home"}</a>&nbsp;&nbsp;&nbsp;&bull;</i></li>
            <li><a href="{route name='zikulaextensionlibrarymodule_user_viewcorereleases'}">{gt text="Core Releases"}</a>&nbsp;&nbsp;&nbsp;&bull;</i></li>
            <li><a href="{route name='zikulaextensionlibrarymodule_user_displaydocindex'}">{gt text="Documentation"}</a>&nbsp;&nbsp;&nbsp;&bull;</li>
            <li><a href="{route name='zikulaextensionlibrarymodule_user_displaylog'}">{gt text="Log File"}</a>{if $admin}&nbsp;&nbsp;&nbsp;&bull;{/if}</li>
            {if $admin}
                <li><a href="{route name='zikulaextensionlibrarymodule_admin_index'}">{gt text="Administration"}</a></li>
            {/if}
        </ul>
    </div>
</div>
