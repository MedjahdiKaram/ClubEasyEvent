<?php
/**
 * Internationalization utilities.
 *
 * @package ClubEasyEvent\Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles loading translations and locale switching.
 */
class CEE_I18n {

	/**
	 * Flag indicating whether switch_to_locale() was called.
	 *
	 * @var bool
	 */
	protected static $did_switch = false;

	/**
	 * Load the plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		add_filter( 'override_load_textdomain', array( __CLASS__, 'override_load_textdomain' ), 10, 3 );
		load_plugin_textdomain( 'club-easy-event', false, dirname( plugin_basename( __FILE__ ), 2 ) . '/languages/' );
		remove_filter( 'override_load_textdomain', array( __CLASS__, 'override_load_textdomain' ), 10 );
	}

	/**
	 * Check if a multilingual plugin already manages locales.
	 *
	 * @return bool
	 */
	public static function is_multilingual_plugin_active() {
		return defined( 'ICL_SITEPRESS_VERSION' ) || function_exists( 'wpml_init' ) || function_exists( 'pll_current_language' );
	}

	/**
	 * Resolve the plugin languages directory path.
	 *
	 * @return string
	 */
	protected static function get_languages_path() {
		return trailingslashit( dirname( __FILE__, 2 ) ) . 'languages/';
	}

	/**
	 * Locate a PO file corresponding to a MO path.
	 *
	 * @param string $mofile Requested MO file.
	 *
	 * @return string|null
	 */
	protected static function locate_po_from_mo( $mofile ) {
		if ( empty( $mofile ) ) {
			return null;
		}

		$possible = array();
		if ( is_string( $mofile ) ) {
			$possible[] = preg_replace( '/\.mo$/', '.po', $mofile );
		}

		$filename = basename( $mofile );
		if ( ! empty( $filename ) ) {
			$possible[] = self::get_languages_path() . preg_replace( '/\.mo$/', '.po', $filename );
		}

		foreach ( $possible as $candidate ) {
			if ( $candidate && file_exists( $candidate ) ) {
				return $candidate;
			}
		}

		return null;
	}

	/**
	 * Get plugin supported locales.
	 *
	 * @return array
	 */
	public static function get_supported_locales() {
		return array( 'fr_FR', 'en_US', 'es_ES' );
	}

	/**
	 * Normalize a language string into one of the supported locales.
	 *
	 * @param string $lang Language string.
	 *
	 * @return string|null
	 */
	public static function normalize_lang_to_locale( $lang ) {
		if ( ! is_string( $lang ) ) {
			return null;
		}

		$lang = trim( $lang );
		if ( '' === $lang ) {
			return null;
		}

		$lang_lower = strtolower( str_replace( '-', '_', $lang ) );
		foreach ( self::get_supported_locales() as $locale ) {
			if ( strtolower( $locale ) === $lang_lower ) {
				return $locale;
			}
		}

		$parts    = explode( '_', $lang_lower );
		$language = $parts[0];
		if ( 'fr' === $language ) {
			return 'fr_FR';
		}
		if ( 'en' === $language ) {
			return 'en_US';
		}
		if ( 'es' === $language ) {
			return 'es_ES';
		}

		return null;
	}

