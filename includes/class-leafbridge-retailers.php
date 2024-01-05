<?php


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


class LeafBridge_Retailers {

	public static function register_retailers() {
		$instance = new self;
		add_action('init', [$instance, 'registerRetailers']);
		add_action('add_meta_boxes', [$instance, 'add_retailer_box']);
	}


	function registerRetailers() {
		register_post_type('retailer', [
			'description' 			=> 'Retailer',
			'show_ui' 				=> true,
			'publicly_queryable' 	=> true,
			'show_in_nav_menus' 	=> false,
			'show_in_menu' 			=> 'edit.php?post_type=retailer',
			// 'menu_position' 		=> 2, 
			//'menu_icon' 			=> 'dashicons-products',    
			'exclude_from_search'	=> true,
			'labels' 				=> array('name' => 'Retailer'),
			'public' 				=> false,
			'capability_type' 		=> 'post',
			'capabilities' 			=> array(
				'create_posts' => true, // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
			),
			'map_meta_cap' 			=> true, // Set to `false`, if users are not allowed to edit/delete existing posts
			'hierarchical' 			=> false,
			'rewrite' 				=> false,
			'has_archive' 			=> false
		]);
	}






	function add_retailer_box() {
		$instance = new self;
		$screens = ['retailer'];
		foreach ($screens as $screen) {
			add_meta_box(
				'leafbridge-retailers_meta',                 // Unique ID
				'Retailer Details',      // Box title
				[$instance, 'leafbridge_retailer_meta_box_html'],  // Content callback, must be of type callable
				$screen                            // Post type
			);
		}
	}

	public static function leafbridge_retailer_meta_box_html($post) {

		$_lb_retailer_options_all 	= get_post_meta($post->ID, '_lb_retailer_options_all', true);
		$_lb_retailer_single_id 	= get_post_meta($post->ID, '_lb_retailer_single_id', true);
		$_lb_retailer_menuTypes 	= get_post_meta($post->ID, '_lb_retailer_menuTypes', true);
		$_lb_retailer_custom_name 	= get_post_meta($post->ID, 'lb_retailer_custom_name', true);


		$_lb_retailer_options 		= ($_lb_retailer_options_all ? $_lb_retailer_options_all['_lb_retailer_options'] : '');
		$_lb_retailer_id 			= ($_lb_retailer_options_all ? $_lb_retailer_options_all['_lb_retailer_id'] : '');

		$rr = unserialize($_lb_retailer_options);
?>

		<div class="leafbridge_product_meta_options_group">
			<p class="leafbridge_product_meta_form-field">
				<label for="$_lb_retailer_id">ID</label>
				<span><?php echo ($_lb_retailer_id); ?><input type="hidden" value="<?php echo $_lb_retailer_single_id; ?>" name="_lb_retailer_single_id" /></span>
			</p>
			<p class="leafbridge_product_meta_form-field">
				<label for="_lb_retailer_custom_name">Custom Name</label>
				<span><?php echo ($_lb_retailer_custom_name); ?><input type="hidden" value="<?php echo $_lb_retailer_custom_name; ?>" name="_lb_retailer_custom_name" /></span>
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_lb_retailer_menuTypes">Menu Types</label>
				<span><input type="hidden" name="_lb_retailer_menuTypes" id="_lb_retailer_menuTypes" value="<?php print_r($_lb_retailer_menuTypes); ?>" /><?php print_r($_lb_retailer_menuTypes); ?></span>
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_lb_retailer_options">Address</label>
				<span><?php echo $rr['address']; ?></span>
			</p>
			<p class="leafbridge_product_meta_form-field">
				<label for="_lb_retailer_options">Phone</label>
				<span><?php echo $rr['phone']; ?></span>
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_lb_retailer_options">Fulfillment Options</label>
				<span>
					<?php
					foreach ($rr['fulfillmentOptions'] as $key => $val) {
						echo ucfirst($key) . ' - ' . ($val == 1 ? 'Yes' : 'No') . '<br/>';
					}
					?>
				</span>
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_lb_retailer_options">Settings</label>
				<span>
					<?php
					foreach ($rr['settings'] as $key => $val) {
						echo ucfirst($key) . ' - ' . ($val) . '<br/>';
					}
					?>
				</span>
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_lb_retailer_options">Coordinates </label>
				<span>
					<?php
					echo '<a href="https://www.google.com/maps?q=' . $rr['coordinates']['latitude'] . ',' . $rr['coordinates']['longitude'] . '" target="_blank">Google map</a>';
					?>
				</span>
			</p>


			<p class="leafbridge_product_meta_form-field">
				<label for="_lb_retailer_options">Payment Options</label>
				<span>
					<?php
					foreach ($rr['paymentOptions'] as $key => $val) {
						echo ucfirst($key) . ' - ' . ($val == true ? 'Yes' : 'No') . '<br/>';
					}
					?>
				</span>
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_lb_retailer_options">All Details</label>
				<span>
					<?php
					echo '<pre>';
					print_r($rr);
					echo '</pre>';
					?>
				</span>
			</p>

			<?php
			/*echo '<p style="display:none"><pre>';
		print_r($rr);
		echo '</pre></p>';*/
			?>

		</div>

<?php
	}



