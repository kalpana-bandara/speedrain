<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    LeafBridge
 * @subpackage LeafBridge/includes
 * @author     Surge <websites@surge.global>
 */


class LeafBridge_Admin {



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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version) {

		$this->plugin_name = $plugin_name;
		$this->version = time(); //$version;

		add_action('wp_ajax_leafbridge_admin_setup_store', array($this, 'leafbridge_admin_setup_store'));
		add_action('wp_ajax_nopriv_leafbridge_admin_setup_store', array($this, 'leafbridge_admin_setup_store'));

		add_action('wp_ajax_leafbridge_admin_setup_retailers', array($this, 'leafbridge_admin_setup_retailers'));
		add_action('wp_ajax_nopriv_leafbridge_admin_setup_retailers', array($this, 'leafbridge_admin_setup_retailers'));

		add_action('wp_ajax_leafbridge_admin_setup_categories', array($this, 'leafbridge_admin_setup_categories'));
		add_action('wp_ajax_nopriv_leafbridge_admin_setup_categories', array($this, 'leafbridge_admin_setup_categories'));

		add_action('wp_ajax_leafbridge_admin_optimizing_store', array($this, 'leafbridge_admin_optimizing_store'));
		add_action('wp_ajax_nopriv_leafbridge_admin_optimizing_store', array($this, 'leafbridge_admin_optimizing_store'));

		add_action('wp_ajax_leafbridge_admin_setup_pages', array($this, 'leafbridge_admin_setup_pages'));
		add_action('wp_ajax_nopriv_leafbridge_admin_setup_pages', array($this, 'leafbridge_admin_setup_pages'));

		add_action('wp_ajax_leafbridge_admin_product_stat', array($this, 'leafbridge_admin_product_stat'));
		add_action('wp_ajax_nopriv_leafbridge_admin_product_stat', array($this, 'leafbridge_admin_product_stat'));

		add_action('wp_ajax_leafbridge_admin_custom_name', array($this, 'leafbridge_admin_custom_name'));
		add_action('wp_ajax_nopriv_leafbridge_admin_custom_name', array($this, 'leafbridge_admin_custom_name'));

		add_action('wp_ajax_leafbridge_remove_retailer', array($this, 'leafbridge_remove_retailer'));
		add_action('wp_ajax_nopriv_leafbridge_remove_retailer', array($this, 'leafbridge_remove_retailer'));

		add_action('wp_ajax_leafbridge_retailer_quick_sync', array($this, 'leafbridge_retailer_quick_sync'));
		add_action('wp_ajax_nopriv_leafbridge_retailer_quick_sync', array($this, 'leafbridge_retailer_quick_sync'));
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is used to load plugin stylesheet for admin.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in LeafBridge_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LeafBridge_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/leafbridge-admin.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . '-select2css', plugin_dir_url(__FILE__) . 'lib/select2/select2.min.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is used to load plugin javascript for admin.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in LeafBridge_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The LeafBridge_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this 
		 * class.
		 */

		wp_enqueue_script($this->plugin_name . '-acejs', plugin_dir_url(__FILE__) . 'lib/ace/ace.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . '-selectjs', plugin_dir_url(__FILE__) . 'lib/select2/select2.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/leafbridge-admin.js', array('jquery'), $this->version, false);
		wp_localize_script($this->plugin_name . '_ajax', 'leafbridge_admin_ajax_obj', array('ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('leafbridge-admin-ajax-nonce')));
	}


	/*
	* Ajax function - admin_setup_store
	*/
	public function leafbridge_admin_setup_store() {
		$json = array();
		$check_nonce = check_ajax_referer('leafbridge-admin-ajax-nonce', 'nonce_ajax');
		if ($check_nonce == 1) {
			$form_data = $_REQUEST['form_data'];
			$retailer_id = trim($form_data['retailer_id']);
			$retailer_name = trim($form_data['retailer_name']);

			$pro = new LeafBridge_Products();

			$res = $pro->add_products_new($retailer_id);
			$product_synced = array_unique($res);

			$total_updated = ($product_synced ? count($product_synced) : 0);


			if (is_array($product_synced) && isset($total_updated)) {
				$json['product_status'] = $retailer_name . ' store\'s ' . $total_updated . ' products have been saved successfully. <i class="dashicons dashicons-saved"></i>';
			} else {
				$json['product_status'] = 'Product syncing error. Please contact administrator.';
			}/**/
			//sleep(5);
			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}


	/*
	* Ajax function - admin save categories and retailers
	*/
	public function leafbridge_admin_setup_retailers() {
		$json = array();
		$check_nonce = check_ajax_referer('leafbridge-admin-ajax-nonce', 'nonce_ajax');
		if ($check_nonce == 1) {
			$form_data = $_REQUEST['form_data'];

			$retailerids = $form_data['retailers'];
			$retailers_core = new LeafBridge_Retailers();
			$retailers = $retailers_core->save_retailers($retailerids);

			// ***************************
			// $lb_product_filter_options = get_option('lb_product_filter_options');

			// $new_array = array();

			// $product_filter_options_final = array(
			// 	'categories' => $new_array,
			// 	'brands' => $new_array,
			// 	'potencyCbd' => $new_array,
			// 	'potencyThc' => $new_array,
			// 	'effects' => $new_array,
			// 	'staffPick' => $new_array,
			// 	'strainType' => $new_array,
			// 	'POS_Categories' => $new_array
			// 	//'weight' => $new_array 							
			// );

			// if (is_array($lb_product_filter_options) && count($lb_product_filter_options) > 0) {
			// 	update_option('lb_product_filter_options', $product_filter_options_final);
			// } else {
			// 	$deprecated = null;
			// 	$autoload = 'true';
			// 	add_option('lb_product_filter_options', $product_filter_options_final, $deprecated, $autoload);
			// }
			// ***************************


			// Save Store Default settings
			//$default_store = $form_data['default_store'];
			//$default_menu_type = $form_data['default_menu_type'];
			//$default_order_type = $form_data['default_order_type'];

			/*$leafbridge_settings = get_option('leafbridge-settings');
			if(is_array($leafbridge_settings) && count($leafbridge_settings) > 0) {
				
				$apikey 			= $leafbridge_settings['leafbridge-settings-api-key'];
				$secretkey 			= $leafbridge_settings['leafbridge-settings-api-secret-key'];
				$age_modal 			= $leafbridge_settings['leafbridge-settings-age-modal'];				
				$page_settings 		= $leafbridge_settings['leafbridge-settings-page-settings'];
				$categories 		= $leafbridge_settings['leafbridge-product-categories'];  
				$custom_css 		= $leafbridge_settings['leafbridge-config-ui-custom-css'];				
				$sync_settings 		= $leafbridge_settings['leafbridge-sync-settings']; 
				
				$leafbridge_settings = array(
					'leafbridge-settings-api-key' 			=> $apikey,
					'leafbridge-settings-api-secret-key' 	=> $secretkey,
					'leafbridge-settings-age-modal' 		=> $age_modal,
					'leafbridge-settings-page-settings' 	=> $page_settings,
					'leafbridge-product-categories' 		=> $categories,
					'leafbridge-config-ui-custom-css' 		=> $custom_css,
					'leafbridge-sync-settings' 				=> $sync_settings,
					'leafbridge_default_settings' 			=> array(
						'default_store' 	 => $default_store,
						'default_menu_type'  => $default_menu_type, 
						'default_order_type' => $default_order_type,
					)
				);
				update_option( 'leafbridge-settings', $leafbridge_settings );
			} else {
				$leafbridge_settings = array(
					'leafbridge-settings-api-key' 			=> $apikey,
					'leafbridge-settings-api-secret-key' 	=> $secretkey,
					'leafbridge-settings-age-modal' 		=> $age_modal,
					'leafbridge-settings-page-settings' 	=> $page_settings,
					'leafbridge-product-categories' 		=> $categories,
					'leafbridge-config-ui-custom-css' 		=> $custom_css,
					'leafbridge-sync-settings' 				=> $sync_settings,
					'leafbridge_default_settings' 			=> array(
						'default_store' 	 => $default_store,
						'default_menu_type'  => $default_menu_type, 
						'default_order_type' => $default_order_type,
					)
				);  
				$deprecated = null; 
				$autoload = 'true';
				add_option( 'leafbridge-settings', $leafbridge_settings, $deprecated, $autoload );
			}*/
			// End Saving details Settings


			if (isset($retailers)) {
				$json['retailers_status'] = 'Retailers have been saved successfully. <i class="dashicons dashicons-saved"></i>';
			} else {
				$json['retailers_status'] = 'Retailers syncing error. Please contact administrator.';
			}
			//sleep(5);
			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}


	/*
	* Ajax function - admin save categories and retailers
	*/
	public function leafbridge_admin_setup_categories() {
		$json = array();
		$check_nonce = check_ajax_referer('leafbridge-admin-ajax-nonce', 'nonce_ajax');
		if ($check_nonce == 1) {
			$form_data = $_REQUEST['form_data'];

			$categories = new LeafBridge_Products();

			// add categories 
			$categories =  $categories->add_product_categories();

			if (isset($categories)) {
				$json['categories_status'] = 'Categories have been saved successfully. <i class="dashicons dashicons-saved"></i>';
			} else {
				$json['categories_status'] = 'Categories syncing error. Please contact administrator.';
			}
			//var_dump($categories);
			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}


	/*
	* Ajax function - admin remove usless products
	*/
	public function leafbridge_admin_optimizing_store() {
		$json = array();
		$check_nonce = check_ajax_referer('leafbridge-admin-ajax-nonce', 'nonce_ajax');
		if ($check_nonce == 1) {
			$form_data = $_REQUEST['form_data'];

			$retailers = new LeafBridge_Retailers();
			$store_status =  $retailers->clear_junk_products();

			if (isset($store_status)) {
				$json['store_status'] = 'Store synchronization  successfully completed. <i class="dashicons dashicons-saved"></i>';
			} else {
				$json['store_status'] = 'Store optimization syncing error. Please contact administrator.';
			}
			//var_dump($categories);
			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}



	/*
	* Ajax function - admin remove usless products
	*/
	public function leafbridge_admin_setup_pages() {
		$json = array();
		$check_nonce = check_ajax_referer('leafbridge-admin-ajax-nonce', 'nonce_ajax');
		if ($check_nonce == 1) {
			$form_data = $_REQUEST['form_data'];

			$pages = new LeafBridge_Products();
			$pages_status =  $pages->setup_pages();

			if (isset($pages_status)) {
				$json['pages_status'] = 'Pages have been created successfully. <i class="dashicons dashicons-saved"></i>';
			} else {
				$json['pages_status'] = 'Store pages creation syncing error. Please contact administrator.';
			}
			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}


	/*
	* Ajax function - get updating product count
	*/
	public function leafbridge_admin_product_stat() {
		$json = array();
		$check_nonce = check_ajax_referer('leafbridge-admin-ajax-nonce', 'nonce_ajax');
		if ($check_nonce == 1) {
			$form_data = $_REQUEST['form_data'];
			$retailer_id = $form_data['retailer_id'];


			$pages = new LeafBridge_Products();
			$publish_stat =  $pages->get_product_count($retailer_id, $status = "publish");
			//$draft_stat =  $pages->get_product_count($retailer_id, $status="draft"); 

			if (isset($publish_stat)) {
				$json['product_stat'] = $publish_stat;
			} else {
				$json['product_stat'] = 'Product Count error. Please contact administrator.';
			}
			wp_send_json_success($json);
		} else {
			wp_send_json_error($json);
		}
		die();
	}


	public function leafbridge_admin_custom_name() {
		$check_nonce = check_ajax_referer('leafbridge-admin-ajax-nonce', 'nonce_ajax');

		if ($check_nonce == 1) {
			$formData = $_REQUEST['my_data'];
			$myData = $formData['customNames'];

			foreach ($myData as $key => $value) {
				$args = array(
					'post_type'              => array('retailer'),
					'post_status'            => array('publish'),
					'meta_query'             => array(
						array(
							'key'       => '_lb_retailer_single_id',
							'value'     => $key,
						),
					),
				);

				$query = new WP_Query($args);

				if ($query->have_posts()) {
					while ($query->have_posts()) {
						$query->the_post();
						update_post_meta(get_the_ID(), 'lb_retailer_custom_name', $value);
					}
				} else {
				}

				// Restore original Post Data
				wp_reset_postdata();
			}
		}
	}
	public function leafbridge_remove_retailer() {
		$check_nonce = check_ajax_referer('leafbridge-admin-ajax-nonce', 'nonce_ajax');

		if ($check_nonce == 1) {
			$formData = $_REQUEST['form_data'];
			$retailerId = $formData['retailerId'];
			$json = array();

			//delete retailer
			$args = array(
				'post_type' => 'retailer',
				'meta_key' => '_lb_retailer_single_id',
				'meta_value' => $retailerId,
				'post_status' => 'publish'
			);

			$retailerQuery = new WP_Query($args);

			if ($retailerQuery->have_posts()) {
				while ($retailerQuery->have_posts()) {
					$retailerQuery->the_post();
					wp_delete_post(get_the_ID(), true);
				}
				wp_reset_postdata();
				$json[] = "Retailer removed successfully";
			} else {
				$json[] = "Retailer not exist";
			}

			//delete products
			$productArgs = array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'meta_key' => '_leafbridge_product_single_meta_retailer_id',
				'meta_value' => $retailerId,
				'posts_per_page' => -1
			);

			$productQuery = new WP_Query($productArgs);

			if ($productQuery->have_posts()) {
				while ($productQuery->have_posts()) {
					$productQuery->the_post();

					wp_delete_post(get_the_ID(), true);
				}
				wp_reset_postdata();
				$json[] = "Retailer products successfully deleted";
			} else {
				$json[] = "This retailer does not have any products";
			}
			wp_send_json_success($json);
		}
	}
	public function leafbridge_retailer_quick_sync() {
		$check_nonce = check_ajax_referer('leafbridge-admin-ajax-nonce', 'nonce_ajax');

		if ($check_nonce == 1) {

			$formData = $_REQUEST['form_data'];
			$retailerId = $formData['retailerId'];

			$updates = array();

			$leafbridge_settings = get_option('leafbridge-settings');
			$sync_settings = $leafbridge_settings['leafbridge-sync-settings'];
			$sync_email = $sync_settings['lb-sync-log-email'];
			$sync_status = $sync_settings['lb-sync-log-status'];

			$admin_email = trim(get_option('admin_email'));
			$site_name = get_bloginfo('name');

			$headers[] = 'From: ' . $site_name . ' <' . $admin_email . '>';
			$headers[] = 'Cc: websites@surge.global';

			$logfilesize = filesize(WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
			if ($sync_status == 1) {
				if ($logfilesize > 150000) {
					unlink(WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
					error_log('=============== Sync Started : ' . date('Y-m-d H:i:s') . ' ========= ' . PHP_EOL, 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
				} else {
					error_log('=============== Sync Started : ' . date('Y-m-d H:i:s') . ' ========= ' . PHP_EOL, 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
				}
			}


			$sync = new LeafBridge_Sync();

			$resp = $sync->sync_retailers($retailerId);
			$updates['retailer_updates'] = $resp;

			//Retailer
			if (is_array($resp) && count($resp) > 0) {
				$resp_cat = $sync->sync_categories();
				$updates['category_updates'] = $resp_cat;
				//categories
				if (is_array($resp_cat) && count($resp_cat) > 0) {
					$resp_product = $sync->sync_products($retailerId);
					if (is_array($resp_product) && count($resp_product) > 0) {
						$body = 'Automatic store synchronization successful of - ' . $site_name . '
	Log file: ' . plugin_dir_url(__DIR__) . "/leafbridge/includes/autosync.log";

						flush_rewrite_rules();
						if ($sync_status == 1) {
							wp_mail($sync_email, 'Automatic store synchronization successful of - ' . $site_name, $body, $headers);
							error_log('=============== Sync End : ' . date('Y-m-d H:i:s') . ' ========= ' . PHP_EOL, 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
						}

						wp_send_json_success("synced successfully");
					} else {
						//$headers[] = 'Bcc: sumith@surge.global';
						if ($sync_status == 1) {
							$body = 'Automatic products synchronization error - ' . $site_name . '
				Log file: ' . plugin_dir_url(__DIR__) . "/leafbridge/includes/autosync.log";
							wp_mail($sync_email, 'Automatic products synchronization error - ' . $site_name, $body, $headers);
						}
					}
				} else {
					//$headers[] = 'Bcc: sumith@surge.global';
					if ($sync_status == 1) {
						$body = 'Automatic categories synchronization error - ' . $site_name . '
		Log file: ' . plugin_dir_url(__DIR__) . "/leafbridge/includes/autosync.log";
						wp_mail($sync_email, 'Automatic categories synchronization error - ' . $site_name, $body, $headers);
					}
				}
			} else {
				//$headers[] = 'Bcc: sumith@surge.global';
				if ($sync_status == 1) {
					$body = 'Automatic retailers synchronization error - ' . $site_name . '
	Log file: ' . plugin_dir_url(__DIR__) . "/leafbridge/includes/autosync.log";
					wp_mail($sync_email, 'Automatic retailers synchronization error - ' . $site_name, $body, $headers);
				}
			}
		}
	}
}