	/**
	 * Detect the best locale from an HTTP Accept-Language header.
	 *
	 * @param string $header Header value.
	 *
	 * @return string|null
	 */
	public static function detect_browser_locale( $header ) {
		if ( empty( $header ) || ! is_string( $header ) ) {
			return null;
		}

		$candidates = array();
		$parts      = explode( ',', $header );

		foreach ( $parts as $part ) {
			$part = trim( $part );
			if ( '' === $part ) {
				continue;
			}

			$segments = explode( ';', $part );
			$lang     = trim( $segments[0] );
			if ( '' === $lang ) {
				continue;
			}

			$quality = 1.0;
			foreach ( array_slice( $segments, 1 ) as $segment ) {
				$segment = trim( $segment );
				if ( 0 === strpos( $segment, 'q=' ) ) {
					$value = substr( $segment, 2 );
					if ( is_numeric( $value ) ) {
						$quality = (float) $value;
					}
					break;
				}
			}

			$locale = self::normalize_lang_to_locale( $lang );
			if ( ! $locale ) {
				continue;
			}

			if ( ! isset( $candidates[ $locale ] ) || $quality > $candidates[ $locale ] ) {
				$candidates[ $locale ] = $quality;
			}
		}

		if ( empty( $candidates ) ) {
			return null;
		}

		arsort( $candidates, SORT_NUMERIC );
		foreach ( array_keys( $candidates ) as $locale ) {
			if ( in_array( $locale, self::get_supported_locales(), true ) ) {
				return $locale;
			}
		}

		return null;
	}

	/**
	 * Maybe switch the locale for the front end.
	 *
	 * @return void
	 */
	public static function maybe_switch_locale() {
		if ( self::is_multilingual_plugin_active() ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		if ( ! function_exists( 'switch_to_locale' ) ) {
			return;
		}

		$supported     = self::get_supported_locales();
		$target_locale = null;

		if ( is_user_logged_in() ) {
			$user_locale = get_user_locale();
			$normalized  = self::normalize_lang_to_locale( $user_locale );
			if ( $normalized && in_array( $normalized, $supported, true ) ) {
				$target_locale = $normalized;
			}
		}

		if ( ! $target_locale ) {
			$header         = filter_input( INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_SPECIAL_CHARS );
			$browser_locale = self::detect_browser_locale( $header );
			if ( $browser_locale && in_array( $browser_locale, $supported, true ) ) {
				$target_locale = $browser_locale;
			}
		}

		if ( ! $target_locale ) {
			$site_locale = get_locale();
			$normalized  = self::normalize_lang_to_locale( $site_locale );
			if ( $normalized && in_array( $normalized, $supported, true ) ) {
				$target_locale = $normalized;
			}
		}

		if ( ! $target_locale ) {
			return;
		}

		$current_locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
		$current_locale = self::normalize_lang_to_locale( $current_locale );
		if ( $current_locale === $target_locale ) {
			return;
		}

		if ( switch_to_locale( $target_locale ) ) {
			self::$did_switch = true;
		}
	}

	/**
	 * Restore the original locale if switched.
	 *
	 * @return void
	 */
	public static function restore_locale() {
		if ( self::$did_switch && function_exists( 'restore_previous_locale' ) ) {
			restore_previous_locale();
			self::$did_switch = false;
		}
	}

	/**
	 * Provide a PO-based fallback when MO files cannot be loaded.
	 *
	 * @param bool   $override Whether to override the text domain loading.
	 * @param string $domain   Text domain.
	 * @param string $mofile   Path to the MO file.
	 *
	 * @return bool
	 */
	public static function override_load_textdomain( $override, $domain, $mofile ) {
		if ( $override || 'club-easy-event' !== $domain ) {
			return $override;
		}

		if ( $mofile && file_exists( $mofile ) ) {
			return $override;
		}

		$po_file = self::locate_po_from_mo( $mofile );
		if ( ! $po_file ) {
			return $override;
		}

		if ( ! class_exists( 'PO' ) ) {
			require_once ABSPATH . 'wp-includes/pomo/po.php';
		}
		if ( ! class_exists( 'MO' ) ) {
			require_once ABSPATH . 'wp-includes/pomo/mo.php';
		}

		$po = new PO();
		if ( ! $po->import_from_file( $po_file ) ) {
			return $override;
		}

		$mo = new MO();
		foreach ( $po->entries as $key => $entry ) {
			$mo->entries[ $key ] = $entry;
		}
		if ( ! empty( $po->headers ) ) {
			$mo->set_headers( $po->headers );
		}

		$GLOBALS['l10n']['club-easy-event'] = $mo;

		return true;
	}
}
