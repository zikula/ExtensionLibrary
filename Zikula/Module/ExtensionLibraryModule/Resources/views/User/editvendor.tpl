{include file='User/header.tpl' displayFilter=true}

{assign var='vendorId' value=null}
{if isset($vendor)}
    {assign var='vendorId' value=$vendor->getId()}
{/if}
<form class="form-horizontal" role="form" method="post" action="{route name='zikulaextensionlibrarymodule_user_editvendor' vendor=$vendorId}">
    <fieldset>
        {if isset($vendor)}
            <legend><i class='fa fa-institution'></i> {gt text='Edit vendor information for %s' tag1=$vendor->getGitHubName()|safetext}</legend>
            <div>
                <input type="hidden" name="vendor[id]" value="{$vendor->getId()|safetext}" />
            </div>
            <div class="form-group">
                <label for="el-edit-vendor-title" class="col-sm-2 control-label">{gt text='Display name'}</label>
                <div class="col-sm-10">
                    <input type="text" name="vendor[title]" value="{$vendor->getTitle()|safetext}" id="el-edit-vendor-title" placeholder="{gt text='e.g. Acme Corporation, Peter Smith'}" class="form-control">
                    <span class="help-block">{gt text='The display title of your vendor. This will be used for the page title and top-level heading on your vendor\'s page. Include spaces and mixed case as desired.'}</span>
                </div>
            </div>
            <div class="form-group">
                <label for="el-edit-vendor-url" class="col-sm-2 control-label">{gt text='Homepage'}</label>
                <div class="col-sm-10">
                    <input type="url" name="vendor[url]" value="{$vendor->getUrl()|safetext}" id="el-edit-vendor-url" class="form-control" placeholder="{gt text='e.g. http://example.com'}">
                    <span class="help-block">{gt text='The url to the homepage for your self, group or company. A Github page is suitable or any other page as you desire.'}</span>
                </div>
            </div>
            <div class="form-group">
                <label for="el-edit-vendor-email" class="col-sm-2 control-label">{gt text='Email'}</label>
                <div class="col-sm-10">
                    <input type="email" name="vendor[email]" value="{$vendor->getEmail()|safetext}" id="el-edit-vendor-email" class="form-control" placeholder="{gt text='e.g. example@zikula.org'}">
                </div>
            </div>
            <div class="form-group">
                <label for="el-edit-vendor-logo" class="col-sm-2 control-label">{gt text='Vendor Logo'}</label>
                <div class="col-sm-10">
                    <input type="url" name="vendor[logo]" value="{$vendor->getLogo()|safetext}" id="el-edit-vendor-logo" class="form-control" placeholder="{gt text='e.g. http://example.com/logo.png'}">
                    <span class="help-block">{gt text='The url to your vendor logo (different from extension logo/icon). Supported image types are `.jpg`, `.jpeg`, `.gif` and `.png`. Images MUST be less than 120px x 120px.'}</span>
                </div>
            </div>
        {else}
            <legend><i class='fa fa-institution'></i> {gt text='Edit vendor information'}</legend>
            <div class="form-group">
                <label for="el-edit-vendor" class="col-sm-2 control-label">{gt text='Choose Vendor'}</label>
                <div class="col-sm-10">
                    <select name="vendor[id]" id="el-edit-vendor" class="form-control">
                        {foreach from=$vendors key='id' item='vendor'}
                            <option value="{$id}">{if !empty($vendor.name)}{$vendor.name|safetext} ({$vendor.login|safetext}){else}{$vendor.login|safetext}{/if}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        {/if}
    </fieldset>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-success" {if !isset($vendors)}name="save"{/if}>{if !isset($vendors)}{gt text='Save'}{else}{gt text='Proceed'}{/if}</button>
            <a class="btn btn-default" href="{route name='zikulaextensionlibrarymodule_user_main'}">{gt text='Cancel'}</a>
        </div>
    </div>
</form>

{include file='User/footer.tpl'}
