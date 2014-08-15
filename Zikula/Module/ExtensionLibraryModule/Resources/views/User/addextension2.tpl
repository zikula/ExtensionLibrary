{include file='User/header.tpl'}
<h2>{gt text='Add your extension to the extension library.'}</h2>
<div class="alert alert-info">{gt text='This step is only required once per extension.'}</div>

<form class="form-horizontal" role="form" method="post" action="{route name='zikulaextensionlibrarymodule_user_addextension'}">
    <fieldset>
        <input type="hidden" value="{$vendor|@json_encode|safetext}" name="_vendor" />
        <legend><i class='fa fa-cube'></i> {gt text='About your extension'}</legend>
        <div class="form-group">
            <label for="el-add-extension-repository" class="col-sm-2 control-label">{gt text='Repository'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <input class="form-control" type="text" name="extension[repository]" value="{$repo.full_name|safetext}" readonly>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-type" class="col-sm-2 control-label">{gt text='Type'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <select class="form-control" name="extension[type]" id="el-add-extension-repository">
                    <option value="zikula-module" selected="selected">{gt text='Module'}</option>
                    <option value="zikula-theme">{gt text='Theme'}</option>
                    <option value="zikula-plugin">{gt text='Plugin'}</option>
                </select>
            </div>
        </div>
        {*<div class="form-group">*}
            {*<label for="el-add-extension-extension-name" class="col-sm-2 control-label">{gt text='Name'}<span class="z-form-mandatory-flag">*</span></label>*}
            {*<div class="col-sm-10">*}
                {*<input required type="text" name="extension[name]" id="el-add-extension-extension-name" class="form-control" placeholder="{gt text='e.g. news'}" />*}
            {*</div>*}
        {*</div>*}
        <input type="hidden" value="{$repo.name}" name="extension[name]" />
        <div class="form-group">
            <label for="el-add-extension-extension-displayName" class="col-sm-2 control-label">{gt text='Display Name'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <input required type="text" name="extension[displayName]" id="el-add-extension-extension-displayName" class="form-control" placeholder="{gt text='e.g. Awesome News Publisher'}" />
                <span class="help-block">{gt text='The display title of your extension. This will be used for the page title and top-level heading on your extensions\'s page. Include spaces and mixed case as desired.'}</span>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-icon" class="col-sm-2 control-label">{gt text='Icon'}</label>
            <div class="col-sm-10">
                <input type="url" name="extension[icon]" id="el-add-extension-extension-icon" class="form-control" placeholder="{gt text='e.g. http://example.com/icon.png'}" />
                <span class="help-block">{gt text='The url to your extension icon (different from vendor logo). Supported image types are `.jpg`, `.jpeg`, `.gif` and `.png`. Images MUST be less than 120px x 120px.'}</span>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend><i class='fa fa-info-circle'></i> {gt text='About this version of your extension'}</legend>
        <div class="form-group">
            <label for="el-add-extension-extension-version" class="col-sm-2 control-label">{gt text='Version'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <input required type="text" name="extension[version]" id="el-add-extension-extension-version" class="form-control" placeholder="1.0.0" />
                <span class="help-block">{gt text='A valid version string as defined by %s' tag1="<a href='http://semver.org'>SemVer 2.0.0</a>"}</span>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-description" class="col-sm-2 control-label">{gt text='Description'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <textarea required name="extension[description]" id="el-add-extension-extension-description" class="form-control">{$repo.description|safetext}</textarea>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-license" class="col-sm-2 control-label">{gt text='License'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <input required type="text" name="extension[license]" id="el-add-extension-extension-license" class="form-control" />
                <span class="help-block">{gt text='License name (string) or an array of license names (array of strings) under which the extension is provided. You must use the standardized identifier acronym for the license as defined by %s' tag1="<a href='http://spdx.org/licenses/'>Software Package Data Exchange</a>"}</span>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-coreCompatability" class="col-sm-2 control-label">{gt text='Core compatability'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <input required type="text" name="extension[coreCompatability]" id="el-add-extension-extension-coreCompatability" class="form-control" value=">=1.3.5 <1.5"/>
                <span class="help-block">{gt text='A string defining Zikula Core version compatibility. Example: %s.' tag1='<code>>=1.3.5 <1.5</code>'}</span>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-url" class="col-sm-2 control-label">{gt text='Url'}</label>
            <div class="col-sm-10">
                <input type="url" name="extension[url]" id="el-add-extension-extension-url" class="form-control" placeholder="{gt text='e.g. http://example.com'}" />
                <span class="help-block">{gt text='The url to the specific version site.'}</span>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-keywords" class="col-sm-2 control-label">{gt text='Keywords'}</label>
            <div class="col-sm-10">
                <input type="url" name="extension[keywords]" id="el-add-extension-extension-keywords" class="form-control" placeholder="{gt text='e.g. module, news, example'}" />
            </div>
        </div>
    </fieldset>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-success">{gt text='Finish'}</button>
        </div>
    </div>
</form>

{include file='User/footer.tpl'}
