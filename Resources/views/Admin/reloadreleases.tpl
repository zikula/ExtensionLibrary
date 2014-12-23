{adminheader}
<h3>
    <span class="fa fa-gears"></span>&nbsp;{gt text="Reload all core releases"}
</h3>
<div class="alert alert-warning">
    {gt text='You are about to reload all core releases from GitHub as well as all development builds from jenkins. Are you sure you want to proceed?'}
</div>
<form class="form-horizontal" role="form" action="{route name='zikulaextensionlibrarymodule_admin_doreloadcorereleases'}" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
    <div>
        <div class="form-group">
            <div class="col-sm-3">
                <label for="el-createnews">{gt text='Create pending news articles for new releases'}</label>
            </div>
            <div class="col-sm-9">
                <input type="checkbox" id="el-createnews" name="createnews" value="1" checked />
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" title="{gt text='Reload all core releases'}">
                    {gt text="Reload all core releases"}
                </button>
                <a class="btn btn-danger" href="{route name='zikulaextensionlibrarymodule_admin_index'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
