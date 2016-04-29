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

});