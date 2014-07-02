{include file='User/header.tpl'}
<h3>{gt text="Documentation"}</h3>
<div>
    <ul class="list-unstyled">
        <li><a href="{route name='zikulaextensionlibrarymodule_user_displaydocfile' file='instructions'}">{gt text="Instructions"}</a></li>
        <li><a href="{route name='zikulaextensionlibrarymodule_user_displaydocfile' file='manifest'}">{gt text="Manifest"}</a></li>
        <li><a href="{route name='zikulaextensionlibrarymodule_user_displaydocfile' file='sample-manifest'}">{gt text="Sample Manifest file"}</a></li>
        <li><a href="{route name='zikulaextensionlibrarymodule_user_validatemanifest'}">{gt text="Validate your Manifest file"}</a></li>
        <li><a href="{route name='zikulaextensionlibrarymodule_user_displaydocfile' file='composer'}">{gt text="Composer"}</a></li>
        <li><a href="{route name='zikulaextensionlibrarymodule_user_displaydocfile' file='sample-composer'}">{gt text="Sample Composer file"}</a></li>
    </ul>
</div>
{include file='User/footer.tpl'}