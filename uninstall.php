<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://surge.global/
 * @since      1.0.0
 *
 * @package    WPDutchie 
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}



//delete leafbridge_settings
$leafbridge_settings = get_option('leafbridge-settings');
if ($leafbridge_settings) {
	delete_option('leafbridge-settings');
}

//delete leafbridge license data
$leafbridge_license_settings = get_option('leafbridge-license-data');
if ($leafbridge_license_settings) {
	delete_option('leafbridge-license-data');
}


$leafbridge_filters_xdx = get_option('leafbridge_filters_xdx');
if ($leafbridge_filters_xdx) {
	delete_option('leafbridge_filters_xdx');
}

// delete cron event of product sync
$timestamp = wp_next_scheduled('leafbridge_cron_worker_two');
wp_unschedule_event($timestamp, 'leafbridge_cron_worker_two');

// delete cron event of license check
$lb_proxy_cron_action = wp_next_scheduled('lb_proxy_li_my_cron_action');
wp_unschedule_event($lb_proxy_cron_action, 'lb_proxy_li_my_cron_action');  

$lb_local_cron_action = wp_next_scheduled('lb_local_cron_action');
wp_unschedule_event($lb_local_cron_action, 'lb_local_cron_action'); 


//remove all retailers
$all_retailers = get_posts( array('post_type'=>'retailer','numberposts'=>-1) );
foreach ($all_retailers as $eachpost) {
	wp_delete_post( $eachpost->ID, true );
} 

//remove all products
$all_products = get_posts( array('post_type'=>'product','numberposts'=>-1) );
foreach ($all_products as $eachpost) {
	wp_delete_post( $eachpost->ID, true );
}

