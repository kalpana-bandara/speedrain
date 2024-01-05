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


class LeafBridge_PublicRetailers
{


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

	public function get_retailers_details($type)
	{
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();

		if ($client) {

			if ($type == 'basic') {
				$gql = (new Query('retailers'))
					->setSelectionSet(
						[
							'id',
							'name',
							'address',
							'menuTypes',
						]
					);
			} else {

				$gql = (new Query('retailers'))
					->setSelectionSet(
						[
							'id',
							'name',
							'address',
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
							(new Query('coordinates'))
								->setSelectionSet(
									[
										'latitude',
										'longitude',
									]
								),
							(new Query('settings'))
								->setSelectionSet(
									[
										'menuWeights',
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
				//$results->reformatResults(true);
				//$products = $results->getData()['menu']['products']; 

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

	public function get_retailer($retailerId = NULL)
	{
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();

		if ($client) {
			$gql = (new Query('retailer'))
				->setArguments([
					'retailerId' => $retailerId
				])
				->setSelectionSet(
					[
						'id',
						'name',
						'address',
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
						(new Query('deliverySettings'))
							->setSelectionSet(
								[
									'afterHoursOrderingForDelivery',
									'afterHoursOrderingForPickup',
									'deliveryArea',
									'deliveryFee',
									'deliveryMinimum',
									'disablePurchaseLimits',
									'limitPerCustomer',
									'pickupMinimum',
									'scheduledOrderingForDelivery',
									'scheduledOrderingForPickup'
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
									'paytender'
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
}
