(function ($) {
        const { __ } = wp.i18n;

        $(function () {
                $('.cee-signup-age-note').each(function () {
                        const $note = $(this);
                        const $input = $note.closest('.cee-signup-field').find('input[type="number"]');
                        if (!$input.length) {
                                return;
                        }
                        const updateNotice = function () {
                                const value = parseInt($input.val(), 10);
                                if (!isNaN(value) && value < 18) {
                                        const notice = (typeof CEEFrontModern !== 'undefined' && CEEFrontModern.ageMinorNotice)
                                                ? CEEFrontModern.ageMinorNotice
                                                : __('Un responsable légal devra valider cette inscription.', 'club-easy-event');
                                        $note.text(notice);
                                } else {
                                        $note.text('');
                                }
                        };
                        $input.on('input change', updateNotice);
                        updateNotice();
                });
        });
})(jQuery);
