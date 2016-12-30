<?php


/* Quit */
defined('ABSPATH') OR exit;


/**
* Cachify
*/

final class Cachify {


	/**
	* Plugin options
	*
	* @since  2.0
	* @var    array
	*/

	private static $options;


	/**
	* Caching method
	*
	* @since  2.0
	* @var    object
	*/

	private static $method;


	/**
	* Method settings
	*
	* @since  2.0.9
	* @var    integer
	*/

	const METHOD_DB = 0;
	const METHOD_APC = 1;
	const METHOD_HDD = 2;
	const METHOD_MMC = 3;


	/**
	* Minify settings
	*
	* @since  2.0.9
	* @var    integer
	*/

	const MINIFY_DISABLED = 0;
	const MINIFY_HTML_ONLY = 1;
	const MINIFY_HTML_JS = 2;


	/**
	* Pseudo-Constructor
	*
	* @since   2.0.5
	* @change  2.0.5
	*/

	public static function instance()
	{
		new self();
	}


	/**
	* Constructor
	*
	* @since   1.0.0
	* @change  2.2.2
	*
	* @param   void
	* @return  void
	*/

	public function __construct()
	{
		/* Set defaults */
		self::_set_default_vars();

		/* Publish hooks */
		add_action(
			'init',
			array(
				__CLASS__,
				'register_publish_hooks'
			),
			99
		);

		/* Flush Hooks */
		add_action(
			'cachify_remove_post_cache',
			array(
				__CLASS__,
				'remove_page_cache_by_post_id'
			)
		);
		add_action(
			'cachify_flush_cache',
			array(
				__CLASS__,
				'flush_total_cache'
			)
		);
		add_action(
			'_core_updated_successfully',
			array(
				__CLASS__,
				'flush_total_cache'
			)
		);
		add_action(
			'switch_theme',
			array(
				__CLASS__,
				'flush_total_cache'
			)
		);
		add_action(
			'wp_trash_post',
			array(
				__CLASS__,
				'flush_total_cache'
			)
		);

        /* Flush icon */
		add_action(
			'admin_bar_menu',
			array(
				__CLASS__,
				'add_flush_icon'
			),
			90
		);
		add_action(
			'init',
			array(
				__CLASS__,
				'process_flush_request'
			)
		);

		/* Flush (post) cache if comment is made from frontend or backend */
		add_action(
			'pre_comment_approved',
			array(
				__CLASS__,
				'pre_comment'
			),
			99,
			2
		);

		/* Backend */
		if ( is_admin() ) {
			add_action(
				'wpmu_new_blog',
				array(
					__CLASS__,
					'install_later'
				)
			);
			add_action(
				'delete_blog',
				array(
					__CLASS__,
					'uninstall_later'
				)
			);

			add_action(
				'admin_init',
				array(
					__CLASS__,
					'register_textdomain'
				)
			);
			add_action(
				'admin_init',
				array(
					__CLASS__,
					'register_settings'
				)
			);

			add_action(
				'admin_menu',
				array(
					__CLASS__,
					'add_page'
				)
			);
			add_action(
				'admin_enqueue_scripts',
				array(
					__CLASS__,
					'add_admin_resources'
				)
			);

			add_action(
				'transition_comment_status',
				array(
					__CLASS__,
					'touch_comment'
				),
				10,
				3
			);
			add_action(
				'edit_comment',
				array(
					__CLASS__,
					'edit_comment'
				)
			);

			add_filter(
				'dashboard_glance_items',
				array(
					__CLASS__,
					'add_dashboard_count'
				)
			);
			add_action(
				'post_submitbox_misc_actions',
				array(
					__CLASS__,
					'print_flush_dropdown'
				)
			);

			add_filter(
				'plugin_row_meta',
				array(
					__CLASS__,
					'row_meta'
				),
				10,
				2
			);
			add_filter(
				'plugin_action_links_' .CACHIFY_BASE,
				array(
					__CLASS__,
					'action_links'
				)
			);

		/* Frontend */
		} else {
			add_action(
				'template_redirect',
				array(
					__CLASS__,
					'manage_cache'
				),
				0
			);
			add_action(
				'robots_txt',
				array(
					__CLASS__,
					'robots_txt'
				)
			);
		}
	}


	/**
	* Deactivation hook
	*
	* @since   2.1.0
	* @change  2.1.0
	*/

	public static function on_deactivation()
	{
		self::flush_total_cache(true);
	}


	/**
	* Activation hook
	*
	* @since   1.0
	* @change  2.1.0
	*/

	public static function on_activation()
	{
		/* Multisite & Network */
		if ( is_multisite() && ! empty($_GET['networkwide']) ) {
			/* Blog-IDs */
			$ids = self::_get_blog_ids();

			/* Loop over blogs */
			foreach ($ids as $id) {
				switch_to_blog($id);
				self::_install_backend();
			}

			/* Switch back */
			restore_current_blog();

		} else {
			self::_install_backend();
		}
	}


