<?php
/**
 * Admin Page
 *
 * Utility class to determine if we're on a specific EDD admin page.
 *
 * @package   EDD\Admin\Utils
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.5.0
 */

namespace EDD\Admin\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\Request;

/**
 * Class Page
 *
 * @since 3.5.0
 */
class Page {

	/**
	 * Static cache for admin page results.
	 *
	 * @since 3.5.0
	 * @var array
	 */
	private static $cache = array();

	/**
	 * The passed page slug.
	 *
	 * @var string
	 */
	private string $passed_page;

	/**
	 * The passed view.
	 *
	 * @var string
	 */
	private string $passed_view;

	/**
	 * Whether to include non-exclusive pages.
	 *
	 * @var bool
	 */
	private bool $include_non_exclusive;

	/**
	 * The current page slug.
	 *
	 * @var bool|string
	 */
	private static $page;

	/**
	 * The current view.
	 *
	 * @var bool|string
	 */
	private static $view;

	/**
	 * Whether the global $typenow is set to 'download'.
	 *
	 * @var bool
	 */
	private $is_download_typenow;

	/**
	 * Whether the current post type is 'download'.
	 *
	 * @var bool
	 */
	private $is_download_post_type;

	/**
	 * Whether the current page is the 'edit.php' page.
	 *
	 * @var bool
	 */
	private $is_edit_pagenow;

	/**
	 * The current taxonomy.
	 *
	 * @var string
	 */
	private $taxonomy;

	/**
	 * The current action.
	 *
	 * @var string
	 */
	private $action;

	/**
	 * The current tab.
	 *
	 * @var string
	 */
	private $tab;

	/**
	 * Constructor for the Page class.
	 *
	 * @param string      $passed_page           Optional. Main page's slug.
	 * @param string      $passed_view           Optional. Page view ( ex: `edit` or `delete` ).
	 * @param bool        $include_non_exclusive Optional. If we should consider pages not exclusive to EDD.
	 *                                           Includes the main dashboard page and custom post types that
	 *                                           support the "Insert Download" button via the TinyMCE editor.
	 * @param bool|string $page                  Optional. The current page (from $_GET).
	 * @param bool|string $view                  Optional. The current view (from $_GET).
	 */
	public function __construct( string $passed_page = '', string $passed_view = '', bool $include_non_exclusive = true, $page = false, $view = false ) {
		$this->passed_page           = $passed_page;
		$this->passed_view           = $passed_view;
		$this->include_non_exclusive = $include_non_exclusive;
		self::$page                  = $page;
		self::$view                  = $view;
	}

	/**
	 * Static method to check if we're on an EDD admin page with caching.
	 *
	 * This method provides an optimized way to check admin page status
	 * with caching to avoid repeated computations. It handles the fact
	 * that $_GET and global state may change during the request lifecycle.
	 *
	 * @since 3.5.0
	 *
	 * @param string $passed_page           Optional. Main page's slug.
	 * @param string $passed_view           Optional. Page view.
	 * @param bool   $include_non_exclusive Optional. Include non-exclusive pages.
	 * @param mixed  $page                  Optional. Current page parameter (auto-detected if null).
	 * @param mixed  $view                  Optional. Current view parameter (auto-detected if null).
	 *
	 * @return bool True if on the specified EDD admin page.
	 */
	public static function is_admin( $passed_page = '', $passed_view = '', $include_non_exclusive = true, $page = null, $view = null ): bool {
		// Auto-detect page/view from $_GET if not provided.
		if ( is_null( $page ) ) {
			$page = self::cast_value( 'page' );
		}
		if ( is_null( $view ) ) {
			$view = self::cast_value( 'view' );
		}

		// Create cache key that includes current global state.
		$cache_key = self::get_cache_key( $passed_page, $passed_view, $include_non_exclusive, $page, $view );

		// Return cached result if available.
		if ( isset( self::$cache[ $cache_key ] ) ) {
			return self::$cache[ $cache_key ];
		}

		// Create instance and compute result.
		$instance = new self( $passed_page, $passed_view, $include_non_exclusive, $page, $view );
		$result   = $instance->check_is_admin();

		// Cache and return result.
		self::$cache[ $cache_key ] = $result;

		return $result;
	}

