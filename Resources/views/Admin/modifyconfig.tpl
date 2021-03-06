{pageaddvar name='javascript' value='@ZikulaExtensionLibraryModule/Resources/public/js/Zikula.ExtensionLibrary.Admin.ModifyConfig.js'}
{adminheader}
    <h3>
        <span class="fa fa-wrench"></span>&nbsp;{gt text="Settings"}
    </h3>
    {if isset($storageDir)}
        <div class="alert alert-danger">
            {gt text='The image storage directory must be created at "%s" relative to the Zikula root.' tag1=$storageDir}
        </div>
    {/if}
    <form class="form-horizontal" id="el-modify-config-form" role="form" action="{route name='zikulaextensionlibrarymodule_admin_modifyconfig'}" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
        <div>
            <fieldset>
                <legend>{gt text='GitHub'}</legend>
                <div class="alert alert-info">
                    {gt text='Rate limit remaining for the next %s minutes: %s / %s' tag1=$rate.minutesUntilReset tag2=$rate.remaining tag3=$rate.limit}
                </div>
                {if $hasPushAccess}
                    <div class="alert alert-success">{gt text='Great! The GitHub client has push access to the core repository!'}</div>
                {else}
                    <div class="alert alert-warning">{gt text='The GitHub client does not have push access to the core repository. Auto-loading Jenkins Build Assets into GitHub Core releases has been disabled.'}</div>
                {/if}
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="settings_github_core_repo">{gt text="Core repository"}</label>
                    <div class="col-lg-9">
                        <input id="settings_github_core_repo" type="text" class="form-control" name="settings[github_core_repo]" value="{$settings.github_core_repo|default:''|safetext}" maxlength="100" />
                        <p class="help-block">{gt text='Fill in the name of the core repository. This should always be "zikula/core"'}</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="settings_github_token">{gt text="Access Token"}</label>
                    <div class="col-lg-9">
                        <input id="settings_github_token" type="password" class="form-control" name="settings[github_token]" value="{$settings.github_token|default:''|safetext}" maxlength="100" autocomplete="off" />
                        <p class="help-block">{gt text='Create a personal access token at %s to raise your api limits.' tag1='<a href="https://github.com/settings/applications">https://github.com/settings/applications</a>'}</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="settings_github_app_id">{gt text="Application Client ID"}</label>
                    <div class="col-lg-9">
                        <input id="settings_github_app_id" type="text" class="form-control" name="settings[github_app_id]" value="{$settings.github_app_id|default:''|safetext}" maxlength="100" />
                        <p class="help-block">{gt text='Create an application at %s to ease the process of adding a module to the Extension Library for new users.' tag1='<a href="https://github.com/settings/applications">https://github.com/settings/applications</a>'}</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="settings_github_app_secret">{gt text="Application Client Secret"}</label>
                    <div class="col-lg-9">
                        <input id="settings_github_app_secret" type="password" class="form-control" name="settings[github_app_secret]" value="{$settings.github_app_secret|default:''|safetext}" maxlength="100" autocomplete="off" />
                        <p class="help-block">{gt text='Create an application at %s to ease the process of adding a module to the Extension Library for new users.' tag1='<a href="https://github.com/settings/applications">https://github.com/settings/applications</a>'}</p>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>{gt text='User View'}</legend>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="settings_image_cache_time">{gt text="Time (in seconds) for images to be cached"}</label>
                    <div class="col-lg-9">
                        <input id="settings_image_cache_time" type="number" class="form-control" name="settings[image_cache_time]" value="{$settings.image_cache_time|default:0|safetext}" maxlength="100" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="settings_perpage">{gt text="Number of extensions per page"}</label>
                    <div class="col-lg-9">
                        <input id="settings_perpage" type="number" class="form-control" name="settings[perpage]" value="{$settings.perpage|default:45|safetext}" maxlength="100" />
                    </div>
                </div>
            </fieldset>
            <div class="form-group">
                <div class="col-lg-offset-3 col-lg-9">
                    <button class="btn btn-success" title="{gt text='Save'}">
                        {gt text="Save"}
                    </button>
                    <a class="btn btn-danger" href="{route name='zikulaextensionlibrarymodule_admin_index'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
                </div>
            </div>
        </div>
    </form>
{adminfooter}
