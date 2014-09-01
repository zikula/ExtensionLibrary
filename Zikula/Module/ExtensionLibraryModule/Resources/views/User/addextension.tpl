{include file='User/header.tpl'}
<h2>{gt text='Add your extension to the extension library (step 1 of 2)'}</h2>
<div class="alert alert-info">{gt text='This step is only required once per extension. It only works if your repository not yet contains a zikula.manifest.json or composer.json file.'}</div>

<form class="form-horizontal" role="form" method="post" action="{route name='zikulaextensionlibrarymodule_user_addextension'}">
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
