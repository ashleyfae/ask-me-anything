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

            Ask_Me_Anything.loadFormTemplate();
            if (ASK_ME_ANYTHING.display_questions == true) {
                Ask_Me_Anything.loadQuestions(1);
            }
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
            /*if (ASK_ME_ANYTHING.display_questions == true) {
                Ask_Me_Anything.loadQuestions(1);
            }*/

        },

        /**
         * Close Modal
         */
        closeModal: function () {

            var modalBox = $('#ask-me-anything');

            if (modalBox.hasClass('ask-me-anything-popup')) {
                // Hide the modal
                modalBox.hide();

                // Remove the backdrop
                $('#ask-me-anything-backdrop').remove();
            }

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
                form_question_field_name: ASK_ME_ANYTHING.form_question_field_name,
                comment_author: ASK_ME_ANYTHING.comment_author,
                comment_author_email: ASK_ME_ANYTHING.comment_author_email,
                comment_author_url: ASK_ME_ANYTHING.comment_author_url
            };

            $('.ask-me-anything-submit-question').empty().append(amaFormTemplate(amaSubmitData));

            $('.ask-me-anything-submit-question-form').submit(function (e) {
                e.preventDefault();

                var submitForm = $(this);

                document.body.style.cursor = 'wait';
                submitForm.find('button').attr('disabled', true);
                submitForm.append('<i class="fa fa-spinner fa-spin"></i>');

                var formData = $(this).serializeArray();
                var data = {
                    action: 'ask_me_anything_submit_question',
                    formData: formData,
                    nonce: ASK_ME_ANYTHING.nonce
                };

                $.ajax({
                    type: 'POST',
                    data: data,
                    url: ASK_ME_ANYTHING.ajaxurl,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (response) {

                        document.body.style.cursor = 'default';
                        var responseClass = 'ama-error';

                        if (response.success == true) {
                            responseClass = 'ama-success';
                        }

                        var finalMessage = '<div class="' + responseClass + '">' + response.data + '</div>';

                        if (response.success == true) {
                            Ask_Me_Anything.loadFormTemplate(finalMessage);

                            // Re-load the questions list.
                            if (ASK_ME_ANYTHING.display_questions == true) {
                                Ask_Me_Anything.loadQuestions(1);
                            }
                        } else {
                            submitForm.find('button').attr('disabled', false);
                            submitForm.find('.fa-spin, .ama-error').remove();
                            submitForm.prepend(finalMessage);
                        }

                    }
                }).fail(function (response) {
                    if ( window.console && window.console.log ) {
                        console.log( response );
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

            $.ajax({
                type: 'POST',
                data: data,
                url: ASK_ME_ANYTHING.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {
                    questionsList.empty();

                    if (response.success == true) {
                        var templateData = {
                            questions: response.data.questions,
                            nextpage: response.data.next_page,
                            previouspage: response.data.previous_page
                        };
                        questionsList.append(amaQuestionTemplate(templateData));
                        Ask_Me_Anything.renderQuestion();
                    } else {
                        questionsList.append(response.data);
                    }

                    // Load pagination
                    Ask_Me_Anything.pagination();
                }
            });

        },

        /**
         * Navigate to next/previous pages.
         */
        pagination: function () {
            $('.ama-pagination button').click(function (e) {
                e.preventDefault();

                var pageToLoad = $(this).data('page');

                Ask_Me_Anything.loadQuestions(pageToLoad);
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
                
                $.ajax({
                    type: 'POST',
                    data: data,
                    url: ASK_ME_ANYTHING.ajaxurl,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (response) {
                        if (response.success == true) {
                            var amaQuestionTemplate = wp.template('ama-single-question');
                            questionArea.empty().append(amaQuestionTemplate(response.data));

                            // Initialize voting.
                            Ask_Me_Anything.initializeVoting(questionID);

                            // Load comments.
                            Ask_Me_Anything.loadCommentsTemplate(questionID);

                            // Initialize comment submission.
                            Ask_Me_Anything.submitComment();

                            // Go back to form.
                            $('.ama-load-question-form').click(function (e) {
                                e.preventDefault();
                                // Remove 'active' classes from all questions.
                                $('.ama-question-item').each(function () {
                                    $(this).removeClass('ama-active');
                                });
                                Ask_Me_Anything.loadFormTemplate();
                            });
                        } else {
                            questionArea.empty().append(response.data);
                        }
                    }
                }).fail(function (response) {
                    if ( window.console && window.console.log ) {
                        console.log( response );
                    }
                });

            });

        },

        /**
         * Initialize Voting
         */
        initializeVoting: function (questionID) {

            $('.ama-up-vote, .ama-down-vote').click(function (e) {
                if ($(this).hasClass('ama-disabled')) {
                    return false;
                }

                // Insert a spinner.
                var votingParent = $(this).parent();
                votingParent.prepend('<i class="fa fa-spinner fa-spin" style="margin-right: 7px"></i>');

                var voteElement = $(this).find('.ama-vote-number');
                var voteType = 'up';

                if ($(this).hasClass('ama-down-vote')) {
                    voteType = 'down';
                }

                $(this).addClass('ama-disabled');

                var data = {
                    action: 'ask_me_anything_vote',
                    vote_type: voteType,
                    question_id: questionID,
                    nonce: ASK_ME_ANYTHING.nonce
                };

                $.ajax({
                    type: 'POST',
                    data: data,
                    url: ASK_ME_ANYTHING.ajaxurl,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (response) {
                        votingParent.find('.fa-spinner').remove();

                        if (response.success == true) {
                            voteElement.text(response.data);

                            // Increment the vote element on the left-hand side too.
                            var voteElementLeft = $('.ask-me-anything-questions-list #ama-question-item-' + questionID).find('.ama-' + voteType + '-vote').find('.ama-vote-number');

                            if (typeof voteElementLeft != 'undefined') {
                                voteElementLeft.text(response.data);
                            }
                        } else {
                            if ( window.console && window.console.log ) {
                                console.log(response); // @todo error
                            }
                        }
                    }
                }).fail(function (response) {
                    if ( window.console && window.console.log ) {
                        console.log( response );
                    }
                });
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

            $.ajax({
                type: 'POST',
                data: data,
                url: ASK_ME_ANYTHING.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {
                    if (response.success == true) {
                        var amaCommentsTemplate = wp.template('ama-comments');
                        commentArea.empty().append(amaCommentsTemplate({comments: response.data}));
                    } else {
                        if ( window.console && window.console.log ) {
                            console.log(response);
                        }
                    }
                }
            }).fail(function (response) {
                if ( window.console && window.console.log ) {
                    console.log( response );
                }
            });

        },

        /**
         * Handles comment submission
         */
        submitComment: function () {

            $('#ama-submit-comment-form').submit(function (e) {
                e.preventDefault();

                var submitForm = $(this);

                document.body.style.cursor = 'wait';
                submitForm.find('button').attr('disabled', true);
                submitForm.append('<i class="fa fa-spinner fa-spin"></i>');

                var questionID = $(this).closest('.ask-me-anything-submit-question').find('.ama-single-question-wrap').data('question-id');

                var formData = $(this).serializeArray();
                var data = {
                    action: 'ask_me_anything_submit_comment',
                    formData: formData,
                    question_id: questionID,
                    nonce: ASK_ME_ANYTHING.nonce
                };

                $.ajax({
                    type: 'POST',
                    data: data,
                    url: ASK_ME_ANYTHING.ajaxurl,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (response) {

                        // Remove "waiting" stuff and old error messages.
                        submitForm.find('button').attr('disabled', false);
                        submitForm.find('.fa-spin, .ama-error, .ama-success').remove();
                        document.body.style.cursor = 'default';

                        var responseClass = 'ama-error';

                        if (response.success == true) {
                            responseClass = 'ama-success';
                        }

                        // Build response message.
                        var responseMessage;
                        if (response.success == true) {
                            responseMessage = response.data.message;
                        } else {
                            responseMessage = response.data;
                        }

                        // Add success/error messages.
                        var finalMessage = '<div class="' + responseClass + '">' + responseMessage + '</div>';
                        submitForm.prepend(finalMessage);

                        if (response.success == true) {
                            var amaCommentsTemplate = wp.template('ama-comments');
                            $('.ama-comments-list').prepend(amaCommentsTemplate({comments: response.data.comment_data}));

                            // Delete the contents of the comment field.
                            $('#ama-comment-message-field').val('');
                        }

                    }
                }).fail(function (response) {
                    if ( window.console && window.console.log ) {
                        console.log( response );
                    }
                });
            });

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