	/**
	 * Plugin-Installation on new MU-Blogs
	 *
	 * @since   1.0
	 * @change  1.0
	 *
	 * @param integer $id  Blog ID
	 */

	public static function install_later($id)
	{
		/* No network-plugin */
		if ( ! is_plugin_active_for_network(CACHIFY_BASE) ) {
			return;
		}

		/* Switch to blog */
		switch_to_blog($id);

		/* Install */
		self::_install_backend();

		/* Switch back */
		restore_current_blog();
	}


	/**
	* Actual installation of the options
	*
	* @since   1.0
	* @change  2.0
	*/

	private static function _install_backend()
	{
		add_option(
			'cachify',
			array()
		);

		/* Flush */
		self::flush_total_cache(true);
	}


	/**
	* Uninstalling of the plugin per MU-Blog
	*
	* @since   1.0
	* @change  2.1.0
	*/

	public static function on_uninstall()
	{
		/* Global */
		global $wpdb;

		/* Multisite & Network */
		if ( is_multisite() && ! empty($_GET['networkwide']) ) {
			/* Alter Blog */
			$old = $wpdb->blogid;

			/* Blog-IDs */
			$ids = self::_get_blog_ids();

			/* Loop */
			foreach ($ids as $id) {
				switch_to_blog($id);
				self::_uninstall_backend();
			}

			/* Switch back */
			switch_to_blog($old);
		} else {
			self::_uninstall_backend();
		}
	}


	/**
	 * Uninstalling of the plugin für MU and Network
	 *
	 * @since   1.0
	 * @change  1.0
	 *
	 * @param integer $id  Blog ID
	 */

	public static function uninstall_later($id)
	{
		/* No network plugin */
		if ( ! is_plugin_active_for_network(CACHIFY_BASE) ) {
			return;
		}

		/* Switch to blog */
		switch_to_blog($id);

		/* Install */
		self::_uninstall_backend();

		/* Switch back */
		restore_current_blog();
	}


	/**
	* Actual uninstalling of the plugin
	*
	* @since   1.0
	* @change  1.0
	*/

	private static function _uninstall_backend()
	{
		/* Option */
		delete_option('cachify');

		/* Flush cache */
		self::flush_total_cache(true);
	}


	/**
	* Get IDs of installed blogs
	*
	* @since   1.0
	* @change  1.0
	*
	* @return  array  Blog-IDs
	*/

	private static function _get_blog_ids()
	{
		/* Global */
		global $wpdb;

		return $wpdb->get_col("SELECT blog_id FROM `$wpdb->blogs`");
	}


	/**
	* Set default options
	*
	* @since   2.0
	* @change  2.0.7
	*/

	private static function _set_default_vars()
	{
		/* Options */
		self::$options = self::_get_options();

		/* APC */
		if ( self::$options['use_apc'] === self::METHOD_APC && Cachify_APC::is_available() ) {
			self::$method = new Cachify_APC;

		/* HDD */
		} else if ( self::$options['use_apc'] === self::METHOD_HDD && Cachify_HDD::is_available() ) {
			self::$method = new Cachify_HDD;

		/* MEMCACHED */
		} else if ( self::$options['use_apc'] === self::METHOD_MMC && Cachify_MEMCACHED::is_available() ) {
			self::$method = new Cachify_MEMCACHED;

		/* DB */
		} else {
			self::$method = new Cachify_DB;
		}
	}


	/**
	* Get options
	*
	* @since   2.0
	* @change  2.1.2
	*
	* @return  array  Array of option values
	*/

	private static function _get_options()
	{
		return wp_parse_args(
			get_option('cachify'),
			array(
				'only_guests'	 	=> 1,
				'compress_html'	 	=> self::MINIFY_DISABLED,
				'cache_expires'	 	=> 12,
				'without_ids'	 	=> '',
				'without_agents' 	=> '',
				'use_apc'		 	=> self::METHOD_DB,
				'reset_on_comment'  => 0
			)
		);
	}


	/**
	* Modify robots.txt
	*
	* @since   1.0
	* @change  2.1.9
	*
	* @param   string  $data  Original content of dynamic robots.txt
	* @return  string         Modified content of robots.txt
	*/

	public static function robots_txt($data)
	{
		/* HDD only */
		if ( self::$options['use_apc'] !== self::METHOD_HDD ) {
			return $data;
		}

		/* Path */
		$path = parse_url(site_url(), PHP_URL_PATH);

		/* Output */
		$data .= sprintf(
			'%2$sDisallow: %1$s/wp-content/cache/cachify/%2$s',
			( empty($path) ? '' : $path ),
			PHP_EOL
		);

		return $data;
	}


