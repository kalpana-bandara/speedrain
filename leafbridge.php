<?php

/**
 * Plugin Name:       LeafBridge Pro
 * Plugin URI:        https://surge.global/
 * Description:       An Ecommerce plugin, create your own store using Dutchie store.
 * Version:           2.0.148
 * Author:            Surge Global
 * Author URI:        https://surge.global/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       leafbridge 2
 * Domain Path:       /languages
 */

 


define('LEAFBRIDGE_VERSION', '2.0.148');
define('LEAFBRIDGE_PATH', plugin_dir_path(__FILE__));
define('LEAFBRIDGE_ADMIN_PATH', plugin_dir_url(dirname(__FILE__)));
error_reporting(E_ALL & ~E_NOTICE);

define('PROXY_URL', 'https://api.leafbridge-proxy.click');

//TODO - change EDD_prefix
// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
define('EDD_Leafbridge_STORE_URL', 'https://leafbridge.io');

// the download ID for the product in Easy Digital Downloads
define('EDD_Leafbridge_ITEM_ID', 1101);


if (!class_exists('EDD_SL_Plugin_Updater')) {
	// load our custom updater
	include dirname(__FILE__) . '/includes/EDD_Leafbridge_Plugin_Updater.php';
}

/**
 * Initialize the updater. Hooked into `init` to work with the
 * wp_version_check cron job, which allows auto-updates.
 */
function edd_leafbridge_plugin_updater()
{

	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined('DOING_CRON') && DOING_CRON;
	if (!current_user_can('manage_options') && !$doing_cron) {
		return;
	}

	$leafbridge_license_data = get_option('leafbridge-license-data');
	$license_key = '';
	if (is_array($leafbridge_license_data) && array_key_exists('lkey', $leafbridge_license_data)) {
		$license_key = $leafbridge_license_data['lkey'];
	}

	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater(
		EDD_Leafbridge_STORE_URL,
		__FILE__,
		array(
			'version' => LEAFBRIDGE_VERSION,
			// current version number
			'license' => $license_key,
			// license key (used get_option above to retrieve from DB)
			'item_id' => EDD_Leafbridge_ITEM_ID,
			// ID of the product
			'author' => 'Surge',
			// author of this plugin
			'beta' => false,
		)
	);
}
add_action('init', 'edd_leafbridge_plugin_updater');


/**
 * The code that runs during plugin activation.
 */
function leafbridge_activate_plugin()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-leafbridge-activator.php';
	LeafBridge_Activator::activate();
}


/**
 * The code that runs during plugin deactivation.
 */
function leafbridge_deactivate_plugin()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-leafbridge-deactivator.php';
	LeafBridge_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'leafbridge_activate_plugin');
register_deactivation_hook(__FILE__, 'leafbridge_deactivate_plugin');




/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-leafbridge.php';

add_filter('plugin_action_links', 'leafbridge_plugin_settings_link', 10, 2);
function leafbridge_plugin_settings_link($links, $file)
{

	if ($file == 'leafbridge/leafbridge.php') {
		/* Insert the link at the end*/
		$links['settings'] = sprintf('<a href="%s"> %s </a>', admin_url('admin.php?page=leafbridge'), __('Settings', 'leafbridge'));
	}
	return $links;
}


function leafbridge_custom_css()
{
	$leafbridge_settings = get_option('leafbridge-settings');
	$custom_css = $leafbridge_settings['leafbridge-config-ui-custom-css'];
	echo '<style>' . $custom_css . '</style>';
}
add_action('wp_footer', 'leafbridge_custom_css', 5);


/*
 * add category image
 */
add_action('categories_add_form_fields', 'leafbridge_add_term_image');
function leafbridge_add_term_image($taxonomy)
{
?>
	<table class="form-table" role="presentation">
		<tbody>
			<tr class="form-field form-required term-name-wrap">
				<th scope="row"><label for="lf_category_upload_image">Image</label></th>
				<td>
					<input type="text" name="lf_category_upload_image" id="lf_category_upload_image" value=""><br />
					<img class="lf_category_image_prev" src="" style="width:100px;display:none" /><br />
					<input type="button" id="lf_category_upload_image_btn" class="button" value="Upload an Image" onclick="lf_uploadSelectedFile(this)" />
				</td>
			</tr>
		</tbody>
	</table>
<?php
}
add_action('created_categories', 'save_term_image', 10, 5);
function save_term_image($term_id, $tt_id)
{
	if (isset($_POST['lf_category_upload_image']) && '' !== $_POST['lf_category_upload_image']) {
		$group = trim($_POST['lf_category_upload_image']);
		add_term_meta($term_id, 'term_image', $group, true);
	}
}