	/**
	 * This function is for return all retailers details.
	 *
	 * An instance of this class should be passed to the run() function
	 * defined in LeafBridge_Loader as all of the hooks are defined
	 * in that particular class.
	 *
	 * The LeafBridge_Loader will then create the relationship
	 * between the defined hooks and the functions defined in this
	 * class.
	 */

	public function get_retailers_details($type) {
		$leafbridge_settings = get_option('leafbridge-settings');
		$api_key = $leafbridge_settings['leafbridge-settings-api-key'];

		$client = new Client(
			'https://plus.dutchie.com/plus/2021-07/graphql',
			['Authorization' => 'Bearer ' . $api_key]
		);
		if ($client) {

			if ($type == 'basic') {
				$gql = (new Query('retailers'))
					->setSelectionSet(
						[
							'id',
							'name',
							'address',
							'phone',
							'menuTypes',
							(new Query('fulfillmentOptions'))
								->setSelectionSet(
									[
										'curbsidePickup',
										'delivery',
										'driveThruPickup',
										'pickup',
									]
								)
						]
					);
			} else {

				$gql = (new Query('retailers'))
					->setSelectionSet(
						[
							'id',
							'name',
							'address',
							'phone',
							'menuTypes',
							(new Query('addressObject'))
								->setSelectionSet(
									[
										'line1',
										'line2',
										'city',
										'postalCode',
										'state',
										'country'
									]
								),
							(new Query('coordinates'))
								->setSelectionSet(
									[
										'latitude',
										'longitude',
									]
								),
							(new Query('fulfillmentOptions'))
								->setSelectionSet(
									[
										'curbsidePickup',
										'delivery',
										'driveThruPickup',
										'pickup',
									]
								),
							(new Query('settings'))
								->setSelectionSet(
									[
										'menuWeights',
									]
								),
							(new Query('paymentOptions'))
								->setSelectionSet(
									[
										'aeropay',
										'alt36',
										'canPay',
										'cashless',
										'cashOnly',
										'check',
										'creditCard',
										'creditCardAtDoor',
										'creditCardByPhone',
										'debitOnly',
										'hypur',
										'linx',
										'merrco',
										'payInStore',
										'paytender',
									]
								),
							(new Query('hours'))
								->setSelectionSet(
									[
										(new Query('delivery'))
											->setSelectionSet(
												[
													(new Query('Sunday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Monday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Tuesday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Wednesday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Thursday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Friday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Saturday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														)
												]
											),
										(new Query('pickup'))
											->setSelectionSet(
												[
													(new Query('Sunday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Monday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Tuesday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Wednesday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Thursday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Friday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Saturday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														)
												]
											),
										(new Query('special'))
											->setSelectionSet(
												[
													'startDate',
													'endDate',
													(new Query('hoursPerDay'))
														->setSelectionSet(
															[
																(new Query('deliveryHours'))
																	->setSelectionSet(
																		[
																			'active',
																			'start',
																			'end',
																		]
																	)
															]
														),
													(new Query('hoursPerDay'))
														->setSelectionSet(
															[
																(new Query('pickupHours'))
																	->setSelectionSet(
																		[
																			'active',
																			'start',
																			'end',
																		]
																	)
															]
														),
													'name'
												]
											)

									]
								),
						]
					);
			} // end advanced query if condition


			$results = '';
			$error_message  = '';
			$reailer_details = '';
			// Run query to get results
			try {
				$results = $client->runQuery($gql);

				if (isset($results)) {
					return $results;
				} else {
					return __('No retailers for the given API key.', 'leafbridge');
				}
			} catch (QueryError $exception) {
				return $error_message = $exception->getErrorDetails()['message'];
			}
		} else {
			return __('Invalid API Key', 'leafbridge');
		}
	}


	/**
	 * This function is for return specific retailer details.
	 *
	 * An instance of this class should be passed to the run() function
	 * defined in LeafBridge_Loader as all of the hooks are defined
	 * in that particular class.
	 *
	 * The LeafBridge_Loader will then create the relationship
	 * between the defined hooks and the functions defined in this
	 * class.
	 */

	public function get_retailer($retailer_id_array) {
		$leafbridge_settings = get_option('leafbridge-settings');
		$api_key = $leafbridge_settings['leafbridge-settings-api-key'];

		$client = new Client(
			'https://plus.dutchie.com/plus/2021-07/graphql',
			['Authorization' => 'Bearer ' . $api_key]
		);
		if ($client) {
			$gql = (new Query('retailers'))
				->setSelectionSet(
					[
						'id',
						'name',
						'address',
						'phone',
						'menuTypes',
						(new Query('addressObject'))
							->setSelectionSet(
								[
									'line1',
									'line2',
									'city',
									'postalCode',
									'state',
									'country'
								]
							),
						(new Query('fulfillmentOptions'))
							->setSelectionSet(
								[
									'curbsidePickup',
									'delivery',
									'driveThruPickup',
									'pickup',
								]
							),
						(new Query('settings'))
							->setSelectionSet(
								[
									'menuWeights',
								]
							),
						(new Query('paymentOptions'))
							->setSelectionSet(
								[
									'aeropay',
									'alt36',
									'canPay',
									'cashless',
									'cashOnly',
									'check',
									'creditCard',
									'creditCardAtDoor',
									'creditCardByPhone',
									'debitOnly',
									'hypur',
									'linx',
									'merrco',
									'payInStore',
									'paytender',
								]
							),
						(new Query('hours'))
							->setSelectionSet(
								[
									(new Query('delivery'))
										->setSelectionSet(
											[
												(new Query('Sunday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Monday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Tuesday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Wednesday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Thursday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Friday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Saturday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													)
											]
										),
									(new Query('pickup'))
										->setSelectionSet(
											[
												(new Query('Sunday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Monday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Tuesday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Wednesday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Thursday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Friday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													),
												(new Query('Saturday'))
													->setSelectionSet(
														[
															'active',
															'start',
															'end',
														]
													)
											]
										),
									(new Query('special'))
										->setSelectionSet(
											[
												'startDate',
												'endDate',
												(new Query('hoursPerDay'))
													->setSelectionSet(
														[
															(new Query('deliveryHours'))
																->setSelectionSet(
																	[
																		'active',
																		'start',
																		'end',
																	]
																)
														]
													),
												(new Query('hoursPerDay'))
													->setSelectionSet(
														[
															(new Query('pickupHours'))
																->setSelectionSet(
																	[
																		'active',
																		'start',
																		'end',
																	]
																)
														]
													),
												'name'
											]
										)

								]
							),
					]
				);
			//echo $gql;
			$results = '';
			$error_message  = '';
			$reailer_details = '';
			// Run query to get results
			try {
				$results = $client->runQuery($gql);
				//var_dump($results); 
				if (isset($results)) {
					return $results;
				} else {
					return 'No retailers for the given API key.';
				}
			} catch (QueryError $exception) {
				return $error_message = $exception->getErrorDetails()['message'];
			}
		} else {
			return __('Invalid API Key', 'leafbridge');
		}
	}




	/**
	 * This function is for return specific retailer details.
	 *
	 * An instance of this class should be passed to the run() function
	 * defined in LeafBridge_Loader as all of the hooks are defined
	 * in that particular class.
	 *
	 * The LeafBridge_Loader will then create the relationship
	 * between the defined hooks and the functions defined in this
	 * class.
	 */

	public function save_retailers($retailer_id_array) {
		$leafbridge_settings = get_option('leafbridge-settings');
		$api_key = $leafbridge_settings['leafbridge-settings-api-key'];
		$previousRetailerids = array();

		$args = array(
			'post_type' => 'retailer',
			'post_status' => 'publish'
		);
		$retailers = new WP_Query($args);

		if ($retailers->have_posts()) {
			while ($retailers->have_posts()) {
				$retailers->the_post();
				array_push($previousRetailerids, get_post_meta(get_the_ID(), '_lb_retailer_single_id'));
			}
			wp_reset_postdata();
		}

		$retailers_obj = $this->get_retailers_details('advanced');

		if (isset($retailers_obj)) {
			$retailers_obj->reformatResults(true);
			$dutchie_store_retailers = $retailers_obj->getData()['retailers'];

			foreach ($dutchie_store_retailers as $retailer) {
				$retailerPoints[] = array(
					"retailer_id"   => $retailer['id'],
					"retailer_data" => $retailer
				);
			}

			$response = '';
			//$leafbridge_retailer_sync = get_option('leafbridge-retailer-sync-settings');
			// Delete all retailers
			if (!empty($previousRetailerids)) {

				if (!empty(array_intersect($previousRetailerids[0], $retailer_id_array))) {
					$allretailers = get_posts(array('post_type' => 'retailer', 'numberposts' => -1));
					foreach ($allretailers as $eachretailer) {
						wp_delete_post($eachretailer->ID, true);
					}
				}
			}

			//$leafbridge_retailer_sync_settings = array();			

			// Save retailers
			foreach ($retailerPoints as $retailer_info) {  //echo $retailer;
				$ret = $retailer_info['retailer_data'];
				if (in_array($retailer_info['retailer_id'], $retailer_id_array)) {
					$post_id = wp_insert_post(array(
						'post_type' => 'retailer',
						'post_title' => $ret['name'],
						'post_content' => $ret['name'],
						'post_status' => 'publish',
						'comment_status' => 'closed',
						'ping_status' => 'closed',
					));


					$postmeta_array = array();


					if ($post_id) {

						// insert post meta
						$menutype = '';
						foreach ($ret['menuTypes'] as $x) {
							$menutype .= $x . ', ';
						}
						$postmeta_array = array(
							'_lb_retailer_id' => $ret['id'],
							'_lb_retailer_menuTypes' => $menutype,
							'_lb_retailer_options' => serialize($ret)
						);

						add_post_meta($post_id, '_lb_retailer_options_all', $postmeta_array);
						add_post_meta($post_id, '_lb_retailer_single_id', $ret['id']);
						add_post_meta($post_id, '_lb_retailer_menuTypes', $menutype);

						$response = __('Retails Details Saved!', 'leafbridge');
					}
				}
			}
		}

		if (!empty($previousRetailerids)) {
			if (!empty(array_intersect($previousRetailerids[0], $retailer_id_array))) {
				// Draft all products
				$product_args = array(
					'post_type'			=> 'product',
					'posts_per_page' 	=> -1,
				);

				$product_query = new WP_Query($product_args);
				if ($product_query->have_posts()) :
					while ($product_query->have_posts()) :
						set_time_limit(1800);
						$product_query->the_post();
						$post_status_draft = array(
							'ID' 				=> get_the_ID(),
							'post_status' 		=> 'draft'
						);
						$res = wp_update_post($post_status_draft);
					endwhile;
					wp_reset_postdata();
				else :
				endif;
			}
		}


		return __('Success', 'leafbridge');
	}



	/**
	 * Delete all Draft Products
	 */

	function clear_junk_products() {
		$allposts = get_posts(array(
			'post_type'			=>	'product',
			'numberposts'		=>	-1,
			'post_status' 		=> 'draft',
		));
		if (isset($allposts)) {
			foreach ($allposts as $key => $eachpost) {
				wp_delete_post($eachpost->ID, true);
			}
		} else {
		}
		return  __('Success', 'leafbridge');
	}


	/**
	 * This function is for return all retailers details.
	 *
	 * An instance of this class should be passed to the run() function
	 * defined in LeafBridge_Loader as all of the hooks are defined
	 * in that particular class.
	 *
	 * The LeafBridge_Loader will then create the relationship
	 * between the defined hooks and the functions defined in this
	 * class.
	 */

	public function get_retailer_details($retailer_id = NULL, $type = NULL) {
		$leafbridge_settings = get_option('leafbridge-settings');
		$api_key = $leafbridge_settings['leafbridge-settings-api-key'];

		$client = new Client(
			'https://plus.dutchie.com/plus/2021-07/graphql',
			['Authorization' => 'Bearer ' . $api_key]
		);
		if ($client) {

			if ($type == 'basic') {
				$gql = (new Query('retailer'))
					->setArguments([
						'id' => $retailer_id
					])
					->setSelectionSet(
						[
							'id',
							'name',
							'address',
							'phone',
							'menuTypes',
						]
					);
			} else {

				$gql = (new Query('retailer'))
					->setArguments([
						'id' => $retailer_id
					])
					->setSelectionSet(
						[
							'id',
							'name',
							'address',
							'phone',
							'menuTypes',
							(new Query('addressObject'))
								->setSelectionSet(
									[
										'line1',
										'line2',
										'city',
										'postalCode',
										'state',
										'country'
									]
								),
							(new Query('coordinates'))
								->setSelectionSet(
									[
										'latitude',
										'longitude',
									]
								),
							(new Query('fulfillmentOptions'))
								->setSelectionSet(
									[
										'curbsidePickup',
										'delivery',
										'driveThruPickup',
										'pickup',
									]
								),
							(new Query('settings'))
								->setSelectionSet(
									[
										'menuWeights',
									]
								),
							(new Query('paymentOptions'))
								->setSelectionSet(
									[
										'aeropay',
										'alt36',
										'canPay',
										'cashless',
										'cashOnly',
										'check',
										'creditCard',
										'creditCardAtDoor',
										'creditCardByPhone',
										'debitOnly',
										'hypur',
										'linx',
										'merrco',
										'payInStore',
										'paytender',
									]
								),
							(new Query('hours'))
								->setSelectionSet(
									[
										(new Query('delivery'))
											->setSelectionSet(
												[
													(new Query('Sunday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Monday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Tuesday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Wednesday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Thursday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Friday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Saturday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														)
												]
											),
										(new Query('pickup'))
											->setSelectionSet(
												[
													(new Query('Sunday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Monday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Tuesday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Wednesday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Thursday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Friday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														),
													(new Query('Saturday'))
														->setSelectionSet(
															[
																'active',
																'start',
																'end',
															]
														)
												]
											),
										(new Query('special'))
											->setSelectionSet(
												[
													'startDate',
													'endDate',
													(new Query('hoursPerDay'))
														->setSelectionSet(
															[
																(new Query('deliveryHours'))
																	->setSelectionSet(
																		[
																			'active',
																			'start',
																			'end',
																		]
																	)
															]
														),
													(new Query('hoursPerDay'))
														->setSelectionSet(
															[
																(new Query('pickupHours'))
																	->setSelectionSet(
																		[
																			'active',
																			'start',
																			'end',
																		]
																	)
															]
														),
													'name'
												]
											)

									]
								),
						]
					);
			} // end advanced query if condition



			//echo $gql;
			$results = '';
			$error_message  = '';
			$reailer_details = '';
			// Run query to get results
			try {
				$results = $client->runQuery($gql);
				$results->reformatResults(true);
				$retailer = $results->getData()['retailer'];
				// var_dump($results); 
				if (isset($retailer)) {
					return $retailer;
				} else {
					return __('No retailers for the given API key.', 'leafbridge');
				}
			} catch (QueryError $exception) {
				return $error_message = $exception->getErrorDetails()['message'];
			}
		} else {
			return __('Invalid API Key', 'leafbridge');
		}
	}


	function get_retailer_list_db() {
		$retailer_array = array();
		$args = array(
			'post_type' => 'retailer'
		);
		$query = new WP_Query($args);

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$_lb_retailer_single_id = get_post_meta(get_the_ID(), '_lb_retailer_single_id', true);
				if ($_lb_retailer_single_id != '') {
					$retailer_array[$_lb_retailer_single_id] = get_the_title();
				}
			}
		}
		return $retailer_array;
	}
}
?>