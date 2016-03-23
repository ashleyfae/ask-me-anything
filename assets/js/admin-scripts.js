(function ($) {

    $('.ama-validate-license').click(function (e) {
        e.preventDefault();

        var button = $(this);

        // Add spinner
        $(this).after('<div id="ama-updater-spinner" class="spinner"></div>');
        $('#ama-updater-spinner').css({
            'visibility': 'visible',
            'float': 'none'
        });

        // Remove the response section.
        $('#ama-updater-response').remove();

        // Get the value of the license key.
        var license_key_value = button.prev('input').val();

        var data = {
            'action': 'ask_me_anything_' + button.data('action'),
            'license': license_key_value,
            'license_key_name': button.data('option-name'),
            'status_option_name': button.data('status-name'),
            'product_name': button.data('product-name')
        };

        $.post(ajaxurl, data, function (response) {
            $('#ama-updater-spinner').remove();
            $(button).after('<div id="ama-updater-response" class="ask-me-anything-updater-' + response.success + '">' + response.data + '</div>');

            if (button.data('action') == 'deactivate_license' && response.success == true) {
                button.prev('input').val('');
            }
        });
    });

})(jQuery);