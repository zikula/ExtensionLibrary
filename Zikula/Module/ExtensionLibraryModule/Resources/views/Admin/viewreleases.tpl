{adminheader}
<h3>
    <span class="fa fa-th-list"></span>&nbsp;{gt text="View core releases"}
</h3>
<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th>{gt text='Id'}</th>
        <th>{gt text='Version'}</th>
        <th>{gt text='Name'}</th>
        <th>{gt text='Status'}</th>
        <th>{gt text='Actions'}</th>
    </tr>
    </thead>
    <tbody>
    {php}
        $this->assign('outdated', \Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity::STATE_OUTDATED);
        $this->assign('supported', \Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity::STATE_SUPPORTED);
    {/php}
    {foreach from=$releases item='release'}
        <tr class="{if $release->getStatus() == $outdated}danger{elseif $release->getStatus() == $supported}success{else}warning{/if}">
            <td>{$release->getId()}</td>
            <td>{$release->getSemver()|safetext}</td>
            <td>{$release->getNameI18n()|safetext}</td>
            <td>{$release->getStatus()|elReleaseStatusToText:'singular'|safetext}</td>
            <td class="text-right">
                <div class="hidden">{$release->getDescriptionI18n()}</div>
                <a href="#" title="{gt text='View release description'}" data-toggle="modal" data-target="#el-modal-release-description" onclick="jQuery('#el-modal-release-description .modal-body').html(jQuery(this).prev().html())">
                    <i class="fa fa-comments-o"></i>
                </a>
                {if $release->getStatus() == $outdated || $release->getStatus() == $supported}
                    <a href="{route name='zikulaextensionlibrarymodule_admin_togglereleasestatus' id=$release->getId()}" title="{if $release->getStatus() == $supported}{gt text='Mark release as outdated'}{else}{gt text='Mark release as supported'}{/if}">
                        {if $release->getStatus() == $supported}
                            <i class="fa fa-arrow-down"></i>
                        {else}
                            <i class="fa fa-arrow-up"></i>
                        {/if}
                    </a>
                {/if}
            </td>
        </tr>
    {foreachelse}
        <tr><td colspan="5">{gt text='No data available'}</td></tr>
    {/foreach}
    </tbody>
</table>

<hr />
<div class="alert alert-info">
    {gt text='The release names and descriptions can be made multilingual, if the correct format is used at GitHub:'}
</div>
<pre>...English release descripion...
# LOCALE:LOCALISED RELEASE NAME
...LOCALISED RELEASE DESCRIPTION...
# LOCALE:LOCALISED RELEASE NAME
...LOCALISED RELEASE DESCRIPTION...</pre>

{adminfooter}

<div class="modal fade" id="el-modal-release-description">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                <h4 class="modal-title">{gt text='Release description'}</h4>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{gt text='Close'}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
