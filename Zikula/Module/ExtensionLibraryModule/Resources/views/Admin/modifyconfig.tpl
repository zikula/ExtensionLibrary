{adminheader}
    <div class="alert alert-info">
        {gt text='Rate limit remaining for the next %s minutes: %s / %s' tag1=$rate.minutesUntilReset tag2=$rate.remaining tag3=$rate.limit}
    </div>
    <form class="form-horizontal" role="form" action="{modurl modname='ZikulaExtensionLibraryModule' type='admin' func='modifyConfig'}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <fieldset>
                <legend>{gt text='GitHub authentication'}</legend>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="settings_github_token">{gt text="GitHub Access Token"}</label>
                    <div class="col-lg-9">
                        <input id="settings_github_token" type="password" class="form-control" name="settings[github_token]" value="{$settings.github_token|default:''|safetext}" maxlength="100" />
                        <p class="help-block">{gt text='Create a personal access token at %s to raise your api limits.' tag1='<a href="https://github.com/settings/applications">https://github.com/settings/applications</a>'}</p>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>{gt text='Image caching'}</legend>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="settings_image_cache_time">{gt text="Time (in seconds) for images to be cached"}</label>
                    <div class="col-lg-9">
                        <input id="settings_image_cache_time" type="number" class="form-control" name="settings[image_cache_time]" value="{$settings.image_cache_time|default:0|safetext}" maxlength="100" />
                    </div>
                </div>
            </fieldset>
            <div class="form-group">
                <div class="col-lg-offset-3 col-lg-9">
                    <button class="btn btn-success" title="{gt text='Save'}">
                        {gt text="Save"}
                    </button>
                    <a class="btn btn-danger" href="{modurl modname='ZikulaExtensionLibraryModule' type='admin' func='index'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
                </div>
            </div>
        </div>
    </form>
{adminfooter}