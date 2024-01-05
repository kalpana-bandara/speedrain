<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://surge.global/
 * @since      1.0.0
 *
 * @package    LeafBridge
 * @subpackage LeafBridge/admin/partials
 */
/**
 * Including GraphQL php lib
 */

require LEAFBRIDGE_PATH . '/vendor/autoload.php';

use GraphQL\Client;
use GraphQL\Exception\QueryError;
use GraphQL\Mutation;
use GraphQL\RawObject;
use GraphQL\Query;
use GraphQL\QueryBuilder\QueryBuilder;
use GuzzleHttp\Promise;
use GuzzleHttp\Middleware;
use GraphQL\Variable;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

/*
$ret = new LeafBridge_Retailers();
$ttt = $ret->get_retailer_details($retailer_id = '6977440f-e913-4e14-890d-1a31a12ebd55', $type = NULL);

echo '<pre>';
print_r($ttt);
echo '</pre>';
*/
/*
$ddd = new LeafBridge_DB();
$teee = $ddd->sh_get_next_cron_time();
*/


$plugin_WPDutchie = new LeafBridge();

$leafbridge_settings = array();
$store_key 				= '-';
$error_message 			= '';
$store_api_status 		= 0;
$api_response 			= '';
$dutchie_store_status 	= 0;
$dutchie_store_key 		= '';
$store_secret_key 		=  '';
$product_categories 	= array('ACCESSORIES', 'APPAREL', 'CBD', 'CLONES', 'CONCENTRATES', 'EDIBLES', 'FLOWER', 'NOT_APPLICABLE', 'ORALS', 'PRE_ROLLS', 'SEEDS', 'TINCTURES', 'TOPICALS', 'VAPORIZERS');
$leafbridge_settings 	= array(
	'leafbridge-settings-api-key' 				    => '',
	'leafbridge-settings-api-secret-key' 		    => '',
	'leafbridge-settings-age-modal' => array(
		'leafbridge-settings-wizard-type' 				=> 'modal',
		'leafbridge-settings-wizard-type-link-element'  => '',
		'leafbridge-settings-wizard-type-modal-element' => '',
		'leafbridge-floating-cart-position' 		=> '',
		'leafbridge-settings-age-modal-is-enable'   => 1,
		'leafbridge-config-ui-heading' 				=> 'Are you at least 21 years old?',
		'leafbridge-config-ui-description' 			=> 'People below 21 years are not allowed.',
		'leafbridge-config-ui-error-message' 		=> "We're sorry, you must be at least 21 yrs old or a valid medical patient to use this site!"
	),
	'leafbridge-settings-page-settings' => array(
		'leafbridge-config-ui-shop-link' 		 => get_site_url() . '/shop/',
		'leafbridge-config-ui-order-status-link' => get_site_url() . '/order-status/',
		'leafbridge-config-ui-terms-link' 		 => get_site_url() . '/terms-and-conditions/',
	),
	'leafbridge-product-categories' 				=> $product_categories,
	'leafbridge-config-ui-custom-css' => '',
	'leafbridge-sync-settings' => array(
		'lb-sync-log-status'   => '',
		'lb-sync-log-email'    => ''
	),
	'leafbridge_default_settings' => array(
		'default_store'      => '',
		'default_menu_type'  => '',
		'default_order_type' => '',
		'disable_wp_nonce' => 'disable'
	)
);
if (!get_option('leafbridge-settings')) {
	add_option('leafbridge-settings', $leafbridge_settings, null, 'true');
}

$leafbridge_filters_xd = array(
	'weights' => array()
);

add_option('leafbridge_filters_xdx', $leafbridge_filters_xd, null, 'true');



/* ************ PRO *********** */
$licence_status   = 0;
$licence_validity = 0;
$license_data = array(
	'lsat' 	=> 0,
	'lvld' 	=> 0,
	'lkey' 	=> 0,
	'ltier' => 0,
	'actn'  => 0,
	'xpd'	=>  ''
);

add_option('leafbridge-license-data', $license_data, null, 'true');


if (isset($_POST['leafbridge-license-status']) && (trim($_POST['leafbridge-license-key']) != '') && strlen(trim($_POST['leafbridge-license-key'])) >= 30) {
	$license_key     	  = trim($_POST['leafbridge-license-key']);
	$subscription_item_id = '1101';

	$server_url = "https://api.leafbridge-proxy.click";

	$response = wp_remote_post(
		$server_url . '/license-activate',
		array(
			'method' 		=> 'POST',
			'timeout' 		=> 45,
			'redirection' 	=> 5,
			'httpversion' 	=> '1.0',
			'blocking' 		=> true,
			'headers'  => array('Nonce' => '"' . md5(uniqid(mt_rand() . time(), true)) . '"'),
			'body' => array(
				'licenseKey' => $license_key,
				'itemId' 	 => $subscription_item_id,
				'retailerIds' => array(1, 2, 3),
				'url' => get_site_url()
			),
			'cookies' => array()
		)
	);


	$body = wp_remote_retrieve_body($response);
	$responceData = (!is_wp_error($response)) ? json_decode($body, true) : null;


	$actn 				= '';
	$licence_status 	= '';
	$licence_validity 	= '';

	if (isset($responceData) && isset($responceData['data'])) {
		$licence_status   	= $responceData['data']['eddResponse']['success'];
		$licence_validity 	= $responceData['data']['eddResponse']['license'];
		$xpd  				= isset($responceData['data']['eddResponse']['expires']) ? $responceData['data']['eddResponse']['expires'] : date("Y-m-d H:i:s");
		$actn 				= isset($responceData['data']['accessToken']) ? $responceData['data']['accessToken'] : '';

		$license_data = array(
			'lsat'  => $licence_status,
			'lvld'  => $licence_validity,
			'lkey'  => $license_key,
			'ltier' => $subscription_item_id,
			'actn'	=> $actn,
			'xpd'	=> strtotime($xpd)
		);
		update_option('leafbridge-license-data', $license_data);
	} else {
		$lf = new LeafBridge();
		echo $lf->leafbridge_admin_notice__error(ucfirst($responceData['message']));
	}
}

// **********************************************************************

if (isset($_POST['leafbridge-settings-api-key']) && ($_POST['leafbridge-settings-api-key'] != '')) {

	$apikey     = trim($_POST['leafbridge-settings-api-key'], "=");
	$secret_key = trim($_POST['leafbridge-settings-api-secret-key'], "=");
	$deprecated = null;
	$autoload = 'no';
	$leafbridge_settings = get_option('leafbridge-settings');

	//print_r($_POST);

	if (is_array($leafbridge_settings) && count($leafbridge_settings) > 0) {
		// The option already exists, so update it.
		$apikey     = trim($_POST['leafbridge-settings-api-key']);
		$secret_key = trim($_POST['leafbridge-settings-api-secret-key']);

		$leafbridge_age_modal 			= $leafbridge_settings['leafbridge-settings-age-modal'];
		$leafbridge_page_settings 		= $leafbridge_settings['leafbridge-settings-page-settings'];
		$leafbridge_config_custom_css 	= $leafbridge_settings['leafbridge-config-ui-custom-css'];
		$leafbridge_sync_settings 	    = $leafbridge_settings['leafbridge-sync-settings'];
		$leafbridge_default_settings    = $leafbridge_settings['leafbridge_default_settings'];

		$leafbridge_settings_new = array(
			'leafbridge-settings-api-key'          => $apikey,
			'leafbridge-settings-api-secret-key'   => $secret_key,
			'leafbridge-settings-age-modal'        => $leafbridge_age_modal,
			'leafbridge-settings-page-settings'    => $leafbridge_page_settings,
			'leafbridge-product-categories'        => $product_categories,
			'leafbridge-config-ui-custom-css'      => $leafbridge_config_custom_css,
			'leafbridge-sync-settings'             => $leafbridge_sync_settings,
			'leafbridge_default_settings'          => $leafbridge_default_settings
		);
		update_option('leafbridge-settings', $leafbridge_settings_new, $deprecated, $autoload);
	} else {
		$leafbridge_settings = array(
			'leafbridge-settings-api-key' 				=> '',
			'leafbridge-settings-api-secret-key' 		=> '',
			'leafbridge-settings-age-modal' => array(
				'leafbridge-settings-wizard-type' 				=> 'modal',
				'leafbridge-settings-wizard-type-link-element' 	=> '',
				'leafbridge-settings-wizard-type-modal-element' => '',
				'leafbridge-floating-cart-position' => '',
				'leafbridge-settings-age-modal-is-enable' => 1,
				'leafbridge-config-ui-heading' 			  => 'Are you at least 21 years old?',
				'leafbridge-config-ui-description' 		  => 'People below 21 years are not allowed.',
				'leafbridge-config-ui-error-message'      => "We're sorry, you must be at least 21 yrs old or a valid medical patient to use this site!"
			),
			'leafbridge-settings-page-settings' => array(
				'leafbridge-config-ui-shop-link' 		 => get_site_url() . '/shop/',
				'leafbridge-config-ui-order-status-link' => get_site_url() . '/order-status/',
				'leafbridge-config-ui-terms-link' 		 => get_site_url() . '/terms-and-conditions/',
			),
			'leafbridge-product-categories'   => $product_categories,
			'leafbridge-config-ui-custom-css' => $leafbridge_config_custom_css,
			'leafbridge-sync-settings' 	      => $leafbridge_sync_settings,
			'leafbridge_default_settings' => array(
				'default_store'      => '',
				'default_menu_type'  => '',
				'default_order_type' => '',
				'disable_wp_nonce' => 'disable'
			)
		);

		add_option('leafbridge-settings', $leafbridge_settings, $deprecated, $autoload);
	}

	$lf = new LeafBridge();
	echo $lf->leafbridge_admin_notice__success('Successfully saved your API keys.');
}


