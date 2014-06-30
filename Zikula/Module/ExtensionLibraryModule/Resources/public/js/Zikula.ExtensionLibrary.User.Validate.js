/**
 * Zikula.ExtensionLibrary.User.Validate.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function() {
    // register the click handler
    jQuery('#validateButton').click(validateManifest);

    // instigate an ajax request to validate the manifest
    function validateManifest(e) {
        e.preventDefault();
        var content = jQuery('#manifest').val();
        jQuery.ajax({
            type: "POST",
            data: {
                content: content
            },
            url: Routing.generate('zikulaextensionlibrarymodule_ajax_validatemanifest'),
            success: function(result) {
                renderResponse(result.data)
            },
            error: function(result) {
                alert(result.responseJSON.core.statusmsg);
            }
        });
    }

    // handle the results of the ajax request to validate the manifest
    function renderResponse(data) {
        var resultDiv = jQuery("#validationResults");
        resultDiv.empty();
        if (data.valid) {
            resultDiv.append('<div class="alert alert-success"><strong><i class="fa fa-smile-o"></i> Valid Manifest!</strong></div>');
        } else {
            resultDiv.append('<div class="alert alert-danger"><strong><i class="fa fa-warning"></i> Invalid Manifest!</strong></div>');
            for (var i = 0; i < data.errors.length; i++) {
                resultDiv.append('<div class="alert alert-danger">In property <strong>'+data.errors[i].property+'</strong>: '+data.errors[i].message+'.</div>');
            }
        }
    }
});
