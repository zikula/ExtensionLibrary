{include file='User/header.tpl'}
<h2>{gt text='Add your extension to the extension library.'}</h2>
<div class="alert alert-info">{gt text='This step is only required once per extension.'}</div>

<form class="form-horizontal" role="form" method="post" action="{route name='zikulaextensionlibrarymodule_user_addextension'}">
    <fieldset>
        <input type="hidden" value="{$vendor|@json_encode|safetext}" name="_vendor" />
        <legend>{gt text='About Your Extension'}</legend>
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
                    <option value="zikula-module">{gt text='Module'}</option>
                    <option value="zikula-theme">{gt text='Theme'}</option>
                    <option value="zikula-plugin">{gt text='Plugin'}</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-name" class="col-sm-2 control-label">{gt text='Name'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <input required type="text" name="extension[name]" id="el-add-extension-extension-name" class="form-control" placeholder="{gt text='e.g. news'}" />
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-displayName" class="col-sm-2 control-label">{gt text='Display Name'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <input required type="text" name="extension[displayName]" id="el-add-extension-extension-displayName" class="form-control" placeholder="{gt text='e.g. Awesome News Publisher'}" />
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
                <span class="help-block">{gt text='See %s.' tag1="<a href=\"https://getcomposer.org/doc/04-schema.md#license\">https://getcomposer.org/doc/04-schema.md#license</a>"}</span>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-coreCompatability" class="col-sm-2 control-label">{gt text='Core compatability'}<span class="z-form-mandatory-flag">*</span></label>
            <div class="col-sm-10">
                <input required type="text" name="extension[coreCompatability]" id="el-add-extension-extension-coreCompatability" class="form-control" value=">=1.3.5 <1.5"/>
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-url" class="col-sm-2 control-label">{gt text='Url'}</label>
            <div class="col-sm-10">
                <input type="url" name="extension[url]" id="el-add-extension-extension-url" class="form-control" placeholder="{gt text='e.g. http://example.com'}" />
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-keywords" class="col-sm-2 control-label">{gt text='Keywords'}</label>
            <div class="col-sm-10">
                <input type="url" name="extension[keywords]" id="el-add-extension-extension-keywords" class="form-control" placeholder="{gt text='e.g. module, news, example'}" />
            </div>
        </div>
        <div class="form-group">
            <label for="el-add-extension-extension-icon" class="col-sm-2 control-label">{gt text='Icon'}</label>
            <div class="col-sm-10">
                <input type="url" name="extension[icon]" id="el-add-extension-extension-icon" class="form-control" placeholder="{gt text='e.g. http://example.com/icon.png'}" />
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
