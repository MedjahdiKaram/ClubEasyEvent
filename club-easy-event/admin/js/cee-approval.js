(function ($) {
        const { __ } = wp.i18n;

        function toggleNote( show ) {
                const $note = $( '#cee_approval_note' );
                if ( ! $note.length ) {
                        return;
                }
                if ( show ) {
                        $note.prop( 'readonly', false ).attr( 'aria-hidden', 'false' ).addClass( 'is-active' );
                } else {
                        $note.prop( 'readonly', true ).attr( 'aria-hidden', 'true' ).removeClass( 'is-active' );
                }
        }

        $( function () {
                const $box = $( '#cee-approval-box' );
                if ( ! $box.length ) {
                        return;
                }

                const $note = $( '#cee_approval_note' );
                if ( $note.length && !$note.val() ) {
                        toggleNote( false );
                }

                $box.on( 'click', 'button[name="cee_approval_action"]', function ( event ) {
                        const action = $( event.currentTarget ).val();
                        if ( 'approve' === action ) {
                                toggleNote( false );
                                $( '#cee_approval_state' ).val( 'approved' );
                        }
                        if ( 'reject' === action ) {
                                toggleNote( true );
                                $( '#cee_approval_state' ).val( 'rejected' );
                                if ( !$note.val() ) {
                                        $note.attr( 'placeholder', __( 'Expliquez la raison du rejet…', 'club-easy-event' ) );
                                }
                        }
                } );

                $( '#cee_approval_state' ).on( 'change', function () {
                        const value = $( this ).val();
                        toggleNote( 'rejected' === value );
                } );
        } );
})(jQuery);
