(function ($) {
        'use strict';

        if ( typeof wp === 'undefined' || typeof wp.i18n === 'undefined' ) {
                return;
        }

        const { __, sprintf } = wp.i18n;

        $(function () {
                const data = window.CEEOnboarding || null;
                const $container = $( '.cee-onboarding' );

                if ( ! $container.length ) {
                        return;
                }

                const $steps = $container.find( '.cee-onboarding__step' );
                if ( ! $steps.length ) {
                        return;
                }

                const $progress = $container.find( '.cee-onboarding__progress' );
                const $nextButton = $container.find( '.cee-onboarding__next' );
                const progressTemplate = $progress.data( 'progress-template' ) || 'Step %1$s of %2$s';
                const totalSteps = $steps.length;
                let currentIndex = 0;

                const updateProgress = function () {
                        $steps.removeClass( 'is-active' ).attr( 'aria-hidden', 'true' );
                        const $current = $steps.eq( currentIndex );
                        $current.addClass( 'is-active' ).attr( 'aria-hidden', 'false' );
                        $progress.text( sprintf( progressTemplate, currentIndex + 1, totalSteps ) );

                        if ( currentIndex === totalSteps - 1 ) {
                                $nextButton.text( __( 'Terminé', 'club-easy-event' ) ).addClass( 'is-last-step' );
                        } else {
                                $nextButton.text( __( 'Suivant', 'club-easy-event' ) ).removeClass( 'is-last-step' );
                        }
                };

                updateProgress();

                $container.on( 'click', '.cee-onboarding__next', function ( event ) {
                        event.preventDefault();

                        if ( currentIndex < totalSteps - 1 ) {
                                currentIndex += 1;
                                updateProgress();
                                return;
                        }

                        $container.fadeOut( 150 );
                } );

                $container.on( 'click', '.cee-onboarding__dismiss', function ( event ) {
                        event.preventDefault();

                        if ( ! data ) {
                                return;
                        }

                        $.post( data.ajax_url, {
                                action: 'cee_onboarding_dismiss',
                                nonce: data.nonce
                        } ).done( function () {
                                $container.fadeOut( 150 );
                        } ).fail( function () {
                                window.alert( __( 'Impossible d’enregistrer votre préférence. Veuillez réessayer.', 'club-easy-event' ) );
                        } );
                } );
        } );
})( jQuery );
