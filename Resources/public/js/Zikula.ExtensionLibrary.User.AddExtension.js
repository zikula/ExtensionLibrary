/**
 * Zikula.ExtensionLibrary.User.AddExtension.js
 *
 * jQuery based JS
 */

(function ($) {
    $(document).ready(function() {
        // register the click handlers
        $('#el-add-extension-extension-apitype').change(updateInput);

        /**
         * hide/show the namespace input
         */
        function updateInput() {
            var apitype = $('#el-add-extension-extension-apitype').val();
            if (apitype === '1.3') {
                $('#el-add-extension-extension-namespace-row').hide(400).find('input').removeAttr('required');
                $('#el-add-extension-extension-coreCompatibility').val('>=1.3.5 <2.0');
            } else {
                $('#el-add-extension-extension-namespace-row').show(400).find('input').attr('required', '');
                $('#el-add-extension-extension-coreCompatibility').val('>=1.4 <2.0');
            }
        }
    });
}(jQuery));
