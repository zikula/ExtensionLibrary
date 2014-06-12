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
        var spinner = jQuery(this).parents('.checkbox').children('i');
        spinner.fadeIn();
        var parentRow = jQuery(this).parents("tr");
        jQuery.ajax({
            type: "POST",
            data: {
                checked: jQuery(this).prop("checked") ? 1 : 0,
                extid: jQuery(this).data("extid"),
                version: jQuery(this).data("version")
            },
            url: Zikula.Config.baseURL + "library/ajax/setVersionStatus",
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
     * enable tooltips
     */
    jQuery('.tooltips').tooltip();
});