<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    LeafBridge
 * @subpackage LeafBridge/includes
 * @author     Surge <websites@surge.global>
 */
class LeafBridge_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */



	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = gmdate('Y.m.d.h.i.s', strtotime(date('Y-m-d h:i:s')));

		// add_action( 'init', array($this,'delete_prods'));
		// add_filter( 'body_class', array($this,'leafbridge_theme_classes') );

		// disable caching headers
		add_action('init', array($this, 'nocache_headers'));

		// remove _ for category page titles
		add_filter('document_title', array($this, 'process_category_seo_titles'));
		add_filter('get_the_archive_title', array($this, 'process_category_seo_titles'));

		// add_shortcode('leafbridge_shop', array( $this , 'get_leafbridge_default_shop' ) ); // [dont use !] old shortcode for shop page
		add_shortcode('leafbridge_shop_wizard', array($this, 'get_leafbridge_shop_wizard')); //new shortcode for shop page
		add_shortcode('retailer_based_store', array($this, 'retailer_based_store')); //show products based on retailer and not changable by the wizard
		add_shortcode('leafbridge-order-status', array($this, 'get_leafbridge_order_status')); //new shortcode for order-status page
		add_shortcode('leafbridge-featured-products', array($this, 'leafbridge_featured_products')); //show featured products on shortcode
		add_shortcode('leafbridge-special-products', array($this, 'leafbridge_special_products')); //show specials products on shortcode
		add_shortcode('leafbridge-product-categories', array($this, 'leafbridge_product_categories')); //show product categories on shortcode

		add_shortcode('leafbridge-product-single-page', array($this, 'leafbridge_product_single_page')); //show product categories on shortcode
		add_shortcode('leafbridge-product-single-category-page', array($this, 'leafbridge_product_single_category_page')); //show product categories on shortcode
		add_shortcode('leafbridge_selection_wizard_v2', array($this, 'leafbridge_selection_wizard_v2_new')); //show the modal or navigatin wizard
		// add_shortcode('leafbridge_link_wizard_age_popup', array( $this , 'leafbridge_link_wizard_age_popup' ) ); //show the modal or navigatin wizard
		add_shortcode('leafbridge_specific_product_filter', array($this, 'leafbridge_specific_product_filter')); //specific product filters
		add_shortcode('leafbridge-special-menu-cards', array($this, 'leafbridge_special_menu_cards')); //show special menu links as cards on shortcode
		add_shortcode('leafbridge-breadcrumbs', array($this, 'leafbridge_breadcrumbs_function')); //show special menu links as cards on shortcode
		add_shortcode('leafbridge-search-bar', array($this, 'leafbridge_search_bar')); //show the search bar on shortcode
		add_shortcode('leafbridge-retailer-name-bar', array($this, 'leafbridge_retailer_name_bar')); //show the search bar on shortcode
		add_shortcode('leafbridge-retailer-details', array($this, 'retailer_details_shortcode')); //show the retailer details on shortcode

		// age confirmation before viewing any page
		// add_action( 'wp_footer', array($this,'wizard_popup_header'));
		add_action('wp_footer', array($this, 'call_the_new_wizard'), 100, 1);
		add_action('wp_footer', array($this, 'leafbridge_cart_render'), 100, 1);

		// Removes LB Scripts on Divi Builder and Elementor Builder
		add_action('wp_enqueue_scripts', array($this, 'remove_lb_scripts_on_divi_builder'), 500);

		// initialize ajax php part for the show_delivery_pickup_func
		add_action('wp_ajax_show_delivery_pickup_ajax', array($this, 'show_delivery_pickup_func'));
		add_action('wp_ajax_nopriv_show_delivery_pickup_ajax', array($this, 'show_delivery_pickup_func'));

		// initialize ajax php part for the wizard_show_products
		add_action('wp_ajax_wizard_show_products', array($this, 'wizard_show_products_func'));
		add_action('wp_ajax_nopriv_wizard_show_products', array($this, 'wizard_show_products_func'));

		add_action('wp_ajax_leafbridge_products_search', array($this, 'leafbridge_products_search'));
		add_action('wp_ajax_nopriv_leafbridge_products_search', array($this, 'leafbridge_products_search'));

		add_action('wp_ajax_show_featured_products_func', array($this, 'show_featured_products_func'));
		add_action('wp_ajax_nopriv_show_featured_products_func', array($this, 'show_featured_products_func'));

		add_action('wp_ajax_leafbridge_shop_add_products_to_cart', array($this, 'leafbridge_shop_add_products_to_cart'));
		add_action('wp_ajax_nopriv_leafbridge_shop_add_products_to_cart', array($this, 'leafbridge_shop_add_products_to_cart'));

		add_action('wp_ajax_leafbridge_get_cart_items', array($this, 'leafbridge_get_cart_items'));
		add_action('wp_ajax_nopriv_leafbridge_get_cart_items', array($this, 'leafbridge_get_cart_items'));

		add_action('wp_ajax_leafbridge_remove_cart_item', array($this, 'leafbridge_remove_cart_item'));
		add_action('wp_ajax_nopriv_leafbridge_remove_cart_item', array($this, 'leafbridge_remove_cart_item'));

		add_action('wp_ajax_leafbridge_update_cart_item_quantity', array($this, 'leafbridge_update_cart_item_quantity'));
		add_action('wp_ajax_nopriv_leafbridge_update_cart_item_quantity', array($this, 'leafbridge_update_cart_item_quantity'));

		add_action('wp_ajax_leafbridge_reset_checkout', array($this, 'leafbridge_reset_checkout'));
		add_action('wp_ajax_nopriv_leafbridge_reset_checkout', array($this, 'leafbridge_reset_checkout'));

		add_action('wp_ajax_leafbridge_order_details', array($this, 'leafbridge_order_details'));
		add_action('wp_ajax_nopriv_leafbridge_order_details', array($this, 'leafbridge_order_details'));

		add_action('wp_ajax_leafbridge_single_product', array($this, 'leafbridge_single_product'));
		add_action('wp_ajax_nopriv_leafbridge_single_product', array($this, 'leafbridge_single_product'));

		add_action('wp_ajax_get_retailer_specials', array($this, 'leafbridge_get_retailer_specials'));
		add_action('wp_ajax_nopriv_get_retailer_specials', array($this, 'leafbridge_get_retailer_specials'));

		add_action('wp_ajax_get_default_retailer', array($this, 'get_default_retailer_function'));
		add_action('wp_ajax_nopriv_get_default_retailer', array($this, 'get_default_retailer_function'));

		add_action('wp_ajax_get_retailer_special_menus', array($this, 'leafbridge_get_retailer_special_menus'));
		add_action('wp_ajax_nopriv_get_retailer_special_menus', array($this, 'leafbridge_get_retailer_special_menus'));

		add_action('wp_ajax_load_retailer_name', array($this, 'load_retailer_name'));
		add_action('wp_ajax_nopriv_load_retailer_name', array($this, 'load_retailer_name'));


		// add menu item to nav
		// add_filter('wp_nav_menu_items', array($this, 'add_cart_to_nav'), 10, 2);

		// add page slug to body css class
		add_filter('body_class', array($this, 'add_slug_body_class'));

		// add page templates
		add_filter('template_include', array($this, 'prod_page_templates'));
	}

	// disable caching headers
	public static function nocache_headers()
	{
		nocache_headers();
	}

	// remove _ for category page titles
	public static function process_category_seo_titles($title)
	{
		$replace_titles = array(
			array(
				'check' => 'Pre_rolls',
				'replace' => 'Pre Rolls',
			),
			array(
				'check' => 'Cbd',
				'replace' => 'CBD',
			),
		);
		$found_check = "";
		$found_replace = "";
		for ($i = 0; $i < count($replace_titles); $i++) {
			$rt_node = $replace_titles[$i];
			if (str_contains($title, $rt_node['check'])) {
				$found_check = $rt_node['check'];
				$found_replace = $rt_node['replace'];
			}
		}

		if ($found_check != "") {
			$title = str_replace($found_check, $found_replace, $title);
		} else {
			$title = $title;
		}
		return $title;
	}

	// function to show loading animation
	public static function loading_animation()
	{
		return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto; animation-play-state: running; animation-delay: 0s;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"><path fill="none" stroke="#f4bd33" stroke-width="8" stroke-dasharray="42.76482137044271 42.76482137044271" d="M24.3 30C11.4 30 5 43.3 5 50s6.4 20 19.3 20c19.3 0 32.1-40 51.4-40 C88.6 30 95 43.3 95 50s-6.4 20-19.3 20C56.4 70 43.6 30 24.3 30z" stroke-linecap="round" style="transform: scale(0.8); transform-origin: 50px 50px; animation-play-state: running; animation-delay: 0s;"><animate attributeName="stroke-dashoffset" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0;256.58892822265625" style="animation-play-state: running; animation-delay: 0s;"/></path></svg>';
		// return '3';
	}

	public static function loading_spinner_pulse()
	{
		return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"><rect x="17.5" y="30" width="15" height="40" fill="#8d8d8d"> <animate attributeName="y" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="18;30;30" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.2s"></animate> <animate attributeName="height" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="64;40;40" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.2s"></animate></rect><rect x="42.5" y="30" width="15" height="40" fill="#153f66"> <animate attributeName="y" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="20.999999999999996;30;30" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.1s"></animate> <animate attributeName="height" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="58.00000000000001;40;40" keySplines="0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.1s"></animate></rect><rect x="67.5" y="30" width="15" height="40" fill="#8d8d8d"> <animate attributeName="y" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="20.999999999999996;30;30" keySplines="0 0.5 0.5 1;0 0.5 0.5 1"></animate> <animate attributeName="height" repeatCount="indefinite" dur="1s" calcMode="spline" keyTimes="0;0.5;1" values="58.00000000000001;40;40" keySplines="0 0.5 0.5 1;0 0.5 0.5 1"></animate></rect></svg>';
	}
	public function delete_prods()
	{
		$allposts = get_posts(array('post_type' => 'product', 'numberposts' => -1));
		foreach ($allposts as $eachpost) {
			wp_delete_post($eachpost->ID, true);
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sh_Projects_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sh_Projects_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		// wp_enqueue_style( $this->plugin_name.'-fontawesome', plugin_dir_url( __FILE__ ) . 'css/fontawesome.min.css', array(), $this->version, 'all' );
		wp_enqueue_style($this->plugin_name . '-fontawesome-all', plugin_dir_url(__FILE__) . 'css/all.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . '-toast', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . '_select2', plugin_dir_url(__FILE__) . 'css/select2.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . '_rSlider', plugin_dir_url(__FILE__) . 'css/rSlider.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . '_owlcarousel', plugin_dir_url(__FILE__) . 'css/owl.carousel.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . '_swiperJS', plugin_dir_url(__FILE__) . 'css/swiper-bundle.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/leafbridge-public.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . '_responsive', plugin_dir_url(__FILE__) . 'css/leafbridge-public-responsive.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . '_builder_support', plugin_dir_url(__FILE__) . 'css/leafbridge-page-builder-support.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sh_Projects_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sh_Projects_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script($this->plugin_name . '_toast', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js', array('jquery'), $this->version, true);
		// wp_enqueue_script( $this->plugin_name.'_slimscroll', 'https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script($this->plugin_name . '_select2', plugin_dir_url(__FILE__) . 'js/select2.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script($this->plugin_name . '_rSlider', plugin_dir_url(__FILE__) . 'js/rSlider.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . '_owlcarousel', plugin_dir_url(__FILE__) . 'js/owl.carousel.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script($this->plugin_name . '_swiperJS', plugin_dir_url(__FILE__) . 'js/swiper-bundle.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/leafbridge-public.js', array('jquery'), $this->version, true);
		wp_enqueue_script($this->plugin_name . '_ajax', plugin_dir_url(__FILE__) . 'js/leafbridge-public-ajax.js', array('jquery'), $this->version, true);
		wp_localize_script($this->plugin_name . '_ajax', 'leafbridge_public_ajax_obj', array('ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('leafbridge-ajax-nonce')));
	}

	/**
	 * Add menu item cart to primary menu
	 */
	public function add_cart_to_nav($items, $args)
	{
		$items .= '<li><a id="nav_open_the_cart" title="Open Cart" href="#"><i class="fa-solid fa-cart-shopping"></i><span class="cart_count"></span></a></li>';
		return $items;
	}

	/**
	 * Add page slug to body class
	 */
	public function add_slug_body_class($classes)
	{
		$LBsttngs = get_option('leafbridge-settings');
		$disable_wp_nonce = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		$version_number_class = ' ' . str_replace('.', '_', LEAFBRIDGE_VERSION) . ' ';
		$block_theme_enabled = (wp_is_block_theme()) ? "block_theme_enabled" : "non_block_theme";
		$block_theme_enabled .= $version_number_class . " kjkszpj_" . $disable_wp_nonce;
		global $post;
		if (isset($post)) {
			$classes[] = $post->post_type . '-' . $post->post_name . ' leafbridge_theme_1 ' . $block_theme_enabled;
		} else {
			$classes[] = ' leafbridge_theme_1 ' . $block_theme_enabled;
		}
		return $classes;
	}

	//----------------- START OF PUBLIC AJAX FUNCTIONS
	/*
	* Ajax function - show delivery options based on retailer selection
	*/
	public function show_delivery_pickup_func()
	{
		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');
		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start

		$json = array();

		if ($flag_check_nonce) {

			$fulfillmentOptions = array();
			$retailer_id = $_REQUEST['retailer_id'];
			$json["retailer_id"] = $retailer_id;
			$json["check_nonce_ajax"] = $check_nonce;
			$retailers_array = array();

			$args = array(
				'post_type' => 'retailer',
			);

			$retailer_loop = new WP_Query($args);
			$arr_coutner = 0;

			while ($retailer_loop->have_posts()) {
				$retailer_loop->the_post();
				$retailer_all_data = get_post_meta(get_the_ID(), '_lb_retailer_options_all', true);
				$retailer_options = unserialize($retailer_all_data['_lb_retailer_options']);

				if ($retailer_id == $retailer_all_data['_lb_retailer_id']) {
					// $json["data"][get_the_id()]['fulfillmentOptions'] = $retailer_options['fulfillmentOptions'] ;
					// $json["data"][get_the_id()]['all_data'] = $retailer_options ;
					$json["data"]['fulfillmentOptions'] = $retailer_options['fulfillmentOptions'];
					$json["data"]['all_data'] = $retailer_options;
					$fulfillmentOptions = $retailer_options['fulfillmentOptions'];
				}
			}

			$returnHTML = '<div class="wizardbox_product_collection_buttons"><div class="wizard_box_button_group">';
			$returnHTML .= ($fulfillmentOptions['delivery']) ? '<button type="button" name="button" class="prod_collection_btn leaf_bridge_btn" style="" value="DELIVERY">' . __('delivery', 'leafbridge') . '</button>' : '';
			$returnHTML .= ($fulfillmentOptions['pickup']) ? '<button type="button" name="button" class="prod_collection_btn leaf_bridge_btn" style="" value="PICKUP">' . __('pickup', 'leafbridge') . '</button>' : '';
			$returnHTML .= '</div></div>';

			$json["returnHTML"] = $returnHTML;
			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		// always die at the end of ajax php function otherwise it will return a false
		die();
	}

	/*
	* Ajax function - show products based on retailer selection
	*/
	public function wizard_show_products_func()
	{
		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');
		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start

		$wizard_data = $_REQUEST['wizard_data'];

		$prod_categories = $_REQUEST['prod_categories'];
		$swiper_wrapper = isset($prod_categories['prod_slider']) ? true : false;
		$prods_pageNumber = isset($_REQUEST['prods_pageNumber']) ? (int) $_REQUEST['prods_pageNumber'] : 1;
		$search_key_word = isset($_REQUEST['search_key_word']) ? $_REQUEST['search_key_word'] : "";
		$filter_attributes = $_REQUEST['filter_attributes'];
		$sort_order = isset($filter_attributes['sort_order']) ? explode('_',  $filter_attributes['sort_order'], 2) : array("ASC", "NAME");
		$products_filter_attrs = array();

		$json = array();
		if ($flag_check_nonce) {
			// if there's special id , products are being called from specials page. in that case show all products. otherwise limit 20 products per page
			$prods_per_page = (isset($prod_categories['special_id']) && ($prod_categories['special_id'] != null)) ? 10000 : 20;

			// check if products count is passed
			$prods_per_page = (isset($prod_categories['fetch_prod_count']) && ($prod_categories['fetch_prod_count'] != null)) ? intval($prod_categories['fetch_prod_count']) : $prods_per_page;

			// $prods_pageNumber should be n-1 eg : if page 1 - $prods_pageNumber shold be 0 if page 2 - $prods_pageNumber should be 1
			// $prods_pageNumber = 0;
			$current_products_set = $prods_per_page * $prods_pageNumber;
			$prods_pageNumber = $prods_pageNumber - 1;
			// $prods_pageNumber = $prods_pageNumber - 1;
			$prods_offset = (int) $prods_per_page * $prods_pageNumber;

			$retailer_id = $wizard_data['retailer_id'];
			$menutype   = (isset($wizard_data['menu_type'])) ? $wizard_data['menu_type'] : false;
			$pagination = "{ limit: " . $prods_per_page . ", offset: " . $prods_offset . " }";
			// $filter     = ($prod_categories == "all") ? "{ }" : "{ category : ".$prod_categories."}";
			$sort       = "{ direction: " . $sort_order[1] . ", key: " . $sort_order[0] . " }";
			$filter = "{ }";

			// set filter args to graphql
			if (is_array($prod_categories)) {

				$prod_categories['potency_cbd'] = (isset($prod_categories['potency_cbd']) && ($prod_categories['potency_cbd'] != null)) ? explode('_', $prod_categories['potency_cbd']) : false;
				$prod_categories['potency_thc'] = (isset($prod_categories['potency_thc']) && ($prod_categories['potency_thc'] != null)) ? explode('_', $prod_categories['potency_thc']) : false;

				// potency CBD ENUMS set
				if (isset($prod_categories['potency_cbd'][2])) {
					if ($prod_categories['potency_cbd'][2] == "mg") {
						$prod_categories['potency_cbd'][2] = "MILLIGRAMS";
					} elseif ($prod_categories['potency_cbd'][2] == "%") {
						$prod_categories['potency_cbd'][2] = "PERCENTAGE";
					}
				}
				if (isset($prod_categories['potency_thc'][2])) {
					if ($prod_categories['potency_thc'][2] == "mg") {
						$prod_categories['potency_thc'][2] = "MILLIGRAMS";
					} elseif ($prod_categories['potency_thc'][2] == "%") {
						$prod_categories['potency_thc'][2] = "PERCENTAGE";
					}
				}


				if (isset($prod_categories['special_id']) && ($prod_categories['special_id'] != null)) {

					if ($prod_categories['MenuSectionFilter'] == "CUSTOM_SECTION") {
						$special_id = 'name : "' . $prod_categories['special_id'] . '"';
					} else {
						$special_id = 'specialId : "' . $prod_categories['special_id'] . '"';
					}
				}

				$filter =  "{";
				$filter .=	(isset($prod_categories['brands']) && ($prod_categories['brands'] != null)) ? 'brandId: "' . $prod_categories['brands'] . '",' : '';
				$filter .=	(isset($prod_categories['categories']) && ($prod_categories['categories'] != null)) ? 'category: ' . strtoupper($prod_categories['categories']) . ',' : '';
				$filter .=	(isset($prod_categories['subcategory']) && ($prod_categories['subcategory'] != null)) ? 'subcategory: ' . strtoupper($prod_categories['subcategory']) . ',' : '';
				$filter .=	(isset($prod_categories['effects']) && ($prod_categories['effects'] != null)) ? 'effects : ' . strtoupper($prod_categories['effects']) . ',' : '';
				// filter: { menuSection: { type: CUSTOM_SECTION, name: "GGWP" } }
				$filter .=	(isset($prod_categories['MenuSectionFilter']) && ($prod_categories['MenuSectionFilter'] != null)) ? 'menuSection : {type : ' . $prod_categories['MenuSectionFilter'] . ' ,' . $special_id . '},' : '';
				// $filter .=	"posMetaData: PosMetaDataFilter";
				$filter .=	(isset($prod_categories['potency_cbd']) && ($prod_categories['potency_cbd'] != null)) ? 'potencyCbd: { min : ' . $prod_categories['potency_cbd'][0] . ' , max : ' . $prod_categories['potency_cbd'][1] . ' , unit : ' . $prod_categories['potency_cbd'][2] . ' }' : '';
				$filter .=	(isset($prod_categories['potency_thc']) && ($prod_categories['potency_thc'] != null)) ? 'potencyThc: { min : ' . $prod_categories['potency_thc'][0] . ' , max : ' . $prod_categories['potency_thc'][1] . ' , unit : ' . $prod_categories['potency_thc'][2] . ' },' : '';

				$filter .=	(isset($prod_categories['search_keyword']) && ($prod_categories['search_keyword'] != null)) ? 'search: "' . $prod_categories['search_keyword'] . '",' : '';
				$filter .=	(isset($prod_categories['strainType']) && ($prod_categories['strainType'] != null)) ? 'strainType: ' . $prod_categories['strainType'] . ',' : '';
				$filter .=	(isset($prod_categories['weight']) && ($prod_categories['weight'] != null)) ? 'weights: "' . str_replace("%2F", "/", $prod_categories['weight']) . '",' : '';

				$filter .= "}";
			} else {
				if ($prod_categories == "all" && $search_key_word == "") {
					$filter = "{ }";
				} elseif ($prod_categories == "all" && $search_key_word != "") {
					$filter = '{ search : "' . $search_key_word . '" }';
				} elseif ($prod_categories != "all" && $search_key_word == "") {
					$filter = '{ category : ' . $prod_categories . ' }';
				} elseif ($prod_categories != "all" && $search_key_word != "") {
					$filter = '{ category : ' . $prod_categories . ' , search : "' . $search_key_word . '" }';
				} else {
					$filter = "{ }";
				}
			}
			$soldout_amount = 0;

			$LeafBridge_Products = new LeafBridge_Products();
			$products_response = $LeafBridge_Products->fetch_retailer_products($retailer_id, $menutype, $pagination, $filter, $sort);

			$products_list = isset($products_response['result']) ? $products_response['result'] : null;

			if ((is_array($products_list)) && (count($products_list) > 0) && (isset($products_list)) && ($products_list != null)) {
				$products_count = (int) $products_response['productsCount'];
				$products_weights = $products_response['weights'];

				$pagination_show_current_page_text = ceil($products_count / $prods_per_page);
				$pagination_show_current_page_text = ($products_count !== 0)  ? 'Page ' . ($current_products_set / $prods_per_page) . ' / ' . $pagination_show_current_page_text : '';
				// $products_list_debug = $LeafBridge_Products->debug_fetch_retailer_products($retailer_id, $menutype , $pagination , $filter , $sort );
				$products_html = "";
				// render set of products html
				foreach ($products_list as $key => $product_node) {
					$render_product_box = $this->render_product_box($product_node, $retailer_id, $menutype);

					// check if product boxes are being called for sliders or not
					if ($swiper_wrapper && (strlen($render_product_box) > 0)) {
						$products_html .= '<div class="swiper-slide" prod_box_chars="' . strlen($render_product_box) . '">' . $render_product_box . '</div>';
					} else {
						$products_html .= $render_product_box;
					}

					$products_filter_attrs[$key] = array(
						'category' => $product_node['category'],
						'subcategory' => $product_node['subcategory'],
						'brand' => $product_node['brand'],
						'effects' => $product_node['effects'],
						'potencyCbd' => $product_node['potencyCbd'],
						'potencyThc' => $product_node['potencyThc'],
						'strainType' => $product_node['strainType'],
						'variants' => $product_node['variants'],
					);
				}
			} else {
				if ($search_key_word !== "") {
					$products_html = '<p class="lb-no-product-text">Could not find products for "' . $search_key_word . '"</p>';
				} elseif (($prods_pageNumber != 0)) {
					$products_html = '<p class="lb-no-product-text">Could not find more products !</p>';
				} elseif ($prod_categories['special_id'] != null) {
					$products_html = '<p class="lb-no-product-text">Sorry, we are currently sold out. Please try another selection.</p>';
				} else {
					$products_html = '<p class="lb-no-product-text">Could not find products !</p>';
				}
			}

			$json['products_list'] = $products_list;
			$json['menutype'] = $menutype;
			$json['pagination_show_current_page_text'] = $pagination_show_current_page_text;

			$json['products_count'] = $products_count;
			$json['current_products_set'] = $current_products_set;
			$json['products_list_count'] = ($products_list != null) ? count($products_list) : 0;
			$json['prods_per_page'] = $prods_per_page;
			$json['prods_pageNumber'] = $prods_pageNumber;

			$json['products_html'] = $products_html;
			$json['filter'] = $filter;
			$json['prod_slider'] = $swiper_wrapper;

			$temp_filter_attrs = array(
				'category' => array(),
				'subcategory' => array(),
				'brand' => array(),
				'effects' => array(),
				'potencyCbd' => array(),
				'potencyThc' => array(),
				'strainType' => array(),
				'variants' => array(),
			);
			// prepare array for search filter attribues
			foreach ($products_filter_attrs as $key => $filter_attr) {
				// array_push($temp_filter_attrs['category'],$filter_attr['category']);
				(!in_array($filter_attr['category'], $temp_filter_attrs['category'])) ? array_push($temp_filter_attrs['category'], $filter_attr['category']) : '';
				(!in_array($filter_attr['subcategory'], $temp_filter_attrs['subcategory'])) ? array_push($temp_filter_attrs['subcategory'], $filter_attr['subcategory']) : '';
				(!in_array($filter_attr['strainType'], $temp_filter_attrs['strainType'])) ? array_push($temp_filter_attrs['strainType'], $filter_attr['strainType']) : '';

				$prod_local_slug = (isset($filter_attr['brand']['name']) && ($filter_attr['brand']['name'] != null)) ? preg_replace('/[^A-Za-z0-9-]+/', '_', $filter_attr['brand']['name']) : '';
				$temp_filter_attrs['brand'][$prod_local_slug] = $filter_attr['brand']; // add unique brand nodes

				foreach ($filter_attr['effects'] as $effect_key => $effect_value) { // add unique effects nodes
					(!in_array($effect_value, $temp_filter_attrs['effects'])) ? array_push($temp_filter_attrs['effects'], $effect_value) : '';
				}

				$temp_filter_attrs['potencyCbd'][$filter_attr['potencyCbd']['formatted']] = $filter_attr['potencyCbd']; // add unique potencycbd valus
				$temp_filter_attrs['potencyThc'][$filter_attr['potencyThc']['formatted']] = $filter_attr['potencyThc']; // add unique potencyThc values
			}

			$json['prod_filter_attrs'] = $temp_filter_attrs;
			// $json['specials_filter_html'][ = $this->generate_specials_filters_html($temp_filter_attrs);
			// sort arrays
			sort($temp_filter_attrs['category']);
			sort($temp_filter_attrs['subcategory']);
			sort($temp_filter_attrs['strainType']);
			sort($temp_filter_attrs['effects']);
			ksort($temp_filter_attrs['potencyCbd']);
			ksort($temp_filter_attrs['potencyThc']);
			ksort($temp_filter_attrs['brand']);

			$json['specials_filter_html']['category'] = $this->generate_specials_filters_html($temp_filter_attrs, 'category');
			$json['specials_filter_html']['subcategory'] = $this->generate_specials_filters_html($temp_filter_attrs, 'subcategory');
			$json['specials_filter_html']['brand'] = $this->generate_specials_filters_html($temp_filter_attrs, 'brand');
			$json['specials_filter_html']['effects'] = $this->generate_specials_filters_html($temp_filter_attrs, 'effects');
			$json['specials_filter_html']['potencyCbd'] = $this->generate_specials_filters_html($temp_filter_attrs, 'potencyCbd');
			$json['specials_filter_html']['potencyThc'] = $this->generate_specials_filters_html($temp_filter_attrs, 'potencyThc');
			$json['specials_filter_html']['strainType'] = $this->generate_specials_filters_html($temp_filter_attrs, 'strainType');
			// $json['specials_filter_html']['variants'] = $this->generate_specials_filters_html($temp_filter_attrs,'variants');
			wp_send_json_success($json);
		} else {
			$json['msg'] = "NONCE ERROR";
			$json['check_nonce'] = $check_nonce;
			wp_send_json_error($json);
		}
		die();
	}

	/*
	* Specials filter generate HTML
	*/
	public function generate_specials_filters_html($filter_attrs_array, $filter_attr_name)
	{
		$specials_filter_html = '';

		$specials_filter_html .= '<div class="specials_filter_attr_box" ' . count($filter_attrs_array[$filter_attr_name]) . '>';
		if (($filter_attr_name == 'potencyCbd') || ($filter_attr_name == 'potencyThc')) {
			$attr_title_arr = explode('potency', $filter_attr_name);
			$attr_title = 'Potency : ' . strtoupper($attr_title_arr[1]);
		} else {
			$attr_title = str_replace("_", " ", strtolower($filter_attr_name));
		}
		$specials_filter_html .= '<button class="specials_filter_attr_title">' . $attr_title . '</button>';
		$specials_filter_html .= '<ul filter_attr="' . $filter_attr_name . '">';
		foreach ($filter_attrs_array[$filter_attr_name] as $key => $value) {
			$value_id = "";
			$value_name = "";

			if (($filter_attr_name == 'potencyCbd') || ($filter_attr_name == 'potencyThc')) {
				$value_id = $key;
				$value_name = $key;
				// $value_name = $value;
			} elseif (($filter_attr_name == 'category') || ($filter_attr_name == 'effects') || ($filter_attr_name == 'strainType') || ($filter_attr_name == 'subcategory')) {
				$value_id = $value;
				$value_name = str_replace("_", " ", strtolower($value));
			} elseif (($filter_attr_name == 'brand')) {
				$value_id = (isset($value['id']) && ($value['id'] != null)) ? $value['id'] : '';
				$value_name = (isset($value['name']) && ($value['name'] != null)) ? $value['name'] : '';
			}

			if ($value_id != "") {
				$specials_filter_html .= '<li class="" attr_value="' . $value_id . '" sasas>';
				$specials_filter_html .= '<div class="prod_cat_select_icon">';
				$specials_filter_html .= '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
				$specials_filter_html .= '</div>';
				$specials_filter_html .= '<div class="prod_cat_select_lable lf-common"><span class="">' .	$value_name . '</span></div>';
				$specials_filter_html .= '</li>';
			}
		}
		$specials_filter_html .= '</ul>';
		$specials_filter_html .= '</div>';

		return $specials_filter_html;
	}

	/*
	* Ajax function - show product search results
	*/
	public function leafbridge_products_search()
	{
		$json = array();

		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');
		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start

		$wizard_data = $_REQUEST['wizard_data'];
		$prod_categories = $_REQUEST['prod_categories'];
		$prods_pageNumber = isset($_REQUEST['prods_pageNumber']) ? $_REQUEST['prods_pageNumber'] : 0;
		$search = $_REQUEST['search_key_word'];

		$prods_per_page = 1000;
		$prods_offset = (int) $prods_per_page * $prods_pageNumber;

		$retailer_id = $wizard_data['retailer_id'];
		$menutype   = $wizard_data['menu_type'];
		$pagination = "{ limit: " . $prods_per_page . " offset: " . $prods_offset . " }";
		// $filter     = ($prod_categories == "all") ? "{ }" : "{ category : ".$prod_categories."}";
		$filter     = ($search) ? '{ search : "' . $search . '" }' : '{ }';

		$sort       = "{ direction: ASC key: NAME }";

		if ($flag_check_nonce) {

			$LeafBridge_Products = new LeafBridge_Products();
			$products_list = $LeafBridge_Products->search_retailer_products($retailer_id, $menutype, $pagination, $filter, $sort);

			// if(count($products_list) == 0 ){
			// $products_html = '<p>'._('No products Found !','leafbridge').'</p>';
			// }
			// else {
			// render set of products html
			// $products_html = "";
			// foreach ($products_list as $key => $product_node){
			// 	$products_html.= $this->render_product_box($product_node,$retailer_id,$menutype);
			// }
			// }

			$json['products_list'] = $products_list;
			// $json['products_list_count'] = count($products_list);

			$json['prods_per_page'] = $prods_per_page;
			$json['prods_pageNumber'] = $prods_pageNumber;
			$json['prods_offset'] = $prods_offset;
			$json['wizard_data'] = $wizard_data;
			$json['search'] = $search;

			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}

	/*
	* Featued Products Section
	* Ajax function - show products based on retailer selection
	* [leafbridge-featured-products product_count="15"]
	*/
	public function show_featured_products_func()
	{

		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');
		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start

		$wizard_data = $_REQUEST['wizard_data'];
		$prod_categories = $_REQUEST['prod_categories'];
		$json = array();

		if ($flag_check_nonce) {
			$json['test'] = true;

			$prods_per_page = (isset($_REQUEST['product_count'])) ? $_REQUEST['product_count'] : 10;

			// $prods_pageNumber should be n-1 eg : if page 1 - $prods_pageNumber shold be 0 if page 2 - $prods_pageNumber should be 1
			$prods_pageNumber = 0;

			$prods_offset = $prods_per_page * $prods_pageNumber;

			$retailer_id = $wizard_data['retailer_id'];
			$menutype   = $wizard_data['menu_type'];
			$pagination = "{ limit: " . $prods_per_page . " offset: " . $prods_offset . " }";
			// $filter     = ($prod_categories == "all") ? "{ }" : "{ category : ".$prod_categories."}";
			$filter     =	"{ }";
			$sort       = "{ direction: DESC key: POPULAR }";

			$soldout_amount = 0;

			$LeafBridge_Products = new LeafBridge_Products();

			// $products_list = $LeafBridge_Products->fetch_retailer_products($retailer_id, $menutype , $pagination , $filter , $sort , $multi_category ,$multiple_categories);
			$products_response = $LeafBridge_Products->fetch_retailer_products($retailer_id, $menutype, $pagination, $filter, $sort);
			$products_list = $products_response['result'];
			$products_count = $products_response['productsCount'];
			$products_weights = $products_response['weights'];


			$json['products_list'] = $products_list;
			$json['products_count'] = $products_count;
			$json['products_weights'] = $products_weights;
			$json['products_list'] = $products_list;
			$json['products_list_count'] = count($products_list);
			$json['prods_per_page'] = $prods_per_page;
			$json['prods_pageNumber'] = $prods_pageNumber;
			$json['prods_offset'] = $prods_offset;
			$json['wizard_data'] = $wizard_data;

			if (count($products_list) == 0) {
				$products_html = '<p>' . _('No products Found !', 'leafbridge') . '</p>';
			} else {
				$products_html = "";
				foreach ($products_list as $key => $product_node) {
					$products_html .= $this->render_product_box($product_node, $retailer_id, $menutype);
				}
			}
			$json['products_html'] = $products_html;

			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}

	// function display retailer details --- shortcode
	public function retailer_details_shortcode($attr)
	{
		if (isset($_GET['action'])) {
			if ($_GET['action'] != 'elementor') {
				return $this->retailer_details_shortcode_output($attr);
			} else {
				return "retailer_details_shortcode";
			}
		} else {
			return $this->retailer_details_shortcode_output($attr);
		}
	}

	public function retailer_details_shortcode_output($attr)
	{
		$args = array(
			'post_type'              => 'retailer',
			'post_status'            => 'publish',
			'posts_per_page'		 => -1,
			'meta_query'             => array(
				array(
					'key'       => '_lb_retailer_single_id',
					'value'     => $attr['retailer_id'],
				),
			),
		);
		$returnHTML = '';
		$retailers = new WP_Query($args);
		$queried_retailers = $retailers->get_posts();
		if (isset($retailers)) {
			foreach ($queried_retailers as $qrts => $q_retailer) {
				$post_id = $q_retailer->ID;
				$retailer_all_data = get_post_meta($post_id, '_lb_retailer_options_all', true);
				$retailer_options = unserialize($retailer_all_data['_lb_retailer_options']);
				$_lb_retailer_custom_name  = get_post_meta($post_id, 'lb_retailer_custom_name', true);
			}
			if (isset($retailer_options)) {

				$returnHTML .= '<div class="ret-det-div">';
				$returnHTML .= '<span class="ret-details ret-custom-name"><span class="ret-custom-name-value">';
				$returnHTML .= ($_lb_retailer_custom_name != '') ? ($_lb_retailer_custom_name) : ($retailer_options['name']);
				$returnHTML .= '</span></span>';
				$returnHTML .= '<span class="ret-details ret-address"><span class="ret-address-label">Address: </span> <span class="ret-address-value">' . ($retailer_options['address']) . '</span></span>';
				$returnHTML .= '<span class="ret-details ret-phone"><span class="ret-phone-label">Phone: </span> <span class="ret-phone-value">' . ($retailer_options['phone']) . '</span></span>';
				$returnHTML .= '<span class="ret-details ret-menu"><span class="ret-menu-label">Menu Types: </span> <span class="ret-menu-value">';
				foreach ($retailer_options['menuTypes'] as $value) {
					$returnHTML .= $value . ', ';
				}
				$returnHTML .= '</span></span>';
				$returnHTML .= '<span class="ret-details ret-order">';
				$returnHTML .= '<span class="ret-order-label">Order Types: </span>';
				$returnHTML .= '<span class="ret-order-value">';
				$orderTypes = $retailer_options['fulfillmentOptions'];
				foreach ($orderTypes as $key => $value) {
					if ($value == 1) {
						$returnHTML .= $key . ', ';
					}
				}
				$returnHTML .= '</span>';
				$returnHTML .= '</span>';


				$lb_rets = new LeafBridge_Retailers();
				$dynamic_data = $lb_rets->get_retailer_details($attr['retailer_id'], NULL);

				$pickup_array = $dynamic_data['hours']['pickup'];
				$open_hours_list =  array();
				foreach ($pickup_array as $pk_key => $pk_value) {
					$day = $pk_key;
					$starting = $pk_value['start'];
					$closing = $pk_value['end'];
					$flag_push_to_days_set = false;
					$current_days_set = 0;

					if (count($open_hours_list) > 0) {
						for ($dsk = 0; $dsk < count($open_hours_list); $dsk++) { //$dsk - days set key
							$days_set = $open_hours_list[$dsk];
							$current_days_set = $dsk;

							if (is_array($days_set)) {
								if ($starting == $days_set['open'] && $closing == $days_set['close']) {
									$flag_push_to_days_set = false;
									break;
								} else {
									$flag_push_to_days_set = true;
								}
							}
						}
					} else {
						$flag_push_to_days_set = false;
					}
					if ($flag_push_to_days_set) {
						array_push($open_hours_list, array(
							'days' => array($day),
							'open' => $starting,
							'close' => $closing,
						));
					} else {
						if (!isset($open_hours_list[$current_days_set])) {
							$open_hours_list[$current_days_set]['days'] = array($day);
							$open_hours_list[$current_days_set]['open'] = $starting;
							$open_hours_list[$current_days_set]['close'] = $closing;
						} else {
							array_push($open_hours_list[$current_days_set]['days'], $day);
						}
					}
				}

				// showing retailer hours type 1
				$returnHTML .= '<div class="ret-hours-types ret-hours-type-1">';
				foreach ($open_hours_list as $ohlskey => $hours_node) {
					// $hours_node $ordered_hours_node
					// foreach ($ordered_hours_node as $hlskey => $hours_node) {
					$days_text = '';
					if (count($hours_node['days']) == 7) {
						$days_text = '';
					} elseif (count($hours_node['days']) == 2) {
						$days_text = '( ' . $hours_node['days'][0] . ' & ' . $hours_node['days'][count($hours_node['days']) - 1] . ' )';
					} elseif (count($hours_node['days']) > 2) {
						$days_text .= '( ';
						foreach ($hours_node['days'] as $di => $days_day) {
							$days_text .= $days_day;
							$days_text .= ($di == (count($hours_node['days']) - 1)) ? "" : ",";
						}
						$days_text .= ' )';
						// $days_text = '(' . $hours_node['days'][0] . ' to ' . $hours_node['days'][count($hours_node['days']) - 1] . ')';
					} else {
						$days_text = '( ' . $hours_node['days'][0] . ' )';
					}
					$returnHTML .= '<span class="ret-details ret-hours-wrapper ' . $ohlskey . ' ' . count($open_hours_list[$ohlskey]) . '">';
					$returnHTML .= '<span class="ret-details ret-hours"><span class="ret-hours-label">Open Hours ' . $days_text . ' : </span> <span class="ret-hours-value"> ' . $hours_node['open'] . ' to ' . $hours_node['close'] . '</span></span>';
					$returnHTML .= '<span class="ret-details ret-until"><span class="ret-until-label">Open Until ' . $days_text . ' : </span> <span class="ret-until-value"> ' . $hours_node['close'] . '</span></span>';
					$returnHTML .= '</span>';
				}
				$returnHTML .= '</div>';

				// showing retailer hours type 2
				$returnHTML .= '';
				$returnHTML .= '<div class="ret-hours-types ret-hours-type-2">';
				foreach ($open_hours_list as $ohlskey => $hours_node) {
					// $hours_node $ordered_hours_node
					// foreach ($ordered_hours_node as $hlskey => $hours_node) {
					$days_text = '';
					if (count($hours_node['days']) == 7) {
						$days_text_2 = '';
					} elseif (count($hours_node['days']) == 2) {
						$days_text_2 = '( ' . $hours_node['days'][0] . ' & ' . $hours_node['days'][count($hours_node['days']) - 1] . ' )';
					} elseif (count($hours_node['days']) > 2) {
						$days_text_2 = '( ' . $hours_node['days'][0] . ' - ' . $hours_node['days'][count($hours_node['days']) - 1] . ' )';
						// foreach ($hours_node['days'] as $di => $days_day) {
						// 	$days_text .= $days_day;
						// 	$days_text .= ($di == (count($hours_node['days']) - 1)) ? "" : ",";
						// }
						// $days_text .= ' )';
						// $days_text = '(' . $hours_node['days'][0] . ' to ' . $hours_node['days'][count($hours_node['days']) - 1] . ')';
					} else {
						$days_text_2 = '( ' . $hours_node['days'][0] . ' )';
					}

					$returnHTML .= '<span class="ret-details ret-hours-wrapper ' . $ohlskey . ' ' . count($open_hours_list[$ohlskey]) . '">';
					$returnHTML .= '<span class="ret-details ret-hours"><span class="ret-hours-label">Open Hours ' . $days_text_2 . ' : </span> <span class="ret-hours-value"> ' . $hours_node['open'] . ' to ' . $hours_node['close'] . '</span></span>';
					$returnHTML .= '<span class="ret-details ret-until"><span class="ret-until-label">Open Until ' . $days_text_2 . ' : </span> <span class="ret-until-value"> ' . $hours_node['close'] . '</span></span>';
					$returnHTML .= '</span>';
				}
				$returnHTML .= '</div>';
				$returnHTML .=	'</div>';
			} else {
				$returnHTML = "No retailer";
			}
			return $returnHTML;
		} else {
			return "Retailer ID is Invalid";
		}
	}

	// function to render product box
	public function render_product_box($product_node, $retailer_id, $menutype)
	{
		$hide_weight = (($product_node['category'] == "APPAREL") || ($product_node['strainType'] == "NOT_APPLICABLE") || ($product_node['subcategory'] == "DRINKS")) ? true : false;

		$products_html = "";
		$soldout_amount = 0;
		$variant_stock = array();
		$temp_effects = "";
		foreach ($product_node['effects'] as $effkey => $effvalue) {
			$temp_effects .= $effvalue . '_';
		}
		$pass_prod_meta = array(
			'item_name' => $product_node['name'],
			'item_id' => $product_node['id'],
			'category' => $product_node['category'],
			'subcategory' => $product_node['subcategory'],
			'brand_name' => (isset($product_node['brand']['name'])) ? $product_node['brand']['name'] : "NO BRAND",
			'brand' => (isset($product_node['brand']['id']) && ($product_node['brand']['id'] != null)) ? $product_node['brand']['id'] : '',
			'effects' => $temp_effects,
			// 'effects' => $product_node['effects'],
			'potencyCbd' => $product_node['potencyCbd']['formatted'],
			'potencyThc' => $product_node['potencyThc']['formatted'],
			'strainType' => $product_node['strainType'],
			'variants' => $product_node['variants'],
		);
		$prod_meta_json_html = (json_encode($pass_prod_meta));

		$variant_count = count($product_node['variants']);

		$check_prods_query = new WP_Query(
			array(
				'post_type' => array('product'),
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => '_leafbridge_product_meta_product_id',
						'value' => $product_node['id']
					),
					array(
						'key' => '_leafbridge_product_single_meta_retailer_id',
						'value' => $retailer_id
					),

				),
			),
		);

		$found_synced_product_objects = array();
		if ($check_prods_query->have_posts()) :
			while ($check_prods_query->have_posts()) :
				$check_prods_query->the_post();
				array_push($found_synced_product_objects, get_post(get_the_ID()));
			endwhile;
		else :
		endif;

		$found_synced_products = $check_prods_query->found_posts;
		// $products_html .= $found_synced_products.'<br/>';
		if ($found_synced_products == 1) {
			foreach ($product_node['variants'] as $key => $variant) {
				$variant_stock[$variant['option']] = $variant['quantity'];
			}

			// start of product box
			$products_html .= '<div filter_data="' . htmlentities($prod_meta_json_html, ENT_QUOTES, 'UTF-8') . '" class="leafbridge_product_card lf-common" data_modal_id="prod_modal_' . $product_node['id'] . '" data_product_id = "' . $product_node['id'] . '" data_retailer_id = "' . $retailer_id . '">';

			// $products_html .= '<a class="leafbridge_product_a" href="'.get_home_url().'/product/'.$product_node['slug'].'">';
			$products_html .= '<div class="leafbridge_product_a" href="' . get_permalink(get_the_ID()) . '" absolute_url>';

			// $products_html .= '<pre>'; ob_start();
			// print_r($product_node);
			// $products_html .= ob_get_contents();
			// ob_end_clean();
			// $products_html .= '</pre>';

			($product_node['staffPick'] == 1) ? $products_html .= '<div class="staff_picks_label"><p>Staff Pick</p></div>' : null;
			$products_html .= '<a href="' . get_permalink(get_the_ID()) . '"><img src="' . $product_node['image'] . '?w=500&h=500&fm=webp" alt="' . __($product_node['name'], 'leafbridge') . '"></a>';
			$products_html .= '<div class="leafbridge_product_price lf-common" data_menu_type="' . $menutype . '">';

			// if menu type is medical
			$normal_price_key = ($menutype == "MEDICAL") ? 'priceMed' : (($menutype == "RECREATIONAL") ? 'priceRec' : '');
			$special_price_key = ($menutype == "MEDICAL") ? 'specialPriceMed' : (($menutype == "RECREATIONAL") ? 'specialPriceRec' : '');

			if ($variant_count > 1) {
				if ($variant[$special_price_key] != null) {
					$old_price = $product_node['variants'][0]['priceMed'];
					$new_price = $product_node['variants'][0]['specialPriceMed'];
					$discount_perc = round((($old_price - $new_price) / $old_price) * 100, 2);

					$old_price_last = $product_node['variants'][$variant_count - 1]['priceMed'];
					$new_price_last  = $product_node['variants'][$variant_count - 1]['specialPriceMed'];
					$discount_perc_last  = round((($old_price_last - $new_price_last) / $old_price_last) * 100, 2);

					$discount_text = ($discount_perc == $discount_perc_last) ? $discount_perc . '%' : $discount_perc . '% - ' . $discount_perc_last . '%';
					$products_html .= '<span>$' . $product_node['variants'][0][$normal_price_key] . ' - $' . $product_node['variants'][$variant_count - 1][$normal_price_key] . '</span>';
					$products_html .= '<span class="disc_perc"><i class="fa-solid fa-tags"></i> <span class="sale_prcnt_val">' . $discount_text  . '</span><span class="sale_prcnt">off</span></span>';
				} else {
					$products_html .= '<span>$' . $product_node['variants'][0][$normal_price_key] . ' - $' . $product_node['variants'][$variant_count - 1][$normal_price_key] . '</span>';
				}
			} else {
				if ($variant[$special_price_key] != null) {
					$old_price = $product_node['variants'][0][$normal_price_key];
					$new_price = $product_node['variants'][0][$special_price_key];
					$discount_perc = round((($old_price - $new_price) / $old_price) * 100, 2);
					$products_html .= '<div class="price_discount"><span><del>$' . $product_node['variants'][0][$normal_price_key] . '</del> <span class="special_price">$' . $product_node['variants'][0][$special_price_key] . '</span></span>';
					$products_html .= '<span class="disc_perc"><i class="fa-solid fa-tags"></i> <span class="sale_prcnt_val">' . $discount_perc . '%</span><span class="sale_prcnt">off</span></span></div>';
				} else {
					$products_html .= '<span>$' . $product_node['variants'][0][$normal_price_key] . '</span>';
				}
			}

			$products_html .= '</div>';
			$products_html .= (isset($product_node['brand']['name'])) ? '<div class="leafbridge_brand_name lf-common">' . __($product_node['brand']['name'], 'leafbridge') . '</div>' : '';
			$products_html .= '<h5 class="leafbridge_product_name lf-common"><a href="' . get_permalink(get_the_ID()) . '">' . __($product_node['name'], 'leafbridge') . '</a></h5>';

			$strain_label_color = ($product_node['strainType'] == "SATIVA") ? "orange_label" : (($product_node['strainType'] == "INDICA") ? "blue_label" : (($product_node['strainType'] == "HYBRID") ? "green_label" : (($product_node['strainType'] == "HIGH CBD") ? "red_label" : "")));

			if ($product_node['strainType']  !== "NOT_APPLICABLE") {
				$formatted_label = ($product_node['strainType'] == "HIGH_CBD") ? "High CBD" : ucwords(strtolower(str_replace("_", " ", $product_node['strainType'])));
				$products_html .= '<div class="leafbridge_strain_type_label ' . $strain_label_color . ' lf-common"><div class="leafbridge_strain_type_label_indicator lf-common"></div><span>' . $formatted_label . '</span></div>';
			}
			$products_html .=	'<div class="potency_card_view">';
			$products_html .= ($product_node['potencyCbd']['formatted'] != "") ? '<span>CBD : ' . $product_node['potencyCbd']['formatted'] . '</span>' : '';
			$products_html .= ($product_node['potencyThc']['formatted'] != "") ? '<span>THC : ' . $product_node['potencyThc']['formatted'] . '</span>' : '';
			$products_html .=	'</div>'; //potency_card_view end

			// lb_prod_box_add_to_cart_wrapper
			$products_html .= '<div style="" quantities = "' . htmlentities(json_encode($variant_stock)) . '" data_product_id ="' . $product_node['id'] . '" class="leafbridge_product_modal_add_to_cart lb_prod_box_add_to_cart_wrapper" variants="' . count($product_node['variants']) . '"  quantity="' . $product_node['variants'][0]['quantity'] . '">';
			$flag_add_to_cart = false;
			if (count($product_node['variants']) == 1) {
				if ($variant['quantity'] > $soldout_amount) {
					$products_html .= '<input type="hidden" value="' . $product_node['variants'][0]['option'] . '" class="add_to_cart_variant" />';
					$products_html .= '<input class="add_to_cart_count" type="number" value="1" min="1" max="' . $product_node['variants'][0]['quantity'] . '"/>';
					$flag_add_to_cart = true;
				}
			} else {
				$products_html .= '<select class="add_to_cart_variant variant_prod_box" name="" data_prod="">';
				foreach ($product_node['variants'] as $key => $variant) {
					if ($variant['quantity'] > $soldout_amount) {
						$flag_add_to_cart = true;
					}
					$disabled = ($variant['quantity'] <= $soldout_amount) ? 'disabled' : '';
					$products_html .= '<option  ' . $disabled . ' value="' . $variant['option'] . '">' . $variant['option'] . '</option>';
				}
				$products_html .= '</select>';
				$products_html .= '<input class="add_to_cart_count" type="number" value="1" min="1" max="' . $product_node['variants'][0]['quantity'] . '"/>';
			}

			if ($flag_add_to_cart) {
				$products_html .= '<button class="" tabindex="0" type="button"><span class="btn_label"><span class="btn_icon_wrapper"><i class="fa-solid fa-cart-shopping"></i></span><span class="atc_text">Add to cart</span></span></button>';
			} else {
				if (count($product_node['variants']) == 1) {
					$products_html .= '<div class="add_to_cart_soldout"><span>' . __('soldout!', 'leafbridge') . '</span></div>';
				}
			}
			$products_html .= '</div>'; //lb_prod_box_add_to_cart_wrapper end


			$products_html .= '<div class="lb_prod_box_bottom">';
			$products_html .= '<a class="see_more_box" href="' . get_permalink(get_the_ID()) . '" absolute_url></a>';
			$products_html .= '<div class="open_prod_modal" data_modal_id="prod_modal_' . $product_node['id'] . '">Quick View</div>';
			$products_html .= '</div>';

			$products_html .= '</div>'; //end of prod box clickable

			// end of product box

			// start of product box's modal
			$products_html .= '<div class="leafbridge_product_modal_outer" id="prod_modal_' . $product_node['id'] . '">';
			$products_html .= '<div class="leafbridge_product_modal_outer_bg"></div>';
			$products_html .= '<div class="leafbridge_product_modal">';
			$products_html .= '<div class="leafbridge_product_modal_image">';
			($product_node['staffPick'] == 1) ? $products_html .= '<div class="staff_picks_label"><p>Staff Pick</p></div>' : null;
			$products_html .= '<a href="' . get_permalink(get_the_ID()) . '"><img src="' . $product_node['image'] . '?w=1000&h=1000&fm=webp" alt=""></a></div>';
			//leafbridge_product_modal_descr
			$products_html .= '<div class="leafbridge_product_modal_descr">';
			//leafbridge_product_modal_close
			$products_html .= '<div class="leafbridge_product_modal_close"><svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" class="close-button__StyledSvg-sc-1rp6nt-0 duhkda"><circle cx="18" cy="18" r="17.5" stroke="#1f2b49"></circle><path d="M22.8536 13.8539C23.0496 13.6578 23.0487 13.3407 22.8515 13.1456C22.6542 12.9506 22.3354 12.9516 22.1394 13.1477L13.1464 22.1461C12.9504 22.3422 12.9513 22.6593 13.1485 22.8544C13.3458 23.0494 13.6646 23.0484 13.8606 22.8523L22.8536 13.8539Z" fill="#1f2b49"></path><path d="M13.1464 13.8539C12.9504 13.6578 12.9513 13.3407 13.1485 13.1456C13.3458 12.9506 13.6646 12.9516 13.8606 13.1477L22.8536 22.1461C23.0496 22.3422 23.0487 22.6593 22.8515 22.8544C22.6542 23.0494 22.3354 23.0484 22.1394 22.8523L13.1464 13.8539Z" fill="#1f2b49"></path></svg></div>';
			$products_html .= '<div class="list_staffpick_flex">';
			($product_node['staffPick'] == 1) ? $products_html .= '<div class="staff_picks_label"><p>Staff Pick</p></div>' : null;
			$products_html .= '</div>';
			//leafbridge_product_modal_descr_inner
			$products_html .= '<div class="leafbridge_product_modal_descr_inner">';

			//leafbridge_brand_details_wrapper
			$products_html .= '<div class="leafbridge_brand_details_wrapper_1">';
			$strain_label_color = ($product_node['strainType'] == "SATIVA") ? "orange_label " : (($product_node['strainType'] == "INDICA") ? "blue_label " : (($product_node['strainType'] == "HYBRID") ? "green_label " : (($product_node['strainType'] == "HIGH CBD") ? "red_label " : "")));

			$products_html .= '<div class="strain_potency">';
			if ($product_node['strainType']  !== "NOT_APPLICABLE") {
				$formatted_label = ($product_node['strainType'] == "HIGH_CBD") ? "High CBD" : ucwords(strtolower(str_replace("_", " ", $product_node['strainType'])));
				$products_html .= '<div class="leafbridge_strain_type_label ' . $strain_label_color . ' lf-common"><div class="leafbridge_strain_type_label_indicator lf-common"></div><span>' . $formatted_label . '</span></div>';
			}
			$products_html .=	'<div class="potency_list_view">';
			$products_html .= ($product_node['potencyCbd']['formatted'] != "") ? '<span>CBD : ' . $product_node['potencyCbd']['formatted'] . '</span>' : '';
			$products_html .= ($product_node['potencyThc']['formatted'] != "") ? '<span>THC : ' . $product_node['potencyThc']['formatted'] . '</span>' : '';
			$products_html .=	'</div>'; //potency_list_view end
			$products_html .= '</div>'; //strain_potency end

			// $products_html .='<div class="see_more_list_view_wrapper"><a class="see_more_list_view" target="_blank" href="'.get_permalink(get_the_ID()).'" absolute_url> See More </a></div>';//strain_potency end

			$products_html .= (isset($product_node['brand']['name']) && ($product_node['brand']['name'] != null)) ? '<p class="leafbridge_brand_name lf-common">' . __($product_node['brand']['name'], 'leafbridge') . '</p>' : '';
			$products_html .= '<h5 class="leafbridge_product_name lf-common"><a href="' . get_permalink(get_the_ID()) . '" absolute_url>' . __($product_node['name'], 'leafbridge') . '</a></h5>';
			$products_html .= '</div>'; //leafbridge_brand_details_wrapper end

			$products_html .= '<div class="leafbridge_brand_details_wrapper_2" checksoldout="' . $variant['quantity'] . ' - ' . $soldout_amount . '">';
			$products_html .= '<div class="leafbridge_product_price lf-common" data_menu_type="' . $menutype . '">';
			foreach ($product_node['variants'] as $key => $variant) {
				$option_label = ($hide_weight) ? '' : (($variant['option'] == "N/A") ? "" :  $variant['option'] . " - ");

				$quantity_label = ($variant['quantity'] <= 5) ? ($variant['quantity'] <= $soldout_amount) ?  __('soldout!', 'leafbridge') : ' - (' . $variant['quantity'] . ' available)' : '';

				// if menu type is Medical
				if ($menutype == "MEDICAL") {
					// $products_html .= '<span><strong>'.$option_label.'<del>$'.$variant['priceMed'].'</del>  $'.$variant['specialPriceMed'].'</strong> - '.$quantity_label.'</span>';
					if ($variant['specialPriceMed'] != null) {
						$old_price = $variant['priceMed'];
						$old_price = $variant['priceMed'];
						$new_price = $variant['specialPriceMed'];
						$discount_perc = round((($old_price - $new_price) / $old_price) * 100, 2);
						$products_html .= '<div class="price_disc_list"><span special_price_med><strong>' . $option_label . '<del>$' . $variant['priceMed'] . '</del>  $' . $variant['specialPriceMed'] . '</strong>' . $quantity_label . '</span>';
						$products_html .= '<span class="disc_perc"><i class="fa-solid fa-tags"></i><span class="sale_prcnt_val">' . $discount_perc . '%</span><span class="sale_prcnt">off</span></span></div>';
					} else {
						$products_html .= '<span price_med><strong>' . $option_label . '$' . $variant['priceMed'] . '</strong>' . $quantity_label . '</span>';
					}
				}
				// if menu type is RECREATIONAL
				elseif ($menutype == "RECREATIONAL") {
					// $products_html .= '<span><strong>'.$option_label.'<del>$'.$variant['priceRec'].'</del>  $'.$variant['specialPriceRec'].'</strong> - '.$quantity_label.'</span>';
					if ($variant['specialPriceRec'] != null) {
						$old_price = $variant['priceRec'];
						$new_price = $variant['specialPriceRec'];
						$discount_perc = round((($old_price - $new_price) / $old_price) * 100, 2);
						$products_html .= '<div class="price_disc_list"><span special_price_rec><strong>' . $option_label . '<del>$' . $variant['priceRec'] . '</del>  $' . $variant['specialPriceRec'] . '</strong>' . $quantity_label . '</span>';
						$products_html .= '<span class="disc_perc"><i class="fa-solid fa-tags"></i><span class="sale_prcnt_val">' . $discount_perc . '%</span><span class="sale_prcnt">off</span></span></div>';
					} else {
						$products_html .= '<span price_rec ><strong>' . $option_label . '$' . $variant['priceRec'] . '</strong>' . $quantity_label . '</span>';
					}
				}
			}

			$products_html .= '</div>'; //leafbridge_product_price end
			$products_html .= '<div class="leafbridge_product_descr">' . __($product_node['description'], 'leafbridge') . '<br><br>';
			$products_html .= '<p>';
			$products_html .= ($product_node['potencyCbd']['formatted'] != "") ? 'CBD : ' . $product_node['potencyCbd']['formatted'] : '';
			$products_html .= '<br/>';
			$products_html .= ($product_node['potencyThc']['formatted'] != "") ? 'THC : ' . $product_node['potencyThc']['formatted'] : '';
			$products_html .= '</p>';
			$products_html .= '</div>'; //leafbridge_product_descr end
			// leafbridge_product_modal_add_to_cart
			$products_html .= '<div quantities = "' . htmlentities(json_encode($variant_stock)) . '" data_product_id ="' . $product_node['id'] . '" class="leafbridge_product_modal_add_to_cart" variants="' . count($product_node['variants']) . '"  quantity="' . $product_node['variants'][0]['quantity'] . '">';
			$flag_add_to_cart = false;
			if (count($product_node['variants']) == 1) {
				if ($variant['quantity'] > $soldout_amount) {
					$products_html .= '<input type="hidden" value="' . $product_node['variants'][0]['option'] . '" class="add_to_cart_variant" />';
					$products_html .= '<input class="add_to_cart_count" type="number" value="1" min="1" max="' . $product_node['variants'][0]['quantity'] . '"/>';
					$flag_add_to_cart = true;
				}
			} else {
				$products_html .= '<select class="add_to_cart_variant variant_prod_modal" name="" data_prod="">';
				foreach ($product_node['variants'] as $key => $variant) {
					if ($variant['quantity'] > $soldout_amount) {
						$flag_add_to_cart = true;
					}
					$disabled = ($variant['quantity'] <= $soldout_amount) ? 'disabled' : '';
					$products_html .= '<option  ' . $disabled . ' value="' . $variant['option'] . '">' . $variant['option'] . '</option>';
				}
				$products_html .= '</select>';
				$products_html .= '<input class="add_to_cart_count" type="number" value="1" min="1" max="' . $product_node['variants'][0]['quantity'] . '"/>';
			}

			if ($flag_add_to_cart) {
				$products_html .= '<button class="" tabindex="0" type="button"><span class="btn_label"><span class="btn_icon_wrapper"><i class="fa-solid fa-cart-shopping"></i></span><span class="atc_text">Add to cart</span></span></button>';
			} else {
				if (count($product_node['variants']) == 1) {
					$products_html .= '<div class="add_to_cart_soldout"><span>' . __('soldout!', 'leafbridge') . '</span></div>';
				}
			}
			$products_html .= '</div>'; //leafbridge_product_modal_add_to_cart end

			$products_html .= '</div>'; //leafbridge_brand_details_wrapper_2 end
			$products_html .= '</div>'; //leafbridge_product_modal_descr_inner end

			$products_html .= '</div>'; //leafbridge_product_modal_descr end
			$products_html .= '</div>'; //leafbridge_product_modal end
			$products_html .= '</div>'; //leafbridge_product_modal_outer end
			$products_html .= '</div>'; //leafbridge_product_card end

		}

		// ob_start();
		// print_r($product_node);
		// echo '<pre>';
		// $products_html = ob_get_contents();
		// echo '</pre>';
		// ob_end_clean();
		return $products_html;
	}



	/*
	* Ajax function - add products to cart
	*/
	public function leafbridge_shop_add_products_to_cart()
	{
		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');
		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start
		$json = array();
		$leafbridge_settings = get_option('leafbridge-settings');
		$leafbridge_settings_page_settings = $leafbridge_settings['leafbridge-settings-page-settings'];

		if ($flag_check_nonce) {

			$json['local_s_cart_data'] = $_REQUEST['init_cart_data'];

			$retailerId = $_REQUEST['retailerId'];
			$checkoutId = (string) $_REQUEST['checkoutId'];
			$orderType = ($_REQUEST['collection_type'] == "curbsidePickup" || $_REQUEST['collection_type'] == "driveThruPickup") ? "PICKUP" : preg_replace("/\s+/", "", $_REQUEST['collection_type']); // PICKUP or DELIVERY
			$pricingType = preg_replace("/\s+/", "", $_REQUEST['pricingType']); //MEDICINE or RECREATIONAL
			$products_list = $_REQUEST['products_list'];

			$LeafBridge_Public_Cart = new LeafBridge_Public_Cart();

			$json['checkoutId test '] = $checkoutId;

			if ($checkoutId == "0") {
				$checkoutId_new = $LeafBridge_Public_Cart->leafbridge_create_checkout($retailerId, $orderType, $pricingType);
				$json['new_checkout'] = $checkoutId_new;
				$json['new_checkout_id'] = $checkoutId_new['id'];
				$json['0 checkout'] = $checkoutId_new['id'];
				$checkoutId = $checkoutId_new['id'];
			}

			$json['$orderType test '] = $orderType;
			$json['checkoutId '] = $checkoutId;

			$prod_variation = ($products_list['variation'] != "N/A") ? $products_list['variation'] : "N/A";
			$add_item_to_checkout = $LeafBridge_Public_Cart->leafbridge_addItemToCheckout($retailerId, $checkoutId, $products_list['product_id'], $products_list['prod_count'], $prod_variation);
			$json['update_cart'] = $add_item_to_checkout;
			$json['order_details_url'] = $leafbridge_settings_page_settings['leafbridge-config-ui-order-status-link'];

			if (is_array($add_item_to_checkout)) {
				$json['add_item_to_checkout'] = $add_item_to_checkout['items'];
				$json['cart_html'] = $this->render_cart_items($add_item_to_checkout['items'], $add_item_to_checkout['pricingType']);
			} else {
				$json['add_to_cart_error'] = $add_item_to_checkout;
			}
			wp_send_json_success($json);
		} else {
			$json["message"] = "nonce error";
			$json["check_nonce"] = $check_nonce;
			wp_send_json_error($json);
		}
	}

	/*
	* Ajax function - get cart details
	*/
	public function leafbridge_get_cart_items()
	{
		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');

		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start

		$json = array();
		$retailer_id = $_REQUEST['retailer_id'];
		$new_checkout_id = $_REQUEST['new_checkout_id'];
		$item_id = $_REQUEST['item_id'];
		$item_quantity = (int) $_REQUEST['item_quantity'];
		$leafbridge_settings = get_option('leafbridge-settings');
		$leafbridge_settings_page_settings = $leafbridge_settings['leafbridge-settings-page-settings'];


		if ($flag_check_nonce) {
			$json['retailer_id'] = $retailer_id;
			$json['new_checkout_id'] = $new_checkout_id;
			$json['item_id'] = $item_id;
			$json['item_quantity'] = $item_quantity;
			$LeafBridge_Public_Cart = new LeafBridge_Public_Cart();
			$get_cart_items_after_update = $LeafBridge_Public_Cart->leafbridge_getCartDetails($retailer_id, $new_checkout_id);

			$json['order_details_url'] = $leafbridge_settings_page_settings['leafbridge-config-ui-order-status-link'];
			$json['update_cart'] = $get_cart_items_after_update;
			$json['cart_html'] = $this->render_cart_items($get_cart_items_after_update['items'], $get_cart_items_after_update['pricingType']);

			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}

	/*
	* Ajax function - remove cart items
	*/
	public function leafbridge_remove_cart_item()
	{
		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');
		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start

		$json = array();
		$item_key = $_REQUEST['item_key'];
		$retailerId = $_REQUEST['retailerId'];
		$checkoutId = $_REQUEST['checkoutId'];

		$json['check_nonce'] = $check_nonce;
		$json['item_key'] = $item_key;
		$json['retailerId'] = $retailerId;
		$json['checkoutId'] = $checkoutId;

		if ($flag_check_nonce) {
			$LeafBridge_Public_Cart = new LeafBridge_Public_Cart();
			$get_cart_items_after_update =  $LeafBridge_Public_Cart->leafbridge_removeItemFromCheckout($retailerId, $checkoutId, $item_key);
			$json['update_cart'] = $get_cart_items_after_update;

			$leafbridge_settings_page_settings = $LBsttngs['leafbridge-settings-page-settings'];
			$json['order_details_url'] = $leafbridge_settings_page_settings['leafbridge-config-ui-order-status-link'];

			$json['cart_html'] = $this->render_cart_items($get_cart_items_after_update['items'], $get_cart_items_after_update['pricingType']);
			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}

	/*
	* Ajax function - update cart item quantity
	*/
	public function leafbridge_update_cart_item_quantity()
	{
		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');

		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start

		$json = array();

		if ($flag_check_nonce) {

			$retailerId = $_REQUEST['retailerId'];
			$checkoutId = $_REQUEST['checkoutId'];
			$itemId = $_REQUEST['itemId'];
			$quantity = (int) $_REQUEST['quantity'];

			$json['retailerId'] = $retailerId;
			$json['checkoutId'] = $checkoutId;
			$json['itemId'] = $itemId;
			$json['quantity'] = $quantity;

			$LeafBridge_Public_Cart = new LeafBridge_Public_Cart();
			$get_cart_items_after_update =  $LeafBridge_Public_Cart->leafbridge_updateQuantity($retailerId, $checkoutId, $itemId, $quantity);
			$json['update_cart'] = $get_cart_items_after_update;

			$leafbridge_settings_page_settings = $LBsttngs['leafbridge-settings-page-settings'];
			$json['order_details_url'] = $leafbridge_settings_page_settings['leafbridge-config-ui-order-status-link'];


			// $json['cart_html'] = $this->render_cart_items($get_cart_items_after_update['items'],$get_cart_items_after_update['pricingType']);

			if (is_array($get_cart_items_after_update)) {
				$json['cart_html'] = $this->render_cart_items($get_cart_items_after_update['items'], $get_cart_items_after_update['pricingType']);
			} else {
				$json['update_cart_error'] = $get_cart_items_after_update;
			}

			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}

	/*
	* Ajax function - reset checkout and selection
	*/
	public function leafbridge_reset_checkout()
	{
		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');
		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start

		$json = array();

		if ($flag_check_nonce) {
			$wizard_data = $_REQUEST['wizard_data'];
			$json['wizard_data'] = $wizard_data;

			// $retailerId = retailer_id
			// $checkoutId =
			// $itemId = null
			// $pricingType = menu_type
			// $address = nul;
			// $orderType = collection_method
			//
			// $LeafBridge_Public_Cart = new LeafBridge_Public_Cart();
			// $get_cart_items_after_update =  $LeafBridge_Public_Cart->leafbridge_updateCheckout($retailerId, $checkoutId, $itemId, $pricingType, $address, $orderType);
			// $json['update_cart'] = $get_cart_items_after_update;

			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}

	// function to render cart items
	public static function render_cart_items($checkout_items, $menu_type)
	{
		$return_cart_items_html = '';
		foreach ($checkout_items as $cart_item_key => $cart_item_node) {
			$gtm_data = $cart_item_node;
			unset($gtm_data['product']['description']);
			unset($gtm_data['product']['descriptionHtml']);
			unset($gtm_data['product']['effects']);
			unset($gtm_data['product']['brand']['description']);
			unset($gtm_data['product']['brand']['imageUrl']);
			unset($gtm_data['product']['image']);

			$return_cart_items_html .= '<div class="floating_cart_item_box" prod_id="' . $cart_item_node['product']['id'] . '" cart_item_id="' . $cart_item_node['id'] . '" gtm_prod_data="' . htmlentities(json_encode($gtm_data)) . '">';
			$return_cart_items_html .= '<div class="floating_cart_item_image"><img src="' . $cart_item_node['product']['image'] . '?w=200&h=100"/></div>';

			$return_cart_items_html .= '<div class="floating_cart_item_data_panel"><div class="floating_cart_item_details">';
			$return_cart_items_html .= '<span class="item_name">' . $cart_item_node['product']['name'] . ' - ' . $cart_item_node['option'] . '</span>';
			$return_cart_items_html .= ($cart_item_node['product']['brand']['name'] !== null) ? '<span class="item_brand">' . $cart_item_node['product']['brand']['name'] . '</span>' : '';
			$return_cart_items_html .= '</div>';


			$return_cart_items_html .= '<div class="floating_cart_item_price-panel">';
			$return_cart_items_html .= '<div class="cart_item_count">';

			// get available qunantity
			$cart_item_option = $cart_item_node['option'];
			$cart_item_option_qty_limit = 0;

			$variants_node = $cart_item_node['product']['variants'];

			for ($i = 0; $i < count($variants_node); $i++) {
				$variant_node = $variants_node[$i];
				if ($variant_node['option'] == $cart_item_option) {
					$cart_item_option_qty_limit = $variant_node['quantity'];
					break;
				}
			}

			$return_cart_items_html .= '<span>Quantity: </span>';

			// $return_cart_items_html .='<select class="cart_item_count_val" data_max="'.$cart_item_option_qty_limit.'">';
			// for ($i=1; $i <= $cart_item_option_qty_limit ; $i++) {
			// 	$selected_option = ($i == $cart_item_node['quantity']) ? "selected" : "";
			// 	$return_cart_items_html .='<option value="'.$i.'" '.$selected_option.' >'.$i.'</option>';
			// 	if( $i >= 8){
			// 		break;
			// 	}
			// }
			// $return_cart_items_html .='</select>';

			$return_cart_items_html .= '<input class="cart_item_count_val" type="number" value="' . $cart_item_node['quantity'] . '" min="1" max="' . $cart_item_option_qty_limit . '"/>';
			$return_cart_items_html .= '<button style="display:none !important;" class="cart_item_count_val_save">save</button>';
			$return_cart_items_html .= '</div>';

			$return_cart_items_html .= '<div class="cart_item_amount" ' . $menu_type . '>';

			if ($menu_type == "MEDICAL") {
				foreach ($cart_item_node['product']['variants'] as $variant_key => $variant_node) {
					$billing_price = ($variant_node['specialPriceMed'] != null) ? $variant_node['specialPriceMed']  : $variant_node['priceMed'];
					$show_price = (float) $billing_price * $cart_item_node['quantity'];
					$return_cart_items_html .= ($variant_node['option'] == $cart_item_node['option']) ? '<span ' . $menu_type . '>$' . number_format($show_price, 2, ".", ",") . '</span>' : '';
					// $return_cart_items_html .= ($variant_node['option'] == $cart_item_node['option']) ? '<span>$'.$show_price.'</span>' : '';
				}
			} elseif ($menu_type == "RECREATIONAL") {
				foreach ($cart_item_node['product']['variants'] as $variant_key => $variant_node) {
					$billing_price = ($variant_node['specialPriceRec'] != null) ? $variant_node['specialPriceRec']  : $variant_node['priceRec'];
					$show_price = (float) $billing_price * $cart_item_node['quantity'];
					$return_cart_items_html .= ($variant_node['option'] == $cart_item_node['option']) ? '<span ' . $menu_type . '>$' . number_format($show_price, 2, ".", ",") . '</span>' : '';
					// $return_cart_items_html .= ($variant_node['option'] == $cart_item_node['option']) ? '<span>$'.$show_price.'</span>' : '';
				}
			}
			$return_cart_items_html .= '</div>';
			$return_cart_items_html .= '</div>';
			$return_cart_items_html .= '<button class="remove_cart_item"><i class="fa-solid fa-square-xmark"></i></button>';
			$return_cart_items_html .= '</div>';
			$return_cart_items_html .= '</div>';
		}
		return $return_cart_items_html;
	}

	/*
	*Ajax function leafbridge_single_product
	*/
	public function leafbridge_single_product()
	{
		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');
		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start

		$json = array();
		if ($flag_check_nonce) {
			$retailerId = $_REQUEST['retailerId'];
			$product_id = $_REQUEST['product_id'];
			$menu_type = $_REQUEST['menu_type'];

			// call the product post on wordpress database
			$check_prods_query = new WP_Query(
				array(
					'post_type' => array('product'),
					'post_status' => 'publish',
					'meta_query' => array(
						array(
							'key' => '_leafbridge_product_meta_product_id',
							'value' => $product_id,
						),
						array(
							'key' => '_leafbridge_product_single_meta_retailer_id',
							'value' => $retailerId,
						),
					),
				),
			);

			$local_prod_post_ids = array();
			if ($check_prods_query->have_posts()) :
				while ($check_prods_query->have_posts()) :
					$check_prods_query->the_post();
					array_push($local_prod_post_ids, get_the_ID());
				endwhile;
			else :
			endif;

			$plugin = new LeafBridge_Products();
			$product_node = $plugin->fetch_single_products($retailerId, $product_id);

			$json['product_node'] = $product_node;
			$json['product_id'] = $product_id;
			$json['retailerId'] = $retailerId;

			if (is_array($product_node)) {
				$menu_type_post_meta = get_post_meta($local_prod_post_ids[0], '_leafbridge_product_meta_menu_type', false);
				$menu_type_match_flag = str_contains($menu_type_post_meta[0], $menu_type);

				$json['render_html'] = $menu_type_match_flag;
				if ($menu_type_match_flag) {
					$json['render_html'] = $this->prepare_single_prod_add_to_cart($product_node, $menu_type, $menu_type_match_flag);
				} else {
					$json['render_html'] = '<p style="background: #fd3c51;color: white;padding: 10px 15px;border-radius: 5px;">This product is not offered by this retailer under <b>' . $menu_type . ' </b> menu type</p>';
				}
			} else {
				$json['render_html'] = '<p style="background: #fd3c51;color: white;padding: 10px 15px;border-radius: 5px;">This product is available at <span style="padding: 0;display: inline-block;margin: 0;" id="prod_real_retailer"></span>.<br/> Please select this location to continue.</p>';
			}


			wp_send_json_success($json);
		} else {
			$json["message"] = "nonce error";
			wp_send_json_error($json);
		}
	}

	public function prepare_single_prod_add_to_cart($product_node, $menu_type, $menu_type_match_flag)
	{
		ob_start();
		$variant_stock = array();
		$soldout_amount = 0;
		$products_html = '';
		$flag_add_to_cart = false;

		$hide_weight = (($product_node['category'] == "APPAREL") || ($product_node['strainType'] == "NOT_APPLICABLE") || ($product_node['subcategory'] == "DRINKS")) ? true : false;

		foreach ($product_node['variants'] as $key => $variant) {
			$variant_stock[$variant['option']] = $variant['quantity'];
		}
?>
		<div class="single_prod_add_to_cart_wrapper">
			<div class="variations_show">
				<div class="leafbridge_product_price lf-common" <?php echo $menu_type; ?>>
					<?php

					foreach ($product_node['variants'] as $key => $variant) {
						$option_label = ($variant['option'] == "N/A" || $hide_weight) ? "" :  $variant['option'] . " - ";
						// $quantity_label = ($variant['quantity'] <= 5) ? ($variant['quantity'] <= $soldout_amount) ?  __('soldout!', 'leafbridge') : ' - (' . $variant['quantity'] . ' available)' :'';
						$quantity_label = ($variant['quantity'] <= $soldout_amount) ?  __('soldout!', 'leafbridge') : '';

						if ($variant['specialPriceMed'] != null && $menu_type == "MEDICAL") {
							$products_html .= '<span><strong>' . $option_label . '<del>$' . $variant['priceMed'] . '</del>  $' . $variant['specialPriceMed'] . '</strong>' . $quantity_label . '</span>';
							$flag_add_to_cart = true;
						} elseif ($variant['specialPriceMed'] == null && $menu_type == "MEDICAL") {
							$products_html .= '<span><strong>' . $option_label . '$' . $variant['priceMed'] . '</strong>' . $quantity_label . '</span>';
							$flag_add_to_cart = true;
						} elseif ($variant['specialPriceRec'] != null && $menu_type == "RECREATIONAL") {
							$products_html .= '<span><strong>' . $option_label . '<del>$' . $variant['priceRec'] . '</del>  $' . $variant['specialPriceRec'] . '</strong>' . $quantity_label . '</span>';
							$flag_add_to_cart = true;
						} elseif ($variant['specialPriceRec'] == null && $menu_type == "RECREATIONAL") {
							$products_html .= '<span><strong>' . $option_label . '$' . $variant['priceRec'] . '</strong>' . $quantity_label . '</span>';
							$flag_add_to_cart = true;
						} else {
							$flag_add_to_cart = false;
						}
					}
					echo $products_html;

					?>
				</div>
			</div>
			<?php if ($flag_add_to_cart && $menu_type_match_flag) : ?>
				<div class="add_to_cart_part">
					<div quantities="<?php echo htmlentities(json_encode($variant_stock)) ?>" data_product_id="<?php echo $product_node['id'] ?>" class="leafbridge_product_modal_add_to_cart" variants="<?php echo count($product_node['variants']) ?>" quantity="">
						<select class="add_to_cart_variant variant_prod_page" name="" data_prod="" <?php echo ($variant['option'] == "N/A" || $hide_weight) ? 'style="display:none;"' : ''; ?>>
							<?php
							foreach ($product_node['variants'] as $key => $variant) {
								echo '<option value="' . $variant['option'] . '">' . $variant['option'] . '</option>';
							}
							?>
						</select>
						<input class="add_to_cart_count" type="number" value="1" min="1" max="<?php echo $product_node['variants'][0]['quantity'] ?>">
						<button class="" tabindex="0" type="button">
							<span class="btn_label">
								<span class="btn_icon_wrapper">
									<i class="fa-solid fa-cart-shopping"></i>
								</span>Add to cart</span>
						</button>
					</div>
				</div>
			<?php endif; ?>
		</div>

	<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}

	/*
	* Ajax function leafbridge_order_details
	*/
	public function leafbridge_order_details()
	{
		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');
		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start
		$json = array();
		if ($flag_check_nonce) {

			$json['local_s_cart_data'] = $_REQUEST['init_cart_data'];
			$json['retailerId'] = $_REQUEST['retailerId'];
			$json['orderId'] = $_REQUEST['orderId'];

			$retailerId = $_REQUEST['retailerId'];
			$orderId = $_REQUEST['orderId'];

			$LeafBridge_Public_Orders = new LeafBridge_Public_Orders();
			$order_details = $LeafBridge_Public_Orders->leafbridge_get_orders($retailerId, $orderId);
			if (is_array($order_details)) {
				$json['order_html'] = $this->render_order_page($order_details[0]);
			} else {
				$json['order_html'] = 'Error : ' . $order_details;
			}

			$json['order_details'] = $order_details;
			wp_send_json_success($json);
		} else {
			$json["message"] = "nonce error";
			wp_send_json_error($json);
		}
	}

	public function render_order_page($order_data)
	{
		ob_start();
	?>
		<div style="white-space:pre;display:none !important;"><?php print_r($order_data); ?></div>


		<div class="lb-order-inner">
			<div class="lb-order-header">
				<h3>Your order has been received.</h3>
				<p>Your order has been confirmed and will be ready soon. Please look out for an alert from us for further details.</p>
				<p style="color:red;display:none;">Do not reload this page. You will be redirected to homepage.</p>
			</div>
			<div class="lb-order-meta">
				<div>
					<h4>Order Status</h4>
					<p><?php echo ucfirst($order_data['status']); ?></p>
				</div>
				<div>
					<h4>Order Number</h4>
					<p><?php echo $order_data['orderNumber']; ?></p>
				</div>
				<div>
					<h4>Order Date</h4>
					<!-- 01 August 2022 -->
					<p><?php echo date("d M Y", strtotime($order_data['createdAt'])); ?></p>
				</div>

			</div>
			<div class="lb-order-body">
				<?php
				foreach ($order_data['items'] as $key => $order_item) {
					//echo '<pre>';print_r($order_item); echo '</pre>';
				?>
					<div class="lb-order-item"> <!--loop mee !!-->
						<div class="lb-order-item-image">
							<img src="<?php echo $order_item['product']['image']; ?>?w=300&h=200" />
						</div>
						<div class="lb-order-item-details">
							<h3><?php echo $order_item['product']['name']; ?></h3>
							<h5><?php echo $order_item['product']['brand']['name']; ?></h5>
							<p>Quantity: <?php echo $order_item['quantity']; ?></p>
						</div>
						<div class="lb-order-item-price">
							<p>$<?php $prod_price = (float) $order_item['price'] / 100;
								echo number_format($prod_price, 2); ?></p>
						</div>
					</div>

				<?php
				}
				?>
			</div>
			<div class="lb-order-summary">
				<?php

				?>
				<div class="lb-ordersummary-item">Subtotal <span>$<?php echo $order_data['subtotal'] ?></span></div>
				<div class="lb-ordersummary-item">Taxes <span>$<?php echo $order_data['tax'] ?></span></div>
				<div class="lb-ordersummary-item" style="display:none;">Discount <span>$<?php echo $order_data['subtotal'] ?></span></div>
				<div class="lb-ordersummary-item">Total <span>$<?php echo $order_data['total'] ?></span></div>
			</div>
			<div class="lb-order-footer">

			</div>
		</div>
		<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}


	/*
	* Ajax function leafbridge_get_retailer_specials
	*/
	public function leafbridge_get_retailer_specials()
	{
		$wizard_data = $_REQUEST['wizard_data'];
		$json = array();
		$json['wizard_data'] = $wizard_data;

		$LeafBridge_Products = new LeafBridge_Products();
		$getSpecials = $LeafBridge_Products->getSpecials($wizard_data['retailer_id']);
		$json['getSpecials'] = $getSpecials;
		$json['getSpecials_html'] = "";

		foreach ($getSpecials as $key => $getSpecial_node) {
			if (($getSpecial_node['menuType'] == "BOTH") || ($getSpecial_node['menuType'] == $wizard_data['menu_type'])) {
				$json['getSpecials_html'] .= '<div class="specials_tab" specials_id="' . $getSpecial_node['id'] . '">';
				$json['getSpecials_html'] .= '<span class="special_title">' . $getSpecial_node['menuDisplayConfiguration']['name'] . '</span>';
				$json['getSpecials_html'] .= '<span class="special_description">' . $getSpecial_node['menuDisplayConfiguration']['description'] . '</span>';
				$json['getSpecials_html'] .= '</div>';
			}
		}

		wp_send_json_success($json);
	}

	/*
	* Ajax functoin get_default_retailer
	*/
	public function get_default_retailer_function()
	{
		$json = array();
		$wp_options = get_option('leafbridge-settings');
		$json['leafbridge_default_settings'] = $wp_options['leafbridge_default_settings'];
		$json['leafbridge_default_settings']['age_confirmation'] = $wp_options['leafbridge-settings-age-modal']['leafbridge-settings-age-modal-is-enable'];
		wp_send_json_success($json);
	}

	/*
	* Ajax function leafbridge_get_retailer_special_menus
	*/
	public function leafbridge_get_retailer_special_menus()
	{
		// nonce override / not - start
		// set a flag to check nonce / override nonce check
		$flag_check_nonce = false;
		$LBsttngs = get_option('leafbridge-settings');
		$nonce_sttng = $LBsttngs['leafbridge_default_settings']['disable_wp_nonce'];

		if ($nonce_sttng == "disable") {
			$flag_check_nonce = true;
		} else {
			$check_nonce = check_ajax_referer('leafbridge-ajax-nonce', 'nonce_ajax');
			$nonce_timeout = ($nonce_sttng == "24") ? 2 : 1;
			$flag_check_nonce = ($check_nonce <= $nonce_timeout) ? true : false;
		}
		// nonce override / not - start

		if ($flag_check_nonce) {
			$wizard_data = $_REQUEST['wizard_data'];
			$json = array();
			$json['wizard_data'] = $wizard_data;

			$LeafBridge_Products = new LeafBridge_Products();
			$getSpecials = $LeafBridge_Products->getSpecials($wizard_data['retailer_id']);
			$json['getSpecials'] = $getSpecials;
			$json['getSpecials_html'] = "";

			foreach ($getSpecials as $key => $getSpecial_node) {
				if (($getSpecial_node['menuType'] == "BOTH") || ($getSpecial_node['menuType'] == $wizard_data['menu_type'])) {
					$json['getSpecials_html'] .= '<a href="/specials/?&specials_id=' . $getSpecial_node['id'] . '" class="specials_tab_new" specials_id="' . $getSpecial_node['id'] . '">';
					$json['getSpecials_html'] .= '<span class="special_title">' . $getSpecial_node['menuDisplayConfiguration']['name'] . '</span>';
					$json['getSpecials_html'] .= '<span class="special_description">' . $getSpecial_node['menuDisplayConfiguration']['description'] . '</span>';
					$json['getSpecials_html'] .= '</a>';
				}
			}

			wp_send_json_success($json);
		}
	}

	/*
	* Ajax function load_retailer_name
	*/
	public function load_retailer_name()
	{
		$json = array();
		$retail_id = $_REQUEST['retailer_id'];
		$args = array(
			'post_type' => 'retailer',
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => '_lb_retailer_single_id',
					'value' => $retail_id,
					'compare' => '=',
				),
			),
		);

		$lb_rets = new LeafBridge_Retailers();
		$dynamic_data = $lb_rets->get_retailer_details($retail_id, NULL);


		$custom_query  = new WP_Query($args);

		if ($custom_query->have_posts()) {
			while ($custom_query->have_posts()) {
				$custom_query->the_post();
				$json['title'] = html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8');
				$post_id = get_the_ID();

				$retailer_all_data = get_post_meta($post_id, '_lb_retailer_options_all', true);
				$retailer_options = unserialize($retailer_all_data['_lb_retailer_options']);

				$_lb_retailer_custom_name = (get_post_meta($post_id, 'lb_retailer_custom_name', true) != "") ? get_post_meta($post_id, 'lb_retailer_custom_name', true) : get_the_title();
				$json['lb_retailer_custom_name'] = html_entity_decode($_lb_retailer_custom_name, ENT_QUOTES, 'UTF-8');
				$json['lb_retailer_address'] = $retailer_options['address'];
				$json['lb_retailer_phone'] = $retailer_options['phone'];
				$inside_days_array_1 = $retailer_options['hours']['pickup'];
				$inside_days_array_monday = $inside_days_array_1[0];
				$inside_days_array_monday_start = $inside_days_array_monday['start'];
				$inside_days_array_monday_end = $inside_days_array_monday['end'];
				$json['inside_days_array_monday'] = $inside_days_array_monday;
				$json['inside_days_array_monday_start'] = $inside_days_array_monday_start;
				$json['inside_days_array_monday_end'] = $inside_days_array_monday_end;

				// Today closing time START
				$current_day = Date('l');
				$inside_days_array = $retailer_options['hours']['pickup'];
				$json['inside_days_array'] = $inside_days_array;
				$json['$dynamic_data inside_days_array'] = $dynamic_data['hours']['pickup'];

				// foreach ($inside_days_array as $pk_key => $pk_value) {
				foreach ($dynamic_data['hours']['pickup'] as $pk_key => $pk_value) {
					if ($pk_key == 'Monday') {
						$json['lb_retailer_day_monday'] = $pk_key;
						$json['lb_retailer_day_monday_start'] = $pk_value['start'];
						$json['lb_retailer_day_monday_end'] = $pk_value['end'];
					}
					if ($pk_key == 'Tuesday') {
						$json['lb_retailer_day_tuesday'] = $pk_key;
						$json['lb_retailer_day_tuesday_start'] = $pk_value['start'];
						$json['lb_retailer_day_tuesday_end'] = $pk_value['end'];
					}
					if ($pk_key == 'Wednesday') {
						$json['lb_retailer_day_wednesday'] = $pk_key;
						$json['lb_retailer_day_wednesday_start'] = $pk_value['start'];
						$json['lb_retailer_day_wednesday_end'] = $pk_value['end'];
					}
					if ($pk_key == 'Thursday') {
						$json['lb_retailer_day_thursday'] = $pk_key;
						$json['lb_retailer_day_thursday_start'] = $pk_value['start'];
						$json['lb_retailer_day_thursday_end'] = $pk_value['end'];
					}
					if ($pk_key == 'Friday') {
						$json['lb_retailer_day_friday'] = $pk_key;
						$json['lb_retailer_day_friday_start'] = $pk_value['start'];
						$json['lb_retailer_day_friday_end'] = $pk_value['end'];
					}
					if ($pk_key == 'Saturday') {
						$json['lb_retailer_day_saturday'] = $pk_key;
						$json['lb_retailer_day_saturday_start'] = $pk_value['start'];
						$json['lb_retailer_day_saturday_end'] = $pk_value['end'];
					}
					if ($pk_key == 'Sunday') {
						$json['lb_retailer_day_sunday'] = $pk_key;
						$json['lb_retailer_day_sunday_start'] = $pk_value['start'];
						$json['lb_retailer_day_sunday_end'] = $pk_value['end'];
					}
					if ($pk_key == $current_day) {
						$today_closing = $pk_value['end'];
						$json['lb_retailer_today_open_time'] = $pk_value['start'];
						$json['lb_retailer_today_closing_time'] = $pk_value['end'];
					} else {
					}
				}
				// Today closing time END

			}
		} else {
		}



		$json['dynamic_data'] = $dynamic_data;

		wp_send_json_success($json);
	}

	//----------END OF PUBLIC AJAX FUNCTIONS

	//----------START OF PUBLIC FRONT END RELATED FUNCTIONS
	/*
	* add selected theme class name to the body css classes
	* Compatible with leafbridge theme
	*/
	public static function leafbridge_theme_classes($classes)
	{
		$block_theme_enabled = (wp_is_block_theme()) ? "block_theme_enabled" : "non_block_theme";
		$classes = array('leafbridge_theme_1', $block_theme_enabled);
		return $classes;
	}

	/*
	* popup wizard before vieweing the site
	*
	*/
	public function wizard_popup_header()
	{
		// get current page
		global $wp;

		$homepage_url = get_home_url();
		$current_url = home_url(add_query_arg(array(), $wp->request));

		// get order-status page slug
		$leafbridge_settings = get_option('leafbridge-settings');
		$floating_cart_btn_position = $leafbridge_settings['leafbridge-settings-age-modal']['leafbridge-floating-cart-position'];
		$floating_cart_btn_inject_location = $leafbridge_settings['leafbridge-settings-age-modal']['leafbridge-settings-wizard-type-modal-element'];
		$leafbridge_settings_page_settings = $leafbridge_settings['leafbridge-settings-page-settings'];
		$leafbridge_order_status_page_url = $leafbridge_settings_page_settings['leafbridge-config-ui-order-status-link'];

		$leafbridge_order_status_page_url =  str_replace($homepage_url, "", $leafbridge_order_status_page_url);
		$leafbridge_order_status_page_slug =  str_replace("/", "", $leafbridge_order_status_page_url);

		// check if order-status/thank-you page slug is not there on the current page.
		// if so, wizard popup will be shown.
		// otherwise it should go to the order-status/thank-you page and then should check the order id.
		// if order id not there on the url. user will be redirected to homepage
		if (!str_contains($current_url, $leafbridge_order_status_page_slug)) {
			// if(is_page('thank-you') || is_page('order-status') ){
			ob_start();
		?>
			<div id="floating_wizard_button" class="<?php echo $floating_cart_btn_position; ?>" <?php echo ($floating_cart_btn_inject_location !== "") ? 'data_inject_location="' . $floating_cart_btn_inject_location . '"' : ''; ?>>
				<button type="button" class="leaf_bridge_btn reset_retailer_selection" name="button" id="reset_retailer_selection" title="Reset Cart"><i class="fa-solid fa-rotate-left"></i></button>
				<button type="button" class="leaf_bridge_btn" name="button" id="open_the_cart" title="Open Cart"><i class="fa-solid fa-cart-shopping"></i><span class="cart_count"></span></button>
			</div>
			<div class="leafbridge_wizard_popup" id="leafbridge_shop_wizard_popup">
				<div class="leafbridge_popup_box" style="display:none;">
					<div class="leafbridge_popup_box_inner">
						<div class="leafbridge_shop_wizard_wrapper">
							<div class="leafbridge_shop_wizard_container">
								<?php
								$leafbridge_settings = get_option('leafbridge-settings');
								$leafbridge_settings_age_modal = $leafbridge_settings['leafbridge-settings-age-modal'];
								$leafbridge_settings_page_settings = $leafbridge_settings['leafbridge-settings-page-settings'];


								$enable_age_popup = $leafbridge_settings_age_modal['leafbridge-settings-age-modal-is-enable'];
								$age_heading = $leafbridge_settings_age_modal['leafbridge-config-ui-heading'];
								$age_descr = $leafbridge_settings_age_modal['leafbridge-config-ui-description'];
								$age_notice = $leafbridge_settings_age_modal['leafbridge-config-ui-error-message'];

								$terms_link = $leafbridge_settings_page_settings['leafbridge-config-ui-terms-link'];

								if ($enable_age_popup != 0) :
								?>
									<!-- age -->
									<div class="leafbridge_shop_wizard_step" id="leafbridge_shop_wizard_set_age" data_age_popup="<?php echo $enable_age_popup; ?>">
										<div class="wizard_box">
											<div class="wizard_box_inner">
												<div class="wizard_box_header">
													<h2 class="leafbridge-bg-red"><?php _e($age_heading, 'leafbridge') ?></h2>
													<p><?php _e($age_descr, 'leafbridge') ?></p>
												</div>
												<div class="wizard_box_container">
													<div class="wizard_box_container_wrapper wizard_box_nav_buttons">
														<div class="leafbridge_popup_box_grouped_inputs wizard_box_button_group disabled-btn-panel">
															<button disabled="disabled" class="wizard_age leaf_bridge_btn disabled-btn" type="button" name="button" value="no"><?php _e('No', 'leafbridge') ?></button>
															<button disabled="disabled" class="wizard_age leaf_bridge_btn disabled-btn lf-btn-primary" data_direction="#leafbridge_shop_wizard_set_location" type="button" name="button" value="yes"><?php _e('Yes', 'leafbridge') ?></button>
														</div>
														<p class="lb-age-warning-error error" style="display:none;"><?php _e($age_notice, 'leafbridge') ?></p>
														<div class="leafbridge_popup_box_tandc_agreement">
															<input type="checkbox" name="LeafBridgeTandC" value="456" id="LeafBridgeTandC">
															<label for="LeafBridgeTandC"><?php _e('I accept the <a href="' . $terms_link . '" target="_blank">Terms and Conditions</a>', 'leafbridge') ?></label>
														</div>

													</div>
												</div>
												<div class="wizard_box_nav_buttons wizard_box_footer">
													<div class="wizard_box_button_group">
														<button type="button" name="button" class="leaf_bridge_btn" style="display:none;" data_direction="#leafbridge_shop_wizard_set_location"><?php _e('next', 'leafbridge') ?></button>
													</div>
												</div>
											</div>
										</div>
									</div>

								<?php endif; ?>
								<!-- reset confirmation -->
								<div class="leafbridge_shop_wizard_step" id="leafbridge_shop_wizard_reset_selection_confirm" style="display:none;">
									<div class="wizard_box">
										<div class="wizard_box_inner">
											<div class="wizard_box_header">
												<h2 class="leafbridge-bg-red"><?php _e('This will clear your cart', 'leafbridge') ?></h2>
												<p><?php _e('This will clear your cart and selections. Are you sure you want to proceed ?', 'leafbridge') ?></p>
											</div>
											<div class="wizard_box_container">
												<div class="wizard_box_container_wrapper wizard_box_nav_buttons">
													<div class="leafbridge_popup_box_grouped_inputs wizard_box_button_group" style="pointer-events: auto;opacity:1;">
														<button class="wizard_reset leaf_bridge_btn" type="button" name="button" value="no"><?php _e('No', 'leafbridge') ?></button>
														<button class="wizard_reset leaf_bridge_btn lf-btn-primary" data_direction="#leafbridge_shop_wizard_set_location" type="button" name="button" value="yes"><?php _e('Yes', 'leafbridge') ?></button>
													</div>
												</div>
											</div>
											<div class="wizard_box_nav_buttons wizard_box_footer">
												<div class="wizard_box_button_group">
													<button type="button" name="button" class="leaf_bridge_btn" style="display:none;" data_direction="#leafbridge_shop_wizard_set_location"><?php _e('next', 'leafbridge') ?></button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- location -->
								<div class="leafbridge_shop_wizard_step" id="leafbridge_shop_wizard_set_location" data_age_popup="<?php echo $enable_age_popup; ?>" style="<?php echo ($enable_age_popup == 0) ? '' : 'display:none;'; ?>">
									<div class="wizard_box">
										<div class="wizard_box_inner">
											<div class="wizard_box_header">
												<h2 class="leafbridge-bg-dark"><?php _e('Select your store', 'leafbridge') ?></h2>
												<p><?php _e('Select a retailer store from either recreational or medicine', 'leafbridge') ?></p>
											</div>
											<div class="wizard_box_container">
												<div class="wizard_box_container_wrapper">
													<div style="white-space:pre;display:none;">
														<?php
														$retailers_array = array();
														$args = array(
															'post_type' => 'retailer',
														);
														$retailer_loop = new WP_Query($args);
														$arr_coutner = 0;
														while ($retailer_loop->have_posts()) {
															$retailer_loop->the_post();
															$retailer_all_data = get_post_meta(get_the_ID(), '_lb_retailer_options_all', true);
															$retailer_options = unserialize($retailer_all_data['_lb_retailer_options']);
															$retailer_menu_types = $retailer_options['menuTypes'];

															foreach ($retailer_menu_types as $retailer_menu_type_key => $retailer_menu_type) {
																$retailers_array[$retailer_menu_type][$arr_coutner]['id'] = $retailer_all_data['_lb_retailer_id'];
																$retailers_array[$retailer_menu_type][$arr_coutner]['data'] = $retailer_options;
															}
															$arr_coutner++;
														}
														// print_r($retailers_array);
														?>
													</div>
													<div class="wizard_box_tabs">
														<div class="wizarbox_tab_header">
															<?php
															$counter = 0;
															foreach ($retailers_array as $menuType => $retailers_nodes) {
																$active_btn = ($counter == 0) ? "active_btn" : "";
															?>
																<div class="wizardbox_tab_head leaf_bridge_btn <?php echo $active_btn;  ?>" data_tab="#<?php echo $menuType;  ?>" data_menu_type="<?php echo $menuType;  ?>"><?php _e(strtolower($menuType), 'leafbridge') ?></div>
															<?php
																$counter++;
															}
															?>
														</div>
														<div class="wizardbox_tab_contents">
															<?php
															foreach ($retailers_array as $menuType => $retailers_nodes) {
															?>
																<div class="wizardbox_tab_content" id="<?php echo $menuType ?>">
																	<?php
																	foreach ($retailers_nodes as $key => $retailers_node) {
																	?>
																		<div class="wizardbox_retailer_box" data_retailer_id="<?php echo $retailers_node['data']['id'] ?>">
																			<span class="rtl_name"><?php echo $retailers_node['data']['name']; ?></span>
																			<span class="rtl_addr"><?php _e($retailers_node['data']['address'], 'leafbridge') ?></span>
																			<button class="leaf_bridge_btn lb-btn-secondary-inner" data_retailer_id="<?php echo $retailers_node['data']['id'] ?>"><?php _e('Select Retailer', 'leafbridge') ?></button>
																		</div>
																	<?php
																	}
																	?>
																</div>
															<?php
															}
															?>
														</div>
													</div>
												</div>
											</div>
											<div class="wizard_box_nav_buttons wizard_box_footer">
												<div class="wizard_box_button_group">
													<button type="button" name="button" style="<?php echo ($enable_age_popup == 0) ? 'display:none;' : ''; ?>" class="leaf_bridge_btn" data_direction="#leafbridge_shop_wizard_set_age"><?php _e('go back', 'leafbridge') ?></button>
													<button type="button" name="button" class="leaf_bridge_btn lf-btn-primary" style="display:none;" data_direction="#leafbridge_shop_wizard_set_delivery"><?php _e('next', 'leafbridge') ?></button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- delivery -->
								<div class="leafbridge_shop_wizard_step leafbridge_shop_wizard_set_delivery-modal" id="leafbridge_shop_wizard_set_delivery" style="display:none;">
									<div class="wizard_box">
										<div class="wizard_box_inner">
											<div class="wizard_box_header">
												<h2 class="leafbridge-bg-dark"><?php _e('How are you placing your order?', 'leafbridge') ?></h2>
												<p><?php _e('Some retailers will only support one order type.', 'leafbridge') ?></p>
											</div>
											<div class="wizard_box_container">
												<div class="wizard_box_container_wrapper product_collection" id="product_collection_2">


												</div>
												<div class="wizardbox_zipcode_validation_wrapper">
													<div class="wizardbox_zipcode_input_group inline_group">
														<input type="number" name="" class="leaf_bridge_input" value="" id="wizardbox_zipcode" placeholder="<?php _e('Enter your Zip Code', 'leafbridge'); ?>">
														<button type="button" name="button" class="leaf_bridge_btn" id="wizardbox_zipcode_validation"><?php _e('check availability', 'leafbridge'); ?></button>
													</div>
													<span class="error_zip_validation" style="display:none;"><?php _e('Sorry, delivery is not avaialable to the given location.<br/> May be try <strong>store pickup</strong> ?', 'leafbridge'); ?></span>
												</div>
											</div>

											<div class="wizard_box_nav_buttons wizard_box_footer">
												<div class="wizard_box_button_group">
													<button type="button" name="button" class="leaf_bridge_btn prev" data_direction="#leafbridge_shop_wizard_set_location"><?php _e('go back', 'leafbridge') ?></button>
													<button id="wizard_show_products" type="button" name="button" class="leaf_bridge_btn next lf-btn-primary" style="display:none;" data_direction="#leafbridge_shop_wizard_view_products"><?php _e('next', 'leafbridge') ?></button>
												</div>
											</div>

										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
			$returnHTML = ob_get_contents();
			ob_end_clean();
			echo $returnHTML;
		} else {
			if (!isset($_GET['orderNumber'])) {
				wp_redirect(get_home_url());
			}
		}
	}

	/*
	* NEW SHORTCODE FOR SHOP PAGE
	* Confirm age and then select retailer and menu type then select deliver or store pickup. after that, show products based on the retailer and menu type
	* Compatible with leafbridge theme
	* Calling from Hook
	*
	*/
	public static function get_leafbridge_shop_wizard($atts = [])
	{

		// $returnHTML = do_shortcode('[leafbridge-breadcrumbs]');
		ob_start();
		echo self::leafbridge_breadcrumb_render();
		// $is_searching = (isset($_GET['products_search'])) ? 'style="display:none;"' : 'noparamm';
		$is_searching = '';
		?>
		<div class="leafbridge_shop_wizard_wrapper">
			<div class="leafbridge_shop_wizard_container">
				<div class="show_products_based_on_retailer" id="leafbridge_shop_wizard_view_products" style="display:none;">
					<div class="wizard_box_header" style="display:none;">
						<h2><?php _e('Select your products', 'leafbridge') ?></h2>
						<p><?php _e('Filter products from category', 'leafbridge') ?></p>
					</div>
					<div class="wizard_box_container">
						<div class="wizard_box_container_wrapper product_collection" id="product_collection">
							<div class="wizard_prods_wrapper">
								<div class="wizard_prods_inner">
									<div id="specials_tab_filter_background"></div>
									<div class="wizard_prods_categories">
										<div class="class_toggle_category open_cat_close">
											<i class="fa-solid fa-xmark"></i>
										</div>
										<div class="lb_search_products">
											<div class="lb_search_products_input_wrapper">
												<input type="text" name="" value="" id="products_search_input" value="" placeholder="Search Products">
												<button type="button" name="button" id="products_clear_search_button" style="" title="Clear product search keywords"><i class="fa-solid fa-rotate-left"></i></button>
												<button type="button" name="button" class="lb_prod_filter_btn" id="products_search_button" title="Search products with matching keywords"><i class="fa-solid fa-magnifying-glass"></i></button>
											</div>
										</div>
										<div class="lb_prod_filter_attrs">
											<?php
											$filter_btn = '<div class="lb_prod_filter_button_wrapper" style="display:none !important;"><button class="lb_prod_filter_btn" title="Apply search filters" type="button" name="button"><label style="display:inline-block;margin-right:5px;">Filter</label><i class="fa-solid fa-filter"></i></button></div>';
											echo $filter_btn;

											$leafbridge_settings = get_option('leafbridge-settings');
											$leafbridge_product_categories = $leafbridge_settings['leafbridge-product-categories'];

											$lb_product_filter_options = get_option('lb_product_filter_options');
											echo "<pre style='display:none;'>";
											print_r($lb_product_filter_options);
											echo "</pre>";

											$terms = get_terms(array(
												'taxonomy' => 'categories',
												'hide_empty' => true,
											));
											?>
											<!-- Specials -->
											<div class="lb_prod_filter_attr_box open_attr_box" filter_selected_val="" filter_attr="MenuSectionFilter">
												<?php
												echo '<button class="lb_prod_filter_attr_title"><strong>Featured</strong></button>';
												echo '<div class="prod_filter_ul_wrapper " >';
												echo '<ul id="staff_picks" class="prod_filter_ul">';
												echo '<li class="" attr_value="STAFF_PICKS">';
												echo '<div class="prod_cat_select_icon">';
												echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
												echo '</div>';
												echo '<div class="prod_cat_select_lable lf-common">';
												echo '<span class="">';
												echo __('Staff Picks', 'leafbridge');
												echo '</span>';
												echo '</div>';
												echo '</li>';

												echo '<li class="" attr_value="SPECIALS">';
												echo '<div class="prod_cat_select_icon">';
												echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
												echo '</div>';
												echo '<div class="prod_cat_select_lable lf-common">';
												echo '<span class="">';
												echo __('Specials', 'leafbridge');
												echo '</span>';
												echo '</div>';
												echo '</li>';

												echo '</ul>';
												echo '</div>';
												?>
											</div>
											<!-- categories -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="categories">
												<button class="lb_prod_filter_attr_title"><strong>Category</strong></button>
												<?php
												echo '<div class="prod_filter_ul_wrapper" >';
												echo '<ul id="prods_categories" class="prod_filter_ul" >';
												foreach ($terms as $key => $term) {
													$term_name = strtoupper($term->name);
													echo '<li class="" attr_value="' . $term_name . '">';
													echo '<div class="prod_cat_select_icon">';
													echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
													echo '</div>';
													echo '<div class="prod_cat_select_lable lf-common">';
													echo '<span class="">';
													echo __(strtolower(str_replace("_", " ", $term_name)), 'leafbridge');
													echo '</span>';
													echo '</div>';
													echo '</li>';
												}
												echo '</ul>';
												echo '</div>';
												?>
											</div>
											<!-- brands -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="brands">
												<?php
												// brands
												echo '<button class="lb_prod_filter_attr_title"><strong>Brands</strong></button>';
												echo '<div class="prod_filter_ul_wrapper" >';
												echo '<ul id="prods_brands" class="prod_filter_ul">';
												$brands_array = $lb_product_filter_options['brands'];
												$brands_array_display = array();
												foreach ($brands_array as $retailer_id => $retailer_node_brands) {

													foreach ($retailer_node_brands as $retailer_category => $retailer_category_brands) {

														asort($retailer_category_brands);
														foreach ($retailer_category_brands as $brand_id => $brand_name) {
															if ($brand_id != '0' || $brand_id != 0) {
																$brands_array_display[$retailer_id][$brand_id] = $brand_name;
															}
														}
													}
												}

												foreach ($brands_array_display as $retailer_id => $retailer_brands) {
													asort($retailer_brands);
													foreach ($retailer_brands as $brand_id => $brand_name) {
														if ($brand_id != '0' || $brand_id != 0) {
															echo '<li class="" brand_retailer="' . $retailer_id . '" attr_value="' . $brand_id . '">';
															echo '<div class="prod_cat_select_icon">';
															echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
															echo '</div>';
															echo '<div class="prod_cat_select_lable lf-common">';
															echo '<span class="">';
															echo __($brand_name, 'leafbridge');
															echo '</span>';
															echo '</div>';
															echo '</li>';
														}
													}
												}
												echo '</ul>';
												echo '</div>';
												?>
											</div>
											<!-- potency CBD -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="potency_cbd">
												<button class="lb_prod_filter_attr_title"><strong>Potency : CBD</strong></button>
												<div class="prod_filter_ul_wrapper">
													<ul id="prods_categories" class="prod_filter_ul">
														<li class="" tch_retailer="" thc_menu_type="" attr_value="0_1_%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">0 - 1%</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="1.0001_100%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">1% +</span></div>
														</li>

														<li class="" tch_retailer="" thc_menu_type="" attr_value="0_100_mg">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">0mg - 100 mg</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="100.0001_100000_mg">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">100mg +</span></div>
														</li>

													</ul>
												</div>
											</div>
											<!-- potency THC -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="potency_thc">
												<button class="lb_prod_filter_attr_title"><strong>Potency : THC</strong></button>
												<div class="prod_filter_ul_wrapper">
													<ul id="prods_categories" class="prod_filter_ul">
														<li class="" tch_retailer="" thc_menu_type="" attr_value="0_20_%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">0-20%</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="20.001_30_%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">20-30%</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="30.001_40_%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">30-40%</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="40.001_1000_%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">40%+</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="0_100_mg">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">0-100mg</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="100.001_10000_mg">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">100mg+</span></div>
														</li>
													</ul>
												</div>
											</div>

											<!-- effects -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="effects">
												<?php
												// Effects
												echo '<button class="lb_prod_filter_attr_title"><strong>Effects</strong></button>';
												$effects_array = array();
												foreach ($lb_product_filter_options['effects'] as $key => $effects_main) {
													foreach ($effects_main as $key => $effect) {
														(!in_array($effect, $effects_array)) ?  array_push($effects_array, $effect) : '';
													}
												}
												echo '<div class="prod_filter_ul_wrapper" >';
												echo '<ul id="prods_effects" class="prod_filter_ul">';
												sort($effects_array);
												foreach ($effects_array as $key => $effects_array_node) {
													echo '<li class="" attr_value="' . $effects_array_node . '">';
													echo '<div class="prod_cat_select_icon">';
													echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
													echo '</div>';
													echo '<div class="prod_cat_select_lable lf-common">';
													echo '<span class="">';
													echo __(strtolower(str_replace("_", " ", $effects_array_node)), 'leafbridge');
													echo '</span>';
													echo '</div>';
													echo '</li>';
												}
												echo '</ul>';
												echo '</div>';

												?>
											</div>
											<!-- strainType -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="strainType">
												<?php
												echo '<button class="lb_prod_filter_attr_title"><strong>Strain Type</strong></button>';

												echo '<div class="prod_filter_ul_wrapper" >';
												echo '<ul id="prods_strainType" class="prod_filter_ul">';
												sort($effects_array);
												foreach ($lb_product_filter_options['strainType'] as $key => $strain_node) {
													echo '<li class="" attr_value="' . $strain_node . '">';
													echo '<div class="prod_cat_select_icon">';
													echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
													echo '</div>';
													echo '<div class="prod_cat_select_lable lf-common">';
													echo '<span class="">';
													echo __(strtolower(str_replace("_", " ", $strain_node)), 'leafbridge');
													echo '</span>';
													echo '</div>';
													echo '</li>';
												}
												echo '</ul>';
												echo '</div>';

												?>
											</div>

											<!-- weight -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="weight">
												<button class="lb_prod_filter_attr_title"><strong>Weight</strong></button>
												<div class="prod_filter_ul_wrapper">
													<ul id="prods_strainType" class="prod_filter_ul">
														<?php
														// leafbridge_filters_xdx
														$leafbridge_filters_xdx = get_option('leafbridge_filters_xdxs');
														$leafbridge_filters_weights = $leafbridge_filters_xdx['weight'];
														echo '<pre style="display:none;">';
														print_r($leafbridge_filters_xdx);
														echo '</pre>';

														foreach ($leafbridge_filters_weights  as $retailer_id_weight => $retailer_weight_node) {
															foreach ($retailer_weight_node as $retailer_menu_type => $r_m_weights) {
																echo '<div class="weight_retailer" retailer_id="' . $retailer_id_weight . '" menu_type="' . $retailer_menu_type . '">';
																sort($r_m_weights);
																echo '<div class="weight_buttons_wrapper">';
																foreach ($r_m_weights as $r_m_weight) {
																	echo '<li class="" attr_value="' . $r_m_weight . '">';
																	echo '<div class="prod_cat_select_icon">';
																	echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
																	echo '</div>';
																	echo '<div class="prod_cat_select_lable lf-common">';
																	echo '<span class="">';
																	echo __(strtolower(str_replace("_", " ", $r_m_weight)), 'leafbridge');
																	echo '</span>';
																	echo '</div>';
																	echo '</li>';
																}
																echo '</div>';
																echo '</div>';
															}
														}

														?>
													</ul>
												</div>
											</div>


											<?php
											echo $filter_btn;
											?>
										</div>
									</div>
									<div class="wizard_prods_view">

										<div class="class_toggle_category">
											<i class="fa-solid fa-bars"></i>
											<p><?php echo __('View Filters', 'leafbridge') ?></p>
										</div>
										<div id="wizard_prods_view">
											<?php echo self::loading_animation()  ?>
										</div>
										<section class="wizard_product_section" id="prod_show_cat_all" data_category="all" style="">
											<!-- <h3><?php //echo __( 'All Products', 'leafbridge' )
														?></h3> -->
											<div class="wizard_sort_wrapper">
												<div class="wizard_sort_by">
													<select class="" name="" id="leafbridge_products_sort_by" title="Select the sorting order">
														<?php
														$sort_dropdown_array = array(
															array('NAME_ASC', 'Name A - Z'),
															array('NAME_DESC', 'Name Z - A'),
															// array('POPULAR_ASC', 'Popularity Low - High'),
															array('POPULAR_DESC', 'Top Sellers'),
															array('PRICE_ASC', 'Price Low - High'),
															array('PRICE_DESC', 'Price High - Low'),
															array('POTENCY_ASC', 'Potency Low - High'),
															array('POTENCY_DESC', 'Potency High - Low'),
														);

														$selected_element = (isset($_GET['sort'])) ? $_GET['sort'] : 'NAME_ASC';

														foreach ($sort_dropdown_array as $key => $sort_link) {
															$mark_selected = ($selected_element == $sort_link[0]) ? 'selected' : '';
															echo '<option value="' . $sort_link[0] . '" ' . $mark_selected . ' >' . $sort_link[1] . '</option>';
														}
														?>
													</select>
												</div>
												<div class="wizard_toggle_list_grid">
													<div class="list_grid_buttons_wrapper">
														<button type="button" value="grid" name="button" value="grid" class="" title="Toggle grid view"><i class="fa-solid fa-grip"></i></button>
														<button type="button" value="list" name="button" value="list" title="Toggle list view"><i class="fa-solid fa-list"></i></button>
													</div>
												</div>
											</div>
											<div class="wizard_category_products_showcase" <?php echo $is_searching; ?>>

											</div>
											<div class="wizard_category_products_pagination allow_pages">
												<button type="button" name="button" class="leaf_bridge_btn prev" data_page="0" style="display:none;"><?php _e('back', 'leafbridge') ?></button>
												<p id="pagination_text"></p>
												<button type="button" name="button" class="leaf_bridge_btn next" gg data_page="2" style="display:none;"><?php _e('next', 'leafbridge') ?></button>
											</div>
										</section>

										<?php
										foreach ($leafbridge_product_categories as $key => $leafbridge_product_category) {
											// if($leafbridge_product_category !== "NOT_APPLICABLE"):
											if (false) :
										?>
												<section class="wizard_product_section" id="prod_show_cat_<?php echo $leafbridge_product_category ?>" data_category="<?php echo $leafbridge_product_category ?>" style="display:none;">
													<h3><?php echo __(strtolower(str_replace("_", " ", $leafbridge_product_category)), 'leafbridge') ?></h3>
													<div class="wizard_category_products_showcase">

													</div>
													<div class="wizard_category_products_pagination">
														<button type="button" name="button" class="leaf_bridge_btn prev" data_page="-1"><?php _e('back', 'leafbridge') ?></button>
														<button type="button" name="button" class="leaf_bridge_btn next" data_page="1"><?php _e('next', 'leafbridge') ?></button>
													</div>
												</section>
										<?php
											endif;
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}

	/*
	* Shortcode for showing products based on retailer. and not changable with the wizard
	*/
	public static function retailer_based_store($atts = [])
	{
		$retailer_id = (isset($atts['retailer_id'])) ? $atts['retailer_id'] : null;
		$retailer_name = (isset($atts['retailer_name'])) ? $atts['retailer_name'] : null;
		$show_filter = (isset($atts['show_filter'])) ? (($atts['show_filter'] == "on") ? true : false) : false;
		$menu_type = (isset($atts['menu_type'])) ? $atts['menu_type'] : "";
		$show_filter = (isset($atts['show_filter'])) ? ($atts['show_filter'] == "off" ?  "hide_filter" : "") : "";
		$force_wizard_change = (isset($atts['force_wizard'])) ? $atts['force_wizard'] : "off";
		$order_type = (isset($atts['order_type'])) ? $atts['order_type'] : "";
		$show_products_rtpg = (isset($atts['show_products'])) ? $atts['show_products'] : "off";

		$returnHTML = "";

		$post_obj = get_queried_object();

		ob_start();
		echo '<div class="retailer_based_store ' . $show_filter . '" retailer_id="' . $retailer_id . '" menu_type="' . $menu_type . '" retailer_name="' . $retailer_name . '" order_type="' . $order_type . '" force_wizard="' . $force_wizard_change . '" custom_name="">';
		echo ($show_products_rtpg == "on") ? do_shortcode('[leafbridge_shop_wizard]') : '';
		echo '</div>';
		// localstorage to save last visited retailer page
		echo '<script>';
		echo 'window.addEventListener("load", function () {';
		echo 'var lb_recent_location = {retailer_id : "' . $atts['retailer_id'] . '",retailer_page : "' . get_permalink($post_obj->ID) . '",page_id : "' . $post_obj->ID . '",retailer_name:"' . get_the_title($post_obj->ID) . '"};';
		echo 'localStorage.setItem("leafbridge_recent_location",JSON.stringify(lb_recent_location));';
		echo '})';
		echo '</script>';

		$returnHTML = ob_get_contents();
		ob_end_clean();

		return $returnHTML;
	}

	public function get_leafbridge_order_status()
	{
		ob_start();

		$orderId =  (isset($_GET['orderNumber']) ? trim($_GET['orderNumber']) : 0);
		$LeafBridge_Public_Orders = new LeafBridge_Public_Orders();
		$order_status = $LeafBridge_Public_Orders->leafbridge_get_orders($retailerId = "f0ff5c46-2f0c-4137-941b-b79b71e1d85c", $orderId);
	?>
		<div id="loading_animation">
			<?php echo self::loading_animation() ?>
		</div>
		<div id="leafbridge_order_details_wrapper" class="leafbridge_order_details_wrapper">

		</div>
	<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}

	// shortcode function to place the section to show featured products
	public function leafbridge_featured_products($attr)
	{
		ob_start();
		$product_count = (isset($attr['product_count'])) ? $attr['product_count'] : 10;
	?>
		<div id="leafbridge_featured_products_wrapper" class="leafbridge_featured_products_wrapper" data_product_count="<?php echo $product_count; ?>">
			<div class="leafbridge_featured_products_inner">

			</div>
		</div>
	<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}

	// shortcode function to place the section to show special products
	public function leafbridge_special_products($attr)
	{
		ob_start();
		echo self::leafbridge_breadcrumb_render();
		// do_shortcode('[leafbridge-breadcrumbs]');

		$product_count = (isset($attr['product_count'])) ? $attr['product_count'] : 10;
	?>
		<div id="leafbridge_special_products_wrapper" class="leafbridge_special_products_wrapper specials_page_wrapper" data_product_count="<?php echo $product_count; ?>" special_id="">

			<div class="leafbridge_special_products_inner">
				<div class="leafbridge_specials_tab_wrapper">
					<div class="specials_tab_notice">

					</div>
					<div class="specials_tabs">
						<div style="display:none;" class="specials_tab" specials_id=""></div>
					</div>

					<div id="specials_view_more_wrapper" class="specials_view_more_wrapper" style="display:none;color:blue">
						<a href="#" class="special_cards_view_more_btn">View More</a>
					</div>

					<div class="class_toggle_category">
						<i class="fa-solid fa-bars"></i>
						<p>View Filters</p>
					</div>
					<div class="specials_tab_content" id="specials_tab_content">
						<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; shape-rendering: auto; animation-play-state: running; animation-delay: 0s;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
							<path fill="none" stroke="#f4bd33" stroke-width="8" stroke-dasharray="42.76482137044271 42.76482137044271" d="M24.3 30C11.4 30 5 43.3 5 50s6.4 20 19.3 20c19.3 0 32.1-40 51.4-40 C88.6 30 95 43.3 95 50s-6.4 20-19.3 20C56.4 70 43.6 30 24.3 30z" stroke-linecap="round" style="transform: scale(0.8); transform-origin: 50px 50px; animation-play-state: running; animation-delay: 0s;">
								<animate attributeName="stroke-dashoffset" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0;256.58892822265625" style="animation-play-state: running; animation-delay: 0s;"></animate>
							</path>
						</svg>
						<div id="specials_tab_filter_background" class=""></div>
						<div class="specials_tab_filter">
							<div class="class_toggle_category open_cat_close">
								<i class="fa-solid fa-xmark"></i>
							</div>
							<div class="specials_tab_filter_inner">

							</div>
						</div>
						<div class="specials_tab_content_inner">

						</div>
					</div>

				</div>
			</div>
			<pre style="display:none;">
				<?php
				$LeafBridge_Products = new LeafBridge_Products();
				// $getSpecials = $LeafBridge_Products->getSpecials('6977440f-e913-4e14-890d-1a31a12ebd55');
				// $getSpecials = $LeafBridge_Products->getSpecials('6977440f-e913-4e14-890d-1a31a12ebd55');
				$getSpecials = $LeafBridge_Products->getSpecials('4bae2ae7-914b-4711-808c-efb4d4051955');

				print_r($getSpecials);
				?>
			</pre>
		</div>
	<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}

	//shortcode function to place section to show product categories
	public function leafbridge_product_categories()
	{
		ob_start();
	?>
		<div class="lb_product_categories_wrapper">
			<div class="lb_product_categories_inner">
				<?php
				$terms = get_terms(array(
					'taxonomy' => 'categories',
					'hide_empty' => true,
				));

				foreach ($terms as $key => $term) {
					$term_link = get_term_link($term);
					$upload_image = (get_term_meta($term->term_id, 'term_image', true) != null) ? get_term_meta($term->term_id, 'term_image', true) : '';
				?>
					<div class="lb_category_box">
						<a href="<?php echo $term_link; ?>">
							<img src="<?php echo $upload_image;  ?>" alt="<?php echo  str_replace("_", " ", $term->name);  ?>">
							<span><?php echo  str_replace("_", " ", $term->name);  ?></span>
						</a>
					</div>
				<?php
				}
				?>
			</div>
		</div>
	<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}

	// shortcode function to single product page to be used on block theme tempaltes
	public function leafbridge_product_single_page()
	{
		if (isset($_GET['action'])) {
			if ($_GET['action'] != "elementor") {
				return $this->output_lb_prod_single_shortcode();
			} else {
				return "[leafbridge-product-single-page]";
			}
		} else {
			return $this->output_lb_prod_single_shortcode();
		}
	}

	public function output_lb_prod_single_shortcode()
	{
		if (file_exists(plugin_dir_path(__FILE__)   . "page_shortcodes/leafbridge_product_single_page.php")) {
			ob_start();
			require_once plugin_dir_path(__FILE__)  . "page_shortcodes/leafbridge_product_single_page.php";
			$returnHTML = ob_get_contents();
			ob_end_clean();
			return $returnHTML;
		} else {
			return 'not found';
		}
	}


	// shortcode function to single product page to be used on block theme tempaltes
	public function leafbridge_product_single_category_page()
	{
		if (isset($_GET['action'])) {
			if ($_GET['action'] != "elementor") {
				return $this->show_lb_single_category_page_shortcode_output();
			} else {
				return "[leafbridge-product-single-category-page]";
			}
		} else {
			return $this->show_lb_single_category_page_shortcode_output();
		}
	}

	public function show_lb_single_category_page_shortcode_output()
	{
		if (file_exists(plugin_dir_path(__FILE__)   . "page_shortcodes/leafbridge_product_single_category_page.php")) {
			ob_start();
			require_once plugin_dir_path(__FILE__)  . "page_shortcodes/leafbridge_product_single_category_page.php";
			$returnHTML = ob_get_contents();
			ob_end_clean();
			return $returnHTML;
		} else {
			return 'not found';
		}
	}

	public function prod_page_templates($template)
	{
		$leafbridge_settings = get_option('leafbridge-settings');
		$leafbridge_settings_page_settings = $leafbridge_settings['leafbridge-settings-page-settings'];
		$disable_single_product_template = (isset($leafbridge_settings_page_settings['disable-single-product-template'])) ? $leafbridge_settings_page_settings['disable-single-product-template'] : "false";
		$disable_default_template = ($disable_single_product_template == "false") ? false : true;

		if (!wp_is_block_theme()) {
			$post_type = 'product'; // Change this to the name of your custom post type!

			if (is_post_type_archive($post_type) && file_exists(plugin_dir_path(__DIR__) . "templates/archive-$post_type.php")) {
				$template = plugin_dir_path(__DIR__) . "templates/archive-$post_type.php";
			}

			if (!$disable_default_template && is_singular($post_type) && file_exists(plugin_dir_path(__DIR__) . "public/single-$post_type.php")) {
				$template = plugin_dir_path(__DIR__) . "public/single-$post_type.php";
			}

			if (is_tax('categories') && file_exists(plugin_dir_path(__DIR__) . "public/archive-product.php")) {
				$template = plugin_dir_path(__DIR__) . "public/archive-product.php";
			}
		}

		return $template;
	}

	/*
	* Just in case if automatic wizard placing does not work. use the shortcode to place the wizard
	*/
	public function leafbridge_selection_wizard_v2_new()
	{
		ob_start();
		$this->call_the_new_wizard('shortcode');
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}

	public function leafbridge_cart_render()
	{
		ob_start();
	?>
		<div id="floating_cart" class="floating_cart_wrapper">
			<div class="floating_cart_bg">

			</div>
			<div class="floating_cart_inner">
				<div class="floating_cart_container">
					<div class="floating_cart_header">
						<h3><?php _e('Shopping Cart', 'leafbridge'); ?></h3>
						<div class="floating_cart_header-btn-panel">
							<button type="button" class="leaf_bridge_btn reset_retailer_selection" name="button" id="reset_retailer_selection" title="Reset cart"><i class="fa-solid fa-rotate-left"></i><?php //_e('Reset','leafbridge');
																																																			?></button>
							<button type="button" class="leaf_bridge_btn" name="button" id="close_floating_cart" title="Close"><i class="fa-solid fa-xmark"></i><?php //_e('Close','leafbridge');
																																								?></button>
						</div>
					</div>
					<div class="floating_cart_items_wrapper">
						<div class="floating_cart_items_inner">
							<div class="floating_cart_item_box">

							</div>
						</div>
					</div>
					<div class="floating_cart_footer_wrapper">
						<div class="lb-update_cart_qts-btn">
							<button type="button" id="update_cart_qts" class="leaf_bridge_btn lf-btn-primary" name="button">Update Cart</button>
						</div>

						<div class="floating_cart_notes">

						</div>
						<div class="floating_cart_footer">
							<a href="#" class="lb-warning"><?php _e('Proceed to checkout', 'leafbridge'); ?><span class="cart_total"></span></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		echo $returnHTML;
	}

	/*
	* remove LB scripts on Divi Builder Editor
	*/
	public function remove_lb_scripts_on_divi_builder()
	{
		$enqued_scripts_styles = array();
		$enqued_scripts_styles['scripts'] = array();

		global $wp_scripts;


		foreach ($wp_scripts->queue as $script) :
			$enqued_scripts_styles['scripts'][] =  $wp_scripts->registered[$script]->handle;
		endforeach;


		if (in_array('et-fb', get_body_class()) || in_array('e-preview--show-hidden-elements', get_body_class())) {
			foreach ($enqued_scripts_styles['scripts'] as $script_i => $enqued_script_handle) {
				if (strpos($enqued_script_handle, 'LeafBridge') !== false) {
					wp_dequeue_script($enqued_script_handle);
				}
			}
		}
	}

	/*
	* New wizard to based on site owner selection.
		wizard will be either modal or link AKA displayed on the header bar using a shortcode
	*/
	public function call_the_new_wizard($shortcode)
	{
		if (class_exists('\Elementor\Plugin')) {
			if (\Elementor\Plugin::$instance->preview->is_preview_mode()) {
				echo "[leafbridge_selection_wizard_v2] will appear din front end";
			} else {
				echo ($shortcode != "shortcode") ? '<div id="popup_container" elementor-out-editor ' . $shortcode . '></div>' : "";
				$atts = ($shortcode == "shortcode") ? array('autoshow' => 'false') : array('autoshow' => 'true');
				echo $this->leafbridge_selection_wizard_v2($atts);
			}
		} else {
			echo ($shortcode != "shortcode") ? '<div id="popup_container" data-no-elementor' . $shortcode . '></div>' : "";
			$atts = ($shortcode == "shortcode") ? array('autoshow' => 'false') : array('autoshow' => 'true');
			echo $this->leafbridge_selection_wizard_v2($atts);
		}
	}

	public function leafbridge_selection_wizard_v2($atts)
	{
		ob_start();
		$lb_product_filter_options = get_option('leafbridge-settings');

		$age_modal_settings = $lb_product_filter_options['leafbridge-settings-age-modal'];
		$wizard_type = $age_modal_settings['leafbridge-settings-wizard-type'];

		if ($wizard_type == 'modal') {
			$this->wizard_popup_header();
		} elseif ($wizard_type == 'link') {
			$this->link_wizard_ui($atts);
			$this->leafbridge_link_wizard_age_popup();
		} else {
		}

		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}

	/*
	* Function for link wizard UI
	*/
	public function link_wizard_ui($atts)
	{
		$lb_product_filter_options = get_option('leafbridge-settings');
		$autoshow = isset($atts['autoshow']) ? (($atts['autoshow'] == 'true') ? 'true' : 'false') : 'true';
		$age_modal_settings = $lb_product_filter_options['leafbridge-settings-age-modal'];
		$wizard_type = $age_modal_settings['leafbridge-settings-wizard-type'];
		$leafbridge_default_settings = $lb_product_filter_options['leafbridge_default_settings'];

		$perent_element = ($autoshow) ?  'parent_element="' . $age_modal_settings["leafbridge-settings-wizard-type-link-element"] . '"' : "no_autoshow";

		echo '<pre class="findme" style="display:none;">';
		print_r($age_modal_settings);
		print_r($atts);
		echo '</pre>';

	?>
		<div id="header_stiky_wizard" ff class="header_stiky_wizard" parent_element="<?php echo $age_modal_settings["leafbridge-settings-wizard-type-link-element"] ?>">
			<div class="header_stiky_wizard_inner" autoshow_wizard="<?php echo $autoshow; ?>">
				<pre style="white-space:pre;display:none;">
					<?php
					$retailers_array = array();
					$args = array(
						'post_type' => 'retailer',
						'posts_per_page' => -1,
						'post_status' => 'publish',
					);
					$retailer_loop = new WP_Query($args);

					while ($retailer_loop->have_posts()) {
						$retailer_loop->the_post();
						$retailer_all_data = get_post_meta(get_the_ID(), '_lb_retailer_options_all', true);
						$retailer_options = unserialize($retailer_all_data['_lb_retailer_options']);
						$retailer_menu_types = $retailer_options['menuTypes'];

						$retailers_array[$retailer_all_data['_lb_retailer_id']] = $retailer_options;
					}
					// print_r($leafbridge_default_settings);
					// print_r($atts);
					?>
				</pre>

				<!-- select retailer -->
				<div class="header_stiky_wizard_select_wrapper">
					<select class="" name="" id="select_store_sticky_wizard" title="You must select a retailer to get the products">
						<?php
						echo '<option value="">Select Retailer</option>';
						foreach ($retailers_array as $retailer_id => $retailer_node) {
							echo '<option value="' . $retailer_id . '">' . $retailer_node['name'] . '</option>';
						}
						?>
					</select>
				</div>

				<!-- select menu type -->
				<div class="group_selects_wrapper" id="menu_types_selector_wrapper" selected_menu_type="">
					<?php
					// there will be a select element for menu type per each retailer
					foreach ($retailers_array as $retailer_id => $retailer_node) {
						echo '<div class="header_stiky_wizard_select_wrapper menu_type_selector" retailer_id="' . $retailer_id . '" >';
						echo '<select class="" name="" id="select_menu_type_sticky_wizard_' . $retailer_id . '" disabled title="You must select the menu type to get the products">';
						echo '<option value="">Select Menu Type</option>';
						foreach ($retailer_node['menuTypes'] as $menutype_count => $menu_type_text) {
							echo '<option value="' . $menu_type_text . '">' . ucfirst(strtolower($menu_type_text)) . '</option>';
						}
						echo '</select>';
						echo '</div>';
					}
					?>
				</div>

				<!-- select collection type -->
				<div class="header_stiky_wizard_select_wrapper select_collection_wrapper">
					<span class="loading_collection_methods">
						<div style="display:inline-flex;"><span>Loading</span><?php echo self::loading_spinner_pulse() ?></div>
					</span>
					<select class="" name="" id="select_collection_type_sticky_wizard" disabled title="You must select a collection type to get the products">
						<option value="">Please Select</option>
						<!-- <option value="PICKUP">Pickup</option>
						<option value="DELIVERY">Delivery</option>
						<option value="curbsidePickup">Curbside Pickup</option>
						<option value="driveThruPickup">Drive-Thru Pickup</option> -->
					</select>
				</div>

				<div class="header_stiky_wizard_button_wrapper">
					<div class="" id="floating_wizard_button">
						<button type="button" name="button" id="set_floating_wizard_data" style="display:none;" disabled>Set</button>
						<button type="button" class="leaf_bridge_btn reset_retailer_selection_v2" name="button" id="reset_retailer_selection_v2" title="Reset Cart"><i class="fa-solid fa-rotate-left"></i></button>
						<button type="button" class="leaf_bridge_btn" name="button" id="open_the_cart" title="Open Cart"><i class="fa-solid fa-cart-shopping"></i><span class="cart_count"></span></button>
					</div>
				</div>
				<div class="" id="reset_notice_header">
					<div class="reset_selection_wrapper">
						<h2 class="reset_notice_h">This will clear your cart</h2>
						<p>This will clear your cart and selections. Are you sure you want to proceed ?</p>
						<div class="new_reset_buttons_wrapper">
							<button type="button" name="button" value="no">No</button>
							<button type="button" class="yes_btn" name="button" value="Yes">Yes</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function leafbridge_link_wizard_age_popup()
	{

		$leafbridge_settings = get_option('leafbridge-settings');
		$leafbridge_settings_age_modal = $leafbridge_settings['leafbridge-settings-age-modal'];
		$leafbridge_settings_page_settings = $leafbridge_settings['leafbridge-settings-page-settings'];

		$enable_age_popup = $leafbridge_settings_age_modal['leafbridge-settings-age-modal-is-enable'];
		$age_heading = $leafbridge_settings_age_modal['leafbridge-config-ui-heading'];
		$age_descr = $leafbridge_settings_age_modal['leafbridge-config-ui-description'];
		$age_notice = $leafbridge_settings_age_modal['leafbridge-config-ui-error-message'];

		$terms_link = $leafbridge_settings_page_settings['leafbridge-config-ui-terms-link'];

		$returnHTML = "";
		if ($enable_age_popup != 0) {
			ob_start();
		?>
			<div class="age-popup-box-overlay" id="age-popup-box-overlay" style="">
				<div class="age-popup-box" id="age-popup-box" style="display:none;">
					<div class="age-popup-box-inner">
						<div class="age-popup-box-header">
							<h2><?php _e($age_heading, 'leafbridge') ?></h2>
							<p><?php _e($age_descr, 'leafbridge') ?></p>
						</div>
						<div class="age-popup-box-buttons-and-terms">
							<div class="age-popup-box-buttons">
								<button class="popup-no-btn age-popup-btn" value="no"><?php _e('No', 'leafbridge') ?></button>
								<button class="popup-yes-btn age-popup-btn" value="yes"><?php _e('Yes', 'leafbridge') ?></button>
							</div>
							<p class="age-popup-warning" style="display:none;"><?php _e($age_notice, 'leafbridge') ?></p>
							<div class="age-popup-box-terms">
								<input type="checkbox" name="age-popup-tnc" value="456" id="age-popup-tnc">
								<label for="age-popup-tnc">I accept the <a href="https://leafbridge.wpengine.com/terms-and-conditions/" target="_blank">Terms and Conditions</a></label>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php
			$returnHTML = ob_get_contents();
			ob_end_clean();
		}
		echo $returnHTML;
	}

	public function leafbridge_specific_product_filter($atts)
	{
		$filter_attributes = json_encode($atts);
		$unique_id = "prod_slider_";
		$autoplay = isset($atts["autoplay"]) ? ($atts["autoplay"] == "true" ? 'true' : 'false') : 'false';
		foreach ($atts as $att) {
			$unique_id .= $att;
		}
		$unique_id .= rand(0, 9999999);
		ob_start();
		?>
		<div class="leafbridge_specific_product_filter_wrapper" id="<?php echo $unique_id; ?>" data_autoplay="<?php echo $autoplay; ?>">
			<pre style="display:none;">
			<?php
			print_r($atts);
			?>
			</pre>
			<div class="lb_specific_prods_slider_wrapper" filter_attributes="<?php echo htmlentities($filter_attributes, ENT_QUOTES, 'UTF-8');  ?>">
				<div id="loading_animation"><?php echo self::loading_animation() ?></div>
				<div class="swipernav_wrapper" style="display:none;">
					<div class="swiper-button-prev swipernav_btn"></div>
					<div class="swiper-button-next swipernav_btn"></div>
				</div>
				<div class="lb_specific_prods_slider_inner swiper" style="display:none;">
					<div class="swiper-wrapper">

					</div>
					<div class="swiper-pagination"></div>
				</div>
			</div>
		</div>
	<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}

	public function leafbridge_special_menu_cards($attr)
	{
		ob_start();
		$product_count = (isset($attr['product_count'])) ? $attr['product_count'] : 10;
	?>
		<div id="leafbridge_special_products_wrapper" class="leafbridge_special_products_wrapper leafbridge_special_cards_wrapper" data_product_count="<?php echo $product_count; ?>" special_id="">

			<div class="leafbridge_special_products_inner">
				<div class="leafbridge_specials_tab_wrapper">
					<div class="specials_tab_notice">
					</div>
					<div class="specials_tabs_new">
						<p style="display:inline-block;width:100%;text-align:center;">Loading Special Menus..</p>
						<div style="display:none;" class="specials_tab_new" specials_id=""></div>
					</div>
					<div class="specials_view_more_wrapper" style="display:none;">
						<a href="/specials?viewmore=true">View More</a>
					</div>
				</div>
			</div>
			<pre style="display:none;">
				<?php
				$LeafBridge_Products = new LeafBridge_Products();
				// $getSpecials = $LeafBridge_Products->getSpecials('6977440f-e913-4e14-890d-1a31a12ebd55');
				// $getSpecials = $LeafBridge_Products->getSpecials('6977440f-e913-4e14-890d-1a31a12ebd55');
				$getSpecials = $LeafBridge_Products->getSpecials('4bae2ae7-914b-4711-808c-efb4d4051955');

				print_r($getSpecials);
				?>
			</pre>
		</div>
	<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}

	public static function leafbridge_breadcrumb_render()
	{
		$post_obj = get_queried_object();
		$parent_post_id = 0;
		$pagination_posts_array = array();

		if (is_page($post_obj->ID)) {
			$temp_pagination_posts_array = get_post_ancestors($post_obj->ID);

			array_push($temp_pagination_posts_array, $post_obj->ID);

			foreach ($temp_pagination_posts_array as $key => $temp_pagination_post) {
				$ancesor_count = count(get_post_ancestors($temp_pagination_post));
				$pagination_posts_array[$ancesor_count]['case'] = 'get ancestors';
				$pagination_posts_array[$ancesor_count]['ancestor_count'] = $ancesor_count;
				$pagination_posts_array[$ancesor_count]['id'] = $temp_pagination_post;
				$pagination_posts_array[$ancesor_count]['parent'] = wp_get_post_parent_id($temp_pagination_post);
				$pagination_posts_array[$ancesor_count]['name'] = get_the_title($temp_pagination_post);
				$pagination_posts_array[$ancesor_count]['url'] = get_post_permalink($temp_pagination_post);
			}
			ksort($pagination_posts_array);
		} elseif (term_exists($post_obj->term_id)) {
			// $temp
			// array_push($pagination_posts_array, $post_obj->term_id);
			// $pagination_posts_array[$post_obj->term_id ]['name'] = get_term( $post_obj->term_id );
			$pagination_posts_array[$post_obj->term_id]['case'] = 'terms';
			$pagination_posts_array[$post_obj->term_id]['name'] = str_replace('_', ' ', get_term($post_obj->term_id)->name);
			$pagination_posts_array[$post_obj->term_id]['url'] = get_term_link($post_obj->term_id);
			$pagination_posts_array[$post_obj->term_id]['id'] = $post_obj->term_id;
		} else {
			$pagination_posts_array[$post_obj->ID]['case'] = 'else......';
			$pagination_posts_array[$post_obj->ID]['name'] = get_the_title($post_obj->ID);
			$pagination_posts_array[$post_obj->ID]['url'] = get_post_permalink($post_obj->ID);
			$pagination_posts_array[$post_obj->ID]['id'] = $post_obj->ID;
		}

		$returnHTML = '';

		$returnHTML .=	'<div class="lb_pagination_wrapper">';
		$returnHTML .=	'<div class="pagination_button">	<a href="' . get_home_url() . '">Home</a></div>';

		if ((get_post_type($post_obj->ID) == 'product') && isset($_SERVER['HTTP_REFERER'])) {
			$referer_url =  $_SERVER['HTTP_REFERER'];
			$parse_url = parse_url($referer_url);
			if (isset($parse_url['scheme']) && isset($parse_url['host']) && isset($parse_url['path'])) {
				$rebuild_url = $parse_url['scheme'] . '://' . $parse_url['host'] . $parse_url['path'];

				$back_id = (int) url_to_postid($_SERVER['HTTP_REFERER']);

				if (get_post_type($back_id) == "page") {
					if (get_option('page_on_front') != $back_id) {
						$returnHTML .=	' <div class="pagination_button " data-post-type="page">	<a href="' . $rebuild_url . '">' . get_the_title($back_id) . '</a></div>';
					}
				} else {
					$category_slug = explode('/', $parse_url['path']);
					if (str_contains($_SERVER['HTTP_REFERER'], 'categories')) {
						$category_name = str_replace("-", " ", $category_slug[2]);
						$category_name = ucfirst($category_name);
						$returnHTML .=	' <div class="pagination_button " data-post-type="notpage">		<a href="' . $rebuild_url . '">Category : ' . $category_name . '</a></div>';
					}
				}
			}
		}

		foreach ($pagination_posts_array as $key => $pagination_post) {
			$key = $pagination_post['id'];
			$current_page = (($key ==  $post_obj->ID) || ($key ==  $post_obj->term_id)) ? 'current_page' : '';

			$returnHTML .= (is_tax('categories', $key)) ? ' <div class="pagination_button lb_recent_location" data-tax="' . is_tax('categories', $key) . '">	<a href="#">...</a></div>' : '';
			$returnHTML .=	' <div class="pagination_button ' . $current_page . '" data-post-type="' . get_post_type($key) . '-' . $key . '" data-tax="' . is_tax('categories', $key) . '"> ';
			$returnHTML .=	' <a href="' . $pagination_post['url'] . '">' . $pagination_post['name'] . '</a>';
			$returnHTML .=	' </div>';
		}

		$returnHTML .=	'</div>';
		return $returnHTML;
	}


	public function leafbridge_breadcrumbs_function()
	{
		return self::leafbridge_breadcrumb_render();
	}

	// shortcode function to place a search bar inside any page
	public function leafbridge_search_bar()
	{
		$wp_options = get_option('leafbridge-settings');
		$store_link = $wp_options['leafbridge-settings-page-settings']['leafbridge-config-ui-shop-link'];
		ob_start();
	?>
		<div class="lb_search_products_shortcode">
			<div class="lb_search_products_input_wrapper_shortcode" store_url="<?php echo $store_link; ?>">
				<input type="text" name="" value="" class="products_search_input_shortcode" placeholder="Search Products">
				<button type="button" name="button" class="products_clear_search_button_shortcode" title="Clear product search keywords"><i class="fa-solid fa-rotate-left"></i></button>
				<button type="button" name="button" class="products_search_button_shortcode" title="Search products with matching keywords"><i class="fa-solid fa-magnifying-glass"></i></button>
			</div>
		</div>
	<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}

	// function to show retailer name bar 
	public function leafbridge_retailer_name_bar()
	{
		if (isset($_GET['action'])) {
			if ($_GET['action'] != 'elementor') {
				return $this->leafbridge_retailer_name_bar_output();
			} else {
				return "leafbridge_retailer_name_bar";
			}
		} else {
			return $this->leafbridge_retailer_name_bar_output();
		}
	}

	public function leafbridge_retailer_name_bar_output()
	{
		$leafbridge_settings = get_option('leafbridge-settings');
		$location_page = $leafbridge_settings['leafbridge-settings-page-settings']['leafbridge-config-location-link'];
		ob_start();
	?>
		<div class="lb_retailer_name_bar_wrapper">
			<pre style="display:none;">
				<?php
				print_r($leafbridge_settings);
				?>
			</pre>
			<span class="lb_retailer_text">You're shopping at <span class="retailer_name"></span><span class="name_bar_seperator"> | </span><span style="text-transform:capitalize;" class="name_bar_menutype"></span><span class="name_bar_seperator"> | </span><span style="text-transform:capitalize;" class="name_bar_collection"></span><span class="name_bar_seperator"> | </span><span style="text-transform:capitalize;" class="name_bar_address"></span><span class="name_bar_seperator"> | </span><span style="text-transform:capitalize;" class="name_bar_phone"></span><span class="name_bar_seperator"> | </span><span class="opens_until">Opens until </span><span style="text-transform:capitalize;" class="name_bar_today_closing_time"></span> <span class="name_bar_seperator"> | </span><span class="line-br"><br></span><span style="text-transform:capitalize;" class="name_bar_days">
					<span style="text-transform:capitalize;" class="name_bar_day_monday"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_monday_start"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_monday_end"></span>
					<span class="days_seperator"> : </span>
					<span style="text-transform:capitalize;" class="name_bar_day_tuesday"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_tuesday_start"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_tuesday_end"></span>
					<span class="days_seperator"> : </span>
					<span style="text-transform:capitalize;" class="name_bar_day_wednesday"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_wednesday_start"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_wednesday_end"></span>
					<span class="days_seperator"> : </span>
					<span style="text-transform:capitalize;" class="name_bar_day_thursday"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_thursday_start"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_thursday_end"></span>
					<span class="days_seperator"> : </span>
					<span style="text-transform:capitalize;" class="name_bar_day_friday"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_friday_start"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_friday_end"></span>
					<span class="days_seperator"> : </span>
					<span style="text-transform:capitalize;" class="name_bar_day_saturday"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_saturday_start"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_saturday_end"></span>
					<span class="days_seperator"> : </span>
					<span style="text-transform:capitalize;" class="name_bar_day_sunday"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_sunday_start"></span><span class="day_time_seperator"> - </span><span style="text-transform:capitalize;" class="name_bar_day_sunday_end"></span>

				</span>
			</span>
		</div>
<?php
		$returnHTML = ob_get_contents();
		ob_end_clean();
		return $returnHTML;
	}
	// END OF PUBLIC FRONT END RELATED FUNCTIONS
} // end of class

?>