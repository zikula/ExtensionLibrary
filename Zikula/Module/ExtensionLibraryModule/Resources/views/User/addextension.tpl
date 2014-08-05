{include file='User/header.tpl'}
<h2>{gt text='Add your extension to the extension library.'}</h2>
<div class="alert alert-info">{gt text='This step is only required once per extension. It only works if your repository not yet contains a zikula.manifest.json or composer.json file.'}</div>

<form class="form-horizontal" role="form" method="post" action="{route name='zikulaextensionlibrarymodule_user_addextension'}">
    <fieldset>
        <legend>{gt text='About You'}</legend>
        <div class="form-group">
            <label for="el-add-extension-vendor-name" class="col-sm-2 control-label">{gt text='Name'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <input required type="text" name="vendor[name]" value="{$vendor.login|safetext}" id="el-add-extension-vendor-name" placeholder="{gt text='e.g. cmfcmf, zikula, craigh, symfony'}" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-vendor-displayName" class="col-sm-2 control-label">{gt text='Display name'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <input type="text" name="vendor[displayName]" value="{$vendor.name|safetext}" id="el-add-extension-vendor-displayName" placeholder="{gt text='e.g. Acme Corporation, Peter Smith'}" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-vendor-url" class="col-sm-2 control-label">{gt text='Homepage'}</label>
            <div class="col-sm-10">
                <input type="url" name="vendor[url]" value="{$vendor.blog|safetext}" id="el-add-extension-vendor-url" class="form-control" placeholder="{gt text='e.g. http://example.com'}">
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-vendor-email" class="col-sm-2 control-label">{gt text='Email'}</label>
            <div class="col-sm-10">
                <input type="email" name="vendor[email]" value="{$vendor.email|safetext}" id="el-add-extension-vendor-email" class="form-control" placeholder="{gt text='e.g. example@zikula.org'}">
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-vendor-logo" class="col-sm-2 control-label">{gt text='Logo'}</label>
            <div class="col-sm-10">
                <input type="url" name="vendor[logo]" value="https://gravatar.com/avatar/{$vendor.gravatar_id|safetext}.png?s=120&d=404" id="el-add-extension-vendor-logo" class="form-control" placeholder="{gt text='e.g. http://example.com/logo.png'}">
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text='About Your Extension'}</legend>
        <div class="form-group">
            <label for="el-add-extension-repository" class="col-sm-2 control-label">{gt text='Repository'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <select required class="form-control" id="el-add-extension-repository" name="extension[repository]">
                    {foreach from=$repos item='repo'}
                        <option value="{$repo|safetext}">{$repo|safetext}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-success">{gt text='Proceed'}</button>
        </div>
    </div>
</form>

{include file='User/footer.tpl'}