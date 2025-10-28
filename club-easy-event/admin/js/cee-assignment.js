(function ($) {
        const { __ } = wp.i18n;

        function filterList($input) {
                const targetId = $input.data('target');
                const query = ($input.val() || '').toLowerCase();
                const $list = $('#' + targetId);
                if (!$list.length) {
                        return;
                }

                let visibleCount = 0;
                $list.children('.cee-assignment-item').each(function () {
                        const $item = $(this);
                        const text = $item.data('search-text') || '';
                        const match = !query || text.indexOf(query) !== -1;
                        $item.toggle(match);
                        if (match) {
                                visibleCount++;
                        }
                });

                const $empty = $list.next('.cee-assignment-empty');
                if (!$empty.length) {
                        return;
                }
                $empty.toggle(visibleCount === 0);
        }

        $(function () {
                        $('.cee-assignment-search').each(function () {
                                const $input = $(this);
                                const $list = $('#' + $input.data('target'));
                                if ($list && !$list.next('.cee-assignment-empty').length) {
                                        $('<p class="cee-assignment-empty" aria-live="polite"></p>')
                                                .text(__('Aucun résultat pour cette recherche.', 'club-easy-event'))
                                                .insertAfter($list)
                                                .hide();
                                }
                        });

                        $('.cee-assignment-search').on('input keyup', function () {
                                filterList($(this));
                        });
        });
})(jQuery);