/* ********************* PRO ************* */
$leafbridge_settings = get_option('leafbridge-settings');
$leafbridge_license  = get_option('leafbridge-license-data');


if (is_array($leafbridge_settings) && count($leafbridge_settings) > 0 && is_array($leafbridge_license)) {
	$store_key  = $leafbridge_settings['leafbridge-settings-api-key'];
	$store_actn = $leafbridge_license['actn'];


	if (strlen($store_actn) > 25) {

		$client = new Client(
			'https://api.leafbridge-proxy.click/graphql',
			[
				'Authorization' => 'Bearer ' . $store_key,
				'Proxy-Authorization-lb' => $store_actn
			]
		);


		// Create the GraphQL mutation
		$gql = (new Mutation('ping'))
			->setSelectionSet(
				[
					'id',
					'time',
				]
			);


		// Run query to get results
		try {
			$api_response = $client->runQuery($gql);
			if (isset($api_response) && $api_response != '') {
				$api_response->reformatResults(true);
				$ping_id = $api_response->getData()['ping']['id'];
				$ping_time = $api_response->getData()['ping']['time'];

				if (isset($ping_id) && isset($ping_time)) {
					$store_api_status = 1;
				}
			}
		} catch (QueryError $exception) {
			$error_message = $exception->getErrorDetails()['message'];
			$lf = new LeafBridge();
			echo $lf->leafbridge_admin_notice__error(ucfirst($error_message));
		} catch (ClientException $e) {
			if ($e->getResponse()->getStatusCode() == 401) {
				$store_api_status = 0;
			}
		} catch (Exception $e) {
			$store_api_status = 0;
		}
	}
}



//============================================
/* get retailer id */

if ($store_api_status == 1) {
	$retailers = new LeafBridge_Retailers();
	$results_retailers = $retailers->get_retailers_details('basic');

	if (isset($results_retailers)) {
		$dutchie_store_status = 1;
		$results_retailers->reformatResults(true);
		$dutchie_store_retailers = $results_retailers->getData()['retailers'];
	}
}

//===================== Store Default settings =================================================================================
if (isset($_POST['setup-btn-default-opt'])  && isset($_POST['leafbridge-settings-default-store'])) {
	$default_store			= trim(($_POST['leafbridge-settings-default-store']));
	$default_menu_type 		= trim(($_POST['leafbridge-settings-default-menu-type']));
	$default_order_type 	= trim(($_POST['leafbridge-settings-default-order-type']));
	$disable_nonce = trim(($_POST['leafbridge-settings-disable-nonce']));


	$leafbridge_settings 			= get_option('leafbridge-settings');

	//------------
	$leafbridge_settings_api_key 	= $leafbridge_settings['leafbridge-settings-api-key'];
	$leafbridge_retailer_secret_key = $leafbridge_settings['leafbridge-settings-api-secret-key'];

	//------------
	$leafbridge_age_modal = $leafbridge_settings['leafbridge-settings-age-modal'];

	//------------
	$leafbridge_product_categories  = $leafbridge_settings['leafbridge-product-categories'];

	//------------
	$leafbridge_config_custom_css 	= $leafbridge_settings['leafbridge-config-ui-custom-css'];

	//------------
	$leafbridge_default_settings 	= array(
		'default_store' 	 => $default_store,
		'default_menu_type'  => $default_menu_type,
		'default_order_type' => $default_order_type,
		'disable_wp_nonce' => $disable_nonce
	);

	//------------
	$page_settings		= $leafbridge_settings['leafbridge-settings-page-settings'];

	//------------
	$sync_settings	= $leafbridge_settings['leafbridge-sync-settings'];


	$leafbridge_settings = array(
		'leafbridge-settings-api-key' 			=> $leafbridge_settings_api_key,
		'leafbridge-settings-api-secret-key' 	=> $leafbridge_retailer_secret_key,
		'leafbridge-settings-age-modal' 		=> $leafbridge_age_modal,
		'leafbridge-settings-page-settings'     => $page_settings,
		'leafbridge-product-categories'         => $leafbridge_product_categories,
		'leafbridge-config-ui-custom-css'       => $leafbridge_config_custom_css,
		'leafbridge-sync-settings'              => $sync_settings,
		'leafbridge_default_settings'           => $leafbridge_default_settings
	);
	$deprecated = null;
	$autoload = 'true';

	update_option('leafbridge-settings', $leafbridge_settings, false);

	$lf = new LeafBridge();
	echo $lf->leafbridge_admin_notice__success('Successfully saved your default store options.');
}



//============================ WIZARD CONFIG ============================================================================

if (isset($_POST['save-ui-btn'])  && isset($_POST['leafbridge-config-ui-heading'])) {
	$wizard_type			    = trim(($_POST['leafbridge-settings-wizard-type']));
	$wizard_type_link_element   = trim(($_POST['leafbridge-settings-wizard-type-link-element']));
	$wizard_type_modal_element  = trim(($_POST['leafbridge-settings-wizard-type-modal-element']));
	$floating_cart_position     = trim(($_POST['leafbridge-floating-cart-position']));
	$age_modal_is_enable 	    = trim(($_POST['leafbridge-settings-age-modal-is-enable']));
	$heading 				    = trim(stripslashes($_POST['leafbridge-config-ui-heading']));
	$description 			    = trim(stripslashes($_POST['leafbridge-config-ui-description']));
	$errmessage 			    = trim(stripslashes($_POST['leafbridge-config-ui-error-message']));

	$leafbridge_settings 			= get_option('leafbridge-settings');

	$leafbridge_settings_api_key 	= $leafbridge_settings['leafbridge-settings-api-key'];
	$leafbridge_retailer_secret_key = $leafbridge_settings['leafbridge-settings-api-secret-key'];

	$leafbridge_product_categories  = $leafbridge_settings['leafbridge-product-categories'];
	$leafbridge_config_custom_css 	= $leafbridge_settings['leafbridge-config-ui-custom-css'];

	$leafbridge_default_settings 	= $leafbridge_settings['leafbridge_default_settings'];

	$page_settings		= $leafbridge_settings['leafbridge-settings-page-settings'];

	$sync_settings	= $leafbridge_settings['leafbridge-sync-settings'];


	$leafbridge_settings = array(
		'leafbridge-settings-api-key' 			=> $leafbridge_settings_api_key,
		'leafbridge-settings-api-secret-key' 	=> $leafbridge_retailer_secret_key,
		'leafbridge-settings-age-modal' 		=> array(
			'leafbridge-settings-wizard-type'			=> $wizard_type,
			'leafbridge-settings-wizard-type-link-element' => $wizard_type_link_element,
			'leafbridge-settings-wizard-type-modal-element' => $wizard_type_modal_element,
			'leafbridge-floating-cart-position' => $floating_cart_position,
			'leafbridge-settings-age-modal-is-enable' 	=> $age_modal_is_enable,
			'leafbridge-config-ui-heading' 				=> $heading,
			'leafbridge-config-ui-description' 			=> $description,
			'leafbridge-config-ui-error-message' 		=> $errmessage
		),
		'leafbridge-settings-page-settings' => $page_settings,
		'leafbridge-product-categories'  => $leafbridge_product_categories,
		'leafbridge-config-ui-custom-css'  => $leafbridge_config_custom_css,
		'leafbridge-sync-settings' => $sync_settings,
		'leafbridge_default_settings' => $leafbridge_default_settings

	);
	update_option('leafbridge-settings', $leafbridge_settings);

	$lf = new LeafBridge();
	echo $lf->leafbridge_admin_notice__success('Successfully saved your wizard configurations.');
}

//=========================== PAGE SETTINGS ============================================================================

if (isset($_POST['save-ui-pages'])  && isset($_POST['leafbridge-config-ui-shop-link'])) {

	$shopurl 	= trim(($_POST['leafbridge-config-ui-shop-link']));
	$orderurl 				= trim(($_POST['leafbridge-config-ui-order-status-link']));
	$termsurl 			= trim(($_POST['leafbridge-config-ui-terms-link']));
	$location_link 			= trim(($_POST['leafbridge-config-location-link']));
	$disable_product_template			= trim(($_POST['disable-single-product-template']));

	$leafbridge_settings 			= get_option('leafbridge-settings');


	$leafbridge_settings_api_key 	= $leafbridge_settings['leafbridge-settings-api-key'];
	$leafbridge_retailer_secret_key = $leafbridge_settings['leafbridge-settings-api-secret-key'];
	$leafbridge_product_categories  = $leafbridge_settings['leafbridge-product-categories'];
	$leafbridge_config_custom_css 	= $leafbridge_settings['leafbridge-config-ui-custom-css'];
	$leafbridge_default_settings 	= $leafbridge_settings['leafbridge_default_settings'];
	$age_modal			= $leafbridge_settings['leafbridge-settings-age-modal'];
	$sync_settings	= $leafbridge_settings['leafbridge-sync-settings'];



	$leafbridge_settings = array(
		'leafbridge-settings-api-key' 			=> $leafbridge_settings_api_key,
		'leafbridge-settings-api-secret-key' 	=> $leafbridge_retailer_secret_key,
		'leafbridge-settings-age-modal' 		=> $age_modal,
		'leafbridge-settings-page-settings' 	=> array(
			'leafbridge-config-ui-shop-link' 		=> $shopurl,
			'leafbridge-config-ui-order-status-link' => $orderurl,
			'leafbridge-config-ui-terms-link' 		=> $termsurl,
			'leafbridge-config-location-link'	=> $location_link,
			'disable-single-product-template' => $disable_product_template
		),
		'leafbridge-product-categories'  		=> $leafbridge_product_categories,
		'leafbridge-config-ui-custom-css'   	=> $leafbridge_config_custom_css,
		'leafbridge-sync-settings' 				=> $sync_settings,
		'leafbridge_default_settings' 			=> $leafbridge_default_settings
	);
	update_option('leafbridge-settings', $leafbridge_settings);

	$lf = new LeafBridge();
	echo $lf->leafbridge_admin_notice__success('Successfully saved your page configurations.');
}

