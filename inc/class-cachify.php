<?php
/**
 * Class for initializing the hooks and actions.
 *
 * @package Cachify
 */

/* Quit */
defined( 'ABSPATH' ) || exit;

/**
 * Cachify
 */
final class Cachify {

	/**
	 * Plugin options
	 *
	 * @var array
	 *
	 * @since 2.0
	 */
	private static $options;

	/**
	 * Caching method
	 *
	 * @var object
	 *
	 * @since 2.0
	 */
	private static $method;

	/**
	 * Whether we are on a Nginx server or not.
	 *
	 * @var bool
	 *
	 * @since 2.2.5
	 */
	private static $is_nginx;

	/**
	 * Method settings
	 *
	 * @var int
	 *
	 * @since 2.0.9
	 */
	const METHOD_DB  = 0;
	const METHOD_APC = 1;  // No longer available.
	const METHOD_HDD = 2;
	const METHOD_MMC = 3;
	const METHOD_REDIS = 4;

	/**
	 * Minify settings
	 *
	 * @var int
	 *
	 * @since 2.0.9
	 */
	const MINIFY_DISABLED  = 0;
	const MINIFY_HTML_ONLY = 1;
	const MINIFY_HTML_JS   = 2;

	/**
	 * REST endpoints
	 *
	 * @var string
	 */
	const REST_NAMESPACE   = 'cachify/v1';
	const REST_ROUTE_FLUSH = 'flush';

	/**
	 * Pseudo constructor
	 *
	 * @since 2.0.5
	 */
	public static function instance() {
		new self();
	}

	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	public function __construct() {
		/* Set defaults */
		self::_set_default_vars();

		self::$is_nginx = $GLOBALS['is_nginx'];

		/* Flush Hooks */
		add_action( 'init', array( __CLASS__, 'register_flush_cache_hooks' ), 10, 0 );
		add_action( 'post_updated', array( __CLASS__, 'save_update_trash_post' ), 10, 3 );
		add_action( 'pre_post_update', array( __CLASS__, 'post_update' ), 10, 2 );
		add_action( 'cachify_remove_post_cache', array( __CLASS__, 'remove_page_cache_by_post_id' ) );
		add_action( 'comment_post', array( __CLASS__, 'new_comment' ), 99, 2 );
		add_action( 'edit_comment', array( __CLASS__, 'comment_edit' ), 10, 2 );
		add_action( 'transition_comment_status', array( __CLASS__, 'comment_status' ), 10, 3 );

		/* Flush Hooks - third party */
		add_action( 'woocommerce_product_set_stock', array( __CLASS__, 'flush_woocommerce' ) );
		add_action( 'woocommerce_variation_set_stock', array( __CLASS__, 'flush_woocommerce' ) );
		add_action( 'woocommerce_product_set_stock_status', array( __CLASS__, 'flush_woocommerce' ) );
		add_action( 'woocommerce_variation_set_stock_status', array( __CLASS__, 'flush_woocommerce' ) );

		/* Register scripts */
		add_action( 'init', array( __CLASS__, 'register_scripts' ) );

		/* Register styles */
		add_action( 'init', array( __CLASS__, 'register_styles' ) );

		/* Flush icon */
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_flush_icon' ), 90 );

		/* Flush icon script */
		add_action( 'admin_bar_menu', array( __CLASS__, 'add_flush_icon_script' ), 90 );

		/* Flush REST endpoint */
		add_action( 'rest_api_init', array( __CLASS__, 'add_flush_rest_endpoint' ) );

		add_action( 'init', array( __CLASS__, 'process_flush_request' ) );

		/* Add Cron for clearing the HDD Cache */
		if ( self::METHOD_HDD === self::$options['use_apc'] ) {
			add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_cache_expiration' ) );

			$timestamp = wp_next_scheduled( 'hdd_cache_cron' );
			if ( false === $timestamp ) {
				wp_schedule_event( time(), 'cachify_cache_expire', 'hdd_cache_cron' );
			}

			add_action( 'hdd_cache_cron', array( __CLASS__, 'run_hdd_cache_cron' ) );
		}

		if ( is_admin() ) {
			/* Backend */
			if ( version_compare( get_bloginfo( 'version' ), '5.1', '<' ) ) {
				// The following hooks are deprecated since WP 5.1 (#246).
				add_action( 'wpmu_new_blog', array( __CLASS__, 'install_later' ) );
				add_action( 'delete_blog', array( __CLASS__, 'uninstall_later' ) );
			} else {
				add_action( 'wp_initialize_site', array( __CLASS__, 'install_later' ) );
				add_action( 'wp_delete_site', array( __CLASS__, 'uninstall_later' ) );
			}

			add_action( 'admin_init', array( __CLASS__, 'register_textdomain' ) );

			add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );

			add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_admin_resources' ) );

			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_dashboard_styles' ) );

			add_filter( 'dashboard_glance_items', array( __CLASS__, 'add_dashboard_count' ) );

			add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );

