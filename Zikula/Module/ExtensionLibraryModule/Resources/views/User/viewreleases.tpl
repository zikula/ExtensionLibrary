{include file='User/header.tpl'}
<h3>
    <span class="fa fa-th-list"></span>&nbsp;{gt text="Core releases"}
</h3>
{checkpermission component="ZikulaExtensionLibraryModule::" instance="::" level="ACCESS_MODERATE" assign="admin"}
<table class="table table-striped table-hover">
    <thead>
    <tr>
        {if $admin}<th>{gt text='Id'}</th>{/if}
        <th>{gt text='Status'}</th>
        <th>{gt text='Name'}</th>
        <th>{gt text='Version'}</th>
        {if $admin}<th>{gt text='Actions'}</th>{/if}
    </tr>
    </thead>
    <tbody>
    {php}
        $this->assign('outdated', \Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity::STATE_OUTDATED);
        $this->assign('supported', \Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity::STATE_SUPPORTED);
        $this->assign('prerelease', \Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity::STATE_PRERELEASE);
        $this->assign('development', \Zikula\Module\ExtensionLibraryModule\Entity\CoreReleaseEntity::STATE_DEVELOPMENT);
    {/php}
    {assign var='stateOld' value=-1}
    {foreach from=$releases item='release'}
        {if $stateOld != $release->getStatus() && $release->getStatus() == $outdated}
            <tr>
                <td colspan="{if $admin}5{else}3{/if}">
                    {gt text='The releases below are outdated and no longer receive bug fixes or maintenance. Please upgrade to supported versions.'}
                </td>
            </tr>
        {/if}
        {if $stateOld != $release->getStatus() && $release->getStatus() == $prerelease}
            <tr>
                <td colspan="{if $admin}5{else}3{/if}">
                    {gt text='Below you see prereleases. They are not yet released, but we invite you to test them and help fixing latest bugs. You\'ll find further information in the release description.'}
                </td>
            </tr>
        {/if}
        {if $stateOld != $release->getStatus() && $release->getStatus() == $development}
            <tr>
                <td colspan="{if $admin}5{else}3{/if}">
                    {gt text='Below you see the latest builds of the core\'s development version. Do NEVER use them on production sites. They may be broken and absolutely not working. Really.'}
                </td>
            </tr>
        {/if}
        <tr class="{if $release->getStatus() == $prerelease || $release->getStatus() == $development}danger{elseif $release->getStatus() == $supported}success{else}warning{/if}">
            {if $admin}<td>{$release->getId()}</td>{/if}
            <td>{$release->getStatus()|elReleaseStatusToText:'singular'|safetext}</td>
            <td>{$release->getNameI18n()|safetext}</td>
            <td>{$release->getSemver()|safetext}</td>
            {if $admin}
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
            {/if}
        </tr>
        {assign var='stateOld' value=$release->getStatus()}
    {foreachelse}
        <tr><td colspan="{if $admin}5{else}3{/if}">{gt text='No releases available'}</td></tr>
    {/foreach}
    </tbody>
</table>
{if $admin}
    <hr />
    <div class="alert alert-info">
        {gt text='The release names and descriptions can be made multilingual, if the correct format is used at GitHub:'}
    </div>
    <pre>...English release descripion...
    # LOCALE:LOCALISED RELEASE NAME
    ...LOCALISED RELEASE DESCRIPTION...
    # LOCALE:LOCALISED RELEASE NAME
    ...LOCALISED RELEASE DESCRIPTION...</pre>
{/if}

{include file='User/footer.tpl'}

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
