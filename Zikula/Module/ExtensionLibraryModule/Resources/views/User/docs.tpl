{include file='User/header.tpl'}
<h3>{gt text="Documentation"}</h3>
<div>
    <ul class="list-unstyled">
        <li><a href="{modurl modname=$module type='user' func='displayDocFile' file='instructions'}">{gt text="Instructions"}</a></li>
        <li><a href="{modurl modname=$module type='user' func='displayDocFile' file='manifest'}">{gt text="Manifest"}</a></li>
        <li><a href="{modurl modname=$module type='user' func='displayDocFile' file='sample-manifest'}">{gt text="Sample Manifest file"}</a></li>
        <li><a href="{modurl modname=$module type='user' func='validateManifest'}">{gt text="Validate your Manifest file"}</a></li>
        <li><a href="{modurl modname=$module type='user' func='displayDocFile' file='composer'}">{gt text="Composer"}</a></li>
        <li><a href="{modurl modname=$module type='user' func='displayDocFile' file='sample-composer'}">{gt text="Sample Composer file"}</a></li>
    </ul>
</div>
{include file='User/footer.tpl'}