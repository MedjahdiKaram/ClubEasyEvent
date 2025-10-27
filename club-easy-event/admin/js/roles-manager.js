(function($){

        function filterRows(value){
                const term = value.toLowerCase();
                $('.cee-roles-manager-table tbody tr').each(function(){
                        const $row = $(this);
                        const haystack = ($row.data('search') || '').toString();
                        const shouldShow = !term || haystack.indexOf(term) !== -1;
                        $row.toggle(shouldShow);
                });
        }

        $(function(){
                const $filter = $('#cee-roles-filter');
                if ($filter.length && window.CEERolesManager && CEERolesManager.filterPlaceholder) {
                        $filter.attr('placeholder', CEERolesManager.filterPlaceholder);
                }
                $filter.on('input', function(){
                        filterRows($(this).val());
                });

                $('#cee-roles-select-all, .cee-roles-header-checkbox').on('change', function(){
                        const checked = $(this).is(':checked');
                        $('.cee-roles-manager-table tbody input[type="checkbox"][name="user_ids[]"]').prop('checked', checked);
                        $('.cee-roles-header-checkbox').prop('checked', checked);
                        $('#cee-roles-select-all').prop('checked', checked);
                });

                $('.cee-roles-manager-table tbody').on('change', 'input[type="checkbox"][name="user_ids[]"]', function(){
                        if (!$(this).is(':checked')) {
                                $('.cee-roles-header-checkbox, #cee-roles-select-all').prop('checked', false);
                        }
                });

                $('form').on('submit', function(){
                        $(this).find('button[type="submit"]').attr('aria-busy', 'true');
                });
        });
})(jQuery);
