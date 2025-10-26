<?php
/**
 * WooCommerce integration helper.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides optional integrations with WooCommerce.
 */
class CEE_WooCommerce {

	/**
	 * Determine if WooCommerce is active.
	 *
	 * @return bool
	 */
	public function is_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Check whether a user has paid for a season product.
	 *
	 * @param int $user_id   User ID.
	 * @param int $season_id Season term ID.
	 *
	 * @return bool
	 */
	public function has_user_paid_for_season( $user_id, $season_id ) {
		if ( ! $this->is_active() ) {
			return false;
		}

		$user_id   = absint( $user_id );
		$season_id = absint( $season_id );

		if ( ! $user_id || ! $season_id ) {
			return false;
		}

		$product_id = absint( get_term_meta( $season_id, '_cee_season_wc_product_id', true ) );
		if ( ! $product_id ) {
			return false;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return false;
		}

		if ( ! function_exists( 'wc_customer_bought_product' ) ) {
			return false;
		}

		return (bool) wc_customer_bought_product( $user->user_email, $user_id, $product_id );
	}
}
if ( ! function_exists( 'cee_has_user_paid_for_season' ) ) {
	/**
	 * Helper to check WooCommerce payment status.
	 *
	 * @param int $user_id   User ID.
	 * @param int $season_id Season ID.
	 *
	 * @return bool
	 */
	function cee_has_user_paid_for_season( $user_id, $season_id ) {
		$helper = new CEE_WooCommerce();
		return $helper->has_user_paid_for_season( $user_id, $season_id );
	}
}
