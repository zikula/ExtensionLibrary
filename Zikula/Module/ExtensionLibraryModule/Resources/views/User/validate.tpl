{pageaddvar name='javascript' value=$moduleBundle->getRelativePath()|cat:'/Resources/public/js/Zikula.ExtensionLibrary.User.Validate.js'}
{include file='User/header.tpl'}
<div class="row">
    <h3>{gt text="Validate %s Content" tag1="<code>zikula.manifest.json</code>"}</h3>
</div>
<div id="validationResults" class="row"></div>
<div class="row">
<form id="manifestvalidation" role="form">
    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
    <div class="form-group">
        <label for="manifest">{gt text="Enter manifest content."}</label>
        <textarea id="manifest" class="form-control" style="height: 20em;"></textarea>
    </div>
    <button id="validateButton" class="btn btn-success">{gt text='Validate!'}</button>
</form>
</div>
{include file='User/footer.tpl'}