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
class LeafBridge_Public_Cart
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
		add_shortcode('leafbridge_cart', array( $this , 'leafbridge_cart_test' ) );


	}*/



	/*
	* Calling to functions
	*
	*/
	public function leafbridge_cart_test()
	{

		//echo 'cart call';
		// $res1 = self::leafbridge_create_checkout($retailerId="f0ff5c46-2f0c-4137-941b-b79b71e1d85c", $orderType="PICKUP", $pricingType="MEDICAL");
		// $res2 = self::leafbridge_addItemToCheckout($retailerId="f0ff5c46-2f0c-4137-941b-b79b71e1d85c", $checkoutId="407128da-9062-4232-8d58-0803e55c1dac", $productId="62a8e84446c02b0001426ccd", $quantity=2, $options="1g");
		// $res3 = self::leafbridge_removeItemFromCheckout($retailerId="f0ff5c46-2f0c-4137-941b-b79b71e1d85c", $checkoutId="407128da-9062-4232-8d58-0803e55c1dac", $itemId="070138ee-8c20-42ed-9d99-d675b894406d");


		// var_dump($res3);
	}



	/*
	* 	Cart function for front-end
	*
	*	Parameters:
	*	address: CheckoutAddressInput
	*   retailerId: "f0ff5c46-2f0c-4137-941b-b79b71e1d85c",
	*   orderType: "PICKUP",
	*   pricingType: "MEDICAL"
	*   metadata: JSON  :- optional

	*   Returns the cart details as an array
	*/
	public function leafbridge_create_checkout($retailerId = NULL, $orderType = NULL, $pricingType = NULL)
	{

		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		if ($client) {

			$gql = (new Mutation('createCheckout'))
				->setArguments([
					'retailerId' => $retailerId,
					'orderType'   => new RawObject($orderType),
					'pricingType' => new RawObject($pricingType),
					'metadata' =>  new RawObject('{ retailerId:"' . $retailerId . '" }'),
				])
				->setSelectionSet(
					[
						'id',
						'orderType',
						'pricingType',
						'redirectUrl',
						'updatedAt',
						'createdAt',
						(new Query('items'))
							->setSelectionSet(
								[
									'id',
									'errors',
									'option',
									(new Query('product'))
										->setSelectionSet(
											[
												(new Query('brand'))
													->setSelectionSet(
														[
															'description',
															'id',
															'imageUrl',
															'name',
														]
													),
												'category',
												'description',
												'descriptionHtml',
												'effects',
												'id',
												'productBatchId',
												'image',
												'name',
												'posId',
											]
										),
									'productId',
									'quantity',
									'valid',
									'isDiscounted',
									'basePrice',
								]
							),
						(new Query('priceSummary'))
							->setSelectionSet(
								[
									'discounts',
									'fees',
									'mixAndMatch',
									'rewards',
									'subtotal',
									'taxes',
									'total',
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
				$checkout = $results->getData()['createCheckout'];
				//echo 'Checkout: <pre>';print_r($checkout);  echo '</pre><hr/>';
				if (isset($results)) {
					return $checkout;
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



	/*
	*   Get Cart details for front-end

	*   Parameters:
	*   $retailerId = "f0ff5c46-2f0c-4137-941b-b79b71e1d85c",
	*   $checkoutId = "e70ca50d-f36d-4953-90b9-9f06261c78f1" 

	*   Returns the cart details as an array
	*/
	public function leafbridge_getCartDetails($retailerId = NULL, $checkoutId = NULL)
	{

		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		if ($client) {

			$gql = (new Query('checkout'))
				->setArguments(['retailerId' => $retailerId, 'id' => $checkoutId])
				->setSelectionSet(
					[
						'id',
						'orderType',
						'pricingType',
						'redirectUrl',
						'updatedAt',
						'createdAt',
						(new Query('items'))
							->setSelectionSet(
								[
									'id',
									'errors',
									'option',
									(new Query('product'))
										->setSelectionSet(
											[
												(new Query('brand'))
													->setSelectionSet(
														[
															'description',
															'id',
															'imageUrl',
															'name',
														]
													),
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
												'subcategory',
												'strainType',
												(new Query('potencyThc'))
													->setSelectionSet(
														[
															'formatted',
															'range',
															'unit'
														]
													),
												(new Query('potencyCbd'))
													->setSelectionSet(
														[
															'formatted',
															'range',
															'unit'
														]
													),
												'description',
												'descriptionHtml',
												'effects',
												'id',
												'productBatchId',
												'image',
												'name',
												'posId',
											]
										),
									'productId',
									'quantity',
									'valid',
									'isDiscounted',
									'basePrice',
								]
							),
						(new Query('priceSummary'))
							->setSelectionSet(
								[
									'discounts',
									'fees',
									'mixAndMatch',
									'rewards',
									'subtotal',
									'taxes',
									'total',
								]
							)
					]
				);
			// end advanced query if condition



			//echo $gql;
			$results = '';
			$error_message  = '';
			$reailer_details = '';
			// Run query to get results
			try {
				$results = $client->runQuery($gql);
				$results->reformatResults(true);
				$cart = $results->getData()['checkout'];
				//echo 'Update Quantity from cart: <pre>';print_r($checkout);  echo '</pre><hr/>';
				if (isset($results)) {
					return $cart;
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




	/*
	*   Add items to cart for front-end

	*   Parameters:
	*   $retailerId	=  "f0ff5c46-2f0c-4137-941b-b79b71e1d85c",
	*   $checkoutId	=  "e70ca50d-f36d-4953-90b9-9f06261c78f1",
	*   $productId	=  "62a8e84446c02b0001426d85",
	*   $quantity	=  2,
	*   $options	=  "1g"

	*   Returns the cart details as an array
	*/


	public function leafbridge_addItemToCheckout($retailerId = NULL, $checkoutId = NULL, $productId = NULL, $quantity = NULL, $options = NULL)
	{

		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		if ($client) {

			$gql = (new Mutation('addItem'))
				->setArguments([
					'retailerId' => $retailerId,
					'checkoutId'   => $checkoutId,
					'productId' => $productId,
					'quantity' => new RawObject($quantity),
					'option' => $options
				])
				->setSelectionSet(
					[
						'id',
						'orderType',
						'pricingType',
						'redirectUrl',
						'updatedAt',
						'createdAt',
						(new Query('items'))
							->setSelectionSet(
								[
									'id',
									'errors',
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
												'subcategory',
												'strainType',
												(new Query('potencyThc'))
													->setSelectionSet(
														[
															'formatted',
															'range',
															'unit'
														]
													),
												(new Query('potencyCbd'))
													->setSelectionSet(
														[
															'formatted',
															'range',
															'unit'
														]
													),
												'description',
												'descriptionHtml',
												'effects',
												'id',
												'productBatchId',
												'image',
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
											]
										),
									'productId',
									'quantity',
									'valid',
									'isDiscounted',
									'basePrice',
								]
							),
						(new Query('priceSummary'))
							->setSelectionSet(
								[
									'discounts',
									'fees',
									'mixAndMatch',
									'rewards',
									'subtotal',
									'taxes',
									'total',
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
				$checkout = $results->getData()['addItem'];
				//echo 'Add to cart: <pre>';print_r($checkout);  echo '</pre><hr/>';
				if (isset($results)) {
					return $checkout;
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


	/*
	*   Remove items from cart for front-end

	*   Parameters:
	*   $retailerId = "f0ff5c46-2f0c-4137-941b-b79b71e1d85c",
	*   $checkoutId = "e70ca50d-f36d-4953-90b9-9f06261c78f1",
	*   $itemId 	= "b833e74e-2d78-4409-877d-886f00e3cf39" 	 // this is not the product id, this will return from add to cart : items para

	*   Returns the cart details as an array
	*/
	public function leafbridge_removeItemFromCheckout($retailerId = NULL, $checkoutId = NULL, $itemId = NULL)
	{

		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		if ($client) {

			$gql = (new Mutation('removeItem'))
				->setArguments([
					'retailerId' => $retailerId,
					'checkoutId'   => $checkoutId,
					'itemId' => $itemId
				])
				->setSelectionSet(
					[
						'id',
						'orderType',
						'pricingType',
						'redirectUrl',
						'updatedAt',
						'createdAt',
						(new Query('items'))
							->setSelectionSet(
								[
									'id',
									'errors',
									'option',
									(new Query('product'))
										->setSelectionSet(
											[
												(new Query('brand'))
													->setSelectionSet(
														[
															'description',
															'id',
															'imageUrl',
															'name',
														]
													),
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
												'subcategory',
												'strainType',
												(new Query('potencyThc'))
													->setSelectionSet(
														[
															'formatted',
															'range',
															'unit'
														]
													),
												(new Query('potencyCbd'))
													->setSelectionSet(
														[
															'formatted',
															'range',
															'unit'
														]
													),
												'description',
												'descriptionHtml',
												'effects',
												'id',
												'productBatchId',
												'image',
												'name',
												'posId',
											]
										),
									'productId',
									'quantity',
									'valid',
									'isDiscounted',
									'basePrice',
								]
							),
						(new Query('priceSummary'))
							->setSelectionSet(
								[
									'discounts',
									'fees',
									'mixAndMatch',
									'rewards',
									'subtotal',
									'taxes',
									'total',
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
				$checkout = $results->getData()['removeItem'];
				//echo 'Remove from cart: <pre>';print_r($checkout);  echo '</pre><hr/>';
				if (isset($results)) {
					return $checkout;
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







	/*
	*   Update Quantity from cart for front-end

	*   Parameters:
	*   $retailerId = "f0ff5c46-2f0c-4137-941b-b79b71e1d85c",
	*   $checkoutId = "e70ca50d-f36d-4953-90b9-9f06261c78f1",
	*   $itemId 	= "b833e74e-2d78-4409-877d-886f00e3cf39" 	 // this is not the product id, this will return from add to cart : items para
	*   $quantity   = 5 // integer

	*   Returns the cart details as an array
	*/
	public function leafbridge_updateQuantity($retailerId = NULL, $checkoutId = NULL, $itemId = NULL, $quantity = NULL)
	{

		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		if ($client) {

			$gql = (new Mutation('updateQuantity'))
				->setArguments([
					'retailerId' => $retailerId,
					'checkoutId'   => $checkoutId,
					'itemId' => $itemId,
					'quantity' => $quantity
				])
				->setSelectionSet(
					[
						'id',
						'orderType',
						'pricingType',
						'redirectUrl',
						'updatedAt',
						'createdAt',
						(new Query('items'))
							->setSelectionSet(
								[
									'id',
									'errors',
									'option',
									(new Query('product'))
										->setSelectionSet(
											[
												(new Query('brand'))
													->setSelectionSet(
														[
															'description',
															'id',
															'imageUrl',
															'name',
														]
													),
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
												'subcategory',
												'strainType',
												(new Query('potencyThc'))
													->setSelectionSet(
														[
															'formatted',
															'range',
															'unit'
														]
													),
												(new Query('potencyCbd'))
													->setSelectionSet(
														[
															'formatted',
															'range',
															'unit'
														]
													),
												'description',
												'descriptionHtml',
												'effects',
												'id',
												'productBatchId',
												'image',
												'name',
												'posId',
											]
										),
									'productId',
									'quantity',
									'valid',
									'isDiscounted',
									'basePrice',
								]
							),
						(new Query('priceSummary'))
							->setSelectionSet(
								[
									'discounts',
									'fees',
									'mixAndMatch',
									'rewards',
									'subtotal',
									'taxes',
									'total',
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
				$checkout = $results->getData()['updateQuantity'];
				//echo 'Update Quantity from cart: <pre>';print_r($checkout);  echo '</pre><hr/>';
				if (isset($results)) {
					return $checkout;
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






	/*
	*   Update Checkout from cart for front-end

	*   Parameters:
	*   $retailerId = "f0ff5c46-2f0c-4137-941b-b79b71e1d85c",
	*   $checkoutId = "e70ca50d-f36d-4953-90b9-9f06261c78f1",
	*   $itemId 	= "b833e74e-2d78-4409-877d-886f00e3cf39" 	 // this is not the product id, this will return from add to cart : items para

	*   Returns the cart details as an array
	*/
	public function leafbridge_updateCheckout($retailerId = NULL, $checkoutId = NULL, $itemId = NULL, $pricingType = NULL, $address = NULL, $orderType = NULL)
	{

		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		if ($client) {

			$gql = (new Mutation('updateCheckout'))
				->setArguments([
					'retailerId' => $retailerId,
					'checkoutId'   => $checkoutId,
					'itemId' => $itemId,
					'quantity' => $quantity
				])
				->setSelectionSet(
					[
						'id',
						'orderType',
						'pricingType',
						'redirectUrl',
						'updatedAt',
						'createdAt',
						(new Query('items'))
							->setSelectionSet(
								[
									'id',
									'errors',
									'option',
									(new Query('product'))
										->setSelectionSet(
											[
												(new Query('brand'))
													->setSelectionSet(
														[
															'description',
															'id',
															'imageUrl',
															'name',
														]
													),
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
												'name',
												'posId',
											]
										),
									'productId',
									'quantity',
									'valid',
									'isDiscounted',
									'basePrice',
								]
							),
						(new Query('priceSummary'))
							->setSelectionSet(
								[
									'discounts',
									'fees',
									'mixAndMatch',
									'rewards',
									'subtotal',
									'taxes',
									'total',
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
				$checkout = $results->getData()['updateCheckout'];
				// echo 'Update Quantity from cart: <pre>';print_r($checkout);  echo '</pre><hr/>';
				if (isset($results)) {
					return $checkout;
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
