(function ($) {

    /**
     * Initialize Colour Picker
     */
    var ama_color_picker = $('.ama-color-picker');

    if (ama_color_picker.length) {
        ama_color_picker.wpColorPicker();
    }

    /**
     * Spam/Unspam
     */
    var Ask_Me_Anything_Spam = {

        init: function () {
            $('.ama-mark-spam').click(function (e) {
                e.preventDefault();

                // Add a spinner after the title.
                $(this).parents('tr').find('strong').append('<span class="spinner is-active"></span>');

                var spamButton = $(this);

                var data = {
                    action: 'ask_me_anything_spam',
                    akismet_action: $(this).data('action'),
                    question_id: $(this).data('question-id'),
                    nonce: $(this).data('nonce')
                };

                $.ajax({
                    type: 'POST',
                    data: data,
                    url: ajaxurl,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (response) {
                        console.log(response);
                        spamButton.parents('tr').slideUp();
                    }
                }).fail(function (response) {
                    if ( window.console && window.console.log ) {
                        console.log( response );
                    }
                });
            });
        }

    };

    Ask_Me_Anything_Spam.init();

})(jQuery);