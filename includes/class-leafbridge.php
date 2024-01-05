<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    LeafBridge
 * @subpackage LeafBridge/includes
 * @author     Surge <websites@surge.global>
 */

require LEAFBRIDGE_PATH . '/vendor/autoload.php';

use GraphQL\Client;
use GraphQL\Mutation;
use GraphQL\RawObject;
use GraphQL\InlineFragment;
use GraphQL\Exception\QueryError;
use GraphQL\Query;
use GraphQL\QueryBuilder\QueryBuilder;
use GraphQL\Variable;
use GuzzleHttp\Promise;
use GuzzleHttp\Middleware;

class LeafBridge {
	static $basename = null;
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      LeafBridge_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if (defined('LEAFBRIDGE_VERSION')) {
			$this->version = LEAFBRIDGE_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$plugin_version = time();

		$this->plugin_name = 'LeafBridge';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->add_plugin_menu();
		$this->plugin_settings_load();

		add_action('init', [$this, 'add_lb_capability_to_administrator']);
		add_action('init', [$this, 'create_lb_contributor_role']);
	}
	function add_lb_capability_to_administrator() {
		$administrator_role = get_role('administrator');
		$administrator_role->add_cap('read_lb_settings');
	}
	function create_lb_contributor_role() {
		// Add the custom role 'lb_contributor'
		add_role(
			'_lb_contributor',
			__('LB Contributor'),
			array(
				'read' => true,
				'read_lb_settings' => true,
			)
		);
	}
	function add_lb_capability_to_lbContributor() {
		$lbContributor_role = get_role('lb_contributor');
		// Add the custom capability 'read_my_data' to the Administrator role
		$lbContributor_role->add_cap('read_lb_settings');
	}



	/**
	 * graphql query client via proxy
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_client() {


		$leafbridge_settings = get_option('leafbridge-settings');
		$api_key = $leafbridge_settings['leafbridge-settings-api-key'];

		$leafbridge_license = get_option('leafbridge-license-data');   // NEW License features
		$store_actn = $leafbridge_license['actn']; // NEW License features

		$client = new Client(
			PROXY_URL . '/graphql',
			[
				'Authorization' => 'Bearer ' . $api_key,
				'Proxy-Authorization-lb' => $store_actn,
				'headers'  => array('Nonce' => '"' . md5(uniqid(mt_rand() . time(), true)) . '"'),
			]
		);

		return $client;
	}


	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-leafbridge-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-leafbridge-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-leafbridge-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the products.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-leafbridge-products.php';

		/**
		 * The class responsible for defining all actions that occur in the retailers.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-leafbridge-retailers.php';


		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-leafbridge-public.php';


		/**
		 * Cart related functions
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-leafbridge-public-cart.php';

		/**
		 * Retailer related functions for frontend
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-leafbridge-public-retailer.php';


		/**
		 * Order related functions for frontend
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-leafbridge-public-orders.php';


		/**
		 * products related functions for frontend
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-leafbridge-public-products.php';

		/**
		 * Data Syncing
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-leafbridge-sync.php';


		$this->loader = new LeafBridge_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sh_Projects_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new LeafBridge_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}




	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 * // $hook, $component, $callback, $priority = 10, $accepted_args = 1 
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new LeafBridge_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		//$this->loader->add_action( 'products_sub_menu', $plugin_admin, 'admin_menu'); 
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new LeafBridge_Public($this->get_plugin_name(), $this->get_version());
		$plugin_public_cart = new LeafBridge_Public_Cart($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		//wp_localize_script( $this->get_plugin_name.'_ajax', 'leafbridge_public_ajax_obj1', array('ajaxurl' => admin_url('admin-ajax.php'),'nonce' => wp_create_nonce('leafbridge-ajax1-nonce')));


	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    LeafBridge_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}


	/**
	 * Admin menu
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_menu() {
		add_action(
			'admin_menu',
			function () {
				add_menu_page('LeafBridge', 'LeafBridge', 'read_lb_settings', 'leafbridge', array(__CLASS__, 'leafbridge_admin_settings'), 'dashicons-store', 6);
				add_submenu_page('leafbridge', 'Settings', 'Settings', 'read_lb_settings', 'leafbridge');
				add_submenu_page('leafbridge', 'Products', 'Products', 'manage_options', 'edit.php?post_type=product');
				add_submenu_page('leafbridge', 'Categories', 'Categories', 'manage_options', 'edit-tags.php?taxonomy=categories&post_type=product');
				add_submenu_page('leafbridge', 'Retailers', 'Retailers', 'manage_options', 'edit.php?post_type=retailer');
				add_submenu_page('leafbridge', 'Shortcode Generator', 'Shortcode Generator', 'manage_options', 'leafbridge-shortcode-generator', array(__CLASS__, 'lb_shortcode_generator_callback'));
				add_submenu_page('leafbridge', 'LeafBridge Documentation', 'Documentation', 'manage_options', 'leafbridge-documentation', array(__CLASS__, 'lb_doc_html_callback'));
			}
		);
	}


	public static function lb_shortcode_generator_callback() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/leafbridge-shortcode-generater.php';
	}

	public static function lb_doc_html_callback() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/leafbridge-plugin-doc.php';
	}

	/**
	 * Admin settings page
	 *
	 * @since    1.0.0
	 */
	public static function leafbridge_admin_settings() {
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/leafbridge-admin-settings.php';
	}

	public static function plugin_settings_load() {
		add_filter('plugin_action_links', 'leafbridge_plugin_settings_link', 10, 2);
	}

	public static function leafbridge_plugin_settings_link($links, $file) {
		if ($file == 'leafbridge/leafbridge.php') {
			/* Insert the link at the end*/
			$links['settings'] = sprintf('<a href="%s"> %s </a>', admin_url('admin.php?page=leafbridge'), __('Settings', 'leafbridge'));
		}
		return $links;
	}


	function products_sub_menu() {
		add_submenu_page('edit.php', 'Products', 'Products', 'manage_options', 'admin.php?page=leafbridge');
	}

	public static function leafbridge_admin_notice__success($message) {
?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e($message, 'leafbridge'); ?></p>
		</div>
	<?php
	}

	public static function leafbridge_admin_notice__error($message) {
	?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e($message, 'leafbridge'); ?></p>
		</div>
<?php
	}
}
?>