	/**
	* Add the action links
	*
	* @since   1.0
	* @change  1.0
	*
	* @param   array  $data  Initial array with action links
	* @return  array         Merged array with action links
	*/

	public static function action_links($data)
	{
		/* Permissions? */
		if ( ! current_user_can('manage_options') ) {
			return $data;
		}

		return array_merge(
			$data,
			array(
				sprintf(
					'<a href="%s">%s</a>',
					add_query_arg(
						array(
							'page' => 'cachify'
						),
						admin_url('options-general.php')
					),
					__( 'Settings', 'cachify' )
				)
			)
		);
	}


	/**
	* Meta links of the plugin
	*
	* @since   0.5
	* @change  2.0.5
	*
	* @param   array   $input  Initial array with meta links
	* @param   string  $page   Current page
	* @return  array           Merged array with meta links
	*/

	public static function row_meta($input, $page)
	{
		/* Permissions */
		if ( $page != CACHIFY_BASE ) {
			return $input;
		}

		return array_merge(
			$input,
			array(
				'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LG5VC9KXMAYXJ" target="_blank">PayPal</a>'
			)
		);
	}


	/**
	* Add cache properties to dashboard
	*
	* @since   2.0.0
	* @change  2.2.2
	*
	* @param   array  $items  Initial array with dashboard items
	* @return  array          Merged array with dashboard items
	*/

	public static function add_dashboard_count( $items = array() )
	{
		/* Skip */
        if ( ! current_user_can('manage_options') ) {
            return $items;
        }

		/* Cache size */
		$size = self::get_cache_size();

		/* Caching method */
		$method = call_user_func(
			array(
				self::$method,
				'stringify‎_method'
			)
		);

		/* Right now item */
		$items[] = sprintf(
			'<a href="%s" class="cachify-icon cachify-icon--%s" title="%s: %s">%s Cache</a>',
			add_query_arg(
				array(
					'page' => 'cachify'
				),
				admin_url('options-general.php')
			),
			esc_attr(strtolower($method)),
			esc_html__('Caching method', 'cachify'),
			esc_attr($method),
			( empty($size) ? esc_html__('Empty', 'cachify') : size_format($size) )
		);

		return $items;
	}


	/**
	* Get the cache size
	*
	* @since   2.0.6
	* @change  2.0.6
	*
	* @return  integer    Cache size in Bytes
	*/