			add_filter( 'plugin_action_links_' . CACHIFY_BASE, array( __CLASS__, 'action_links' ) );

		} else {
			/* Frontend */
			add_action( 'template_redirect', array( __CLASS__, 'manage_cache' ), 0 );
			add_filter( 'robots_txt', array( __CLASS__, 'robots_txt' ) );
		}
	}

	/**
	 * Deactivation hook
	 *
	 * @since 2.1.0
	 */
	public static function on_deactivation() {
		/* Remove hdd cache cron when hdd is selected */
		if ( self::METHOD_HDD === self::$options['use_apc'] ) {
			$timestamp = wp_next_scheduled( 'hdd_cache_cron' );
			if ( false !== $timestamp ) {
				wp_unschedule_event( $timestamp, 'hdd_cache_cron' );
			}
		}

		self::flush_total_cache( true );
	}

	/**
	 * Activation hook
	 *
	 * @since 1.0
	 */
	public static function on_activation() {
		/* Multisite & Network */
		if ( is_multisite() && ! empty( $_GET['networkwide'] ) ) {
			/* Blog IDs */
			$ids = self::_get_blog_ids();

			/* Loop over blogs */
			foreach ( $ids as $id ) {
				switch_to_blog( $id );
				self::_install_backend();
			}

			/* Switch back */
			restore_current_blog();

		} else {
			self::_install_backend();
		}
	}

	/**
	 * Plugin installation on new WPMS site.
	 *
	 * @param int|WP_Site $new_site New site ID or object.
	 *
	 * @since 1.0
	 * @since 2.4.0 supports WP_Site argument
	 */
	public static function install_later( $new_site ) {
		/* No network plugin */
		if ( ! is_plugin_active_for_network( CACHIFY_BASE ) ) {
			return;
		}

		/* Switch to blog */
		switch_to_blog( is_int( $new_site ) ? $new_site : $new_site->blog_id );

		/* Install */
		self::_install_backend();

		/* Switch back */
		restore_current_blog();
	}

	/**
	 * Actual installation of the options
	 *
	 * @since 1.0
	 */
	private static function _install_backend() {
		add_option(
			'cachify',
			array()
		);

		/* Flush */
		self::flush_total_cache( true );
	}

	/**
	 * Uninstalling of the plugin per MU blog.
	 *
	 * @since 1.0
	 */
	public static function on_uninstall() {
		/* Global */
		global $wpdb;

		/* Multisite & Network */
		if ( is_multisite() && ! empty( $_GET['networkwide'] ) ) {
			/* Alter Blog */
			$old = $wpdb->blogid;

			/* Blog IDs */
			$ids = self::_get_blog_ids();

			/* Loop */
			foreach ( $ids as $id ) {
				switch_to_blog( $id );
				self::_uninstall_backend();
			}

			/* Switch back */
			switch_to_blog( $old );
		} else {
			self::_uninstall_backend();
		}
	}

	/**
	 * Uninstalling of the plugin for WPMS site.
	 *
	 * @param int|WP_Site $old_site Old site ID or object.
	 *
	 * @since 1.0
	 * @since 2.4.0 supports WP_Site argument
	 */
	public static function uninstall_later( $old_site ) {
		/* No network plugin */
		if ( ! is_plugin_active_for_network( CACHIFY_BASE ) ) {
			return;
		}

		/* Switch to blog */
		switch_to_blog( is_int( $old_site ) ? $old_site : $old_site->blog_id );

		/* Install */
		self::_uninstall_backend();

		/* Switch back */
		restore_current_blog();
	}

	/**
	 * Actual uninstalling of the plugin
	 *
	 * @since 1.0
	 */
	private static function _uninstall_backend() {
		/* Option */
		delete_option( 'cachify' );

		/* Flush cache */
		self::flush_total_cache( true );
	}

	/**
	 * Get IDs of installed blogs
	 *
	 * @return array Blog IDs
	 *
	 * @since 1.0
	 */
	private static function _get_blog_ids() {
		/* Global */
		global $wpdb;

		return $wpdb->get_col( "SELECT blog_id FROM `$wpdb->blogs`" );
	}

	/**
	 * Register the styles
	 *
	 * @since 2.4.0
	 */
	public static function register_styles() {
		/* Register dashboard CSS */
		wp_register_style(
			'cachify-dashboard',
			plugins_url( 'css/dashboard.min.css', CACHIFY_FILE ),
			array(),
			filemtime( plugin_dir_path( CACHIFY_FILE ) . 'css/dashboard.min.css' )
		);

		/* Register admin bar flush CSS */
		wp_register_style(
			'cachify-admin-bar-flush',
			plugins_url( 'css/admin-bar-flush.min.css', CACHIFY_FILE ),
			array(),
			filemtime( plugin_dir_path( CACHIFY_FILE ) . 'css/admin-bar-flush.min.css' )
		);
	}

	/**
	 * Register the scripts
	 *
	 * @since 2.4.0
	 */
	public static function register_scripts() {
		/* Register admin bar flush script */
		wp_register_script(
			'cachify-admin-bar-flush',
			plugins_url( 'js/admin-bar-flush.min.js', CACHIFY_FILE ),
			array(),
			filemtime( plugin_dir_path( CACHIFY_FILE ) . 'js/admin-bar-flush.min.js' ),
			true
		);
	}

	/**
	 * Register the language file
	 *
	 * @since 2.1.3
	 */
	public static function register_textdomain() {
		load_plugin_textdomain( 'cachify' );
	}

	/**
	 * Set default options
	 *
	 * @since 2.0
	 */
	private static function _set_default_vars() {
		/* Options */
		self::$options = self::_get_options();

		if ( self::METHOD_APC === self::$options['use_apc'] ) {
			/* APC */
			add_action( 'admin_notices', array( __CLASS__, 'admin_notice_unavailable' ) );
			self::$method = new Cachify_NOOP( 'APC' );
		} elseif ( self::METHOD_HDD === self::$options['use_apc'] ) {
			/* HDD */
			if ( Cachify_HDD::is_available() ) {
				self::$method = new Cachify_HDD();
			} else {
				add_action( 'admin_notices', array( __CLASS__, 'admin_notice_unavailable' ) );
				self::$method = new Cachify_NOOP( Cachify_HDD::stringify_method() );
			}
		} elseif ( self::METHOD_MMC === self::$options['use_apc'] ) {
			/* Memcached */
			if ( Cachify_MEMCACHED::is_available() ) {
				self::$method = new Cachify_MEMCACHED();
			} else {
				add_action( 'admin_notices', array( __CLASS__, 'admin_notice_unavailable' ) );
				self::$method = new Cachify_NOOP( Cachify_MEMCACHED::stringify_method() );
			}
		} elseif ( self::METHOD_REDIS === self::$options['use_apc'] ) {
			/* Redis */
			if ( Cachify_REDIS::is_available() ) {
				self::$method = new Cachify_REDIS();
			} else {
				add_action( 'admin_notices', array( __CLASS__, 'admin_notice_unavailable' ) );
				self::$method = new Cachify_NOOP( Cachify_REDIS::stringify_method() );
			}
		} else {
			/* Database */
			self::$method = new Cachify_DB();
		}
	}

	/**
	 * Show admin notice if caching backend is unavailable.
	 *
	 * @since 2.4.0
	 */
	public static function admin_notice_unavailable() {
		if ( current_user_can( 'manage_options' ) ) {
			$unavailable_method = '-';
			if ( self::$method instanceof Cachify_NOOP ) {
				$unavailable_method = self::$method->unavailable_method;
			}

			printf(
				'<div class="notice notice-warning is-dismissible"><p><strong>%1$s</strong></p><p>%2$s</p><p>%3$s</p></div>',
				esc_html__( 'Cachify backend not available', 'cachify' ),
				esc_html(
					sprintf(
						/* translators: Name of the caching backend inserted for placeholder */
						__( 'The configured caching backend is not available: %s', 'cachify' ),
						$unavailable_method
					)
				),
				wp_kses(
					sprintf(
						/* translators: Link to Cachify settings page inserted at placeholder */
						__( 'Please check your server configuration and visit the <a href="%s">settings page</a> to chose a different method.', 'cachify' ),
						add_query_arg( array( 'page' => 'cachify' ), admin_url( 'options-general.php' ) )
					),
					array( 'a' => array( 'href' => array() ) )
				)
			);
		}
	}

	/**
	 * Get options
	 *
	 * @return array Array of option values
	 *
	 * @since 2.0
	 */
	private static function _get_options() {
		return wp_parse_args(
			get_option( 'cachify' ),
			array(
				'only_guests'       => 1,
				'compress_html'     => self::MINIFY_DISABLED,
				'cache_expires'     => 12,
				'without_ids'       => '',
				'without_agents'    => '',
				'use_apc'           => self::METHOD_DB,
				'reset_on_post'     => 1,
				'reset_on_comment'  => 0,
				'sig_detail'        => 0,
				'change_robots_txt' => 1,
			)
		);
	}

	/**
	 * Modify robots.txt
	 *
	 * @param string $output The robots.txt output.
	 *
	 * @since 1.0
	 * @since 2.1.9
	 */
	public static function robots_txt( $output ) {
		if ( ! self::$options['change_robots_txt'] ) {
			return $output;
		}
		/* HDD only */
		if ( self::METHOD_HDD === self::$options['use_apc'] ) {
			$output .= "\nUser-agent: *\nDisallow: */cache/cachify/\n";
		}

		return $output;
	}

	/**
	 * HDD Cache expiration cron action.
	 *
	 * @since 2.4.0
	 */
	public static function run_hdd_cache_cron() {
		Cachify_HDD::clear_cache();
	}

	/**
	 * Add cache expiration cron schedule.
	 *
	 * @param array $schedules Array of previously added non-default schedules.
	 *
	 * @return array Array of non-default schedules with our tasks added.
	 *
	 * @since 2.4.0
	 */
	public static function add_cron_cache_expiration( $schedules ) {
		$schedules['cachify_cache_expire'] = array(
			'interval' => self::$options['cache_expires'] * 3600,
			'display'  => esc_html__( 'Cachify expire', 'cachify' ),
		);
		return $schedules;
	}

	/**
	 * Add the action links
	 *
	 * @param array $data Initial array with action links.
	 *
	 * @return array Merged array with action links.
	 *
	 * @since 1.0
	 */
	public static function action_links( $data ) {
		/* Permissions? */
		if ( ! current_user_can( 'manage_options' ) ) {
			return $data;
		}

		return array_merge(
			$data,
			array(
				sprintf(
					'<a href="%s">%s</a>',
					add_query_arg(
						array(
							'page' => 'cachify',
						),
						admin_url( 'options-general.php' )
					),
					esc_html__( 'Settings', 'cachify' )
				),
			)
		);
	}

	/**
	 * Meta links of the plugin
	 *
	 * @param array  $input Initial array with meta links.
	 * @param string $page  Current page.
	 *
	 * @return array Merged array with meta links.
	 *
	 * @since 0.5
	 */
	public static function row_meta( $input, $page ) {
		/* Permissions */
		if ( CACHIFY_BASE !== $page ) {
			return $input;
		}

		return array_merge(
			$input,
			array(
				'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8CH5FPR88QYML" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Donate', 'cachify' ) . '</a>',
				'<a href="https://wordpress.org/support/plugin/cachify" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support', 'cachify' ) . '</a>',
			)
		);
	}

	/**
	 * Add cache properties to dashboard
	 *
	 * @param array $items Initial array with dashboard items.
	 *
	 * @return array Merged array with dashboard items.
	 *
	 * @since 2.0.0
	 */
	public static function add_dashboard_count( $items = array() ) {
		/* Skip */
		if ( ! current_user_can( 'manage_options' ) ) {
			return $items;
		}

		/* Cache size */
		$size = self::get_cache_size();

		/* Caching method */
		$method = call_user_func(
			array(
				self::$method,
				'stringify_method',
			)
		);

		/* Output of the cache size */
		$cachesize = ( 0 === $size )
			? esc_html__( 'Empty Cache', 'cachify' ) :
			/* translators: %s: cache size */
			sprintf( esc_html__( '%s Cache', 'cachify' ), size_format( $size ) );

		/* Right now item */
		$items[] = sprintf(
			'<a href="%s" title="%s" class="cachify-glance">
            <svg class="cachify-icon cachify-icon--%s" aria-hidden="true" role="img">
                <use href="%s#cachify-icon-%s" xlink:href="%s#cachify-icon-%s" />
            </svg> %s</a>',
			add_query_arg(
				array(
					'page' => 'cachify',
				),
				admin_url( 'options-general.php' )
			),
			sprintf(
				/* translators: 1: "Caching method label"; 2: Actual method. */
				esc_html__( '%1$s: %2$s', 'cachify' ),
				esc_html__( 'Caching method', 'cachify' ),
				esc_attr( strtolower( $method ) )
			),
			esc_attr( $method ),
			plugins_url( 'images/symbols.svg', CACHIFY_FILE ),
			esc_attr( strtolower( $method ) ),
			plugins_url( 'images/symbols.svg', CACHIFY_FILE ),
			esc_attr( strtolower( $method ) ),
			$cachesize
		);

		return $items;
	}

	/**
	 * Get the cache size
	 *
	 * @return int Cache size in bytes.
	 *
	 * @since 2.0.6
	 */
	public static function get_cache_size() {
		$size = get_transient( 'cachify_cache_size' );
		if ( ! $size ) {
			/* Read */
			$size = (int) call_user_func(
				array(
					self::$method,
					'get_stats',
				)
			);

			/* Save */
			set_transient(
				'cachify_cache_size',
				$size,
				60 * 15
			);
		}

		return $size;
	}

	/**
	 * Add flush icon to admin bar menu
	 *
	 * @hook    mixed cachify_user_can_flush_cache
	 *
	 * @param object $wp_admin_bar Object of menu items.
	 *
	 * @since 1.2
	 * @since 2.2.2
	 * @since 2.4.0 Adjust icon for flush request via AJAX
	 */
	public static function add_flush_icon( $wp_admin_bar ) {
		/* Quit */
		if ( ! is_admin_bar_showing() || ! apply_filters( 'cachify_user_can_flush_cache', current_user_can( 'manage_options' ) ) ) {
			return;
		}

		/* Enqueue style */
		wp_enqueue_style( 'cachify-admin-bar-flush' );

		/* Print area for aria live updates */
		echo '<span class="ab-aria-live-area screen-reader-text" aria-live="polite"></span>';
		/* Check if the flush action was used without AJAX */
		$dashicon_class = 'dashicons-trash';
		if ( isset( $_GET['_cachify'] ) && 'flushed' === $_GET['_cachify'] ) {
			$dashicon_class = self::get_dashicon_success_class();
		}

		/* Add menu item */
		$wp_admin_bar->add_menu(
			array(
				'id'     => 'cachify',
				'href'   => wp_nonce_url( add_query_arg( '_cachify', 'flush' ), '_cachify__flush_nonce' ), // esc_url in /wp-includes/class-wp-admin-bar.php#L438.
				'parent' => 'top-secondary',
				'title'  => '<span class="ab-icon dashicons ' . $dashicon_class . '" aria-hidden="true"></span>' .
										'<span class="ab-label">' .
											__(
												'Flush site cache',
												'cachify'
											) .
										'</span>',
				'meta'   => array(
					'title' => esc_html__( 'Flush the Cachify cache', 'cachify' ),
				),
			)
		);
	}

	/**
	 * Returns the dashicon class for the success state in admin bar flush button
	 *
	 * @return string
	 *
	 * @since 2.4.0
	 */
	public static function get_dashicon_success_class() {
		global $wp_version;
		if ( version_compare( $wp_version, '5.2', '<' ) ) {
			return 'dashicons-yes';
		}

		return 'dashicons-yes-alt';
	}

	/**
	 * Add a script to query the REST endpoint and animate the flush icon in admin bar menu
	 *
	 * @hook  mixed  cachify_user_can_flush_cache ?
	 *
	 * @param object $wp_admin_bar Object of menu items.
	 *
	 * @since 2.4.0
	 */
	public static function add_flush_icon_script( $wp_admin_bar ) {
		/* Quit */
		if ( ! is_admin_bar_showing() || ! apply_filters( 'cachify_user_can_flush_cache', current_user_can( 'manage_options' ) ) ) {
			return;
		}

		/* Enqueue script */
		wp_enqueue_script( 'cachify-admin-bar-flush' );

		/* Localize script */
		wp_localize_script(
			'cachify-admin-bar-flush',
			'cachify_admin_bar_flush_ajax_object',
			array(
				'url'              => esc_url_raw( rest_url( self::REST_NAMESPACE . '/' . self::REST_ROUTE_FLUSH ) ),
				'nonce'            => wp_create_nonce( 'wp_rest' ),
				'flushing'         => __( 'Flushing cache', 'cachify' ),
				'flushed'          => __( 'Cache flushed successfully', 'cachify' ),
				'dashicon_success' => self::get_dashicon_success_class(),
			)
		);
	}


	/**
	 * Registers an REST endpoint for the flush operation
	 *
	 * @since 2.4.0
	 */
	public static function add_flush_rest_endpoint() {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE_FLUSH,
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array(
					__CLASS__,
					'flush_cache',
				),
				'permission_callback' => array(
					__CLASS__,
					'user_can_manage_options',
				),
			)
		);
	}

	/**
	 * Check if user can manage options
	 *
	 * @return bool
	 *
	 * @since 2.4.0
	 */
	public static function user_can_manage_options() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Process plugin's meta actions
	 *
	 * @hook    mixed  cachify_user_can_flush_cache
	 *
	 * @param array $data Metadata of the plugin.
	 *
	 * @since 0.5
	 * @since 2.2.2
	 * @since 2.4.0  Extract cache flushing to own method and always redirect to referer with new value for `_cachify` param.
	 */
	public static function process_flush_request( $data ) {
		/* Skip if not a flush request */
		if ( empty( $_GET['_cachify'] ) || 'flush' !== $_GET['_cachify'] ) {
			return;
		}

		/* Check nonce */
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), '_cachify__flush_nonce' ) ) {
			return;
		}

		/* Skip if not necessary */
		if ( ! is_admin_bar_showing() || ! apply_filters( 'cachify_user_can_flush_cache', current_user_can( 'manage_options' ) ) ) {
			return;
		}

		/* Load on demand */
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		/* Flush cache */
		self::flush_cache();

		wp_safe_redirect(
			add_query_arg(
				'_cachify',
				'flushed',
				wp_get_referer()
			)
		);

		exit();
	}

	/**
	 * Flush cache
	 *
	 * @since 2.4.0
	 */
	public static function flush_cache() {
		/* Flush cache */
		if ( is_multisite() && is_network_admin() ) {
			/* Old blog */
			$old = $GLOBALS['wpdb']->blogid;

			/* Blog IDs */
			$ids = self::_get_blog_ids();

			/* Loop over blogs */
			foreach ( $ids as $id ) {
				switch_to_blog( $id );
				self::flush_total_cache();
			}

			/* Switch back to old blog */
			switch_to_blog( $old );

			/* Notice */
			if ( is_admin() ) {
				add_action( 'network_admin_notices', array( __CLASS__, 'flush_notice' ) );
			}
		} else {
			self::flush_total_cache();

			/* Notice */
			if ( is_admin() ) {
				add_action( 'admin_notices', array( __CLASS__, 'flush_notice' ) );
			}
		}

		/* Reschedule HDD Cache Cron */
		if ( self::METHOD_HDD === self::$options['use_apc'] ) {
			$timestamp = wp_next_scheduled( 'hdd_cache_cron' );
			if ( false !== $timestamp ) {
				wp_reschedule_event( $timestamp, 'cachify_cache_expire', 'hdd_cache_cron' );
				wp_unschedule_event( $timestamp, 'hdd_cache_cron' );
			}
		}

		if ( ! is_admin() ) {
			wp_safe_redirect(
				remove_query_arg(
					'_cachify',
					wp_get_referer()
				)
			);

			exit();
		}
	}

	/**
	 * Notice after successful flushing of the cache
	 *
	 * @hook  mixed  cachify_user_can_flush_cache
	 *
	 * @since 1.2
	 * @since 2.2.2
	 */
	public static function flush_notice() {
		/* No admin */
		if ( ! is_admin_bar_showing() || ! apply_filters( 'cachify_user_can_flush_cache', current_user_can( 'manage_options' ) ) ) {
			return;
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html__( 'Cachify cache is flushed.', 'cachify' )
		);
	}

	/**
	 * Remove page from cache or flush on comment edit
	 *
	 * @param int $id Comment ID.
	 *
	 * @since 0.1.0
	 * @since 2.1.2
	 *
	 * @deprecated 2.4.0 Use comment_edit($id, $comment) instead.
	 */
	public static function edit_comment( $id ) {
		self::comment_edit( $id, array( 'comment_approved' => 1 ) );
	}

	/**
	 * Remove page from cache or flush on comment edit.
	 *
	 * @param integer $id      Comment ID.
	 * @param array   $comment Comment data.
	 *
	 * @since 2.4.0 Replacement for edit_comment($id) with additional comment parameter.
	 */
	public static function comment_edit( $id, $comment ) {
		$approved = (int) $comment['comment_approved'];

		/* Approved comment? */
		if ( 1 === $approved ) {
			if ( self::$options['reset_on_comment'] ) {
				self::flush_total_cache();
			} else {
				self::remove_page_cache_by_post_id(
					get_comment( $id )->comment_post_ID
				);
			}
		}
	}

	/**
	 * Remove page from cache or flush on new comment
	 *
	 * @param mixed $approved Comment status.
	 * @param array $comment  Array of properties.
	 *
	 * @return mixed Comment status.
	 *
	 * @since 0.1
	 * @since 2.1.2
	 * @since 2.4.0 Replacement for edit_comment($id) with additional comment parameter.
	 */
	public static function pre_comment( $approved, $comment ) {
		self::new_comment( $comment['comment_ID'], $approved );

		return $approved;
	}

	/**
	 * Remove page from cache or flush on new comment
	 *
	 * @param integer|string $id       Comment ID.
	 * @param integer|string $approved Comment status.
	 *
	 * @since 0.1.0
	 * @since 2.1.2
	 * @since 2.4.0 Renamed with ID parameter instead of comment array.
	 */
	public static function new_comment( $id, $approved ) {
		/* Approved comment? */
		if ( 1 === $approved ) {
			if ( self::$options['reset_on_comment'] ) {
				self::flush_total_cache();
			} else {
				self::remove_page_cache_by_post_id( get_comment( $id )->comment_post_ID );
			}
		}
	}

	/**
	 * Remove page from cache or flush on comment edit
	 *
	 * @param string $new_status New status.
	 * @param string $old_status Old status.
	 * @param object $comment    The comment.
	 *
	 * @since 0.1
	 * @since 2.1.2
	 *
	 * @deprecated 2.4.0 Use comment_status($new_status, $old_status, $comment) instead.
	 */
	public static function touch_comment( $new_status, $old_status, $comment ) {
		self::comment_status( $new_status, $old_status, $comment );
	}

	/**
	 * Remove page from cache or flush on comment edit.
	 *
	 * @param string     $new_status New status.
	 * @param string     $old_status Old status.
	 * @param WP_Comment $comment    The comment.
	 *
	 * @since 0.1
	 * @since 2.1.2
	 * @since 2.4.0 Renamed from touch_comment().
	 */
	public static function comment_status( $new_status, $old_status, $comment ) {
		if ( 'approved' === $old_status || 'approved' === $new_status ) {
			if ( self::$options['reset_on_comment'] ) {
				self::flush_total_cache();
			} else {
				self::remove_page_cache_by_post_id( $comment->comment_post_ID );
			}
		}
	}

	/**
	 * Generate publish hook for custom post types
	 *
	 * @since 2.0.3
	 * @since 2.1.7  Make the function public
	 *
	 * @deprecated no longer used since 2.4
	 */
	public static function register_publish_hooks() {
		/* Available post types */
		$post_types = get_post_types(
			array(
				'public' => true,
			)
		);

		/* Empty data? */
		if ( empty( $post_types ) ) {
			return;
		}

		/* Loop the post types */
		foreach ( $post_types as $post_type ) {
			add_action( 'publish_' . $post_type, array( __CLASS__, 'publish_post_types' ), 10, 2 );
			add_action( 'publish_future_' . $post_type, array( __CLASS__, 'flush_total_cache' ) );
		}
	}

	/**
	 * Removes the post type cache on post updates
	 *
	 * @param int    $post_id Post ID.
	 * @param object $post    Post object.
	 *
	 * @since 2.0.3
	 *
	 * @deprecated no longer used since 2.4
	 */
	public static function publish_post_types( $post_id, $post ) {
		/* No post_id? */
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		/* Post status check */
		if ( ! in_array( $post->post_status, array( 'publish', 'future' ), true ) ) {
			return;
		}

		/* Check user role */
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		/* Remove cache OR flush */
		if ( 1 !== self::$options['reset_on_post'] ) {
			self::remove_page_cache_by_post_id( $post_id );
		} else {
			self::flush_total_cache();
		}
	}

	/**
	 * Removes the post type cache if saved or updated
	 *
	 * @param int     $id          Post ID.
	 * @param WP_Post $post_after  Post object following the update.
	 * @param WP_Post $post_before Post object before the update.
	 *
	 * @since 2.0.3
	 * @since 2.1.7 Make the function public.
	 * @since 2.4.0 Renamed to save_update_trash_post and introduced parameters.
	 */
	public static function save_update_trash_post( $id, $post_after, $post_before ) {
		$status = get_post_status( $post_before );

		/* Post type published? */
		if ( 'publish' === $status ) {
			self::flush_cache_for_posts( $id );
		}
	}

	/**
	 * Removes the post type cache before an existing post type is updated in the db
	 *
	 * @param int   $id   Post ID.
	 * @param array $data Post data.
	 *
	 * @since 2.0.3
	 * @since 2.3.0
	 * @since 2.4.0 Renamed to post_update.
	 */
	public static function post_update( $id, $data ) {
		$new_status = $data['post_status'];
		$old_status = get_post_status( $id );

		/* Was it published and is it not trashed now? */
		if ( 'trash' !== $new_status && 'publish' === $old_status ) {
			self::flush_cache_for_posts( $id );
		}
	}

	/**
	 * Clear cache when any post type has been created or updated
	 *
	 * @param int|WP_Post $post Post ID or object.
	 *
	 * @since 2.4.0
	 */
	public static function flush_cache_for_posts( $post ) {
		if ( is_int( $post ) ) {
			$post_id = $post;
			$data    = get_post( $post_id );

			if ( ! is_object( $data ) ) {
				return;
			}
		} elseif ( is_object( $post ) ) {
			$post_id = $post->ID;
		} else {
			return;
		}

		/* Remove cache OR flush */
		if ( 1 !== self::$options['reset_on_post'] ) {
			self::remove_page_cache_by_post_id( $post_id );
		} else {
			self::flush_total_cache();
		}
	}

	/**
	 * Flush post cache on WooCommerce stock changes.
	 *
	 * @param int|WC_Product $product Product ID or object.
	 *
	 * @since 2.4.0
	 */
	public static function flush_woocommerce( $product ) {
		if ( is_int( $product ) ) {
			$id = $product;
		} else {
			$id = $product->get_id();
		}

		self::flush_cache_for_posts( $id );
	}

	/**
	 * Removes a page (id) from cache
	 *
	 * @param int $post_id Post ID.
	 *
	 * @since 2.0.3
	 */
	public static function remove_page_cache_by_post_id( $post_id ) {
		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return;
		}

		self::remove_page_cache_by_url( get_permalink( $post_id ) );
	}

	/**
	 * Removes a page url from cache
	 *
	 * @param string $url Page URL.
	 *
	 * @since 0.1
	 */
	public static function remove_page_cache_by_url( $url ) {
		$url = (string) $url;
		if ( ! $url ) {
			return;
		}

		$hash = self::_cache_hash( $url );
		call_user_func( array( self::$method, 'delete_item' ), $hash, $url );

		/**
		 * Call hook for further actions after cache has been flushed for a single page.
		 *
		 * @since 2.4.0
		 *
		 * @param string $url  Page URL.
		 * @param string $hash Cache hash for given URL.
		 */
		do_action( 'cachify_removed_cache_by_url', $url, $hash );
	}

	/**
	 * Get cache validity
	 *
	 * @return int Validity period in seconds.
	 *
	 * @since 2.0.0
	 */
	private static function _cache_expires() {
		return HOUR_IN_SECONDS * self::$options['cache_expires'];
	}

	/**
	 * Determine if cache details should be printed in signature
	 *
	 * @return bool Show details in signature.
	 *
	 * @since 2.3.0
	 */
	private static function _signature_details() {
		return 1 === self::$options['sig_detail'];
	}

	/**
	 * Get hash value for caching
	 *
	 * @param string $url URL to hash [optional].
	 *
	 * @return string Cachify hash value.
	 *
	 * @since 0.1
	 * @since 2.0
	 */
	private static function _cache_hash( $url = '' ) {
		$prefix = is_ssl() ? 'https-' : '';

		if ( empty( $url ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			$url = '//' . wp_unslash( $_SERVER['HTTP_HOST'] ) . wp_unslash( $_SERVER['REQUEST_URI'] );
		}

		$url_parts = wp_parse_url( $url );
		$hash_key  = $prefix . $url_parts['host'] . $url_parts['path'];

		return md5( $hash_key ) . '.cachify';
	}

	/**
	 * Split by comma
	 *
	 * @param string $input String to split.
	 *
	 * @return array Splitted values.
	 *
	 * @since 0.9.1
	 * @since 1.0
	 */
	private static function _preg_split( $input ) {
		return (array) preg_split( '/,/', $input, -1, PREG_SPLIT_NO_EMPTY );
	}

	/**
	 * Check for index page
	 *
	 * @return bool TRUE if index
	 *
	 * @since 0.6
	 */
	private static function _is_index() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		return basename( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) === 'index.php';
	}

	/**
	 * Check for mobile devices
	 *
	 * @return bool TRUE if mobile
	 *
	 * @since 0.9.1
	 */
	private static function _is_mobile() {
		$templatedir = get_template_directory();
		return ( strpos( $templatedir, 'wptouch' ) || strpos( $templatedir, 'carrington' ) || strpos( $templatedir, 'jetpack' ) || strpos( $templatedir, 'handheld' ) );
	}

	/**
	 * Check if user is logged in or marked
	 *
	 * @return bool TRUE on "marked" users
	 *
	 * @since 2.0.0
	 */
	private static function _is_logged_in() {
		/* Logged in */
		if ( is_user_logged_in() ) {
			return true;
		}

		/* Cookie? */
		if ( empty( $_COOKIE ) ) {
			return false;
		}

		/* Loop */
		foreach ( $_COOKIE as $k => $v ) {
			if ( preg_match( '/^(wp-postpass|wordpress_logged_in|comment_author)_/', $k ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Register all hooks to flush the total cache
	 *
	 * @since 2.4.0
	 */
	public static function register_flush_cache_hooks() {
		/* Define all default flush cache hooks */
		$flush_cache_hooks = array(
			'cachify_flush_cache'            => 10,
			'_core_updated_successfully'     => 10,
			'switch_theme'                   => 10,
			'before_delete_post'             => 10,
			'wp_trash_post'                  => 10,
			'create_term'                    => 10,
			'delete_term'                    => 10,
			'edit_terms'                     => 10,
			'user_register'                  => 10,
			'edit_user_profile_update'       => 10,
			'delete_user'                    => 10,
			/* third party */
			'autoptimize_action_cachepurged' => 10,
		);

		$flush_cache_hooks = apply_filters( 'cachify_flush_cache_hooks', $flush_cache_hooks );

		/* Loop all hooks and register actions */
		foreach ( $flush_cache_hooks as $hook => $priority ) {
			add_action( $hook, array( 'Cachify', 'flush_total_cache' ), $priority, 0 );
		}
	}

	/**
	 * Define exclusions for caching
	 *
	 * @hook bool cachify_skip_cache
	 *
	 * @return bool TRUE on exclusion
	 *
	 * @since 0.2
	 */
	private static function _skip_cache() {

		/* Plugin options */
		$options = self::$options;

		/* Skip for all request methods except GET */
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' !== $_SERVER['REQUEST_METHOD'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}
		if ( ! empty( $_GET ) && get_option( 'permalink_structure' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return true;
		}

		/* Only cache requests routed through main index.php (skip AJAX, WP-Cron, WP-CLI etc.) */
		if ( ! self::_is_index() ) {
			return true;
		}

		/* Logged in */
		if ( $options['only_guests'] && self::_is_logged_in() ) {
			return true;
		}

		/* No cache hook */
		if ( apply_filters( 'cachify_skip_cache', false ) ) {
			return true;
		}

		/* Conditional Tags */
		if ( is_search() || is_404() || is_feed() || is_trackback() || is_robots() || is_preview() || post_password_required() ) {
			return true;
		}

		/* WooCommerce usage */
		if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
			return true;
		}

		/* Mobile request */
		if ( self::_is_mobile() ) {
			return true;
		}

		/* Post IDs */
		if ( $options['without_ids'] && is_singular() ) {
			$without_ids = array_map( 'intval', self::_preg_split( $options['without_ids'] ) );
			if ( in_array( $GLOBALS['wp_query']->get_queried_object_id(), $without_ids, true ) ) {
				return true;
			}
		}

		/* User Agents */
		if ( $options['without_agents'] && isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent_strings = self::_preg_split( $options['without_agents'] );
			foreach ( $user_agent_strings as $user_agent_string ) {
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				if ( strpos( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ), $user_agent_string ) !== false ) {
					return true;
				}
			}
		}

		// Sitemap feature added in WP 5.5.
		if ( get_query_var( 'sitemap' ) || get_query_var( 'sitemap-subtype' ) || get_query_var( 'sitemap-stylesheet' ) ) {
			return true;
		}

		/* Content Negotiation */

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( isset( $_SERVER['HTTP_ACCEPT'] ) && false === strpos( $_SERVER['HTTP_ACCEPT'], 'text/html' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Minify HTML code
	 *
	 * @hook  array cachify_minify_ignore_tags
	 *
	 * @param string $data Original HTML code.
	 *
	 * @return string Minified code
	 *
	 * @since 0.9.2
	 */
	private static function _minify_cache( $data ) {
		/* Disabled? */
		if ( ! self::$options['compress_html'] ) {
			return $data;
		}

		/* Avoid slow rendering */
		if ( strlen( $data ) > 700000 ) {
			return $data;
		}

		/* Ignore this html tags */
		$ignore_tags = (array) apply_filters(
			'cachify_minify_ignore_tags',
			array(
				'textarea',
				'pre',
			)
		);

		/* Add the script tag */
		if ( self::MINIFY_HTML_JS !== self::$options['compress_html'] ) {
			$ignore_tags[] = 'script';
		}

		/* Empty blacklist? | TODO: Make it better */
		if ( ! $ignore_tags ) {
			return $data;
		}

		/* Convert to string */
		$ignore_regex = implode( '|', $ignore_tags );

		/* Minify */
		$cleaned = preg_replace(
			array(
				'/<!--[^\[><](.*?)-->/s',
				'#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:' . $ignore_regex . ')\b))*+)(?:<(?>' . $ignore_regex . ')\b|\z))#',
			),
			array(
				'',
				' ',
			),
			$data
		);

		/* Fault */
		if ( strlen( $cleaned ) <= 1 ) {
			return $data;
		}

		return $cleaned;
	}

	/**
	 * Flush total cache
	 *
	 * @param bool $clear_all_methods Flush all caching methods (default: FALSE).
	 *
	 * @since 0.1
	 * @since 2.0
	 */
	public static function flush_total_cache( $clear_all_methods = false ) {
		// We do not need to flush the cache for saved post revisions.
		if ( did_action( 'save_post_revision' ) ) {
			return;
		}

		if ( $clear_all_methods ) {
			/* DB */
			Cachify_DB::clear_cache();

			/* HDD */
			Cachify_HDD::clear_cache();

			/* REDIS */
			Cachify_REDIS::clear_cache();

			/* MEMCACHED */
			Cachify_MEMCACHED::clear_cache();
		} else {
			call_user_func( array( self::$method, 'clear_cache' ) );
		}

		/**
		 * Call hook for further actions after total cache has been flushed.
		 *
		 * @since 2.4.0
		 *
		 * @param bool $clear_all_methods All available caching backends have been flushed.
		 */
		do_action( 'cachify_flushed_total_cache', $clear_all_methods );

		/* Transient */
		delete_transient( 'cachify_cache_size' );
	}

	/**
	 * Assign the cache
	 *
	 * @param string $data Content of the page.
	 *
	 * @return string Content of the page.
	 *
	 * @since 0.1
	 * @since 2.0
	 */
	public static function set_cache( $data ) {
		/* Empty? */
		if ( empty( $data ) ) {
			return '';
		}

		/**
		 * Filters whether the buffered data should actually be cached
		 *
		 * @param bool   $should_cache  Whether the data should be cached.
		 * @param string $data          The actual data.
		 * @param object $method        Instance of the selected caching method.
		 * @param string $cache_hash    The cache hash.
		 * @param int    $cache_expires Cache validity period.
		 *
		 * @since 2.3.0
		 */
		$should_cache = apply_filters(
			'cachify_store_item',
			200 === http_response_code(),
			$data,
			self::$method,
			self::_cache_hash(),
			self::_cache_expires()
		);

		/* Save? */
		if ( $should_cache ) {
			/**
			 * Filters the buffered data itself
			 *
			 * @param string $data          The actual data.
			 * @param object $method        Instance of the selected caching method.
			 * @param string $cache_hash    The cache hash.
			 * @param int    $cache_expires Cache validity period.
			 *
			 * @since 2.4.0
			 */
			$data = apply_filters( 'cachify_modify_output', $data, self::$method, self::_cache_hash(), self::_cache_expires() );

			call_user_func(
				array(
					self::$method,
					'store_item',
				),
				self::_cache_hash(),
				self::_minify_cache( $data ),
				self::_cache_expires(),
				self::_signature_details()
			);
		}

		return $data;
	}

	/**
	 * Manage the cache.
	 *
	 * @since 0.1
	 */
	public static function manage_cache() {
		/* No caching? */
		if ( self::_skip_cache() ) {
			return;
		}

		/* Data present in cache */
		$cache = call_user_func(
			array(
				self::$method,
				'get_item',
			),
			self::_cache_hash()
		);

		/* No cache? */
		if ( empty( $cache ) ) {
			ob_start( 'Cachify::set_cache' );
			return;
		}

		/* Process cache */
		call_user_func(
			array(
				self::$method,
				'print_cache',
			),
			self::_signature_details(),
			$cache
		);
	}

	/**
	 * Register CSS
	 *
	 * @param string $hook Current hook.
	 *
	 * @since 1.0
	 */
	public static function add_admin_resources( $hook ) {
		/* Hooks check */
		if ( 'index.php' !== $hook && 'settings_page_cachify' !== $hook ) {
			return;
		}

		/* Register css */
		switch ( $hook ) {
			case 'index.php':
				wp_enqueue_style( 'cachify-dashboard' );
				break;

			default:
				break;
		}
	}

	/**
	 * Fixing some admin dashboard styles
	 *
	 * @since 2.3.0
	 */
	public static function admin_dashboard_styles() {
		$wp_version = get_bloginfo( 'version' );

		if ( version_compare( $wp_version, '5.3', '<' ) ) {
			wp_add_inline_style( 'cachify-dashboard', '#dashboard_right_now .cachify-icon use { fill: #82878c; }' );
		}
	}

	/**
	 * Fixing some admin dashboard styles
	 *
	 * @since 2.3.0
	 *
	 * @deprecated included in dashboard.css since 2.4
	 */
	public static function admin_dashboard_dark_mode_styles() {
		wp_add_inline_style( 'cachify-dashboard', '#dashboard_right_now .cachify-icon use { fill: #bbc8d4; }' );
	}

	/**
	 * Add options page
	 *
	 * @since 1.0
	 */
	public static function add_page() {
		add_options_page(
			__( 'Cachify', 'cachify' ),
			__( 'Cachify', 'cachify' ),
			'manage_options',
			'cachify',
			array(
				__CLASS__,
				'options_page',
			)
		);
	}

	/**
	 * Available caching methods
	 *
	 * @return array Array of actually available methods.
	 *
	 * @since 2.0
	 */
	private static function _method_select() {
		/* Defaults */
		$methods = array(
			self::METHOD_DB  => esc_html__( 'Database', 'cachify' ),
			self::METHOD_HDD => esc_html__( 'Hard disk', 'cachify' ),
			self::METHOD_MMC => esc_html__( 'Memcached', 'cachify' ),
			self::METHOD_REDIS => esc_html__( 'Redis', 'cachify' ),
		);

		/* Memcached? */
		if ( ! Cachify_MEMCACHED::is_available() ) {
			unset( $methods[3] );
		}

		/* HDD */
		if ( ! Cachify_HDD::is_available() ) {
			unset( $methods[2] );
		}

		/* Redis */
		if ( ! Cachify_REDIS::is_available() ) {
			unset( $methods[4] );
		}

		return $methods;
	}

	/**
	 * Minify cache dropdown
	 *
	 * @return array Key => value array
	 *
	 * @since 2.1.3
	 */
	private static function _minify_select() {
		return array(
			self::MINIFY_DISABLED  => esc_html__( 'No minify', 'cachify' ),
			self::MINIFY_HTML_ONLY => esc_html__( 'HTML', 'cachify' ),
			self::MINIFY_HTML_JS   => esc_html__( 'HTML + Inline JavaScript', 'cachify' ),
		);
	}

	/**
	 * Register settings
	 *
	 * @since 1.0
	 */
	public static function register_settings() {
		register_setting(
			'cachify',
			'cachify',
			array(
				__CLASS__,
				'validate_options',
			)
		);
	}

	/**
	 * Validate options
	 *
	 * @param array $data Array of form values.
	 *
	 * @return array Array of validated values.
	 *
	 * @since 1.0
	 * @since 2.1.3
	 */
	public static function validate_options( $data ) {
		/* Empty data? */
		if ( empty( $data ) ) {
			return array();
		}

		/* Flush cache */
		self::flush_total_cache( true );

		/* Notification */
		if ( self::$options['use_apc'] !== $data['use_apc'] && $data['use_apc'] >= self::METHOD_HDD && self::METHOD_REDIS != $data['use_apc'] ) {
			add_settings_error(
				'cachify_method_tip',
				'cachify_method_tip',
				esc_html__( 'The server configuration file (e.g. .htaccess) needs to be adjusted. Please have a look at the setup tab.', 'cachify' ),
				'notice-warning'
			);
		}

		/* Return */
		return array(
			'only_guests'       => (int) ( ! empty( $data['only_guests'] ) ),
			'compress_html'     => (int) $data['compress_html'],
			'cache_expires'     => (int) ( isset( $data['cache_expires'] ) ? $data['cache_expires'] : self::$options['cache_expires'] ),
			'without_ids'       => (string) isset( $data['without_ids'] ) ? sanitize_text_field( $data['without_ids'] ) : '',
			'without_agents'    => (string) isset( $data['without_agents'] ) ? sanitize_text_field( $data['without_agents'] ) : '',
			'use_apc'           => (int) $data['use_apc'],
			'reset_on_post'     => (int) ( ! empty( $data['reset_on_post'] ) ),
			'reset_on_comment'  => (int) ( ! empty( $data['reset_on_comment'] ) ),
			'sig_detail'        => (int) ( ! empty( $data['sig_detail'] ) ),
			'change_robots_txt' => (int) ( ! empty( $data['change_robots_txt'] ) ),
		);
	}

	/**
	 * Display options page
	 *
	 * @since 1.0
	 */
	public static function options_page() {
		$options      = self::_get_options();
		$cachify_tabs = self::_get_tabs( $options );
		$current_tab  = isset( $_GET['cachify_tab'] ) && isset( $cachify_tabs[ $_GET['cachify_tab'] ] )
			? sanitize_text_field( wp_unslash( $_GET['cachify_tab'] ) )
			: 'settings';
		?>

		<div class="wrap" id="cachify_settings">
			<h1>Cachify</h1>

			<?php
			// Add a navbar if necessary.
			if ( count( $cachify_tabs ) > 1 ) {
				echo '<h2 class="nav-tab-wrapper">';
				foreach ( $cachify_tabs as $tab_key => $tab_data ) {
					printf(
						'<a class="nav-tab %s" href="%s">%s</a>',
						esc_attr( $tab_key === $current_tab ? 'nav-tab-active' : '' ),
						esc_url(
							add_query_arg(
								array(
									'page'        => 'cachify',
									'cachify_tab' => $tab_key,
								),
								admin_url( 'options-general.php' )
							)
						),
						esc_html( $tab_data['name'] )
					);
				}
				echo '</h2>';
			}

				/* Include current tab */
				include $cachify_tabs[ $current_tab ]['page'];

				/* Include common footer */
				include 'cachify.settings-footer.php';
			?>
		</div>
		<?php
	}

	/**
	 * Return an array with all settings tabs applicable in context of current plugin options.
	 *
	 * @param array $options the options.
	 *
	 * @return array
	 *
	 * @since 2.3.0
	 */
	private static function _get_tabs( $options ) {
		/* Settings tab is always present */
		$tabs = array(
			'settings' => array(
				'name' => __( 'Settings', 'cachify' ),
				'page' => 'cachify.settings.php',
			),
		);

		if ( self::METHOD_HDD === $options['use_apc'] ) {
			/* Setup tab for HDD Cache */
			$tabs['setup'] = array(
				'name' => __( 'Setup', 'cachify' ),
				'page' => 'setup/cachify.hdd.' . ( self::$is_nginx ? 'nginx' : 'htaccess' ) . '.php',
			);
		} elseif ( self::METHOD_MMC === $options['use_apc'] && self::$is_nginx ) {
			/* Setup tab for Memcached */
			$tabs['setup'] = array(
				'name' => __( 'Setup', 'cachify' ),
				'page' => 'setup/cachify.memcached.nginx.php',
			);
		}

		return $tabs;
	}
}
