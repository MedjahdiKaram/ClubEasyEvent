(function ( $ ) {
        'use strict';

        $( function () {
                $( '.cee-dashboard' ).on( 'click', '.cee-card__toggle', function ( event ) {
                        event.preventDefault();

                        var $button         = $( this );
                        var $card           = $button.closest( '.cee-card' );
                        var isExpanded      = $button.attr( 'aria-expanded' ) === 'true';
                        var expandedIcon    = $button.data( 'expanded-icon' ) || '−';
                        var collapsedIcon   = $button.data( 'collapsed-icon' ) || '+';
                        var $iconContainer  = $button.find( '.cee-card__toggle-icon' );

                        if ( isExpanded ) {
                                $card.addClass( 'is-collapsed' );
                                $button.attr( 'aria-expanded', 'false' );
                                $iconContainer.text( collapsedIcon );
                        } else {
                                $card.removeClass( 'is-collapsed' );
                                $button.attr( 'aria-expanded', 'true' );
                                $iconContainer.text( expandedIcon );
                        }
                } );
        } );
})( jQuery );
