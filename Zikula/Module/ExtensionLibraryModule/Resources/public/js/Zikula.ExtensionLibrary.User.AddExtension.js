/**
 * Zikula.ExtensionLibrary.User.AddExtension.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function() {
    // register the click handlers
    jQuery('#el-add-extension-extension-apitype').change(updateInput);

    /**
     * hide/show the namespace input
     */
    function updateInput() {
        var apitype = jQuery('#el-add-extension-extension-apitype').val();
        if (apitype != '1.3') {
            jQuery('#el-add-extension-extension-namespace-input').show(400).find('input').attr('required', '');
            jQuery('#el-add-extension-extension-coreCompatibility').val('>=1.4 <1.5');
        } else {
            jQuery('#el-add-extension-extension-namespace-input').hide(400).find('input').removeAttr('required');
            jQuery('#el-add-extension-extension-coreCompatibility').val('>=1.3.5 <1.5');
        }
    }
});
