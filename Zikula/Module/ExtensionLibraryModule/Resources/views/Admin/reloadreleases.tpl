{adminheader}
<h3>
    <span class="fa fa-gears"></span>&nbsp;{gt text="Reload all core releases"}
</h3>
<div class="alert alert-warning">
    {gt text='You are about to reload all core releases from GitHub. Are you sure you want to proceed?'}
</div>
<form class="form-horizontal" role="form" action="{route name='zikulaextensionlibrarymodule_admin_doreloadcorereleases'}" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
    <div>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text='Reload all core releases'}">
                    {gt text="Reload all core releases"}
                </button>
                <a class="btn btn-danger" href="{route name='zikulaextensionlibrarymodule_admin_index'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