	/**
	 * Determines whether the current admin page is a specific EDD admin page.
	 *
	 * Only works after the `wp_loaded` hook, & most effective
	 * starting on `admin_menu` hook. Failure to pass in self::$view will match all views of $passed_page.
	 * Failure to pass in $passed_page will return true if on any EDD page
	 *
	 * @since 3.5.0
	 *
	 * @return bool True if EDD admin page we're looking for or an EDD page or if self::$page is empty, any EDD page
	 */
	private function check_is_admin(): bool {

		if ( ! Request::is_request( 'admin' ) || Request::is_request( 'ajax' ) ) {
			return false;
		}

		$found = false;
		switch ( $this->passed_page ) {
			case 'download':
				$found = $this->is_download();
				break;
			case 'categories':
				$found = $this->is_categories();
				break;
			case 'tags':
				$found = $this->is_tags();
				break;
			case 'payments':
				$found = $this->is_payments();
				break;
			case 'discounts':
				$found = $this->is_discounts();
				break;
			case 'reports':
				$found = $this->is_reports();
				break;
			case 'settings':
				$found = $this->is_settings();
				break;
			case 'tools':
				$found = $this->is_tools();
				break;
			case 'addons':
				$found = $this->is_download_screen() && $this->is_edit_pagenow() && 'edd-addons' === self::$page;
				break;
			case 'customers':
				$found = $this->is_customers();
				break;
			case 'index.php':
				global $pagenow;
				if ( 'index.php' === $pagenow ) {
					$found = true;
				}
				break;

			default:
				$found = $this->is_default();
				break;
		}

		return $found;
	}

	/**
	 * Clear the static cache.
	 *
	 * This method can be used to clear the cache if needed during testing
	 * or when global state changes significantly.
	 *
	 * @since 3.5.0
	 */
	public static function clear_cache() {
		self::$cache = array();
	}

