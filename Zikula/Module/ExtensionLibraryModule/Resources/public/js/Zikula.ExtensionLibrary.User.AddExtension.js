/**
 * Zikula.ExtensionLibrary.User.AddExtension.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function() {
    // register the click handlers
    jQuery('#el-add-extension-repository-apitype').change(updateInput);

    /**
     * hide/show the namespace input
     */
    function updateInput() {
        var apitype = jQuery('#el-add-extension-repository-apitype').val();
        if (apitype != '1.3') {
            jQuery('#el-add-extension-extension-namespace-input').show(400);
        } else {
            jQuery('#el-add-extension-extension-namespace-input').hide(400);
        }
    }
});