add_action('categories_edit_form_fields', 'leafbridge_edit_image_upload', 10, 5);
function leafbridge_edit_image_upload($term, $taxonomy)
{
	// get current group
	$lf_category_upload_image = get_term_meta($term->term_id, 'term_image', true);
?>
	<table class="form-table" role="presentation">
		<tbody>
			<tr class="form-field form-required term-name-wrap">
				<th scope="row"><label for="lf_category_upload_image">Image</label></th>
				<td>
					<input type="text" name="lf_category_upload_image" id="lf_category_upload_image" value="<?php echo $lf_category_upload_image ?>"><br />
					<img class="lf_category_image_prev" src="<?php echo $lf_category_upload_image ?>" style="width:100px;" /><br />
					<input type="button" id="lf_category_upload_image_btn" class="button" value="Upload an Image" onclick="lf_uploadSelectedFile(this)" />
				</td>
			</tr>
		</tbody>
	</table>
<?php
}

add_action('edited_categories', 'leafbridge_update_image_upload', 10, 2);
function leafbridge_update_image_upload($term_id, $tt_id)
{
	if (isset($_POST['lf_category_upload_image']) && '' !== $_POST['lf_category_upload_image']) {
		$group = trim($_POST['lf_category_upload_image']);
		update_term_meta($term_id, 'term_image', $group);
	}
}

function leafbridge_image_uploader_enqueue()
{
	global $typenow;
	if (($typenow == 'product')) {
		wp_enqueue_media();

		//wp_register_script( 'meta-image', get_template_directory_uri() . '/js/media-uploader.js', array( 'jquery' ) );
		wp_localize_script(
			'meta-image',
			'meta_image',
			array(
				'title' => 'Upload an Image',
				'button' => 'Use this Image',
			)
		);

		wp_enqueue_script('meta-image');
	}
}
add_action('admin_enqueue_scripts', 'leafbridge_image_uploader_enqueue');


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_leafbridge()
{
	$plugin = new LeafBridge();
	$plugin->run();
}
run_leafbridge();

function run_leafbridge_products()
{
	$plugin = new LeafBridge_Products();
	$plugin->register_products();
	//$plugin->fetch_products();
}
run_leafbridge_products();

function run_leafbridge_retailers()
{
	$plugin = new LeafBridge_Retailers();
	$plugin->register_retailers();
}
run_leafbridge_retailers();





//*******************************************

function licenceIsExpired($timestamp)
{
	$currentTimestamp = time();
	return $timestamp < $currentTimestamp ? 1 : 0;
}

//add_action( 'init','json_refresh_cron');

register_activation_hook(__FILE__, 'json_refresh_cron');
function json_refresh_cron()
{
	if (!wp_next_scheduled('leafbridge_sync_hook')) {
		wp_schedule_event(strtotime('tomorrow 06:00:00'), 'leafbridge_cron_worker_two', 'leafbridge_sync_hook');
	}
}


add_filter('cron_schedules', 'primary_sync_cron_add_schedule');
function primary_sync_cron_add_schedule()
{
	$schedules['leafbridge_cron_worker_two'] = array('interval' => 86400, 'display' => 'Leafbridge Sync Worker 2');
	return $schedules;
}


