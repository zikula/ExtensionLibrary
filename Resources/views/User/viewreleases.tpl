{include file='User/header.tpl'}
{checkpermission component="ZikulaExtensionLibraryModule::" instance="::" level="ACCESS_MODERATE" assign="admin"}
<h3>
    <span class="fa fa-th-list"></span>&nbsp;{gt text="Core releases"}
</h3>
<table class="table table-striped table-hover">
    <thead>
    <tr>
        {if $admin}<th>{gt text='Id'}</th>{/if}
        <th>{gt text='Version'}</th>
        <th>{gt text='Name'}</th>
        <th>{gt text='State'}</th>
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
    {foreach from=$releases item='release' name='releases'}
        {if $stateOld != $release->getState() && $release->getState() == $outdated}
            <tr>
                <td colspan="{if $admin}5{else}3{/if}">
                    {gt text='Outdated releases:'}
                </td>
            </tr>
        {/if}
        {if $stateOld != $release->getState() && $release->getState() == $prerelease}
            <tr>
                <td colspan="{if $admin}5{else}3{/if}">
                    {gt text='Pre-releases:'}
                </td>
            </tr>
        {/if}
        {if $stateOld != $release->getState() && $release->getState() == $development}
            <tr>
                <td colspan="{if $admin}5{else}3{/if}">
                    {gt text='Development builds:'}
                </td>
            </tr>
        {/if}
        <tr class="{if $release->getState() == $prerelease || $release->getState() == $development}danger{elseif $release->getState() == $supported}success{else}warning{/if}">
            {if $admin}<td style="cursor: pointer" data-toggle="modal" data-target="#el-download-release-modal-{$smarty.foreach.releases.iteration}">{$release->getId()}</td>{/if}
            <td style="cursor: pointer" data-toggle="modal" data-target="#el-download-release-modal-{$smarty.foreach.releases.iteration}">{$release->getSemver()|safetext}</td>
            <td style="cursor: pointer" data-toggle="modal" data-target="#el-download-release-modal-{$smarty.foreach.releases.iteration}">{$release->getNameI18n()|safetext}</td>
            <td style="cursor: pointer" data-toggle="modal" data-target="#el-download-release-modal-{$smarty.foreach.releases.iteration}">{$release->getState()|elReleaseStateToText:'singular'|safetext}</td>
            {if $admin}
                <td class="text-right">
                    {if $release->getState() == $outdated || $release->getState() == $supported}
                        <a href="{route name='zikulaextensionlibrarymodule_admin_togglereleasestate' id=$release->getId()}" title="{if $release->getState() == $supported}{gt text='Mark release as outdated'}{else}{gt text='Mark release as supported'}{/if}" data-toggle="">
                            {if $release->getState() == $supported}
                                <i class="fa fa-arrow-down"></i>
                            {else}
                                <i class="fa fa-arrow-up"></i>
                            {/if}
                        </a>
                    {/if}
                </td>
            {/if}
        </tr>
        {include file='User/releasedownloadmodal.tpl' modalRelease=$release id="el-download-release-modal-`$smarty.foreach.releases.iteration`"}
        {assign var='stateOld' value=$release->getState()}
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
