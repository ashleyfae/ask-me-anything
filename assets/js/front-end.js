jQuery(document).ready(function ($) {

    var Ask_Me_Anything = {

        init: function () {
            $('.ask-me-anything-trigger-button').click(function (e) {
                e.preventDefault();
                var targetID = $(this).data('target');

                // Can't find the target - bail.
                if (targetID == '' || typeof targetID === 'undefined') {
                    return false;
                }

                Ask_Me_Anything.launchModal(targetID);
            });
        },

        /**
         * Launch Modal
         */
        launchModal: function (targetID) {

            // Add backdrop
            $('body').append('<div id="ask-me-anything-backdrop"></div>');
            $('#ask-me-anything-backdrop').addClass('ama-in');

            // Show the modal
            $(targetID).show().addClass('ama-in');

            // Insert the question form.
            Ask_Me_Anything.loadFormTemplate();

            // Insert the questions
            if (ASK_ME_ANYTHING.display_questions == true) {
                Ask_Me_Anything.loadQuestions(1);
            }

        },

        /**
         * Close Modal
         */
        closeModal: function () {

            // Hide the modal
            $('#ask-me-anything').hide();

            // Remove the backdrop
            $('#ask-me-anything-backdrop').remove();

        },

        /**
         * Load Form Template
         *
         * @param message
         */
        loadFormTemplate: function (message) {

            var amaFormTemplate = wp.template('ama-submit-form');
            var amaSubmitData = {
                message: typeof message !== 'undefined' ? message : '',
                form_title_text: ASK_ME_ANYTHING.form_title_text,
                form_description: ASK_ME_ANYTHING.form_description,
                form_require_name: ASK_ME_ANYTHING.form_require_name,
                form_require_email: ASK_ME_ANYTHING.form_require_email,
                form_question_field_name: ASK_ME_ANYTHING.form_question_field_name
            };

            $('.ask-me-anything-submit-question').empty().append(amaFormTemplate(amaSubmitData));

            $('.ask-me-anything-submit-question-form').submit(function (e) {
                e.preventDefault();

                var submitForm = $(this);

                submitForm.find('button').attr('disabled', true);
                submitForm.append('<i class="fa fa-spinner fa-spin"></i>');

                var formData = $(this).serializeArray();
                var data = {
                    action: 'ask_me_anything_submit_question',
                    formData: formData,
                    nonce: ASK_ME_ANYTHING.nonce
                };

                $.post(ASK_ME_ANYTHING.ajaxurl, data, function (response) {
                    console.log(response); //@todo remove

                    var responseClass = 'ama-error';

                    if (response.success == true) {
                        responseClass = 'ama-success';
                    }

                    var finalMessage = '<div class="' + responseClass + '">' + response.data + '</div>';

                    if (response.success == true) {
                        Ask_Me_Anything.loadFormTemplate(finalMessage);
                    } else {
                        submitForm.find('button').attr('disabled', false);
                        submitForm.find('.fa-spin, .ama-error').remove();
                        submitForm.prepend(finalMessage);
                    }

                });
            });

        },

        /**
         * Load Questions
         *
         * @param page Page number to load
         */
        loadQuestions: function (page) {

            var amaQuestionTemplate = wp.template('ama-question');
            var questionsList = $('.ask-me-anything-questions-list');

            questionsList.empty().append('<div style="text-align: center; padding: 1em;"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');

            var data = {
                action: 'ask_me_anything_get_questions',
                page_number: page,
                nonce: ASK_ME_ANYTHING.nonce
            };

            $.post(ASK_ME_ANYTHING.ajaxurl, data, function (response) {
                if (response.success == true) {
                    questionsList.empty().append(amaQuestionTemplate({questions: response.data}));
                    Ask_Me_Anything.renderQuestion();
                } else {
                    console.log(response); //@todo error
                }
            });

        },

        /**
         * Render Question
         *
         * When a single question is clicked on, we render the actual question content
         * on the left-hand side.
         */
        renderQuestion: function () {

            $('.ama-question-item').click(function (e) {
                e.preventDefault();

                var questionID = $(this).data('postid');

                // Remove the 'ama-active' class from all items except the one we clicked on.
                $('.ama-question-item').each(function () {
                    $(this).removeClass('ama-active');

                    if ($(this).data('postid') == questionID) {
                        $(this).addClass('ama-active');
                    }
                });

                var questionArea = $('.ask-me-anything-submit-question');

                // Add a spinner.
                questionArea.empty().append('<div style="text-align: center;"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');

                var data = {
                    action: 'ask_me_anything_load_question',
                    question_id: questionID,
                    nonce: ASK_ME_ANYTHING.nonce
                };

                $.post(ASK_ME_ANYTHING.ajaxurl, data, function (response) {

                    if (response.success == true) {
                        var amaQuestionTemplate = wp.template('ama-single-question');
                        questionArea.empty().append(amaQuestionTemplate(response.data));

                        // Initialize voting.
                        Ask_Me_Anything.initializeVoting();

                        // Load comments.
                        Ask_Me_Anything.loadCommentsTemplate(questionID);

                        // Initialize comment submission.
                        Ask_Me_Anything.submitComment();

                        // Go back to form.
                        $('.ama-load-question-form').click(function (e) {
                            e.preventDefault();
                            Ask_Me_Anything.loadFormTemplate();
                        });
                    } else {
                        console.log(response); // @todo error
                    }

                });

            });

        },

        /**
         * Initialize Voting
         *
         * @todo
         */
        initializeVoting: function () {

            $('.ama-up-vote').click(function (e) {
                console.log('clicked');
            });

        },

        /**
         * Load Comments Template
         *
         * @param questionID
         */
        loadCommentsTemplate: function (questionID) {

            var commentArea = $('.ama-comments-list');

            var data = {
                action: 'ask_me_anything_load_comments',
                question_id: questionID,
                nonce: ASK_ME_ANYTHING.nonce
            };

            $.post(ASK_ME_ANYTHING.ajaxurl, data, function (response) {

                if (response.success == true) {
                    var amaCommentsTemplate = wp.template('ama-comments');
                    commentArea.empty().append(amaCommentsTemplate({comments: response.data}));
                } else {
                    console.log(response); // @todo error
                }

            });

        },

        /**
         * Handles comment submission
         */
        submitComment: function () {


        }

    };

    Ask_Me_Anything.init();

    /**
     * Close the modal when we click outside of it.
     */
    $(document).mouseup(function (e) {
        var container = $('.ask-me-anything-modal-inner');

        if (!container.is(e.target) && container.has(e.target).length === 0) {
            Ask_Me_Anything.closeModal();
        }
    });

    /**
     * Close the modal when we click "ESC" on the keyboard.
     */
    document.addEventListener('keydown', function (ev) {
        var keyCode = ev.keyCode || ev.which;
        if (keyCode === 27) {
            Ask_Me_Anything.closeModal();
        }
    });

    /**
     * Close the modal when we click the 'x' button.
     */
    $('.ama-close-modal').click(function (e) {
        e.preventDefault();

        Ask_Me_Anything.closeModal();
    });


});