/**
 * Zikula.ExtensionLibrary.User.Validate.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function() {
    // register the click handlers
    jQuery('#schema').change(updateTitle);
    jQuery('#validateButton').click(validateJson);

    // update the title to reflect the selected json type
    function updateTitle() {
        jQuery('#json-type').html(jQuery("#schema option:selected").text());
    }

    // instigate an ajax request to validate the json
    function validateJson(e) {
        e.preventDefault();
        var content = jQuery('#json').val();
        var schema = jQuery('#schema').val();
        jQuery.ajax({
            type: "POST",
            data: {
                content: content,
                schema: schema
            },
            url: Routing.generate('zikulaextensionlibrarymodule_ajax_validatejson'),
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
        function capitaliseFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        var resultDiv = jQuery("#validationResults");
        resultDiv.empty();
        if (data.valid) {
            resultDiv.append('<div class="alert alert-success"><strong><i class="fa fa-smile-o"></i> Valid '+data.schemaName+'!</strong></div>');
        } else {
            resultDiv.append('<div class="alert alert-danger"><strong><i class="fa fa-warning"></i> Invalid '+data.schemaName+'!</strong></div>');
            for (var i = 0; i < data.errors.length; i++) {
                var msg = '<div class="alert alert-danger">';
                if  (data.errors[i].property.length > 0) {
                    msg += 'In property <strong>' + data.errors[i].property + '</strong>: ' + data.errors[i].message;
                } else {
                    msg += capitaliseFirstLetter(data.errors[i].message);
                }
                resultDiv.append(msg + '.</div>');
            }
        }
    }
});
