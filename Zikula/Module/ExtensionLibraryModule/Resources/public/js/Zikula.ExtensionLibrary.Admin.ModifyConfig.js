(function ($) {
    $(function() {
        $('#el-modify-config-form input[type="password"]').each(function () {
            $(this).wrap('<div class="input-group"></div>').after('<span class="input-group-addon show-password" style="cursor: pointer"><i class="fa fa-eye"></i></span>');
        });
        $('#el-modify-config-form .show-password').live('click', function () {
            $(this).prev().attr('type', 'text');
        });
    });
})(jQuery);
