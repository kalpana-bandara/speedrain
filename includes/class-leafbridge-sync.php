<?php

/**
 * Products and Retailers syncing
 *
 * This file is used to Products and Retailers syncing of the plugin.
 *
 * @link       https://surge.global/
 * @since      1.0.0
 *
 * @package    LeafBridge
 **/


class LeafBridge_Sync {

	/*
	* Syncing Retailers 
	* Post type: retailer
	*/
	public function sync_retailers($single_retailer_id = "") {

		$json = array();

		if (strlen($single_retailer_id) > 0) {

			/** 
			 ******************** change retailer to drafts ***************** 
			 */
			$retailer_args = array(
				'post_type' => 'retailer',
				'posts_per_page' => -1,
				'meta_key'         => '_lb_retailer_single_id',
				'meta_value'       => $single_retailer_id

			);

			$retailer_query = new WP_Query($retailer_args);
			if ($retailer_query->have_posts()) :
				while ($retailer_query->have_posts()) :
					$retailer_query->the_post();
					$post_status_draft = array('ID' => get_the_ID(), 'post_status' => 'draft');
					wp_update_post($post_status_draft);
				endwhile;
				wp_reset_postdata();
			else :
			endif;

			// *****************************************************************

			//get all from api
			$retailers_obj = new LeafBridge_Retailers();
			$retailers = $retailers_obj->get_retailers_details('advanced');

			$leafbridge_settings 	= get_option('leafbridge-settings');
			$sync_settings			= $leafbridge_settings['leafbridge-sync-settings'];
			$sync_status			= $sync_settings['lb-sync-log-status'];

			if (isset($retailers)) {

				if ($sync_status == 1) {
					error_log('=============== Retailers Synced: ' . date('Y-m-d H:i:s') . ' ========= ' . PHP_EOL, 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
				}
				//----------------------------------------------------------
				$retailers->reformatResults(true);
				$store_retailers = $retailers->getData()['retailers'];

				foreach ($store_retailers as $ret) {

					$retailerPoints = array();
					$retailer_post_id = '';
					$retailer_id = '';

					$retailer_id = $ret['id'];

					//----------------------------------------------------------
					// get retailers from db
					$args = array(
						'posts_per_page'   => 1,
						'post_type'        => 'retailer',
						'post_status' 	   => 'draft',
						'meta_key'         => '_lb_retailer_single_id',
						'meta_value'       => $retailer_id
					);
					$query = new WP_Query($args);

					if ($query->have_posts()) {
						while ($query->have_posts()) {
							$query->the_post();
							$retailer_post_id = (get_the_ID() ? get_the_ID() : '');
						}
						wp_reset_postdata();
					}

					$menutype = '';

					foreach ($ret['menuTypes'] as $x) {
						$menutype .= $x . ', ';
					}

					$postmeta_array = array(
						'_lb_retailer_id' => $ret['id'],
						'_lb_retailer_menuTypes' => $menutype,
						'_lb_retailer_options' => serialize($ret)
					);

					if (isset($retailer_post_id) && $retailer_post_id != '') {
						update_post_meta($retailer_post_id, '_lb_retailer_options_all', $postmeta_array);
						update_post_meta($retailer_post_id, '_lb_retailer_single_id', $ret['id']);
						update_post_meta($retailer_post_id, '_lb_retailer_menuTypes', $menutype);

						$post_status_publish = array(
							'ID' => $retailer_post_id,
							'post_title' => $ret['name'],
							'post_status' => 'publish',
							'post_content' => $ret['name']
						);
						$rid = $ret['id'];
						$updated_retailer_post_id = wp_update_post($post_status_publish);
						$response = ($updated_retailer_post_id ? $updated_retailer_post_id : 0);


						$updated = array('retailer' => $rid, 'post_id' => $response);

						if ($sync_status == 1) {
							error_log(print_r($updated, true), 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
						}

						$json['retailer_sync'] = true;
					}
				} // end foreach retailers 	 
			}

			return $json;
		} else {


			/** 
			 ******************** change all retailers to drafts ***************** 
			 */
			$retailer_args = array(
				'post_type' => 'retailer',
				'posts_per_page' => -1

			);

			$retailer_query = new WP_Query($retailer_args);
			if ($retailer_query->have_posts()) :
				while ($retailer_query->have_posts()) :
					$retailer_query->the_post();
					$post_status_draft = array('ID' => get_the_ID(), 'post_status' => 'draft');
					wp_update_post($post_status_draft);
				endwhile;
				wp_reset_postdata();
			else :
			endif;


			// *****************************************************************

			//get all from api
			$retailers_obj = new LeafBridge_Retailers();
			$retailers = $retailers_obj->get_retailers_details('advanced');

			// echo '<pre>dddddddd'; print_r($retailers);echo '</pre>';
			$leafbridge_settings 	= get_option('leafbridge-settings');
			$sync_settings			= $leafbridge_settings['leafbridge-sync-settings'];
			$sync_status			= $sync_settings['lb-sync-log-status'];
			if (isset($retailers)) {

				if ($sync_status == 1) {
					error_log('=============== Retailers Synced: ' . date('Y-m-d H:i:s') . ' ========= ' . PHP_EOL, 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
				}
				//----------------------------------------------------------
				$retailers->reformatResults(true);
				$store_retailers = $retailers->getData()['retailers'];

				foreach ($store_retailers as $ret) {

					$retailerPoints = array();
					$retailer_post_id = '';
					$retailer_id = '';

					$retailer_id = $ret['id'];

					//----------------------------------------------------------
					// get retailers from db
					$args = array(
						'posts_per_page'   => 1,
						'post_type'        => 'retailer',
						'post_status' 	   => 'draft',
						'meta_key'         => '_lb_retailer_single_id',
						'meta_value'       => $retailer_id
					);
					$query = new WP_Query($args);

					if ($query->have_posts()) {
						while ($query->have_posts()) {
							$query->the_post();
							$retailer_post_id = (get_the_ID() ? get_the_ID() : '');
							//var_dump($retailer_post_id);
						}
						wp_reset_postdata();
					} //else echo 'no retailer';

					$menutype = '';

					foreach ($ret['menuTypes'] as $x) {
						$menutype .= $x . ', ';
					}

					$postmeta_array = array(
						'_lb_retailer_id' => $ret['id'],
						'_lb_retailer_menuTypes' => $menutype,
						'_lb_retailer_options' => serialize($ret)
					);

					//die();
					if (isset($retailer_post_id) && $retailer_post_id != '') {
						update_post_meta($retailer_post_id, '_lb_retailer_options_all', $postmeta_array);
						update_post_meta($retailer_post_id, '_lb_retailer_single_id', $ret['id']);
						update_post_meta($retailer_post_id, '_lb_retailer_menuTypes', $menutype);

						//$json['synced_retailer'][$ret['id']] = $retailer_post_id;		

						$post_status_publish = array(
							'ID' => $retailer_post_id,
							'post_title' => $ret['name'],
							'post_status' => 'publish',
							'post_content' => $ret['name']
						);
						$rid = $ret['id'];
						$updated_retailer_post_id = wp_update_post($post_status_publish);
						$response = ($updated_retailer_post_id ? $updated_retailer_post_id : 0);


						$updated = array('retailer' => $rid, 'post_id' => $response);

						if ($sync_status == 1) {
							error_log(print_r($updated, true), 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
						}

						//array_push($json,$response);
						//$json['synced_retailer'][$ret['id']] = $response;	
						$json['retailer_sync'] = true;
					} else {
						//$json = '0';
					}
				} // end foreach retailers 	 

			}

			return $json;
		}
	}


	/*
	* Syncing Product Categories 
	* Taxonomy: categories
	*/

	public function sync_categories() {

		$json = array();

		// ***************************  ADD OR UPDATE CATEGORIES TAXONOMY ******************************
		$categories = array(
			'Accessories',
			'Apparel',
			'Cbd',
			'Clones',
			'Concentrates',
			'Edibles',
			'Flower',
			'Not_Applicable',
			'Orals',
			'Pre_rolls',
			'Seeds',
			'Tinctures',
			'Topicals',
			'Vaporizers'
		);

		$leafbridge_settings 	= get_option('leafbridge-settings');
		$sync_settings			= $leafbridge_settings['leafbridge-sync-settings'];
		$sync_status			= $sync_settings['lb-sync-log-status'];
		if ($sync_status == 1) {
			error_log('=============== Categories Synced: ' . date('Y-m-d H:i:s') . ' ========= ' . PHP_EOL, 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
		}

		foreach ($categories as $key => $name) {
			$term = term_exists($name, 'categories');
			if (!is_array($term) && !isset($term['term_id'])) {
				$term_id = wp_insert_term(
					$name,   // the term 
					'categories', // the taxonomy
					array(
						'description' => $name,
						'slug'        => strtolower($name),
					)
				);
				$json['check_taxonomy'][$term_id['term_id']] = $term_id['term_id'];
			} else {
				$update = wp_update_term($term['term_id'], 'categories', array(
					'name' => $name,
					'slug' => strtolower($name)
				));
				$json['check_taxonomy'][$term['term_id']] = $name;
			}
		}
		if ($sync_status == 1) {
			error_log(print_r($json, true), 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
			error_log('=========================', 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
		}

		return $json;
	}


	/*
	* Draft Products before add or update
	*/
	public function draft_products() {
		$product_args = array(
			'post_type'			=> 'product',
			'posts_per_page' 	=> -1,
		);

		$product_query = new WP_Query($product_args);
		if ($product_query->have_posts()) :
			while ($product_query->have_posts()) :
				$product_query->the_post();
				$post_status_draft = array('ID' => get_the_ID(), 'post_status' => 'draft');
				$res = wp_update_post($post_status_draft);
				update_post_meta(get_the_ID(), '_leafbridge_product_meta_menu_type', '');
			endwhile;
			wp_reset_postdata();
		else :
		endif;
	}




	/*
	* Syncing Products 
	* Custom Post Type: product
	*/

	public function sync_products($single_retailer_id = "") {

		$json = array();
		$leafbridge_settings 	= get_option('leafbridge-settings');
		$sync_settings			= $leafbridge_settings['leafbridge-sync-settings'];
		$sync_status			= $sync_settings['lb-sync-log-status'];

		if (strlen($single_retailer_id) > 0) {
			// ********************* GET RETAILERS DETAILS  ***********
			$args = array(
				'posts_per_page'   => -1,
				'post_type'        => 'retailer',
				'meta_key'         => '_lb_retailer_single_id',
				'meta_value'       => $single_retailer_id
			);
			$query = new WP_Query($args);

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$retailer_post_id 	= get_the_ID();
					$retailer_key_id = get_post_meta($retailer_post_id, '_lb_retailer_single_id', true);

					$product = new LeafBridge_Products();
					$res = $product->add_products_new($retailer_id = $retailer_key_id);
					$product_synced = array_unique($res);
					$json['product_sync'] = $product_synced;
				} // End while retailer

				if ($sync_status == 1) {
					error_log('=============== Products Synced: ' . date('Y-m-d H:i:s') . ' ========= ' . PHP_EOL, 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
					error_log(print_r($json, true), 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
				}
			} // End if query loop

			return $json;
		} else {

			// ********************* GET RETAILERS DETAILS  ***********
			$args = array(
				'posts_per_page'   => -1,
				'post_type'        => 'retailer'
			);
			$query = new WP_Query($args);

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$retailer_post_id 	= get_the_ID();
					$retailer_key_id = get_post_meta($retailer_post_id, '_lb_retailer_single_id', true);

					$product = new LeafBridge_Products();
					$res = $product->add_products_new($retailer_id = $retailer_key_id);
					$product_synced = array_unique($res);
					$json['product_sync'] = $product_synced;
				} // End while retailer

				//$this->clear_junk_products();
				if ($sync_status == 1) {
					error_log('=============== Products Synced: ' . date('Y-m-d H:i:s') . ' ========= ' . PHP_EOL, 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
					error_log(print_r($json, true), 3, WP_PLUGIN_DIR . "/leafbridge/includes/autosync.log");
				}
			} // End if query loop

			return $json;
		}
	}
}
