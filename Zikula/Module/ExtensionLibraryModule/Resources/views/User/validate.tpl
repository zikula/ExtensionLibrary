{pageaddvar name='javascript' value='@ZikulaExtensionLibraryModule/Resources/public/js/Zikula.ExtensionLibrary.User.Validate.js'}
{include file='User/header.tpl'}
<div class="row">
    <h3>{gt text="Validate %s Content" tag1="<code id='json-type'>zikula.manifest.json</code>"}</h3>
</div>
<div id="validationResults" class="row"></div>
<div class="row">
<form id="jsonvalidation" role="form">
    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
    <div class="form-group">
        <label for="json">{gt text="Enter Json content."}</label>
        <textarea id="json" class="form-control" style="height: 20em;"></textarea>
    </div>
    <div class="form-group">
        <select id='schema' class="form-control">
            <option value='schema.manifest.json' selected="selected">zikula.manifest.json</option>
            <option value='schema.composer.json'>composer.json</option>
        </select>
    </div>
    <button id="validateButton" class="btn btn-success">{gt text='Validate!'}</button>
</form>
</div>
{include file='User/footer.tpl'}
