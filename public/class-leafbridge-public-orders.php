<?php

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


/**
 * The public-facing functionality of the plugin cart.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    LeafBridge
 * @subpackage LeafBridge/includes
 * @author     Surge <websites@surge.global>
 */
class LeafBridge_Public_Orders
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
	/*public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		 

	}*/





	/*
	* 	Orders function for front-end
	*
	*	Parameters: 
	*   retailerId: "f0ff5c46-2f0c-4137-941b-b79b71e1d85c", 
	*   orderId: "123456789" 

	*   Returns the cart details as an array
	*/
	public function leafbridge_get_orders($retailerId = NULL, $orderId = NULL)
	{

		$leafbridge_settings = get_option('leafbridge-settings');
		$leafbridge_license  = get_option('leafbridge-license-data');
		$api_secret_key      = $leafbridge_settings['leafbridge-settings-api-secret-key'];
		$store_actn          = $leafbridge_license['actn'];

		$client = new Client(
			'https://api.leafbridge-proxy.click/graphql',
			[
				'Authorization' => 'Bearer ' . $api_secret_key,
				'Proxy-Authorization-lb' => $store_actn,
				'headers'  => array('Nonce' => '"' . md5(uniqid(mt_rand() . time(), true)) . '"'),
			]
		);


		$filter     = '{ orderNumber: "' . $orderId . '" }';

		if ($client) {

			$gql = (new Query('orders'))
				->setArguments([
					'retailerId' => $retailerId,
					'filter'     => new RawObject($filter),
				])
				->setSelectionSet(
					[
						'id',
						'orderNumber',
						'customerId',
						(new Query('customer'))
							->setSelectionSet(
								[
									'birthdate',
									'email',
									'guest',
									'id',
									'phone',
									'name',
									(new Query('medicalCard'))
										->setSelectionSet(
											[
												'expirationDate',
												'number',
												'photo',
												'state',
											]
										),
									(new Query('optIns'))
										->setSelectionSet(
											[
												'marketing',
												'orderStatus',
												'specials',
											]
										)
								]
							),
						'metadata',
						'createdAt',
						'pickup',
						'status',
						'subtotal',
						'tax',
						'total',
						(new Query('items'))
							->setSelectionSet(
								[
									'option',
									(new Query('product'))
										->setSelectionSet(
											[
												(new Query('variants'))
													->setSelectionSet(
														[
															'id',
															'option',
															'priceMed',
															'priceRec',
															'specialPriceMed',
															'specialPriceRec',
															'quantity'
														]
													),
												'category',
												'description',
												'descriptionHtml',
												'effects',
												'id',
												'productBatchId',
												'image',
												'strainType',
												'subcategory',
												'name',
												'posId',
												(new Query('brand'))
													->setSelectionSet(
														[
															'description',
															'id',
															'imageUrl',
															'name',
														]
													),
												(new Query('potencyCbd'))
													->setSelectionSet(
														[
															'formatted',
															'range',
															'unit',
														]
													),
												(new Query('potencyThc'))
													->setSelectionSet(
														[
															'formatted',
															'range',
															'unit',
														]
													)
											]
										),
									'price',
									'productId',
									'quantity'
								]
							)
					]
				); // end advanced query if condition



			//echo $gql;
			$results = '';
			$error_message  = '';
			$reailer_details = '';
			// Run query to get results
			try {
				$results = $client->runQuery($gql);
				$results->reformatResults(true);
				$order_details = $results->getData()['orders'];
				//echo 'order: <pre>';print_r($order_details);  echo '</pre><hr/>';
				if (isset($results)) {
					return $order_details;
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
} // end of class
