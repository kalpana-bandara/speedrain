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


class LeafBridge_Public_Products
{


	//****************************************************
	/*
			Return products as an array
		*/


	public function fetch_products($retailer_id = NULL)
	{
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		$retailerId = $retailer_id;

		if ($client) {
			$gql = (new Query('menu'))
				->setArguments([
					'retailerId' => $retailerId,
					/*'menuType' => new RawObject('RECREATIONAL'),*/
					'pagination' => new RawObject('{limit: 10000, offset: 0}')
				])
				->setSelectionSet(
					[
						(new Query('products'))
							->setSelectionSet(
								[
									'id',
									'name',
									'image',
									'description',
									'category',
									'strainType',
									'slug',
									'staffPick',
									'effects',
									'productBatchId',
									'posId',
									'subcategory',
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
									(new Query('brand'))
										->setSelectionSet(
											[
												'name',
												'description',
												'id',
												'imageUrl'
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
									(new Query('potencyThc'))
										->setSelectionSet(
											[
												'formatted',
												'range',
												'unit'
											]
										),
									(new Query('terpenes'))
										->setSelectionSet(
											[
												'id',
												'name',
												'terpeneId',
												'unit',
												'unitSymbol',
												'value',
												(new Query('terpenes'))
													->setSelectionSet(
														[
															'aliasList',
															'aromas',
															'description',
															'effects',
															'id',
															'name',
															'potentialHealthBenefits',
															'unitSymbol'
														]
													)
											]
										),
									(new Query('cannabinoids'))
										->setSelectionSet(
											[
												'cannabinoidId',
												'unit',
												'value',
												(new Query('cannabinoid'))
													->setSelectionSet(
														[
															'description',
															'id',
															'name'
														]
													)
											]
										)
								]
							)
					]
				);



			// Run query to get results
			try {
				$results = $client->runQuery($gql);
				$results->reformatResults(true);
				$products = $results->getData()['menu']['products'];
				return $products;
			} catch (QueryError $exception) {
				return $error_message = $exception->getErrorDetails()['message'];
			}
		} else {
			return __('Invalid API Key', 'leafbridge');
		}
	}





	//****************************************************
	/*
			Get retailer wise all products from API
		*/

	public function fetch_retailer_products($retailerId = NULL, $menutype = NULL, $pagination = NULL, $filter = NULL, $sort = NULL)
	{
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		/*$menutype   = 'RECREATIONAL';
			$pagination = "{ limit: 1 offset: 0 }";
			$filter     = "{ category: FLOWER }";
			$sort       = "{ direction: ASC key: POPULAR }";*/

		//echo $var_pagination." }"; 


		if ($client) {

			$gql = (new Query('menu'))
				->setArguments([
					'retailerId' => $retailerId,
					'menuType'   => new RawObject($menutype),
					'pagination' => new RawObject($pagination),
					'filter'     => new RawObject($filter),
					'sort'       => new RawObject($sort),
				])
				->setSelectionSet(
					[
						(new Query('products'))
							->setSelectionSet(
								[
									'id',
									'name',
									'image',
									'description',
									'category',
									'strainType',
									'slug',
									'staffPick',
									'effects',
									'productBatchId',
									'posId',
									'subcategory',
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
									(new Query('brand'))
										->setSelectionSet(
											[
												'name',
												'description',
												'id',
												'imageUrl'
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
									(new Query('potencyThc'))
										->setSelectionSet(
											[
												'formatted',
												'range',
												'unit'
											]
										),
									(new Query('terpenes'))
										->setSelectionSet(
											[
												'id',
												'name',
												'terpeneId',
												'unit',
												'unitSymbol',
												'value',
												(new Query('terpene'))
													->setSelectionSet(
														[
															'aliasList',
															'aromas',
															'description',
															'effects',
															'id',
															'name',
															'potentialHealthBenefits',
															'unitSymbol'
														]
													)
											]
										),
									(new Query('cannabinoids'))
										->setSelectionSet(
											[
												'cannabinoidId',
												'unit',
												'value',
												(new Query('cannabinoid'))
													->setSelectionSet(
														[
															'description',
															'id',
															'name'
														]
													)
											]
										)
								]
							)
					]
				);

			// Run query to get results
			try {
				$results = $client->runQuery($gql);
				$results->reformatResults(true);
				$products = $results->getData()['menu']['products'];
				return $products;
				// print_r($products);
			} catch (QueryError $exception) {
				return $error_message = $exception->getErrorDetails()['message'];
			}
		} else {
			return __('Invalid API Key', 'leafbridge');
		}
	}




	//****************************************************
	/*
			Get retailer wise all products from API
			Return products and query
		*/

	public function debug_fetch_retailer_products($retailerId = NULL, $menutype = NULL, $pagination = NULL, $filter = NULL, $sort = NULL)
	{
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		/*$menutype   = 'RECREATIONAL';
			$pagination = "{ limit: 1 offset: 0 }";
			$filter     = "{ category: FLOWER }";
			$sort       = "{ direction: ASC key: POPULAR }";*/

		//echo $var_pagination." }"; 


		if ($client) {

			$gql = (new Query('menu'))
				->setArguments([
					'retailerId' => $retailerId,
					'menuType'   => new RawObject($menutype),
					'pagination' => new RawObject($pagination),
					'filter'     => new RawObject($filter),
					'sort'       => new RawObject($sort),
				])
				->setSelectionSet(
					[
						(new Query('products'))
							->setSelectionSet(
								[
									'id',
									'name',
									'image',
									'description',
									'category',
									'strainType',
									'slug',
									'staffPick',
									'effects',
									'productBatchId',
									'posId',
									'subcategory',
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
									(new Query('brand'))
										->setSelectionSet(
											[
												'name',
												'description',
												'id',
												'imageUrl'
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
									(new Query('potencyThc'))
										->setSelectionSet(
											[
												'formatted',
												'range',
												'unit'
											]
										),
									(new Query('terpenes'))
										->setSelectionSet(
											[
												'id',
												'name',
												'terpeneId',
												'unit',
												'unitSymbol',
												'value',
												(new Query('terpene'))
													->setSelectionSet(
														[
															'aliasList',
															'aromas',
															'description',
															'effects',
															'id',
															'name',
															'potentialHealthBenefits',
															'unitSymbol'
														]
													)
											]
										),
									(new Query('cannabinoids'))
										->setSelectionSet(
											[
												'cannabinoidId',
												'unit',
												'value',
												(new Query('cannabinoid'))
													->setSelectionSet(
														[
															'description',
															'id',
															'name'
														]
													)
											]
										)
								]
							)
					]
				);

			// Run query to get results
			try {
				$results = $client->runQuery($gql);
				$results->reformatResults(true);
				$products = $results->getData()['menu']['products'];
				$response = array('query' => $gql, 'result' => $products);
				return $response;
				// print_r($products);
			} catch (QueryError $exception) {
				return $error_message = $exception->getErrorDetails()['message'];
			}
		} else {
			return __('Invalid API Key', 'leafbridge');
		}
	}





	//******************************************************************************
	//****************************************************
	/*
			Get retailer wise all products from API without menu type
		*/

	public function fetch_retailer_products_no_menu($retailerId = NULL, $pagination = NULL, $filter = NULL, $sort = NULL)
	{
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();




		/*$menutype   = 'RECREATIONAL';
			$pagination = "{ limit: 1 offset: 0 }";
			$filter     = "{ category: FLOWER }";
			$sort       = "{ direction: ASC key: POPULAR }";*/

		//echo $var_pagination." }";  

		if ($client) {

			$gql = (new Query('menu'))
				->setArguments([
					'retailerId' => $retailerId,
					'pagination' => new RawObject($pagination),
					'filter'     => new RawObject($filter),
					'sort'       => new RawObject($sort),
				])
				->setSelectionSet(
					[
						(new Query('products'))
							->setSelectionSet(
								[
									'id',
									'name',
									'image',
									'description',
									'category',
									'strainType',
									'slug',
									'staffPick',
									'effects',
									'productBatchId',
									'posId',
									'subcategory',
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
									(new Query('brand'))
										->setSelectionSet(
											[
												'name',
												'description',
												'id',
												'imageUrl'
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
									(new Query('potencyThc'))
										->setSelectionSet(
											[
												'formatted',
												'range',
												'unit'
											]
										),
									(new Query('terpenes'))
										->setSelectionSet(
											[
												'id',
												'name',
												'terpeneId',
												'unit',
												'unitSymbol',
												'value',
												(new Query('terpene'))
													->setSelectionSet(
														[
															'aliasList',
															'aromas',
															'description',
															'effects',
															'id',
															'name',
															'potentialHealthBenefits',
															'unitSymbol'
														]
													)
											]
										),
									(new Query('cannabinoids'))
										->setSelectionSet(
											[
												'cannabinoidId',
												'unit',
												'value',
												(new Query('cannabinoid'))
													->setSelectionSet(
														[
															'description',
															'id',
															'name'
														]
													)
											]
										)
								]
							)
					]
				);

			// Run query to get results
			try {
				$results = $client->runQuery($gql);
				$results->reformatResults(true);
				$products = $results->getData()['menu']['products'];
				return $products;
			} catch (QueryError $exception) {
				return $error_message = $exception->getErrorDetails()['message'];
			}
		} else {
			return __('Invalid API Key', 'leafbridge');
		}
	}





	//****************************************************
	/*
			Return single product details as an array
		*/


	public function fetch_single_products($retailer_id = NULL, $product_id = NULL)
	{


		$retailerId = $retailer_id;

		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		if ($client) {
			$gql = (new Query('product'))
				->setArguments(['retailerId' => $retailerId, 'id' => $product_id])
				->setSelectionSet(
					[
						'id',
						'name',
						'image',
						'description',
						'category',
						'strainType',
						'slug',
						'staffPick',
						'effects',
						'productBatchId',
						'posId',
						'subcategory',
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
						(new Query('brand'))
							->setSelectionSet(
								[
									'name',
									'description',
									'id',
									'imageUrl'
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
						(new Query('potencyThc'))
							->setSelectionSet(
								[
									'formatted',
									'range',
									'unit'
								]
							),
						(new Query('terpenes'))
							->setSelectionSet(
								[
									'id',
									'name',
									'terpeneId',
									'unit',
									'unitSymbol',
									'value',
									(new Query('terpene'))
										->setSelectionSet(
											[
												'aliasList',
												'aromas',
												'description',
												'effects',
												'id',
												'name',
												'potentialHealthBenefits',
												'unitSymbol'
											]
										)
								]
							),
						(new Query('cannabinoids'))
							->setSelectionSet(
								[
									'cannabinoidId',
									'unit',
									'value',
									(new Query('cannabinoid'))
										->setSelectionSet(
											[
												'description',
												'id',
												'name'
											]
										)
								]
							)
					]
				);



			// Run query to get results
			try {
				$results = $client->runQuery($gql);
				$results->reformatResults(true);
				$products = $results->getData()['product'];
				return $products;
			} catch (QueryError $exception) {
				return $error_message = $exception->getErrorDetails()['message'];
			}
		} else {
			return __('Invalid API Key', 'leafbridge');
		}
	}
}
