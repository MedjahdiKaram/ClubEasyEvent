(function($){
        const __ = wp.i18n.__;

        function supportsType(type){
                const input = document.createElement('input');
                input.setAttribute('type', type);
                const supported = input.type === type;
                return supported;
        }

        function enhanceDateField($input){
                if (supportsType('date')) {
                        return;
                }

                $input.attr('type', 'text');
                $input.datepicker({
                        dateFormat: 'yy-mm-dd',
                        showAnim: 'fadeIn'
                });
        }

        function enhanceTimeField($input){
                if (supportsType('time')) {
                        return;
                }

                const currentValue = $input.val();
                const $select = $('<select class="cee-time-select" aria-label="' + __('Sélectionnez une heure', 'club-easy-event') + '"></select>');

                for ( let hour = 0; hour < 24; hour++ ) {
                        for ( let minutes = 0; minutes < 60; minutes += 15 ) {
                                const value = ('0' + hour).slice(-2) + ':' + ('0' + minutes).slice(-2);
                                const label = value;
                                const $option = $('<option></option>').val(value).text(label);
                                if ( currentValue && value === currentValue ) {
                                        $option.prop('selected', true);
                                }
                                $select.append($option);
                        }
                }

                $select.on('change', function(){
                        $input.val($(this).val());
                });

                $input.after($select).hide();
        }

        $(function(){
                const $dateField = $('#cee_event_date');
                const $timeField = $('#cee_event_time');

                if ($dateField.length) {
                        enhanceDateField($dateField);
                }

                if ($timeField.length) {
                        enhanceTimeField($timeField);
                }
        });
})(jQuery);