add_action('leafbridge_sync_hook', 'leafbridge_task_function');
function leafbridge_task_function()
{
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

	$resp = $sync->sync_retailers();
	$updates['retailer_updates'] = $resp;
	//Retailer
	if (is_array($resp) && count($resp) > 0) {
		$resp_cat = $sync->sync_categories();
		$updates['category_updates'] = $resp_cat;
		//categories
		if (is_array($resp_cat) && count($resp_cat) > 0) {
			$resp_product = $sync->sync_products();
			//$updates['products_updates'] = $resp_product;
			// products
			if (is_array($resp_product) && count($resp_product) > 0) {
				$body = 'Automatic store synchronization successful of - ' . $site_name . '
	Log file: ' . plugin_dir_url(__DIR__) . "/leafbridge/includes/autosync.log";

				flush_rewrite_rules();
				if ($sync_status == 1) {
					wp_mail($sync_email, 'Automatic store synchronization successful of - ' . $site_name, $body, $headers);
					error_log('=============== Sync End : ' . date('Y-m-d H:i:s') . ' ========= ' . PHP_EOL, 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
				}
			} else {
				if ($sync_status == 1) {
					$body = 'Automatic products synchronization error - ' . $site_name . '
				Log file: ' . plugin_dir_url(__DIR__) . "/leafbridge/includes/autosync.log";
					wp_mail($sync_email, 'Automatic products synchronization error - ' . $site_name, $body, $headers);
				}
			}
		} else {
			if ($sync_status == 1) {
				$body = 'Automatic categories synchronization error - ' . $site_name . '
		Log file: ' . plugin_dir_url(__DIR__) . "/leafbridge/includes/autosync.log";
				wp_mail($sync_email, 'Automatic categories synchronization error - ' . $site_name, $body, $headers);
			}
		}
	} else {
		if ($sync_status == 1) {
			$body = 'Automatic retailers synchronization error - ' . $site_name . '
	Log file: ' . plugin_dir_url(__DIR__) . "/leafbridge/includes/autosync.log";
			wp_mail($sync_email, 'Automatic retailers synchronization error - ' . $site_name, $body, $headers);
		}
	}
}

register_deactivation_hook(__FILE__, 'leafbridge_sync_deactivation');
function leafbridge_sync_deactivation()
{
	wp_clear_scheduled_hook('leafbridge_sync_hook');
}



/* ****************** PRO ************ */
function leafbridge_proxy_deactivation()
{
	wp_clear_scheduled_hook('lb_proxy_li_my_cron_action');
}


//------------------ for proxy ------------------------------------------------------------

add_filter('cron_schedules', 'lb_proxy_li_add__cron_schedule');
function lb_proxy_li_add__cron_schedule($schedules)
{
	$schedules['daily'] = array(
		'interval' => 86400,
		'display' => __('License Checking from Proxy Server'),
	);
	return $schedules;
}



add_action('lb_proxy_li_my_cron_action', 'lb_proxy_function_to_run');
function lb_proxy_function_to_run()
{

	// call to proxy
	$leafbridge_license_data = get_option('leafbridge-license-data');
	$license_key = $leafbridge_license_data['lkey'];
	$actndb = $leafbridge_license_data['actn'];

	$server_url = "https://api.leafbridge-proxy.click";

	$response = wp_remote_post(
		$server_url . '/license-activate',
		array(
			'method' 		=> 'POST',
			'timeout' 		=> 45,
			'redirection' 	=> 5,
			'httpversion' 	=> '1.0',
			'blocking' 		=> true,
			'headers' 		=> array(),
			'body' => array(
				'licenseKey' => $license_key,
				'itemId' => '1101',
				'retailerIds' => array(1, 2, 3),
				'url' => get_site_url()
			),
			'cookies' => array()
		)
	);

	$body = wp_remote_retrieve_body($response);
	$responceData = (!is_wp_error($response)) ? json_decode($body, true) : null;
	if (isset($responceData) && isset($responceData['data'])) {
		$licence_status = $responceData['data']['eddResponse']['success'];
		$licence_validity = $responceData['data']['eddResponse']['license'];
		$xpd = isset($responceData['data']['eddResponse']['expires']) ? $responceData['data']['eddResponse']['expires'] : date("Y-m-d H:i:s");
		$actn = isset($responceData['data']['accessToken']) ? $responceData['data']['accessToken'] : $actndb;

		$license_data = array(
			'lsat' => $licence_status,
			'lvld' => $licence_validity,
			'lkey' => $license_key,
			'ltier' => '1101',
			'actn' => $actn,
			'xpd' => strtotime($xpd)
		);
		update_option('leafbridge-license-data', $license_data);

		// check for expired		
		$isExpired = licenceIsExpired(strtotime($xpd));

		if ($isExpired == 1) {
			if (!wp_next_scheduled('lb_proxy_li_my_cron_action')) {
				wp_schedule_event(time(), 'daily', 'lb_proxy_li_my_cron_action');
			}
		} else if ($licence_validity == 'invalid_item_id' || $licence_validity == 'invalid' &&  (!wp_next_scheduled('lb_proxy_li_my_cron_action'))) {
			wp_schedule_event(time(), 'daily', 'lb_proxy_li_my_cron_action');
		} else {
			// deregister cron
			wp_clear_scheduled_hook('leafbridge_proxy_deactivation');
			wp_clear_scheduled_hook('lb_proxy_li_my_cron_action');
		}
	}
}


//--------------- for local -----------------------------------------------------------
add_filter('cron_schedules', 'lb_local_cron_schedule');
function lb_local_cron_schedule($schedules)
{
	$schedules['daily'] = array(
		'interval' => 86400,
		'display' => __('Local License Check'),
	);
	return $schedules;
}

if (!wp_next_scheduled('lb_local_cron_action')) {
	wp_schedule_event(time(), 'daily', 'lb_local_cron_action');
}



add_action('lb_local_cron_action', 'lb_local_function_to_run');
function lb_local_function_to_run()
{
	$leafbridge_license = get_option('leafbridge-license-data');
	$lsat = $leafbridge_license['lsat'];
	$lvld = $leafbridge_license['lvld'];
	$lkey = $leafbridge_license['lkey'];
	$ltier = $leafbridge_license['ltier'];
	$xpd = $leafbridge_license['xpd'];
	$actn = $leafbridge_license['actn'];


	// check for expired		
	$isExpired = licenceIsExpired($xpd);


	if ($isExpired == 1) {
		$license_data = array(
			'lsat' => '0',
			'lvld' => 'invalid',
			'lkey' => $lkey,
			'ltier' => $ltier,
			'actn' => $actn,
			'xpd' => $xpd
		);
		update_option('leafbridge-license-data', $license_data);

		if (!wp_next_scheduled('lb_proxy_li_my_cron_action')) {
			wp_schedule_event(time(), 'daily', 'lb_proxy_li_my_cron_action');
		}
	} else if ($lvld == 'invalid_item_id' || $lvld == 'invalid' &&  (!wp_next_scheduled('lb_proxy_li_my_cron_action'))) {
		wp_schedule_event(time(), 'daily', 'lb_proxy_li_my_cron_action');
	} else {
		$leafbridge_license_data = get_option('leafbridge-license-data');
		$license_key = $leafbridge_license_data['lkey'];
		$actndb = $leafbridge_license_data['actn'];

		$server_url = "https://api.leafbridge-proxy.click";

		$response = wp_remote_post(
			$server_url . '/license-activate',
			array(
				'method' 		=> 'POST',
				'timeout' 		=> 45,
				'redirection' 	=> 5,
				'httpversion' 	=> '1.0',
				'blocking' 		=> true,
				'headers' 		=> array(),
				'body' => array(
					'licenseKey' => $license_key,
					'itemId' => '1101',
					'retailerIds' => array(1, 2, 3),
					'url' => get_site_url()
				),
				'cookies' => array()
			)
		);

		$body = wp_remote_retrieve_body($response);
		$responceData = (!is_wp_error($response)) ? json_decode($body, true) : null;
		if (isset($responceData) && isset($responceData['data'])) {
			$licence_status = $responceData['data']['eddResponse']['success'];
			$licence_validity = $responceData['data']['eddResponse']['license'];
			$xpd = isset($responceData['data']['eddResponse']['expires']) ? $responceData['data']['eddResponse']['expires'] : date("Y-m-d H:i:s");
			$actn = isset($responceData['data']['accessToken']) ? $responceData['data']['accessToken'] : $actndb;

			$license_data = array(
				'lsat' => $licence_status,
				'lvld' => $licence_validity,
				'lkey' => $license_key,
				'ltier' => '1101',
				'actn' => $actn,
				'xpd' => strtotime($xpd)
			);
			update_option('leafbridge-license-data', $license_data);

			// check for expired		
			$isExpired = licenceIsExpired($xpd);

			if ($isExpired == 1) {
				if (!wp_next_scheduled('lb_proxy_li_my_cron_action')) {
					wp_schedule_event(time(), 'daily', 'lb_proxy_li_my_cron_action');
				}
			} else if ($licence_validity == 'invalid_item_id' || $licence_validity == 'invalid' &&  (!wp_next_scheduled('lb_proxy_li_my_cron_action'))) {
				wp_schedule_event(time(), 'daily', 'lb_proxy_li_my_cron_action');
			} else {
				// deregister cron
				wp_clear_scheduled_hook('leafbridge_proxy_deactivation');
				wp_clear_scheduled_hook('lb_proxy_li_my_cron_action');
			}
		}
		//register_deactivation_hook(__FILE__, 'leafbridge_proxy_deactivation');
		wp_clear_scheduled_hook('lb_proxy_li_my_cron_action');
		wp_clear_scheduled_hook('leafbridge_proxy_deactivation');
	}
}