//========================= SAVE CUSTOM STYLES ==================================================================================
if (isset($_POST['save-ui-css'])  && isset($_POST['leafbridge-config-ui-custom-css'])) {

	$custom_css 	= esc_attr($_POST['leafbridge-config-ui-custom-css']);

	$leafbridge_settings 			= get_option('leafbridge-settings');


	$leafbridge_settings_api_key 	= $leafbridge_settings['leafbridge-settings-api-key'];
	$leafbridge_retailer_secret_key = $leafbridge_settings['leafbridge-settings-api-secret-key'];
	$leafbridge_product_categories  = $leafbridge_settings['leafbridge-product-categories'];
	$leafbridge_default_settings 	= $leafbridge_settings['leafbridge_default_settings'];
	$age_modal						= $leafbridge_settings['leafbridge-settings-age-modal'];
	$page_settings					= $leafbridge_settings['leafbridge-settings-page-settings'];
	$sync_settings					= $leafbridge_settings['leafbridge-sync-settings'];

	$leafbridge_settings = array(
		'leafbridge-settings-api-key' 			=> $leafbridge_settings_api_key,
		'leafbridge-settings-api-secret-key' 	=> $leafbridge_retailer_secret_key,
		'leafbridge-settings-age-modal' 		=> $age_modal,
		'leafbridge-settings-page-settings' 	=> $page_settings,
		'leafbridge-product-categories'  		=> $leafbridge_product_categories,
		'leafbridge-config-ui-custom-css'  		=> $custom_css,
		'leafbridge-sync-settings' 				=> $sync_settings,
		'leafbridge_default_settings'			=> $leafbridge_default_settings
	);
	update_option('leafbridge-settings', $leafbridge_settings);

	$lf = new LeafBridge();
	echo $lf->leafbridge_admin_notice__success('Successfully saved your custom styles.');
}




//======================  SAVE LOGGIN DETAILS  ====================================================================================
if (isset($_POST['save-ui-log'])  && isset($_POST['lb-sync-log-email'])) {

	$sync_email 	= trim($_POST['lb-sync-log-email']);
	$sync_status 	= (isset($_POST['lb-sync-log-status']) && $_POST['lb-sync-log-status'] == 'on' ? 1 : 0);

	$leafbridge_settings 			= get_option('leafbridge-settings');


	$leafbridge_settings_api_key 	= $leafbridge_settings['leafbridge-settings-api-key'];
	$leafbridge_retailer_secret_key = $leafbridge_settings['leafbridge-settings-api-secret-key'];
	$leafbridge_product_categories  = $leafbridge_settings['leafbridge-product-categories'];
	$leafbridge_default_settings 	= $leafbridge_settings['leafbridge_default_settings'];
	$age_modal						= $leafbridge_settings['leafbridge-settings-age-modal'];
	$custom_css 					= $leafbridge_settings['leafbridge-config-ui-custom-css'];
	$page_settings					= $leafbridge_settings['leafbridge-settings-page-settings'];

	$leafbridge_settings = array(
		'leafbridge-settings-api-key' 			=> $leafbridge_settings_api_key,
		'leafbridge-settings-api-secret-key' 	=> $leafbridge_retailer_secret_key,
		'leafbridge-settings-age-modal' 		=> $age_modal,
		'leafbridge-settings-page-settings'		=> $page_settings,
		'leafbridge-product-categories'  		=> $leafbridge_product_categories,
		'leafbridge-config-ui-custom-css'  		=> $custom_css,
		'leafbridge-sync-settings' => array(
			'lb-sync-log-status' => $sync_status,
			'lb-sync-log-email'  => $sync_email
		),
		'leafbridge_default_settings' 			=> $leafbridge_default_settings
	);
	update_option('leafbridge-settings', $leafbridge_settings);

	$lf = new LeafBridge();
	echo $lf->leafbridge_admin_notice__success('Successfully saved your logging settings.');
}


/* ********************* */


$leafbridge_license_data = get_option('leafbridge-license-data');
$lkey_db  = $leafbridge_license_data['lkey'];
$actn     = $leafbridge_license_data['actn'];
$licence_validity = '';

$server_url = "https://api.leafbridge-proxy.click";

