/**
 * Zikula.ExtensionLibrary.User.Display.js
 *
 * jQuery based JS
 */

jQuery(document).ready(function() {
    /**
     * show a modal dialog with contributors information
     */
    jQuery('#contributorsModal').on('show.bs.modal', function (e) {
        var people = jQuery(e.relatedTarget).data('people');
        var content = "<ul>";
        jQuery.each(people, function(k, v) {
            content = content + "<li><strong>" + v.name + "</strong><ul>";
            if (v.role) content = content + "<li><em>" + v.role + "</em></li>";
            if (v.email) content = content + "<li>" + v.email + "</li>";
            if (v. homepage) content = content + "<li><a href='"+ v.homepage+"'>" + v.homepage + "</a></li>";
            content = content + "</ul></li>";
        });
        content = content + "</ul>";
        jQuery(this).find(".modal-body").html(content);
    });

    /**
     * update the database to reflect admin choice to verify/unverify a version
     */
    jQuery('.verify').change(function() {
        var spinner = jQuery(this).parents('.verify-checkbox').children('i');
        spinner.fadeIn();
        var versionData = jQuery(this).data();
        var parentRow = jQuery(jq(versionData.version));
        jQuery.ajax({
            type: "POST",
            data: {
                checked: jQuery(this).prop("checked") ? 1 : 0,
                extid: versionData.extid,
                version: versionData.version
            },
            url: Routing.generate('zikulaextensionlibrarymodule_ajax_setversionstatus'),
            success: function(result) {
                spinner.fadeOut(400, function() {
                    if (result.data.status == 1) {
                        parentRow.removeClass("warning").addClass("success");
                    } else {
                        parentRow.removeClass("success").addClass("warning");
                    }
                });
            },
            error: function(result) {
                alert(result.responseJSON.core.statusmsg);
            }
        });
    });

    /**
     * update modal data values on open
     */
    jQuery('#deleteVersionModal').on('show.bs.modal', function (e) {
        var extData = jQuery(e.relatedTarget).data();
        jQuery(this).find("#deleteVersionButton").data({
                version: extData.version,
                extid: extData.extid
            });
        jQuery(this).find('#version-tag').text(extData.version)
        jQuery(this).find('#extension-title-tag').text(extData.title);
    });

    /**
     * process delete version function on button click
     */
    jQuery('#deleteVersionButton').click(function() {
        var icon = jQuery('#deleteVersionIcon');
        icon.addClass('fa-spin');
        var extData = jQuery(this).data();
        jQuery.ajax({
            type: "POST",
            data: extData,
            url: Routing.generate('zikulaextensionlibrarymodule_ajax_deleteversion'),
            success: function(result) {
                icon.removeClass(function() {
                    if (result.data.status == 1) {
                        return "fa-spin";
                    }
                    return "";
                });
                jQuery('#deleteVersionModal').modal('hide');
                jQuery(jq(extData.version)).fadeOut();
            },
            error: function(result) {
                alert(result.responseJSON.core.statusmsg);
            }
        });

    });

    /**
     * enable tooltips
     */
    jQuery('.tooltips').tooltip();

    /**
     * escape disallowed characters in an id name and add `#` to beginning
     *
     * @see http://learn.jquery.com/using-jquery-core/faq/how-do-i-select-an-element-by-an-id-that-has-characters-used-in-css-notation/
     * @param myid
     * @returns {string}
     */
    function jq( myid ) {
        return "#" + myid.replace( /(:|\.|\[|\])/g, "\\$1" );
    }
});
