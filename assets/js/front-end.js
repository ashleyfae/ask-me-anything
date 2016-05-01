jQuery(document).ready(function ($) {

    /**
     * Open the modal when we click on the button.
     */
    $('.ask-me-anything-button').click(function (e) {
        e.preventDefault();

        var targetID = $(this).data('target');

        AskMeAnythingLaunchModal(targetID);
    });

    /**
     * Close the modal when we click outside of it.
     */
    $(document).mouseup(function (e) {
        var container = $('.ask-me-anything-modal-inner');

        if (!container.is(e.target) && container.has(e.target).length === 0) {
            AskMeAnythingCloseModal('#ask-me-anything');
        }
    });

    /**
     * Close the modal when we click "ESC" on the keyboard.
     */
    document.addEventListener('keydown', function (ev) {
        var keyCode = ev.keyCode || ev.which;
        if (keyCode === 27) {
            AskMeAnythingCloseModal('#ask-me-anything');
        }
    });

    /**
     * Launch Modal
     *
     * @param targetID ID of the target div.
     * @returns {boolean}
     * @constructor
     */
    function AskMeAnythingLaunchModal(targetID) {

        // Can't find the target - bail.
        if (targetID == '' || typeof targetID === 'undefined') {
            return false;
        }

        // Add backdrop
        $('body').append('<div id="ask-me-anything-backdrop"></div>');
        $('#ask-me-anything-backdrop').addClass('ama-in');

        // Show the modal
        $(targetID).show().addClass('ama-in');

        // Insert the question form.
        AskMeAnythingLoadFormTemplate();

        // Insert the questions
        if (ASK_ME_ANYTHING.display_questions == true) {
            AskMeAnythingGetQuestions(1);
        }

    }

    /**
     * Close Modal
     *
     * @param targetID ID of the modal to close
     * @returns {boolean|void}
     * @constructor
     */
    function AskMeAnythingCloseModal(targetID) {

        // Can't find the target - bail.
        if (targetID == '' || typeof targetID === 'undefined') {
            return false;
        }

        // Hide the modal
        $(targetID).hide();

        // Remove the backdrop
        $('#ask-me-anything-backdrop').remove();

    }

    /**
     * Load Form Template
     *
     * Also processes question submission.
     *
     * @constructor
     */
    function AskMeAnythingLoadFormTemplate(message) {
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

        $('.ask-me-anything-submit-question-form').submit(function(e) {
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
                //submitForm.append(response);
                //return false;

                var responseClass = 'ama-error';

                if (response.success == true) {
                    responseClass = 'ama-success';
                }

                var finalMessage = '<div class="' + responseClass + '">' + response.data + '</div>';

                if (response.success == true) {
                    AskMeAnythingLoadFormTemplate(finalMessage);
                } else {
                    submitForm.find('.fa-spin').remove();
                    submitForm.insertBefore(finalMessage);
                }

            });
        });
    }

    /**
     * Get Questions
     *
     * @param page Page number to retrieve
     * @constructor
     */
    function AskMeAnythingGetQuestions(page) {
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
                AskMeAnythingRenderQuestion();
            } else {
                console.log(response);
            }
        });
    }

    /**
     * Render Question
     *
     * When a single question is clicked on, we render the actual question content
     * on the left-hand side.
     *
     * @constructor
     */
    function AskMeAnythingRenderQuestion() {
        $('.ama-question-item').click(function (e) {
            e.preventDefault();

            var questionID = $(this).data('postid');

            $('.ama-question-item').each(function () {
                $(this).removeClass('ama-active');

                if ($(this).data('postid') == questionID) {
                    $(this).addClass('ama-active');
                }
            });

            var questionArea = $('.ask-me-anything-submit-question');

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
                    AskMeAnythingInitializeVoting();
                    
                    // Initialize comment submission.
                    AskMeAnythingSubmitComment();

                    // Go back to form.
                    $('.ama-load-question-form').click(function(e) {
                        e.preventDefault();
                        AskMeAnythingLoadFormTemplate();
                    });
                } else {
                    console.log(response);
                }
            });

        });
    }

    /**
     * Initialize Voting
     * 
     * Processes up/down votes.
     * 
     * @constructor
     */
    function AskMeAnythingInitializeVoting() {
        $('.ama-up-vote').click(function (e) {
            console.log('clicked');
        });
    }

    /**
     * Initialize Comment Submission
     * 
     * @constructor
     */
    function AskMeAnythingSubmitComment() {
        
    }


});