	/**
	 * Generate a cache key that includes current global and $_GET state.
	 *
	 * This ensures that cache results are invalidated when the state changes
	 * during the request lifecycle (e.g., $_GET gets populated by later hooks).
	 *
	 * @since 3.5.0
	 *
	 * @param string $passed_page           The passed page parameter.
	 * @param string $passed_view           The passed view parameter.
	 * @param bool   $include_non_exclusive The include_non_exclusive flag.
	 * @param mixed  $page                  The current page parameter.
	 * @param mixed  $view                  The current view parameter.
	 *
	 * @return string The cache key.
	 */
	private static function get_cache_key( $passed_page, $passed_view, $include_non_exclusive, $page, $view ) {
		global $pagenow, $typenow;

		// Get only the $_GET parameters that are relevant to admin page detection.
		$relevant_get_keys = array( 'post_type', 'taxonomy', 'action', 'tab' );
		$relevant_get      = array();

		foreach ( $relevant_get_keys as $key ) {
			if ( isset( $_GET[ $key ] ) ) {
				$relevant_get[ $key ] = $_GET[ $key ];
			}
		}

		// Create cache key from all relevant state.
		$cache_data = array(
			'passed_page'           => $passed_page,
			'passed_view'           => $passed_view,
			'include_non_exclusive' => $include_non_exclusive,
			'page'                  => $page,
			'view'                  => $view,
			'pagenow'               => $pagenow,
			'typenow'               => $typenow,
			'relevant_get'          => $relevant_get,
			'is_admin'              => is_admin(),
		);

		return md5( json_encode( $cache_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	}

	/**
	 * Whether this is a downloads screen.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_download(): bool {
		if ( ! $this->is_download_screen() ) {
			return false;
		}
		global $pagenow;
		$found = false;
		switch ( $this->passed_view ) {
			case 'list-table':
				if ( $this->is_edit_pagenow() ) {
					$found = true;
				}
				break;
			case 'edit':
				if ( 'post.php' === $pagenow ) {
					$found = true;
				}
				break;
			case 'new':
				if ( 'post-new.php' === $pagenow ) {
					$found = true;
				}
				break;
			default:
				// When no specific view is requested, match any download-related page.
				$download_pages = array( 'edit.php', 'post.php', 'post-new.php' );
				if ( in_array( $pagenow, $download_pages, true ) ) {
					$found = true;
				}
				break;
		}

		return $found;
	}

	/**
	 * Whether this is a downloads categories screen.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_categories(): bool {
		if ( ! $this->is_download_screen() ) {
			return false;
		}
		global $pagenow;
		if ( 'edit-tags.php' !== $pagenow ) {
			return false;
		}
		if ( 'download_category' !== $this->get_taxonomy() ) {
			return false;
		}

		$found = false;
		switch ( $this->passed_view ) {
			case 'list-table':
			case 'new':
				if ( 'edit' !== $this->get_action() ) {
					$found = true;
				}
				break;
			case 'edit':
				if ( 'edit' === $this->get_action() ) {
					$found = true;
				}
				break;
			default:
				$found = true;
				break;
		}

		return $found;
	}

	/**
	 * Whether this is a downloads tags screen.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_tags(): bool {
		if ( ! $this->is_download_screen() ) {
			return false;
		}
		global $pagenow;
		if ( 'edit-tags.php' !== $pagenow ) {
			return false;
		}
		if ( 'download_tag' !== $this->get_taxonomy() ) {
			return false;
		}

		$found = false;
		switch ( $this->passed_view ) {
			case 'list-table':
			case 'new':
				if ( 'edit' !== $this->get_action() ) {
					$found = true;
				}
				break;
			case 'edit':
				if ( 'edit' === $this->get_action() ) {
					$found = true;
				}
				break;
			default:
				$found = true;
				break;
		}

		return $found;
	}

	/**
	 * Whether this is a discounts screen.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_discounts(): bool {
		if ( ! $this->is_download_screen() ) {
			return false;
		}
		if ( 'edd-discounts' !== self::$page ) {
			return false;
		}
		if ( ! $this->is_edit_pagenow() ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether this is an orders screen.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_payments(): bool {
		if ( ! $this->is_download_screen() ) {
			return false;
		}
		if ( 'edd-payment-history' !== self::$page ) {
			return false;
		}
		if ( ! $this->is_edit_pagenow() ) {
			return false;
		}

		$found = false;
		switch ( $this->passed_view ) {
			case 'list-table':
				if ( false === self::$view ) {
					$found = true;
				}
				break;
			case 'edit':
				if ( 'view-order-details' === self::$view ) {
					$found = true;
				}
				break;
			default:
				$found = true;
				break;
		}

		return $found;
	}

	/**
	 * Whether this is a reports screen.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_reports(): bool {
		if ( ! $this->is_download_screen() ) {
			return false;
		}
		if ( 'edd-reports' !== self::$page ) {
			return false;
		}
		if ( ! $this->is_edit_pagenow() ) {
			return false;
		}

		$found = false;
		$views = array( 'earnings', 'downloads', 'customers', 'gateways', 'taxes', 'export' );
		if ( in_array( $this->passed_view, $views, true ) ) {
			return true;
		}

		switch ( $this->passed_view ) {
			// If you want to do something like enqueue a script on a particular report's duration, look at $_GET[ 'range' ].
			case 'earnings':
				if ( 'earnings' === self::$view || '-1' === self::$view || false === self::$view ) {
					$found = true;
				}
				break;
			default:
				$found = true;
				break;
		}

		return $found;
	}

	/**
	 * Whether this is a settings screen.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_settings(): bool {
		if ( ! $this->is_download_screen() ) {
			return false;
		}
		if ( 'edd-settings' !== self::$page ) {
			return false;
		}
		if ( ! $this->is_edit_pagenow() ) {
			return false;
		}

		// If a specific view is passed, check if it matches the current tab and is a valid settings tab.
		if ( ! empty( $this->passed_view ) ) {
			if ( 'general' === $this->passed_view ) {
				return true;
			}

			return $this->get_tab() === $this->passed_view && in_array( $this->passed_view, array_keys( \edd_get_settings_tabs() ), true );
		}

		return true;
	}

	/**
	 * Whether this is a tools screen.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_tools(): bool {
		if ( ! $this->is_download_screen() ) {
			return false;
		}
		if ( 'edd-tools' !== self::$page ) {
			return false;
		}
		if ( ! $this->is_edit_pagenow() ) {
			return false;
		}

		$found = false;
		switch ( $this->passed_view ) {
			case 'general':
				if ( 'general' === $this->get_tab() || false === $this->get_tab() ) {
					$found = true;
				}
				break;
			case 'api_keys':
				if ( 'api_keys' === $this->get_tab() ) {
					$found = true;
				}
				break;
			case 'system_info':
				if ( 'system_info' === $this->get_tab() ) {
					$found = true;
				}
				break;
			case 'logs':
				if ( 'logs' === $this->get_tab() ) {
					$found = true;
				}
				break;
			case 'import_export':
				if ( 'import_export' === $this->get_tab() ) {
					$found = true;
				}
				break;
			default:
				$found = true;
				break;
		}

		return $found;
	}

	/**
	 * Whether this is a customers screen.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_customers(): bool {
		if ( ! $this->is_download_screen() ) {
			return false;
		}
		if ( 'edd-customers' !== self::$page ) {
			return false;
		}
		if ( ! $this->is_edit_pagenow() ) {
			return false;
		}

		$found = false;
		switch ( $this->passed_view ) {
			case 'list-table':
				if ( false === self::$view ) {
					$found = true;
				}
				break;
			case 'overview':
				if ( 'overview' === self::$view ) {
					$found = true;
				}
				break;
			case 'notes':
				if ( 'notes' === self::$view ) {
					$found = true;
				}
				break;
			default:
				$found = true;
				break;
		}

		return $found;
	}

	/**
	 * Whether this is a default EDD admin page.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_default(): bool {
		global $pagenow;
		// Downloads sub-page or Dashboard page.
		if ( ( $this->is_download_typenow() ) || ( $this->include_non_exclusive && 'index.php' === $pagenow ) ) {
			return true;
		}

		// Registered global pages.
		if ( function_exists( '\edd_get_admin_pages' ) && in_array( $pagenow, \edd_get_admin_pages(), true ) ) {
			return true;
		}

		// Supported post types.
		if ( $this->include_non_exclusive && function_exists( '\edd_is_insertable_admin_page' ) && \edd_is_insertable_admin_page() ) {
			return true;
		}

		// The EDD settings screen (fallback if mislinked).
		return 'edd-settings' === self::$page;
	}

	/**
	 * Whether this is a downloads screen.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_download_screen(): bool {
		return $this->is_download_typenow() || $this->is_download_post_type();
	}

	/**
	 * Whether the global $typenow is `download`.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_download_typenow(): bool {
		if ( ! is_null( $this->is_download_typenow ) ) {
			return $this->is_download_typenow;
		}
		global $typenow;

		$this->is_download_typenow = (bool) ( 'download' === $typenow );

		return $this->is_download_typenow;
	}

	/**
	 * Check if the current page is the Downloads post type.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_download_post_type(): bool {
		if ( ! is_null( $this->is_download_post_type ) ) {
			return $this->is_download_post_type;
		}

		$this->is_download_post_type = (bool) ( ! empty( $_GET['post_type'] ) && 'download' === $_GET['post_type'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return $this->is_download_post_type;
	}

	/**
	 * Whether the global $pagenow is `edit.php`.
	 *
	 * @since 3.5.0
	 * @return bool
	 */
	private function is_edit_pagenow(): bool {
		if ( ! is_null( $this->is_edit_pagenow ) ) {
			return $this->is_edit_pagenow;
		}
		global $pagenow;

		$this->is_edit_pagenow = (bool) ( 'edit.php' === $pagenow );

		return $this->is_edit_pagenow;
	}

	/**
	 * Gets the current taxonomy.
	 *
	 * @since 3.5.0
	 * @return string|false
	 */
	private function get_taxonomy() {
		if ( ! is_null( $this->taxonomy ) ) {
			return $this->taxonomy;
		}
		$this->taxonomy = self::cast_value( 'taxonomy' );

		return $this->taxonomy;
	}

	/**
	 * Gets the current action.
	 *
	 * @since 3.5.0
	 * @return string|false
	 */
	private function get_action() {
		if ( ! is_null( $this->action ) ) {
			return $this->action;
		}
		$this->action = self::cast_value( 'action' );

		return $this->action;
	}

	/**
	 * Gets the current tab.
	 *
	 * @since 3.5.0
	 * @return string|false
	 */
	private function get_tab() {
		if ( ! is_null( $this->tab ) ) {
			return $this->tab;
		}
		$this->tab = self::cast_value( 'tab' );

		return $this->tab;
	}

	/**
	 * Casts a value to a string or false.
	 *
	 * @since 3.5.0
	 * @param string $parameter The parameter to cast.
	 * @return string|false
	 */
	private static function cast_value( string $parameter ) {
		if ( ! empty( $_GET[ $parameter ] ) && is_string( $_GET[ $parameter ] ) ) {
			return strtolower( sanitize_text_field( $_GET[ $parameter ] ) );
		}

		return false;
	}
}