$response = wp_remote_get(
	$server_url . '/license-check',
	array(
		'method' => 'POST',
		'timeout' => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking' => true,
		'headers'  => array('Nonce' => '"' . md5(uniqid(mt_rand() . time(), true)) . '"'),
		'body' => array(
			'licenseKey' => $lkey_db,
			'itemId' 	 => '1101',
			'url' => get_site_url()
		),
		'cookies' => array()
	)
);
$body = wp_remote_retrieve_body($response);
$responceData = (!is_wp_error($response)) ? json_decode($body, true) : null;
//echo '<pre>';var_dump($response);echo '</pre>';
if (isset($responceData) && isset($responceData['data'])) {

	$licence_status   = $responceData['data']['eddResponse']['success'];
	$licence_validity = $responceData['data']['eddResponse']['license'];
	$xpd  = isset($responceData['data']['eddResponse']['expires']) ? $responceData['data']['eddResponse']['expires'] : date("Y-m-d H:i:s");
	$actn = isset($responceData['data']['accessToken']) ? $responceData['data']['accessToken'] : $actn;

	$license_data = array(
		'lsat'  => $licence_status,
		'lvld'  => $licence_validity,
		'lkey'  => $lkey_db,
		'ltier' => '1101',
		'actn'	=> $actn,
		'xpd'	=> strtotime($xpd)
	);

	// check for expired
	$isExpired = licenceIsExpired($xpd);

	if ($isExpired == 1) { // expired
		// Is 1 if the interval represents a negative time period and 0 otherwise
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
} else {
	$lf = new LeafBridge();
	echo $lf->leafbridge_admin_notice__error(ucfirst($responceData['message']));
	$store_api_status = 0;
	$lsat = 0;
}
//}


/* ***************************** */


$leafbridge_settings_db 	 = get_option('leafbridge-settings');
$ui_values 				     = $leafbridge_settings_db['leafbridge-settings-age-modal'];
$page_values 			     = $leafbridge_settings_db['leafbridge-settings-page-settings'];
$custom_css 			     = $leafbridge_settings_db['leafbridge-config-ui-custom-css'];
$default_settings			 = $leafbridge_settings_db['leafbridge_default_settings'];
$age_status 			     = $ui_values['leafbridge-settings-age-modal-is-enable'];
$wizard_type			     = $ui_values['leafbridge-settings-wizard-type'];
$wizard_type_link_element    = $ui_values['leafbridge-settings-wizard-type-link-element'];
$wizard_type_modal_element   = $ui_values['leafbridge-settings-wizard-type-modal-element'];
$floating_cart_position      = $ui_values['leafbridge-floating-cart-position'];
$sync_settings				 = $leafbridge_settings_db['leafbridge-sync-settings'];

$store_key 					 = $leafbridge_settings_db['leafbridge-settings-api-key'];
$store_secret_key 			 = $leafbridge_settings_db['leafbridge-settings-api-secret-key'];

// license data 
$leafbridge_license = get_option('leafbridge-license-data');
$lsat  = $leafbridge_license['lsat'];
$lvld  = $leafbridge_license['lvld'];
$lkey  = $leafbridge_license['lkey'];
$ltier = $leafbridge_license['ltier'];
$xpd   = $leafbridge_license['xpd'];



if (isset($sync_settings) && is_array($sync_settings) && $sync_settings['lb-sync-log-status'] == 1) {
	echo '<div class="notice notice-error lf-notice" id="lf_disable_logging_notice">';
	echo '<p>Store Synchronization logging is currently enabled. Since logs may contain sensitive information, please ensure that you only leave it enabled for as long as it is needed for troubleshooting.';
	echo '<br/><strong>If you currently have a support ticket open, please do not disable logging until the Support Team has reviewed your logs. </strong></p>';
	echo '<p><strong>Once troubleshooting is complete, disable logging. You can view log file from <a href="' . get_site_url() . '/wp-content/plugins/leafbridge/includes/autosync.log" target="_blank">here</a>.</strong></p></div>';
}

?>






<!-- ======================================================================================== Modal ============================================================================================ -->
<div id="leafbridge-settings-modal">
	<div class="leafbridge-settings-modal-inner">
		<a id="leafbridge-settings-modal-close-btn" onclick="lb_close_modal();" href="javascript:void();">X</a>
		<div class="leafbridge-settings-modal-warpper">
			<h3>Preparing your store. Please wait!</h3>
			<img class="lf-admin-loading-gif" src="<?php echo LEAFBRIDGE_ADMIN_PATH . 'leafbridge/admin/images/loading.gif'; ?>">
			<ul>
				<li id="lf-admin-modal-li-retailer" style="display:none">Saving retailers...</li>
				<li id="lf-admin-modal-li-category" style="display:none">Saving categories...</li>
				<li class="lf-admin-modal-li-product" style="display:none"></li>
			</ul>
		</div>
	</div>
</div>
<!-- ===== Modal End ========= -->
<?php include plugin_dir_path(dirname(__FILE__)) . 'partials/leafbridge-admin-info-popup.php'; ?>

<div id="leafbridge-settings" class="group leafbridge-admin-settings">



	<?php
	if (current_user_can('manage_options')) { ?>
		<!-- =================================================================================== License KEYS Section ============================================================================== -->
		<div class="lf-action-wrap">
			<div class="lf-ui-sidebar-wrapper">

				<div class="lf-inside">

					<div class="lf-panel">

						<div class="lf-panel-header">
							<h3><?php _e('LeafBridge License Activation ', 'leafbridge'); ?></h3>
						</div>

						<div class="lf-panel-content">

							<div class="lf-activation-notice-panel">
								<?php
								if (isset($lsat) && $lsat == 1 && $lvld == 'valid' && $lkey != '') {
								?>
									<div class="notice notice-success">
										<p><?php echo __('Your license key has been verified.', 'leafbridge'); ?></p>
									</div>
								<?php
								} else {
								?>
									<div class="notice notice-error">
										<p><?php echo __('Invalid license key.', 'leafbridge'); ?></p>
									</div>
								<?php
								}
								?>
							</div>

							<form method="post" action="">

								<table class="form-table" role="presentation">
									<tbody>
										<tr class="leafbridge-settings-row">
											<th scope="row">
												<label for="leafbridge-license-key"><?php _e('License Key', 'leafbridge'); ?></label>
											</th>
											<td>
												<input type="text" id="leafbridge-license-key" name="leafbridge-license-key" value="<?php echo ($lkey ? $lkey : ''); ?>" />
												<?php
												if (isset($lsat) && $lsat == 1 && $lvld == 'valid' && $lkey != '' && isset($xpd) && $xpd != '') {
													if (is_numeric($xpd)) {
														echo ' Expire on ' . date('j F, Y', $xpd);
													} else {
														echo ' Expire on ' . date('j F, Y', strtotime($xpd));
													}
												} ?>
												<p class="description"><?php _e('Enter your license key', 'leafbridge'); ?></p>
											</td>
										</tr>

									</tbody>
								</table>
								<div>
									<input type="hidden" id="leafbridge-license-status" name="leafbridge-license-status" />
									<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary lf-button-sm" value="<?php _e('Save Changes', 'leafbridge'); ?>"></p>
								</div>
							</form>

						</div>
					</div>




				</div>

			</div>
		</div>

	<?php }

	?>






	<?php
	if (current_user_can('manage_options')) { ?>
		<!-- =================================================================================== API KEYS Section ============================================================================== -->
		<div class="lf-action-wrap">
			<div class="lf-ui-sidebar-wrapper">

				<div class="lf-inside">

					<div class="lf-panel">

						<div class="lf-panel-header">
							<h3><?php _e('LeafBridge Plugin Activation ', 'leafbridge'); ?></h3>
						</div>

						<div class="lf-panel-content">

							<div class="lf-activation-notice-panel">
								<?php
								if ($store_api_status == 1) {
								?>
									<div class="notice notice-success">
										<p><?php echo __('Your API key has been verified.', 'leafbridge'); ?></p>
									</div>
								<?php
								} else {
								?>
									<div class="notice notice-error">
										<p><?php echo __('Your API key is invalid.', 'leafbridge'); ?></p>
									</div>
								<?php
								}
								?>
							</div>

							<form method="post" action="">

								<table class="form-table" role="presentation">
									<tbody>
										<tr class="leafbridge-settings-row">
											<th scope="row">
												<label for="leafbridge-settings-api-key"><?php _e('Dutchie API Key', 'leafbridge'); ?></label>
											</th>
											<td>
												<textarea id="leafbridge-settings-api-key" name="leafbridge-settings-api-key" rows="3" cols="5"><?php echo ($store_key ? $store_key : ''); ?></textarea>
												<p class="description"><?php _e('Enter your Dutchie API Key from account.', 'leafbridge'); ?></p>
											</td>
										</tr>

										<tr class="leafbridge-settings-row">
											<th scope="row">
												<label for="leafbridge-settings-api-secret-key"><?php _e('Dutchie Secret API Key', 'leafbridge'); ?></label>
											</th>
											<td>
												<textarea id="leafbridge-settings-api-secret-key" name="leafbridge-settings-api-secret-key" rows="3" cols="5"><?php echo ($store_secret_key ? $store_secret_key : ''); ?></textarea>
												<p class="description"><?php _e('Make sure to enter valid API secret key to work the store properly.', 'leafbridge'); ?></p>
											</td>
										</tr>

									</tbody>
								</table>
								<div>
									<input type="hidden" id="leafbridge-settings-status" name="leafbridge-settings-status" />
									<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary lf-button-sm" value="<?php _e('Save Changes', 'leafbridge'); ?>"></p>
								</div>
							</form>

						</div>
					</div>




				</div>

			</div>
		</div>

	<?php }

	?>






	<!-- ======================================================================================== Setup Store Section ============================================================================================ -->
	<?php if (isset($lsat) && $lsat == 1 && $lvld == 'valid' && isset($store_api_status) && $store_api_status == 1) { ?>
		<!-- ---------------- Pull products -------------------- -->


		<div class="lf-action-wrap">
			<div class="lf-ui-sidebar-wrapper">

				<div class="lf-inside">

					<div class="lf-panel">

						<div class="lf-panel-header">
							<h3><?php _e('Setup your store', 'leafbridge'); ?></h3>
						</div>

						<div class="lf-panel-content">

							<form method="post" action="" id="lb-admin-select-stores-form">
								<?php

								$nonce = wp_create_nonce('leafbridge-admin-ajax-nonce');

								if (class_exists('WooCommerce')) { ?>
									<p class="leafbridge-woonotice"> <?php _e("You have activated WooCommerce plugin that might be conflicted with LeafBridge plugin. We recomend to deactivate the WooCommerce plugin to work Dutchie store.", "leafbridge"); ?></p>
								<?php }  ?>
								<p><?php _e('Select retailer stores and click on setup store button to complete your Dutchie store installation.', 'leafbridge'); ?></p>
								<?php
								$timezone = new DateTimeZone('America/New_York');

								$timestamp =  wp_next_scheduled('leafbridge_sync_hook');
								$date = new DateTime("@$timestamp");
								$date->setTimezone($timezone);
								$formatted_date_time = $date->format('dS M Y g.ia');


								$timeNow = time();
								$date1 = new DateTime('@' . $timestamp);
								$date1->setTimezone($timezone);
								$date2 = new DateTime('@' . $timeNow);
								$date2->setTimezone($timezone);

								$timeDifference = $date2->diff($date1);
								$hoursDifference = $timeDifference->h;
								$minutesDifference = $timeDifference->i;

								echo "<p class='lb-cron-notice'>Automatic synchronization is scheduled at : $formatted_date_time.</p>";
								echo "<p class='lb-cron-notice'>Next run in : $hoursDifference hours $minutesDifference minutes.</p>";
								?>
								<input type="hidden" name="leafbridge-admin-ajax-nonce" id="leafbridge-admin-ajax-nonce" value="<?php echo $nonce; ?>" />

								<div class="lb-admin-select-stores-wrapper">
									<?php

									$leafbridge_settings = get_option('leafbridge-settings');
									$retailer_details = '';

									$meta_reailer_saved_array = array();
									$args = array(
										'post_type' => 'retailer',
										'post_status' => array('publish'),
										'posts_per_page' => -1,
									);
									$loop = new WP_Query($args);
									if ($loop->have_posts()) {
										while ($loop->have_posts()) : $loop->the_post();
											$meta_reailer_saved = get_post_meta(get_the_ID(), '_lb_retailer_options_all', true);
											$meta_reailer_saved_array[] = $meta_reailer_saved['_lb_retailer_id'];
										endwhile;
									}


									$i = 0;
									foreach ($dutchie_store_retailers as $retailer) {
										$rid = $retailer['id'];
										$rname = $retailer['name'];
										$raddress = $retailer['address'];
										$menuTypes = $retailer['menuTypes'];
										$fulfillmentOptions = $retailer['fulfillmentOptions'];
									?>
										<div class="lb-card">

											<div class="lb-card__body">
												<?php
												if (in_array($rid, $meta_reailer_saved_array)) { ?>
													<div onclick="leafbridge_remove_retailer(this)" class="lb-remove-item"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
															<path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z" />
															<path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z" />
														</svg></div>
												<?php }
												?>


												<span class="lb-card__body-cover-checkbox">
													<label class="lb-form-control">
														<input type="checkbox" name="leafbridge-settings-retailer-key[]" value="<?php echo $rid; ?>" <?php if (in_array($rid, $meta_reailer_saved_array)) {
																																							echo 'checked="checked" class="leafbridge-settings-retailer-key selected"';
																																						} else {
																																							echo 'class="leafbridge-settings-retailer-key"';
																																						} ?> />
													</label>
												</span>
												<div class="lb-card__body-cover">
													<svg width="130" height="88" viewBox="0 0 130 88" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M118.445 47.8451V64.8063H114.833V21.6393L98.2218 12.5688V27.608H92.4448V17.0995H78V11.3935H82.0311C82.607 11.3831 83.0666 10.9384 83.0552 10.3991V7.38134C83.0666 6.84209 82.607 6.39732 82.0311 6.38695H78L72.7179 0L67.1667 6.38695H62.4137C61.8367 6.39732 61.3782 6.84209 61.3885 7.38134V10.3979C61.3782 10.9372 61.8367 11.382 62.4137 11.3923H67.1667V25.5075H57.0552V19.2622H59.9448V14.3559H14.4448V19.2622H18.0552V60.0025L13 59.8873V46.293L0 52.0324V79.6496H130V47.7552L118.445 47.8451Z" fill="#E3E6E7"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M80.1666 20.2349C77.375 20.2349 75.1114 22.5037 75.1114 25.3036L85.2218 25.3048C85.2218 22.5048 82.9582 20.2349 80.1666 20.2349ZM50.8437 22.1396H25.7104V27.2095H50.8437V22.1396ZM3.61035 55.7151H10.1104V59.3355H3.61035V55.7151ZM10.1104 62.9559H3.61035V66.5762H10.1104V62.9559ZM3.61035 70.1966H10.1104V73.817H3.61035V70.1966ZM104.722 23.8552H101.833V30.3724H104.722V23.8552ZM107.61 23.8552H110.5V30.3724H107.611L107.61 23.8552ZM104.722 34.7175H101.833V41.2335H104.722V34.7175ZM107.61 33.268H110.5V39.7852H107.611L107.61 33.268ZM104.722 41.2739H101.833V47.7899H104.722V41.2739ZM107.61 42.6819H110.5V49.1991H107.611L107.61 42.6819ZM104.722 50.6855H101.833V57.2027H104.722V50.6855ZM107.61 52.0948H110.5V58.6119H107.611L107.61 52.0948ZM127.112 51.4103H121.333V55.0307H127.112V51.4103ZM121.333 58.651H127.112V62.2714H121.333V58.651ZM127.112 65.8918H121.333V69.5122H127.112V65.8918ZM101.833 60.0994H104.722V66.6166H101.833V60.0994ZM110.5 61.5075H107.61L107.611 68.0246H110.5V61.5075Z" fill="white"></path>
														<path d="M99.256 84.2437H27.6625C17.4449 84.2437 12.6235 84.9915 12.6235 85.9087C12.6235 86.827 13.0728 88 24.7728 88L32.7838 87.9827V88H76.2471V87.9723L102.464 88C115.432 88 115.931 86.8328 115.931 85.911C115.931 84.9892 110.585 84.2437 99.2583 84.2437L99.256 84.2437Z" fill="#E3E6E7"></path>
														<path d="M104.932 84.7449V40.3428C106.152 40.1748 107.064 39.1258 107.072 37.8816V36.0541L100.116 28.2395H29.1441L22.188 36.0541V37.8816C22.1949 39.2653 23.309 40.3823 24.6785 40.3785L25.04 41.8269V84.7449" fill="white"></path>
														<path d="M25.2783 40.5479V43.0552L104 44.1694V40.549L25.2783 40.5479Z" fill="#C9CDCF"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M24.5552 86.8914H50.9167V86.8891C51.5154 86.8891 52 86.4029 52 85.8026C52 85.2034 51.5154 84.7171 50.9167 84.7171H26.7218V40.1859C26.7218 39.5856 26.2372 39.0994 25.6385 39.0994C25.041 39.0994 24.5552 39.5868 24.5552 40.1859V86.8914ZM79.0834 86.8914H105.445V86.8903V40.1871C105.445 39.5868 104.959 39.1005 104.362 39.1005C103.763 39.1005 103.278 39.5879 103.278 40.1871V84.7183H79.0834C78.4847 84.7183 78 85.2045 78 85.8037C78.0007 86.0932 78.1152 86.3705 78.3184 86.5746C78.5217 86.7787 78.7969 86.8926 79.0834 86.8914Z" fill="#153F66"></path>
														<path d="M68.2498 76.7528C67.9637 76.754 67.6889 76.6403 67.4857 76.4368C67.2825 76.2332 67.1677 75.9564 67.1665 75.6673V66.9782C67.1665 66.379 67.6512 65.8916 68.2498 65.8916C68.8485 65.8916 69.3332 66.3779 69.3332 66.9782V75.6662C69.3332 76.2665 68.8485 76.7528 68.2498 76.7528Z" fill="#C9CDCF"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M57.0552 86.8916H72.9448V86.8892V56.4778H57.0552V86.8916ZM70.7782 84.7184H59.2219V84.7173V58.6498H70.7782V84.7184Z" fill="#153F66"></path>
														<path d="M58.1387 53.2205H71.8605V59.0128H58.1387V53.2205Z" fill="white"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M57.0552 60.0993H72.9448V52.1338H57.0552V60.0993ZM70.7782 57.9273H59.2219V57.9262V54.3058H70.7782V57.9273Z" fill="#153F66"></path>
														<path d="M36.4717 51.7732H48.0269V78.5631H36.4717V51.7732Z" fill="#E3E6E7"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M35.3887 79.6496H49.1117V50.6865H35.3887V79.6496ZM46.945 77.4776H37.5554V52.8586H46.945V77.4776Z" fill="#153F66"></path>
														<path d="M25.6387 51.7732H36.472V78.5631H25.6387V51.7732Z" fill="#E3E6E7"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M24.5552 79.6496H37.5552V50.6865H24.5552V79.6496ZM35.3886 77.4776H26.7219V52.8586H35.3886V77.4776Z" fill="#153F66"></path>
														<path d="M93.5283 51.7732H104.362V78.5631H93.5283V51.7732Z" fill="#E3E6E7"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M92.4448 79.6496H105.445V50.6865H92.4448V79.6496ZM103.278 77.4776H94.6116V52.8586H103.278V77.4776Z" fill="#153F66"></path>
														<path d="M81.9717 51.7732H93.5268V78.5631H81.9717V51.7732Z" fill="#E3E6E7"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M80.8887 79.6496H94.6117V50.6865H80.8887V79.6496ZM92.4451 77.4776H83.0554V52.8586H92.4451V77.4776Z" fill="#153F66"></path>
														<path d="M61.6726 50.3235L58.1387 47.6872V44.9955L61.6726 42.3591H68.3277L71.8617 44.9955V47.6872L68.3277 50.3235H61.6726Z" fill="#C9CDCF"></path>
														<path d="M25.6387 48.8762H49.472V51.0482C49.4714 51.2409 49.395 51.4255 49.2596 51.5613C49.1242 51.697 48.9409 51.7728 48.7502 51.7718H25.6387V48.8762V48.8762Z" fill="#153F66"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M24.5552 52.8586H48.75V52.8574C49.7467 52.8574 50.5552 52.0474 50.5552 51.0484V47.7898H24.5552V52.8586ZM48.3886 50.6866H26.7219V49.9618H48.3886V50.6866Z" fill="#153F66"></path>
														<path d="M80.5283 48.8762H104.362V51.7718H81.2502C81.0594 51.7728 80.8762 51.697 80.7408 51.5613C80.6054 51.4255 80.5289 51.2409 80.5283 51.0482V48.8762V48.8762Z" fill="#153F66"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M81.25 52.8586H105.445V47.7898H79.4448V51.0484C79.4466 51.5303 79.6378 51.9918 79.9764 52.3312C80.3149 52.6707 80.7731 52.8604 81.25 52.8586ZM103.278 50.6866H81.6116V49.9618H103.278V50.6866Z" fill="#153F66"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M30.8784 57.5655C36.4662 53.2203 32.9938 52.4967 26.7104 58.2891L26.7219 68.7885C26.7219 70.9605 30.1783 69.1503 34.3713 65.5299C36.3978 63.7792 36.4645 64.0603 36.6246 64.7356C36.7956 65.4569 37.0733 66.6278 39.959 66.2535C45.5479 65.5299 46.9448 63.3579 46.9448 63.3579V60.4611C46.9448 60.4611 37.1549 64.0815 41.3503 59.7375C45.5445 55.3923 44.8455 53.944 35.0669 60.4611C25.7845 66.6476 25.7297 61.5673 30.8784 57.5655ZM88.1115 58.9816C90.2781 56.51 89.3385 54.7171 83.0552 58.1577V65.8386C83.0552 67.1292 87.2311 66.053 91.4231 63.9017C93.3859 62.8949 93.4588 63.8335 93.555 65.0721C93.6642 66.4786 93.8035 68.2721 96.7747 68.0429C99.9152 67.9104 102.579 65.6854 103.275 62.6123V60.892C103.275 60.892 94.2192 63.0433 98.4088 60.4622C102.092 58.1957 100.39 56.2749 92.1255 60.892C85.2218 64.7486 83.85 63.8441 88.1115 58.9816Z" fill="white"></path>
														<path d="M49.9052 73.1335L54.1131 68.7884H50.1128L53.153 65.168H49.6566L47.6667 61.5476L45.6802 65.168H42.1839L45.2241 68.7884H41.2237L45.4316 73.1324H40.3833L46.4614 78.7508C47.1509 79.3649 48.1848 79.3649 48.8744 78.7508L54.9524 73.1324H49.9064L49.9052 73.1335Z" fill="#FF3E51"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M51.6396 79.2891H42.9707V85.078H51.6396V79.2891ZM85.5844 79.2891H76.9155V85.8028H85.5844V79.2891Z" fill="#8DA1B4"></path>
														<path d="M42.9707 79.2891V78.8726H42.5542V79.2891H42.9707ZM51.6396 79.2891H52.0561V78.8726H51.6396V79.2891ZM42.9707 85.078H42.5542V85.4945H42.9707V85.078ZM51.6396 85.078V85.4945H52.0561V85.078H51.6396ZM76.9155 79.2891V78.8726H76.4991V79.2891H76.9155ZM85.5844 79.2891H86.0009V78.8726H85.5844V79.2891ZM76.9155 85.8028H76.4991V86.2192H76.9155V85.8028ZM85.5844 85.8028V86.2192H86.0009V85.8028H85.5844ZM42.9707 79.7055H51.6396V78.8726H42.9707V79.7055ZM43.3872 85.078V79.2891H42.5542V85.078H43.3872ZM51.6396 84.6615H42.9707V85.4945H51.6396V84.6615ZM51.2232 79.2891V85.078H52.0561V79.2891H51.2232ZM76.9155 79.7055H85.5844V78.8726H76.9155V79.7055ZM77.332 85.8028V79.2891H76.4991V85.8028H77.332ZM85.5844 85.3863H76.9155V86.2192H85.5844V85.3863ZM85.168 79.2891V85.8028H86.0009V79.2891H85.168Z" fill="#979797"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M45.2261 86.8915H49.7453V86.8903C50.998 86.9293 52.1045 86.071 52.3909 84.8381L54.0832 77.4775H40.8882L42.5713 84.7909C42.842 86.0445 43.9567 86.9265 45.2261 86.8915ZM44.6935 84.3508L43.6102 79.6496H43.6113H51.36L50.2733 84.3957C50.1884 84.6088 49.975 84.74 49.7487 84.7183H45.2295C44.9866 84.7391 44.7619 84.5847 44.6935 84.3508Z" fill="#153F66"></path>
														<path d="M83.8501 73.1335L88.058 68.7884H84.0576L87.0966 65.168H83.6015L81.6115 61.5476L79.6239 65.168H76.1299L79.1701 68.7884H75.1686L79.3765 73.1324H74.3281L80.4062 78.7508C81.0957 79.3649 82.1297 79.3649 82.8192 78.7508L88.8973 73.1324H83.8501V73.1335Z" fill="#FF3E51"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M79.1714 86.8915H83.6906V86.8903C84.9433 86.9293 86.0498 86.071 86.3362 84.8381L88.0274 77.4775H74.8335L76.5155 84.7909C76.7863 86.0449 77.9016 86.927 79.1714 86.8915ZM78.6388 84.3508L77.5555 79.6496H85.3088L84.2186 84.3957C84.1337 84.6088 83.9203 84.74 83.694 84.7183H79.1748C78.9308 84.7391 78.7073 84.5847 78.6388 84.3508Z" fill="#153F66"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M21.6665 36.5668L28.8883 28.2395H100.388L107.61 36.5668V38.021C107.601 39.4207 106.475 40.5503 105.09 40.549H24.1878C22.8021 40.551 21.6753 39.4211 21.6665 38.021V36.5668ZM35.7071 79.3333C35.9103 79.5371 36.1853 79.6509 36.4716 79.6497C37.0703 79.6497 37.5549 79.1634 37.5549 78.5631V51.772C37.5549 51.1728 37.0703 50.6866 36.4716 50.6866C35.8741 50.6866 35.3883 51.1728 35.3883 51.7732V78.5631C35.3892 78.8524 35.5039 79.1295 35.7071 79.3333Z" fill="#153F66"></path>
														<path d="M102.268 34.7566H27.0093L30.6926 30.4126H98.5868L102.268 34.7566Z" fill="#446583"></path>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M94.6115 50.6865H92.4448V78.2024H94.6115V50.6865ZM45.1385 86.8915H84.8615V86.8903C85.459 86.8903 85.9448 86.4041 85.9448 85.8038C85.9448 85.2046 85.459 84.7183 84.8615 84.7183H45.1385C44.541 84.7183 44.0552 85.2046 44.0552 85.8038C44.0558 86.0933 44.1703 86.3706 44.3736 86.5747C44.5768 86.7787 44.852 86.8927 45.1385 86.8915Z" fill="#153F66"></path>
													</svg>

												</div>
												<header class="lb-card__body-header" id="lb-settings-retailer_<?php echo $rid; ?>">
													<h2 class="lb-card__body-header-title"><?php echo $rname; ?></h2>
													<p class="lb-card__body-header-subtitle"><?php echo $raddress; ?></p>
													<p class="lb-card-details">
														<?php
														foreach ($menuTypes as $menuType) {
															echo '<span>' . $menuType . '</span>';
														}
														?>
													</p>
													<?php
													$args = array(
														'post_type'  => 'retailer',
														'meta_query' => array(
															array(
																'key'   => '_lb_retailer_single_id',
																'value' => $rid,
															),
														),
													);
													$posts = get_posts($args);
													if (!empty($posts)) {
														// Get the ID of the first post in the array
														$post_id = $posts[0]->ID;

														$custom_name = get_post_meta($post_id, 'lb_retailer_custom_name', true); ?>
														<div class="lb-retailer-custom-name">
															<input placeholder="Enter retailer name" value="<?php echo $custom_name ?>" class="leafbridge-settings-retailer-custom-name" name="leafbridge-settings-retailer-custom-name" type="text">
															<button class="lb-settings-retailer-submit">Update</button>
														</div>

													<?php } else { ?>
														<div class="lb-retailer-custom-name">
															<input placeholder="Enter retailer name" value="" class="leafbridge-settings-retailer-custom-name" name="leafbridge-settings-retailer-custom-name" type="text">
															<button class="lb-settings-retailer-submit">Update</button>
														</div>

													<?php }
													if (in_array($rid, $meta_reailer_saved_array)) { ?>
														<button class="quick-sync" onclick="leafbridge_retailer_quick_sync(event,this)">Quick Sync</button>
													<?php }
													?>
													<p class="lb-retailer-order-type-hidden" style="display:none">
														<?php
														foreach ($fulfillmentOptions as $key => $val) {
															if (isset($val) && $val != '') {
																echo '<span data-val="' . $key . '">' . ucfirst($key) . '</span>';
															}
														}
														?>
													</p>
												</header>

											</div>
										</div>
									<?php
										$i++;
									}
									?>
								</div>





								<div class="leafbridge-config-ui-footer">
									<p class="submit">
										<input type="button" name="setup-btn2" id="submit3" onclick="leafbridge_setup_store();" class="button button-primary lf-btn-setup-store lf-button-sm" value="<?php _e('Sync All Retailers', 'leafbridge'); ?>">
										<input type="button" name="setup-btn3" id="submit4" onclick="leafbridge_new_setup_store();" class="button button-primary lf-btn-setup-store lf-button-sm" value="<?php _e('Sync Only New Retailers', 'leafbridge'); ?>">
									</p>
								</div>
							</form>



						</div>
					</div>






				</div>

			</div>

		</div>


		<br />


		<?php
		if (current_user_can('manage_options')) { ?>
			<!-- ======================================================================================== Configurations Wizard ============================================================================================ -->

			<div class="lf-action-wrap">
				<div class="lf-ui-sidebar-wrapper">

					<div class="lf-inside">

						<div class="lf-panel">

							<div class="lf-panel-header">
								<h3><?php _e('Default Store Options', 'leafbridge'); ?></h3>
							</div>

							<div class="lf-panel-content">

								<form method="post" action="" id="lb-admin-select-stores-form">
									<?php
									$nonce = wp_create_nonce('leafbridge-admin-ajax-nonce');
									?>
									<input type="hidden" name="leafbridge-admin-ajax-nonce" id="leafbridge-admin-ajax-nonce" value="<?php echo $nonce; ?>" />

									<?php

									$leafbridge_settings = get_option('leafbridge-settings');
									$retailer_details = '';

									$meta_reailer_saved_array = array();
									$retailers_array = array();
									$args = array(
										'post_type' => 'retailer',
										'post_status' => array('publish'),
										'posts_per_page' => -1,
									);
									$loop = new WP_Query($args);
									if ($loop->have_posts()) {
										while ($loop->have_posts()) : $loop->the_post();
											$meta_reailer_saved = get_post_meta(get_the_ID(), '_lb_retailer_options_all', true);
											$meta_reailer_saved_array[] = $meta_reailer_saved['_lb_retailer_id'];
											$_lb_retailer_single_title = get_the_title();
											$retailers_array[$meta_reailer_saved['_lb_retailer_id']] = $_lb_retailer_single_title;
										endwhile;
									}
									//echo '<pre>......';print_r(get_option('leafbridge-settings'));echo '</pre>'; 
									?>

									<div class="leafbridge-config-ui-left">
										<div class="leafbridge-config-ui-left-inner">


											<div class="leafbridge-config-ui-content">
												<table class="form-table" role="presentation">
													<tbody>
														<tr class="leafbridge-settings-row">
															<th scope="row">
																<label for="leafbridge-settings-wizard-type">Store Location</label>
															</th>
															<td>
																<select autocomplete="off" class="leafbridge-settings-default-store" name="leafbridge-settings-default-store" id="leafbridge-settings-default-store" onchange="lb_set_menuType(this);">
																	<!-- <option value="0">-Select-</option> -->
																	<?php
																	foreach ($retailers_array as $retailer_id => $retailer_name) {
																		$rid = $retailer_id;
																		$rname = $retailer_name;
																		$saved_retailer = $leafbridge_settings['leafbridge_default_settings']['default_store'];

																		//echo "<pre>"; print_r($saved_retailer) ; echo "</pre>";
																		if (in_array($rid, $meta_reailer_saved_array)) {

																			echo '<option value="' . $rid . '" ' . ($rid == $saved_retailer ? 'selected="selected"' : '') . '>' . $rname . '</option>';

																			/*$opt = '<option value="'.$rid.'"';
														 if( trim($rid) == trim($saved_retailer) ) {	
															$opt .= ' selected="selected"'; 
														 }  
														 $opt .= '>'.$rname.' '.$rid.'</option>'; 
														 echo $opt;	*/
																		}
																	}
																	?>
																</select>
															</td>
														</tr>

														<tr class="leafbridge-settings-row">
															<th scope="row">
																<label for="leafbridge-settings-menu-type">Menu Type</label>
															</th>
															<td>
																<input type="hidden" id="lb-hdn-default-menu-type" value="<?php echo $leafbridge_settings['leafbridge_default_settings']['default_menu_type']; ?>" />
																<select class="leafbridge-settings-default-menu-type" name="leafbridge-settings-default-menu-type" id="leafbridge-settings-default-menu-type">
																	<!-- <option value="0">-Select-</option> -->
																	<?php

																	?>
																</select>
															</td>
														</tr>

														<tr class="leafbridge-settings-row">
															<th scope="row">
																<label for="leafbridge-settings-order-type">Order Type</label>
															</th>
															<td>
																<input type="hidden" id="lb-hdn-default-order-type" value="<?php echo $leafbridge_settings['leafbridge_default_settings']['default_order_type']; ?>" />
																<select class="leafbridge-settings-default-order-type" name="leafbridge-settings-default-order-type" id="leafbridge-settings-default-order-type">
																	<!-- <option value="0">-Select-</option> -->
																	<?php

																	?>
																</select>

															</td>
														</tr>
														<tr class="leafbridge-settings-row">
															<th scope="row">
																<label for="leafbridge-settings-order-type">Cache Status</label>
															</th>
															<td>
																<?php $hasValueCache = empty($leafbridge_settings['leafbridge_default_settings']['disable_wp_nonce']) ? false : true ?>
																<input type="hidden" id="lb-hdn-disable-nonce" value="<?php echo isset($leafbridge_settings['leafbridge_default_settings']['disable_wp_nonce']) ? $leafbridge_settings['leafbridge_default_settings']['disable_wp_nonce'] : '' ?>" />
																<select class="leafbridge-settings-disable-nonce" name="leafbridge-settings-disable-nonce" id="leafbridge-settings-disable-nonce">
																	<option value="12" <?php echo ($hasValueCache) && $leafbridge_settings['leafbridge_default_settings']['disable_wp_nonce'] == "12" ? 'selected="selected"' : '' ?>>12 hours</option>
																	<option value="24" <?php echo ($hasValueCache) && $leafbridge_settings['leafbridge_default_settings']['disable_wp_nonce'] == "24" ? 'selected="selected"' : '' ?>>24 hours</option>
																	<option value="disable" <?php echo ($hasValueCache) && $leafbridge_settings['leafbridge_default_settings']['disable_wp_nonce'] == "disable" ? 'selected="selected"' : '' ?>>Disable</option>
																</select>
																<p class="template-disabled-description">If your hosting is entirely caching the front end you can disable the nonces. But disabling nonce could be a security threat.</p>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>
									</div>





									<div class="leafbridge-config-ui-footer">
										<p class="submit">
											<input type="submit" name="setup-btn-default-opt" id="submit-default-opt" class="button button-primary lf-btn-setup-store lf-button-sm" value="<?php _e('Save Changes', 'leafbridge'); ?>">

										</p>
									</div>
								</form>



							</div>
						</div>



					</div>

				</div>

			</div>


			<br />

		<?php }
		?>



		<?php
		if (current_user_can('manage_options')) { ?>
			<!-- ======================================================================================== Default options ============================================================================================ -->
			<div class="lf-action-wrap">
				<div class="lf-ui-sidebar-wrapper">
					<div class="lf-inside">
						<div class="lf-panel">
							<div class="lf-panel-header">
								<h3><?php _e('Wizard Configurations', 'leafbridge'); ?></h3>
							</div>

							<div class="lf-panel-content">
								<form method="post" action="" name="leafbridge-config-ui">
									<div class="leafbridge-config-ui-left">
										<div class="leafbridge-config-ui-left-inner">
											<h1>Wizard Option </h1>

											<div class="leafbridge-config-ui-content">
												<table class="form-table" role="presentation">
													<tbody>
														<tr class="leafbridge-settings-row">
															<th scope="row">
																<label for="leafbridge-settings-wizard-type"><?php _e('Wizard Type', 'leafbridge'); ?></label>
															</th>
															<td>
																<select class="" name="leafbridge-settings-wizard-type" id="leafbridge-settings-wizard-type" onchange="lb_load_menuType(this);">
																	<option value="modal" <?php echo ($wizard_type == 'modal' ? 'selected="selected"' : ""); ?>>Modal</option>
																	<option value="link" <?php echo ($wizard_type == 'link' ? 'selected="selected"' : ""); ?>>Link</option>
																</select>
															</td>
														</tr>



														<tr class="leafbridge-settings-row" id="leafbridge-settings-wizard-type-link-element-row" style="display:none;">
															<th scope="row">
																<label for="leafbridge-settings-wizard-type-link-element">Element ID or Class <span onclick="lb_show_info_modal();" style="color: #3181d7;" class="dashicons dashicons-info"></span></label>
															</th>
															<td>
																<input type="text" name="leafbridge-settings-wizard-type-link-element" id="leafbridge-settings-wizard-type-link-element" value="<?php echo $wizard_type_link_element; ?>" />
															</td>
														</tr>



													</tbody>
												</table>
											</div>
										</div>
										<div id="leafbridge-floating-cart-position-row" class="leafbridge-config-ui-left-inner">
											<h1>Floating cart options</h1>
											<div class="leafbridge-config-ui-content">
												<table class="form-table" role="presentation">
													<tbody>
														<tr class="leafbridge-settings-row" id="leafbridge-settings-wizard-type-modal-element-row">
															<th scope="row">
																<label for="leafbridge-settings-wizard-type-modal-element">Element ID or Class
																	<div class="lb-tooltip">
																		<span style="color: #3181d7;" class="dashicons dashicons-info"></span>
																		<div class="lb-right">
																			<div class="lb-text-content">
																				<p>Element ID or Class to append the floating cart and reset buttons </p>
																			</div>
																			<i></i>
																		</div>
																	</div>
																</label>
															</th>
															<td>
																<input type="text" tabindex="-1" name="leafbridge-settings-wizard-type-modal-element" id="leafbridge-settings-wizard-type-modal-element" value="<?php echo $wizard_type_modal_element; ?>" />
															</td>
														</tr>
														<tr class="leafbridge-settings-row">
															<th scope="row">
																<label for="leafbridge-floating-cart-position"><?php _e('Floating cart position', 'leafbridge'); ?></label>
															</th>
															<td>
																<select class="" name="leafbridge-floating-cart-position" id="leafbridge-floating-cart-position" onchange="lb_load_menuType(this);">
																	<option value="" <?php echo ($floating_cart_position == '' ? 'selected="selected"' : ""); ?>>- Select -</option>
																	<option value="top-left" <?php echo ($floating_cart_position == 'top-left' ? 'selected="selected"' : ""); ?>>Top left</option>
																	<option value="center-left" <?php echo ($floating_cart_position == 'center-left' ? 'selected="selected"' : ""); ?>>Center left</option>
																	<option value="bottom-left" <?php echo ($floating_cart_position == 'bottom-left' ? 'selected="selected"' : ""); ?>>Bottom left</option>
																	<option value="bottom-center" <?php echo ($floating_cart_position == 'bottom-center' ? 'selected="selected"' : ""); ?>>Bottom center</option>
																	<option value="bottom-right" <?php echo ($floating_cart_position == 'bottom-right' ? 'selected="selected"' : ""); ?>>Bottom right</option>
																	<option value="center-right" <?php echo ($floating_cart_position == 'center-right' ? 'selected="selected"' : ""); ?>>Center right</option>
																	<option value="top-right" <?php echo ($floating_cart_position == 'top-right' ? 'selected="selected"' : ""); ?>>Top right</option>
																	<option value="top-center" <?php echo ($floating_cart_position == 'top-center' ? 'selected="selected"' : ""); ?>>Top center</option>
																</select>
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>






										<div class="leafbridge-config-ui-left-inner">
											<h1>Age Confimation </h1>

											<div class="leafbridge-config-ui-content">
												<table class="form-table" role="presentation">
													<tbody>
														<tr class="leafbridge-settings-row">
															<th scope="row">
																<label for="leafbridge-settings-age-modal-is-enable"><?php _e('Age Modal is enable', 'leafbridge'); ?></label>
															</th>
															<td>
																<select class="" name="leafbridge-settings-age-modal-is-enable" id="leafbridge-settings-age-modal-is-enable">
																	<option value="1" <?php echo ($age_status == 1 ? 'selected="selected"' : ""); ?>>Yes</option>
																	<option value="0" <?php echo ($age_status == 0 ? 'selected="selected"' : ""); ?>>No</option>
																</select>
															</td>
														</tr>
														<tr class="leafbridge-settings-row">
															<th scope="row">
																<label for="leafbridge-config-ui-heading"><?php _e('Confimation Title', 'leafbridge'); ?></label>
															</th>
															<td>
																<input type="text" class="regular-text" id="leafbridge-config-ui-heading" placeholder="Ex: Are you at least 21 years old ?" name="leafbridge-config-ui-heading" value="<?php echo ($ui_values['leafbridge-config-ui-heading'] ? $ui_values['leafbridge-config-ui-heading'] : ''); ?>" />
															</td>
														</tr>

														<tr class="leafbridge-settings-row">
															<th scope="row">
																<label for="leafbridge-config-ui-description"><?php _e('Description', 'leafbridge'); ?></label>
															</th>
															<td>
																<input type="text" class="regular-text" id="leafbridge-config-ui-description" placeholder="" name="leafbridge-config-ui-description" value="<?php echo ($ui_values['leafbridge-config-ui-description'] ? $ui_values['leafbridge-config-ui-description'] : ''); ?>" />
															</td>
														</tr>

														<tr class="leafbridge-settings-row">
															<th scope="row">
																<label for="leafbridge-config-ui-error-message"><?php _e('Error Message', 'leafbridge'); ?></label>
															</th>
															<td>
																<input type="text" class="regular-text" id="leafbridge-config-ui-error-message" placeholder="We're sorry, you must be at least 21 yrs old or a valid medical patient to use this site!" name="leafbridge-config-ui-error-message" value="<?php echo ($ui_values['leafbridge-config-ui-error-message'] ? $ui_values['leafbridge-config-ui-error-message'] : ''); ?>" />
															</td>
														</tr>
													</tbody>
												</table>
											</div>
										</div>

									</div>
									<div class="leafbridge-config-ui-right">
										<img class="lb-card__body-cover-image" src="<?php echo plugin_dir_url(__DIR__) . 'images/lb-age-modal-1.png'; ?>" />
									</div>

									<div class="leafbridge-config-ui-footer">
										<p class="submit"><input type="submit" name="save-ui-btn" id="lf-submit-ui" class="button button-primary lf-button-sm" value="<?php _e('Save Changes', 'leafbridge'); ?>"></p>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>



		<?php if (current_user_can('manage_options')) { ?>
			<!-- ======================================================================================== Configurations Pages =========================================================== -->


			<div class="lf-action-wrap">
				<div class="lf-ui-sidebar-wrapper">
					<div class="lf-inside">
						<div class="lf-panel">
							<div class="lf-panel-header">
								<h3><?php _e('Pages Configurations', 'leafbridge'); ?></h3>
							</div>

							<div class="lf-panel-content">
								<form method="post" action="" name="leafbridge-config-ui-pages">
									<div class="leafbridge-config-ui-left-inner">
										<h1>Page Selection</h1>
										<div class="leafbridge-config-ui-content lf-page-config-box">
											<table class="form-table" role="presentation">
												<tbody>

													<tr class="leafbridge-settings-row">
														<th scope="row">
															<label for="leafbridge-config-ui-shop-link"><?php _e('Product Shop Page', 'leafbridge'); ?></label>
														</th>
														<td>
															<select name="leafbridge-config-ui-shop-link">
																<option value=""><?php echo esc_attr(__('Select page')); ?></option>
																<?php
																$wppages = get_pages();
																foreach ($wppages as $page) {
																	$link = get_page_link($page->ID);
																	if ($page_values['leafbridge-config-ui-shop-link'] == $link) {
																		$selected = 'selected="selected"';
																	} else {
																		$selected = '';
																	}
																	$option = '<option value="' . $link . '" ' . $selected . '>' . $page->post_title . '</option>';
																	echo $option;
																}
																?>
															</select>
														</td>
													</tr>

													<tr class="leafbridge-settings-row">
														<th scope="row">
															<label for="leafbridge-config-ui-order-status-link"><?php _e('Thank you Page', 'leafbridge'); ?></label>
														</th>
														<td>
															<select name="leafbridge-config-ui-order-status-link">
																<option value=""><?php echo esc_attr(__('Select page')); ?></option>
																<?php

																foreach ($wppages as $page) {
																	$link = get_page_link($page->ID);
																	if ($page_values['leafbridge-config-ui-order-status-link'] == $link) {
																		$selected = 'selected="selected"';
																	} else {
																		$selected = '';
																	}
																	$option = '<option value="' . $link . '" ' . $selected . '>' . $page->post_title . '</option>';
																	echo $option;
																}
																?>
															</select>
														</td>
													</tr>

													<tr class="leafbridge-settings-row">
														<th scope="row">
															<label for="leafbridge-config-ui-terms-link"><?php _e('Terms & Condition Page', 'leafbridge'); ?></label>
														</th>
														<td>
															<select name="leafbridge-config-ui-terms-link">
																<option value=""><?php echo esc_attr(__('Select page')); ?></option>
																<?php
																foreach ($wppages as $page) {
																	$link = get_page_link($page->ID);
																	if ($page_values['leafbridge-config-ui-terms-link'] == $link) {
																		$selected = 'selected="selected"';
																	} else {
																		$selected = '';
																	}
																	$option = '<option value="' . $link . '" ' . $selected . '>' . $page->post_title . '</option>';
																	echo $option;
																}
																?>
															</select>
														</td>
													</tr>

													<tr class="leafbridge-settings-row">
														<th scope="row">
															<label for="leafbridge-config-location-link"><?php _e('Location Page', 'leafbridge'); ?></label>
														</th>
														<td>
															<select name="leafbridge-config-location-link">
																<option value=""><?php echo esc_attr(__('Select page')); ?></option>
																<?php
																var_dump($page_values);
																foreach ($wppages as $page) {
																	$link = get_page_link($page->ID);
																	if ($page_values['leafbridge-config-location-link'] == $page->ID) {
																		$selected = 'selected="selected"';
																	} else {
																		$selected = '';
																	}
																	$option = '<option value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
																	echo $option;
																}
																?>
															</select>
														</td>
													</tr>
													<tr class="leafbridge-settings-row">
														<th scope="row">
															<label for="disable-single-product-template"><?php _e('Disable Single Product template:', 'leafbridge'); ?></label>
														</th>
														<td>
															<select name="disable-single-product-template">
																<?php $isDisabled = $leafbridge_settings['leafbridge-settings-page-settings']['disable-single-product-template']; ?>
																<option value="false" <?php echo $isDisabled == "false" ? 'selected="selected"' : "" ?>><?php echo esc_attr(__('No')); ?></option>
																<option value="true" <?php echo $isDisabled == "true" ? 'selected="selected"' : "" ?>><?php echo esc_attr(__('Yes')); ?></option>
															</select>
															<p class="template-disabled-description">Select this if you want to override the default Single Product Page template .php file. If you disable this, you will have to use this shortcode <code>[leafbridge-product-single-page]</code> to show the single product page content.<br /> Some themes do not offer editing page templates. In that case, you will have to select this option as "No" otherwise expected product single product page content will not appear.
																<br /> In block themes, you will have to use the above shortcode anyways since page templates do not work on Block themes.
															</p>
														</td>
													</tr>


												</tbody>
											</table>
										</div>
									</div>

									<div class="leafbridge-config-ui-footer">
										<p class="submit"><input type="submit" name="save-ui-pages" id="lf-submit-ui-pages" class="button button-primary lf-button-sm" value="<?php _e('Save Changes', 'leafbridge'); ?>"></p>
									</div>

								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php } ?>




		<?php
		if (current_user_can('manage_options')) { ?>
			<!-- ======================================================================================== Custom CSS =========================================================== -->


			<div class="lf-action-wrap">
				<div class="lf-ui-sidebar-wrapper">
					<div class="lf-inside">
						<div class="lf-panel">
							<div class="lf-panel-header">
								<h3><?php _e('Custom CSS', 'leafbridge'); ?></h3>
							</div>

							<div class="lf-panel-content">
								<form method="post" action="" id="leafbridge-config-ui-css-editor" name="leafbridge-config-ui-css">
									<div class="leafbridge-config-ui-left-inner">
										<div class="leafbridge-config-ui-content leafbridge-config-ui-css">
											<table class="form-table" role="presentation">
												<tbody>
													<tr class="leafbridge-settings-row">
														<th scope="row">
															<label for="leafbridge-config-ui-custom-css"><?php _e('Add your custom styles', 'leafbridge'); ?></label>
														</th>
													</tr>

													<tr class="leafbridge-settings-row lf-w-full">
														<td>
															<div class="leafbridge_custom_css_container" id="leafbridge_custom_css_container">
																<textarea data-editor="css" data-gutter="0" id="leafbridge-config-ui-custom-css" name="leafbridge-config-ui-custom-css" rows="8" cols="50"><?php echo wp_unslash($custom_css ? $custom_css : ''); ?></textarea>
															</div>
														</td>
													</tr>

												</tbody>
											</table>
										</div>
									</div>

									<div class="leafbridge-config-ui-footer">
										<p class="submit"><input type="submit" name="save-ui-css" id="lf-submit-ui-css" class="button button-primary lf-button-sm" value="<?php _e('Save Changes', 'leafbridge'); ?>"></p>
									</div>

								</form>
							</div>
						</div>
					</div>
				</div>
			</div>

		<?php } ?>



		<?php
		if (current_user_can('manage_options')) { ?>
			<!-- ======================================================================================== Logging =========================================================== -->


			<div class="lf-action-wrap">
				<div class="lf-ui-sidebar-wrapper">
					<div class="lf-inside">
						<div class="lf-panel">
							<div class="lf-panel-header">
								<h3><?php _e('Logging', 'leafbridge'); ?></h3>
							</div>

							<div class="lf-panel-content">
								<form method="post" action="" name="leafbridge-config-ui-log">
									<div class="leafbridge-config-ui-left-inner">

										<p>
											<label for="leafbridge-config-ui-custom-log"><?php _e('Enable if you would like logging within LeafBridge. Logging allows you to easily debug the store synchronization workings of LeafBridge to view store synced details. ', 'leafbridge'); ?></label>
										</p>

										<div class="leafbridge-config-ui-content leafbridge-config-ui-log">
											<table class="form-table" role="presentation">
												<tbody>

													<tr class="leafbridge-settings-row">
														<th scope="row">
															<label for="lb-sync-log"><?php _e('Is log enable', 'leafbridge'); ?></label>
														</th>
														<td>
															<label class="lb-switch">
																<input type="checkbox" name="lb-sync-log-status" class="lb-sync-log" <?php echo (is_array($sync_settings) && $sync_settings['lb-sync-log-status'] == 1 ? 'checked="checked"' : ""); ?>>
																<span class="lb-slider lb-round"></span>
															</label>
														</td>
													</tr>

													<tr class="leafbridge-settings-row lf-d-none" <?php echo (is_array($sync_settings) &&  $sync_settings['lb-sync-log-status'] == 1 ? 'style="display:table-row"' : ""); ?>>
														<th scope="row">
															<label for="lb-sync-log-email"><?php _e('Email Address', 'leafbridge'); ?></label>
														</th>
														<td>
															<input type="email" class="regular-text" id="lb-sync-log-email" name="lb-sync-log-email" value="<?php echo (is_array($sync_settings) &&  $sync_settings['lb-sync-log-email'] ? $sync_settings['lb-sync-log-email'] : ''); ?>" onkeydown="lf_validate_email(this.value);" />
															<span class="lf-error-email-required">Valid email address is required.</span>
														</td>
													</tr>


												</tbody>
											</table>
										</div>
									</div>

									<div class="leafbridge-config-ui-footer">
										<p class="submit"><input type="submit" onclick="lf_validate_log();" name="save-ui-log" id="lf-submit-ui-log" class="button button-primary lf-button-sm" value="<?php _e('Save Changes', 'leafbridge'); ?>"></p>
									</div>

								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
	<?php }
	} ?>



</div>