	public static function get_cache_size()
	{
		if ( ! $size = get_transient('cachify_cache_size') ) {
			/* Read */
			$size = (int) call_user_func(
				array(
					self::$method,
					'get_stats'
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
	* @since   1.2
	* @change  2.2.2
    *
    * @hook    mixed   cachify_user_can_flush_cache
	*
	* @param   object  $wp_admin_bar  Object of menu items
	*/

	public static function add_flush_icon($wp_admin_bar)
	{
		/* Quit */
		if ( ! is_admin_bar_showing() OR ! apply_filters('cachify_user_can_flush_cache', current_user_can('manage_options')) ) {
			return;
		}

		/* Display the admin icon anytime */
		echo '<style>#wp-admin-bar-cachify{display:list-item !important} #wp-admin-bar-cachify .ab-icon{margin:0 !important} #wp-admin-bar-cachify .ab-icon:before{content:"\f182";top:2px;margin:0}</style>';

		/* Add menu item */
		$wp_admin_bar->add_menu(
			array(
				'id' 	 => 'cachify',
				'href'   => wp_nonce_url( add_query_arg('_cachify', 'flush'), '_cachify__flush_nonce'), // esc_url in /wp-includes/class-wp-admin-bar.php#L438
				'parent' => 'top-secondary',
				'title'	 => '<span class="ab-icon dashicons"></span>',
				'meta'   => array( 'title' => esc_html__('Flush the cachify cache', 'cachify') )
			)
		);
	}


	/**
	* Process plugin's meta actions
	*
	* @since   0.5
	* @change  2.2.2
    *
    * @hook    mixed  cachify_user_can_flush_cache
	*
	* @param   array  $data  Metadata of the plugin
	*/

	public static function process_flush_request($data)
	{
		/* Skip if not a flush request */
		if ( empty($_GET['_cachify']) OR $_GET['_cachify'] !== 'flush' ) {
			return;
		}

        /* Check nonce */
        if ( empty($_GET['_wpnonce']) OR ! wp_verify_nonce($_GET['_wpnonce'], '_cachify__flush_nonce') ) {
            return;
        }

		/* Skip if not necessary */
		if ( ! is_admin_bar_showing() OR ! apply_filters('cachify_user_can_flush_cache', current_user_can('manage_options')) ) {
			return;
		}

		/* Load on demand */
		if ( ! function_exists('is_plugin_active_for_network') ) {
			require_once( ABSPATH. 'wp-admin/includes/plugin.php' );
		}

		/* Multisite & Network */
		if ( is_multisite() && is_plugin_active_for_network(CACHIFY_BASE) ) {
			/* Old blog */
			$old = $GLOBALS['wpdb']->blogid;

			/* Blog-IDs */
			$ids = self::_get_blog_ids();

			/* Loop over blogs */
			foreach ($ids as $id) {
				switch_to_blog($id);
				self::flush_total_cache();
			}

			/* Switch back to old blog */
			switch_to_blog($old);

			/* Notice */
			if ( is_admin() ) {
				add_action(
					'network_admin_notices',
					array(
						__CLASS__,
						'flush_notice'
					)
				);
			}
		} else {
			/* Flush cache */
			self::flush_total_cache();

			/* Notice */
			if ( is_admin() ) {
				add_action(
					'admin_notices',
					array(
						__CLASS__,
						'flush_notice'
					)
				);
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
	* @since   1.2
	* @change  2.2.2
    *
    * @hook    mixed  cachify_user_can_flush_cache
	*/

	public static function flush_notice()
	{
		/* No Admin */
		if ( ! is_admin_bar_showing() OR ! apply_filters('cachify_user_can_flush_cache', current_user_can('manage_options')) ) {
			return false;
		}

		echo sprintf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html__('Cachify cache is flushed.', 'cachify')
		);
	}


	/**
	* Remove page from cache or flush on comment edit
	*
	* @since   0.1.0
	* @change  2.1.2
	*
	* @param   integer  $id  Comment ID
	*/

	public static function edit_comment($id)
	{
		if ( self::$options['reset_on_comment'] ) {
			self::flush_total_cache();
		} else {
			self::remove_page_cache_by_post_id(
				get_comment($id)->comment_post_ID
			);
		}
	}


	/**
	* Remove page from cache or flush on new comment
	*
	* @since   0.1.0
	* @change  2.1.2
	*
	* @param   mixed  $approved  Comment status
	* @param   array  $comment   Array of properties
	* @return  mixed             Comment status
	*/

	public static function pre_comment($approved, $comment)
	{
		/* Approved comment? */
		if ( $approved === 1 ) {
			if ( self::$options['reset_on_comment'] ) {
				self::flush_total_cache();
			} else {
				self::remove_page_cache_by_post_id( $comment['comment_post_ID'] );
			}
		}

		return $approved;
	}


	/**
	* Remove page from cache or flush on comment edit
	*
	* @since   0.1
	* @change  2.1.2
	*
	* @param   string  $new_status  New status
	* @param   string  $old_status  Old status
	* @param   object  $comment     The comment
	*/

	public static function touch_comment($new_status, $old_status, $comment)
	{
		if ( $new_status != $old_status ) {
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
	* @since   2.1.7  Make the function public
	* @since   2.0.3
	*
	* @param   void
	* @return  void
	*/

	public static function register_publish_hooks()
	{
		/* Available post types */
		$post_types = get_post_types(
			array('public' => true)
		);

		/* Empty data? */
		if ( empty($post_types) ) {
			return;
		}

		/* Loop the post types */
		foreach ( $post_types as $post_type ) {
			add_action(
				'publish_' .$post_type,
				array(
					__CLASS__,
					'publish_post_types'
				),
				10,
				2
			);
			add_action(
				'publish_future_' .$post_type,
				array(
					__CLASS__,
					'flush_total_cache'
				)
			);
		}
	}


	/**
	 * Removes the post type cache on post updates
	 *
	 * @since   2.0.3
	 * @change  2.2.2
	 *
	 * @param   integer $post_ID  Post ID
	 * @param   object  $post     Post object
	 */

	public static function publish_post_types($post_ID, $post)
	{
		/* No Post_ID? */
		if ( empty($post_ID) OR empty($post) ) {
			return;
		}

		/* Post status check */
		if ( ! in_array( $post->post_status, array('publish', 'future') ) ) {
			return;
		}

		/* Check for post var AND flush */
		if ( ! isset($_POST['_cachify_remove_post_type_cache_on_update']) ) {
			return self::flush_total_cache();
		}

		/* Check nonce */
		if ( ! isset($_POST['_cachify__status_nonce_' .$post_ID]) OR ! wp_verify_nonce($_POST['_cachify__status_nonce_' .$post_ID], CACHIFY_BASE) ) {
			return;
		}

		/* Check user role */
		if ( ! current_user_can('publish_posts') ) {
			return;
		}

		/* Save as var */
		$remove_post_type_cache = (int)$_POST['_cachify_remove_post_type_cache_on_update'];

		/* Save as user meta */
		update_user_meta(
			get_current_user_id(),
			'_cachify_remove_post_type_cache_on_update',
			$remove_post_type_cache
		);

		/* Remove cache OR flush */
		if ( $remove_post_type_cache ) {
			self::remove_page_cache_by_post_id( $post_ID );
		} else {
			self::flush_total_cache();
		}
	}


	/**
	* Removes a page (id) from cache
	*
	* @since   2.0.3
	* @change  2.1.3
	*
	* @param   integer  $post_ID  Post ID
	*/

	public static function remove_page_cache_by_post_id($post_ID)
	{
		/* Value check */
		if ( ! $post_ID = (int)$post_ID ) {
			return;
		}

		/* Remove page by url */
		self::remove_page_cache_by_url(
			get_permalink( $post_ID )
		);
	}


	/**
	* Removes a page url from cache
	*
	* @since   0.1
	* @change  2.1.3
	*
	* @param  string  $url  Page URL
	*/

	public static function remove_page_cache_by_url($url)
	{
		/* Value check */
		if ( ! $url = (string)$url ) {
			return;
		}

		call_user_func(
			array(
				self::$method,
				'delete_item'
			),
			self::_cache_hash( $url ),
			$url
		);
	}


	/**
	* Get cache validity
	*
	* @since   2.0.0
	* @change  2.1.7
	*
	* @return  integer    Validity period in seconds
	*/

	private static function _cache_expires()
	{
		return HOUR_IN_SECONDS * self::$options['cache_expires'];
	}


	/**
	* Get hash value for caching
	*
	* @since   0.1
	* @change  2.0
	*
	* @param   string  $url  URL to hash [optional]
	* @return  string        Cachify hash value
	*/

	private static function _cache_hash($url = '')
	{
		$prefix = is_ssl() ? 'https-' : '';
		return md5(
			empty($url) ? ( $prefix . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) : ( $prefix . parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH) )
		) . '.cachify';
	}


	/**
	* Split by comma
	*
	* @since   0.9.1
	* @change  1.0
	*
	* @param   string  $input  String to split
	* @return  array           Splitted values
	*/

	private static function _preg_split($input)
	{
		return (array)preg_split('/,/', $input, -1, PREG_SPLIT_NO_EMPTY);
	}


	/**
	* Check for index page
	*
	* @since   0.6
	* @change  1.0
	*
	* @return  boolean  TRUE if index
	*/

	private static function _is_index()
	{
		return basename($_SERVER['SCRIPT_NAME']) != 'index.php';
	}


	/**
	* Check for mobile devices
	*
	* @since   0.9.1
	* @change  2.2.2
	*
	* @return  boolean  TRUE if mobile
	*/

	private static function _is_mobile()
	{
		return ( strpos(TEMPLATEPATH, 'wptouch') OR strpos(TEMPLATEPATH, 'carrington') OR strpos(TEMPLATEPATH, 'jetpack') OR strpos(TEMPLATEPATH, 'handheld') );
	}


	/**
	* Check if user is logged in or marked
	*
	* @since   2.0.0
	* @change  2.0.5
	*
	* @return  boolean  $diff  TRUE on "marked" users
	*/

	private static function _is_logged_in()
	{
		/* Logged in */
		if ( is_user_logged_in() ) {
			return true;
		}

		/* Cookie? */
		if ( empty($_COOKIE) ) {
			return false;
		}

		/* Loop */
		foreach ( $_COOKIE as $k => $v) {
			if ( preg_match('/^(wp-postpass|wordpress_logged_in|comment_author)_/', $k) ) {
				return true;
			}
		}
	}


	/**
	* Define exclusions for caching
	*
	* @since   0.2
	* @change  2.1.7
	*
	* @return  boolean  TRUE on exclusion
	*
	* @hook    boolean  cachify_skip_cache
	*/

	private static function _skip_cache()
	{
		/* No cache hook */
		if ( apply_filters('cachify_skip_cache', false) ) {
			return true;
		}

		/* Conditional Tags */
		if ( self::_is_index() OR is_search() OR is_404() OR is_feed() OR is_trackback() OR is_robots() OR is_preview() OR post_password_required() ) {
			return true;
		}

		/* WooCommerce usage */
		if ( defined('DONOTCACHEPAGE') && DONOTCACHEPAGE ) {
			return true;
		}

		/* Plugin options */
		$options = self::$options;

		/* Request vars */
		if ( ! empty($_POST) OR ( ! empty($_GET) && get_option('permalink_structure') ) ) {
			return true;
		}

		/* Logged in */
		if ( $options['only_guests'] && self::_is_logged_in() ) {
			return true;
		}

		/* Mobile request */
		if ( self::_is_mobile() ) {
			return true;
		}

		/* Post IDs */
		if ( $options['without_ids'] && is_singular() ) {
			if ( in_array( $GLOBALS['wp_query']->get_queried_object_id(), self::_preg_split($options['without_ids']) ) ) {
				return true;
			}
		}

		/* User Agents */
		if ( $options['without_agents'] && isset($_SERVER['HTTP_USER_AGENT']) ) {
			if ( array_filter( self::_preg_split($options['without_agents']), create_function('$a', 'return strpos($_SERVER["HTTP_USER_AGENT"], $a);') ) ) {
				return true;
			}
		}

		return false;
	}


	/**
	* Minify HTML code
	*
	* @since   0.9.2
	* @change  2.0.9
	*
	* @param   string  $data  Original HTML code
	* @return  string         Minified code
	*
	* @hook    array   cachify_minify_ignore_tags
	*/

	private static function _minify_cache($data)
	{
		/* Disabled? */
		if ( ! self::$options['compress_html'] ) {
			return $data;
		}

		/* Avoid slow rendering */
		if ( strlen($data) > 700000) {
			return $data;
		}

		/* Ignore this html tags */
		$ignore_tags = (array)apply_filters(
			'cachify_minify_ignore_tags',
			array(
				'textarea',
				'pre'
			)
		);

		/* Add the script tag */
		if ( self::$options['compress_html'] !== self::MINIFY_HTML_JS ) {
			$ignore_tags[] = 'script';
		}

		/* Empty blacklist? | TODO: Make it better */
		if ( ! $ignore_tags ) {
			return $data;
		}

		/* Convert to string */
		$ignore_regex = implode('|', $ignore_tags);

		/* Minify */
		$cleaned = preg_replace(
			array(
				'/<!--[^\[><](.*?)-->/s',
				'#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:' .$ignore_regex. ')\b))*+)(?:<(?>' .$ignore_regex. ')\b|\z))#'
			),
			array(
				'',
				' '
			),
			$data
		);

		/* Fault */
		if ( strlen($cleaned) <= 1 ) {
			return $data;
		}

		return $cleaned;
	}


	/**
	* Flush total cache
	*
	* @since   0.1
	* @change  2.0
	*
	* @param bool  $clear_all_methods  Flush all caching methods (default: FALSE)
	*/

	public static function flush_total_cache($clear_all_methods = false)
	{
		if ( $clear_all_methods ) {
			/* DB */
			Cachify_DB::clear_cache();

			/* APC */
			Cachify_APC::clear_cache();

			/* HDD */
			Cachify_HDD::clear_cache();

			/* MEMCACHED */
			Cachify_MEMCACHED::clear_cache();
		} else {
			call_user_func(
				array(
					self::$method,
					'clear_cache'
				)
			);
		}

		/* Transient */
		delete_transient('cachify_cache_size');
	}


	/**
	* Assign the cache
	*
	* @since   0.1
	* @change  2.0
	*
	* @param   string  $data  Content of the page
	* @return  string         Content of the page
	*/

	public static function set_cache($data)
	{
		/* Empty? */
		if ( empty($data) ) {
			return '';
		}

		/* Save */
		call_user_func(
			array(
				self::$method,
				'store_item'
			),
			self::_cache_hash(),
			self::_minify_cache($data),
			self::_cache_expires()
		);

		return $data;
	}


	/**
	* Manage the cache
	*
	* @since   0.1
	* @change  2.0
	*/

	public static function manage_cache()
	{
		/* No caching? */
		if ( self::_skip_cache() ) {
			return;
		}

		/* Data present in cache */
		$cache = call_user_func(
			array(
				self::$method,
				'get_item'
			),
			self::_cache_hash()
		);

		/* No cache? */
		if ( empty($cache) ) {
			ob_start('Cachify::set_cache');
			return;
		}

		/* Process cache */
		call_user_func(
			array(
				self::$method,
				'print_cache'
			),
			$cache
		);
	}


	/**
	 * Register CSS
	 *
	 * @since   1.0
	 * @change  2.1.3
	 *
	 * @param   string $hook  Current hook
	 */

	public static function add_admin_resources($hook)
	{
		/* Hooks check */
		if ( $hook !== 'index.php' AND $hook !== 'post.php' ) {
			return;
		}

		/* Plugin data */
		$plugin_data = get_plugin_data(CACHIFY_FILE);

		/* Register css */
		switch($hook) {
			case 'index.php':
				wp_enqueue_style(
					'cachify-dashboard',
					plugins_url('css/dashboard.min.css', CACHIFY_FILE),
					array(),
					$plugin_data['Version']
				);
			break;

			case 'post.php':
				wp_enqueue_script(
					'cachify-post',
					plugins_url('js/post.min.js', CACHIFY_FILE),
					array('jquery'),
					$plugin_data['Version'],
					true
				);
			break;

			default:
			break;
		}
	}


	/**
	* Display a combo select on post publish box
	*
	* @since   2.1.3
	* @change  2.2.2
	*/

	public static function print_flush_dropdown()
	{
		/* Post page only */
		if ( empty($GLOBALS['pagenow']) OR $GLOBALS['pagenow'] !== 'post.php' ) {
			return;
		}

		/* Published posts only */
		if ( empty($GLOBALS['post']) OR ! is_object($GLOBALS['post']) OR $GLOBALS['post']->post_status !== 'publish' ) {
			return;
		}

		/* Check user role */
		if ( ! current_user_can('publish_posts') ) {
			return;
		}

		/* Security */
		wp_nonce_field(CACHIFY_BASE, '_cachify__status_nonce_' .$GLOBALS['post']->ID);

		/* Already saved? */
		$current_action = (int)get_user_meta(
			get_current_user_id(),
			'_cachify_remove_post_type_cache_on_update',
			true
		);

		/* Init vars */
		$dropdown_options = '';
		$available_options = array(
			esc_html__('Total cache', 'cachify'),
			esc_html__('Page cache', 'cachify')
		);

		/* Select options */
		foreach( $available_options as $key => $value ) {
			$dropdown_options .= sprintf(
				'<option value="%1$d" %3$s>%2$s</option>',
				$key,
				$value,
				selected($key, $current_action, false)
			);
		}

		/* Output */
		echo sprintf(
			'<div class="misc-pub-section" style="border-top:1px solid #eee">
				<label for="cachify_status">
					%1$s: <span id="output-cachify-status">%2$s</span>
				</label>
				<a href="#" class="edit-cachify-status hide-if-no-js">%3$s</a>

				<div class="hide-if-js">
					<select name="_cachify_remove_post_type_cache_on_update" id="cachify_status">
						%4$s
					</select>

					<a href="#" class="save-cachify-status hide-if-no-js button">%5$s</a>
	 				<a href="#" class="cancel-cachify-status hide-if-no-js button-cancel">%6$s</a>
	 			</div>
			</div>',
			esc_html__( 'Remove', 'cachify' ),
			$available_options[$current_action],
			esc_html__( 'Edit', 'cachify' ),
			$dropdown_options,
			esc_html__( 'OK', 'cachify' ),
			esc_html__( 'Cancel', 'cachify' )
		);
	}


	/**
	* Add options page
	*
	* @since   1.0
	* @change  2.2.2
	*/

	public static function add_page()
	{
		add_options_page(
			__( 'Cachify', 'cachify' ),
			__( 'Cachify', 'cachify' ),
			'manage_options',
			'cachify',
			array(
				__CLASS__,
				'options_page'
			)
		);
	}


	/**
	* Available caching methods
	*
	* @since  2.0.0
	* @change 2.1.3
	*
	* @param  array  $methods  Array of all available methods
	* @return array            Array of actually available methods
	*/

	private static function _method_select()
	{
		/* Defaults */
		$methods = array(
			self::METHOD_DB  => esc_html__('Database', 'cachify'),
			self::METHOD_APC => 'APC',
			self::METHOD_HDD => esc_html__('Hard disk', 'cachify'),
			self::METHOD_MMC => 'Memcached'
		);

		/* APC */
		if ( ! Cachify_APC::is_available() ) {
			unset($methods[1]);
		}

		/* Memcached? */
		if ( ! Cachify_MEMCACHED::is_available() ) {
			unset($methods[3]);
		}

		/* HDD */
		if ( ! Cachify_HDD::is_available() ) {
			unset($methods[2]);
		}

		return $methods;
	}


	/**
	* Minify cache dropdown
	*
	* @since   2.1.3
	* @change  2.1.3
	*
	* @return  array    Key => value array
	*/

	private static function _minify_select()
	{
		return array(
			self::MINIFY_DISABLED  => esc_html__( 'No minify', 'cachify' ),
			self::MINIFY_HTML_ONLY => esc_html__( 'HTML', 'cachify' ),
			self::MINIFY_HTML_JS   => esc_html__( 'HTML + Inline JavaScript', 'cachify' )
		);
	}


	/**
	* Register the language file
	*
	* @since   2.1.3
	* @change  2.1.3
	*/

	public static function register_textdomain()
	{
		load_plugin_textdomain(
			'cachify',
			false,
			CACHIFY_DIR . '/lang'
		);
	}

	/**
	* Register settings
	*
	* @since   1.0
	* @change  1.0
	*/

	public static function register_settings()
	{
		register_setting(
			'cachify',
			'cachify',
			array(
				__CLASS__,
				'validate_options'
			)
		);
	}


	/**
	* Validate options
	*
	* @since   1.0.0
	* @change  2.1.3
	*
	* @param   array  $data  Array of form values
	* @return  array         Array of validated values
	*/

	public static function validate_options($data)
	{
		/* Empty data? */
		if ( empty($data) ) {
			return;
		}

		/* Flush cache */
		self::flush_total_cache(true);

		/* Notification */
		if ( self::$options['use_apc'] != $data['use_apc'] && $data['use_apc'] >= self::METHOD_APC ) {
			add_settings_error(
				'cachify_method_tip',
				'cachify_method_tip',
				sprintf(
					'%s [<a href="https://github.com/pluginkollektiv/cachify/wiki" target="_blank">?</a>]',
					esc_html__('The server configuration file (e.g. .htaccess) needs to be adjusted', 'cachify')
				),
				'updated'
			);
		}

		/* Return */
		return array(
			'only_guests'	 	=> (int)(!empty($data['only_guests'])),
			'compress_html'	 	=> (int)$data['compress_html'],
			'cache_expires'	 	=> (int)(@$data['cache_expires']),
			'without_ids'	 	=> (string)sanitize_text_field(@$data['without_ids']),
			'without_agents' 	=> (string)sanitize_text_field(@$data['without_agents']),
			'use_apc'	 	 	=> (int)$data['use_apc'],
			'reset_on_comment'  => (int)(!empty($data['reset_on_comment']))
		);
	}


	/**
	* Display options page
	*
	* @since   1.0
	* @change  2.2.2
	*/

	public static function options_page()
	{ ?>
		<style>
			#cachify_settings input[type="text"],
			#cachify_settings input[type="number"] {
				height: 30px;
			}
		</style>

		<div class="wrap" id="cachify_settings">
			<h2>
				Cachify
			</h2>

			<form method="post" action="options.php">
				<?php settings_fields('cachify') ?>

				<?php $options = self::_get_options() ?>

				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<?php esc_html_e('Cache method', 'cachify') ?>
						</th>
						<td>
							<label for="cachify_cache_method">
								<select name="cachify[use_apc]" id="cachify_cache_method">
									<?php foreach( self::_method_select() as $k => $v ) { ?>
										<option value="<?php echo esc_attr($k) ?>" <?php selected($options['use_apc'], $k); ?>><?php echo esc_html($v) ?></option>
									<?php } ?>
								</select>
							</label>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<?php esc_html_e('Cache expiration', 'cachify') ?>
						</th>
						<td>
							<label for="cachify_cache_expires">
								<input type="number" min="0" step="1" name="cachify[cache_expires]" id="cachify_cache_expires" value="<?php echo esc_attr($options['cache_expires']) ?>" class="small-text" />
								<?php esc_html_e('Hours', 'cachify') ?>
							</label>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<?php esc_html_e('Cache generation', 'cachify') ?>
						</th>
						<td>
							<fieldset>
								<label for="cachify_only_guests">
									<input type="checkbox" name="cachify[only_guests]" id="cachify_only_guests" value="1" <?php checked('1', $options['only_guests']); ?> />
									<?php esc_html_e('No cache generation by logged in users', 'cachify') ?>
								</label>

								<br />

								<label for="cachify_reset_on_comment">
									<input type="checkbox" name="cachify[reset_on_comment]" id="cachify_reset_on_comment" value="1" <?php checked('1', $options['reset_on_comment']); ?> />
									<?php esc_html_e('Flush the cache at new comments', 'cachify') ?>
								</label>
							</fieldset>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<?php esc_html_e('Cache exceptions', 'cachify') ?>
						</th>
						<td>
							<fieldset>
								<label for="cachify_without_ids">
									<textarea name="cachify[without_ids]" id="cachify_without_ids" rows="1" placeholder="<?php esc_attr_e( 'e.g. 1,2,3', 'cachify' ); ?>"><?php echo esc_attr($options['without_ids']) ?></textarea>
									esc_html__( 'Post/Pages-IDs (separated by commas)', 'cachify' );
								</label>

								<br />

								<label for="cachify_without_agents">
									<textarea name="cachify[without_agents]" id="cachify_without_agents" rows="1" placeholder="<?php esc_attr_e( 'e.g. MSIE 6, Opera', 'cachify' ); ?>"><?php echo esc_attr($options['without_agents']) ?></textarea>
									esc_html__( 'Browser User-Agents (separated by commas)', 'cachify' );
								</label>
							</fieldset>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<?php esc_html_e('Cache minify', 'cachify') ?>
						</th>
						<td>
							<label for="cachify_compress_html">
								<select name="cachify[compress_html]" id="cachify_compress_html">
									<?php foreach( self::_minify_select() as $k => $v ) { ?>
										<option value="<?php echo esc_attr($k) ?>" <?php selected($options['compress_html'], $k); ?>>
											<?php echo esc_html($v) ?>
										</option>
									<?php } ?>
								</select>
							</label>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">
							<?php submit_button() ?>
						</th>
						<td>
							<a href="<?php esc_html_e( 'https://wordpress.org/plugins/cachify/faq/', 'cachify' ) ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'FAQ', 'cachify' ) ?></a> &bull; <a href="https://github.com/pluginkollektiv/cachify/wiki" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Manual', 'cachify' ) ?></a> &bull; <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LG5VC9KXMAYXJ" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'PayPal', 'cachify' ) ?></a> &bull; <a href="https://wordpress.org/support/plugin/cachify" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Support', 'cachify' ) ?></a>
						</td>
					</tr>
				</table>
			</form>
		</div><?php
	}
}
