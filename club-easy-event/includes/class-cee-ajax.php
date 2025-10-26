<?php
/**
 * AJAX controller.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles AJAX requests for the plugin.
 */
class CEE_Ajax {

	/**
	 * Process RSVP submissions.
	 *
	 * @return void
	 */
	public function handle_rsvp() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'cee_rsvp_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Nonce invalide.', 'club-easy-event' ) ), 400 );
		}

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Vous devez être connecté pour répondre.', 'club-easy-event' ) ), 403 );
		}

		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permissions insuffisantes.', 'club-easy-event' ) ), 403 );
		}

		$event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
		$response = isset( $_POST['response'] ) ? sanitize_key( wp_unslash( $_POST['response'] ) ) : '';

		if ( ! $event_id || ! in_array( $response, array( 'yes', 'no', 'maybe' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Données incorrectes.', 'club-easy-event' ) ), 400 );
		}

		if ( 'cee_event' !== get_post_type( $event_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Événement introuvable.', 'club-easy-event' ) ), 404 );
		}

		$user_id   = get_current_user_id();
		$rsvp_data = get_post_meta( $event_id, '_cee_rsvp_data', true );
		$rsvp_data = is_array( $rsvp_data ) ? $rsvp_data : array();
		$rsvp_data[ $user_id ] = $response;

		update_post_meta( $event_id, '_cee_rsvp_data', $rsvp_data );

		do_action( 'cee_rsvp_updated', $event_id, $user_id, $response );

		wp_send_json_success( array( 'message' => __( 'Votre réponse a bien été enregistrée.', 'club-easy-event' ), 'response' => $response ) );
	}
}
