(function ($) {
    'use strict';
    $(document).ready(function () {

        $('.plugin-notice-btn, .stm-plugin-admin-notice-close').on('click', function () {

            let type = $(this).attr('data-type');
            let pluginName = $(this).parent().find("input[name='plugin-name']").val();
            let pluginEvent = $(this).parent().find("input[name='plugin-event']").val();

            if (type && !$(this).prop('disabled')) {
                $(this).prop('disabled', true);
                let $this = $(this);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'stm_ajax_admin_notice',
                        type,
                        pluginName,
                        pluginEvent
                    },
                    success: function () {
                        $this.closest('.stm-plugin-admin-notice').fadeOut(10);
                    },
                    complete: function () {
                        $(this).prop('disabled', false);
                    }
                });
            }
        });

    })
})(jQuery);