{include file='User/header.tpl'}
<h2>{gt text='Add your extension to the extension library (step 1 of 2)'}</h2>
<div class="alert alert-info">{gt text='This step is only required once per extension. It only works if your repository not yet contains a zikula.manifest.json or composer.json file.'}</div>

<form class="form-horizontal" role="form" method="post" action="{route name='zikulaextensionlibrarymodule_user_addextension'}">
    <fieldset>
        <legend><i class='fa fa-institution'></i> {gt text='Vendor information'}</legend>
        <input type="hidden" name="vendor[name]" value="{$vendor.login|safetext}" />
        <div class="form-group">
            <label for="el-add-extension-vendor-displayName" class="col-sm-2 control-label">{gt text='Display name'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <input required type="text" name="vendor[displayName]" value="{$vendor.name|safetext}" id="el-add-extension-vendor-displayName" placeholder="{gt text='e.g. Acme Corporation, Peter Smith'}" class="form-control">
                <span class="help-block">{gt text='The display title of your vendor. This will be used for the page title and top-level heading on your vendor\'s page. Include spaces and mixed case as desired.'}</span>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-vendor-url" class="col-sm-2 control-label">{gt text='Homepage'}</label>
            <div class="col-sm-10">
                <input type="url" name="vendor[url]" value="{$vendor.html_url|safetext}" id="el-add-extension-vendor-url" class="form-control" placeholder="{gt text='e.g. http://example.com'}">
                <span class="help-block">{gt text='The url to the homepage for your self, group or company. A Github page is suitable or any other page as you desire.'}</span>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-vendor-email" class="col-sm-2 control-label">{gt text='Email'}</label>
            <div class="col-sm-10">
                <input type="email" name="vendor[email]" value="{$vendor.email|safetext}" id="el-add-extension-vendor-email" class="form-control" placeholder="{gt text='e.g. example@zikula.org'}">
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-vendor-logo" class="col-sm-2 control-label">{gt text='Vendor Logo'}</label>
            <div class="col-sm-10">
                <input type="url" name="vendor[logo]" value="https://gravatar.com/avatar/{$vendor.gravatar_id|safetext}.png?s=120&d=404" id="el-add-extension-vendor-logo" class="form-control" placeholder="{gt text='e.g. http://example.com/logo.png'}">
                <span class="help-block">{gt text='The url to your vendor logo (different from extension logo/icon). Supported image types are `.jpg`, `.jpeg`, `.gif` and `.png`. Images MUST be less than 120px x 120px.'}</span>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend><i class='fa fa-github'></i> {gt text='GitHub Repo'}</legend>
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
