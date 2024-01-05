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


class LeafBridge_Products {

	public static function register_products() {
		$instance = new self;
		add_action('init', [$instance, 'registerProduct']);
		add_action('init', [$instance, 'product_categories']);
		add_action('init', [$instance, 'product_retailer_taxonomy']);
		add_action('add_meta_boxes', [$instance, 'add_product_box']);
		add_filter('manage_product_posts_columns', [$instance, 'hs_product_table_head']);
		add_action('manage_product_posts_custom_column', [$instance, 'hs_product_table_content'], 10, 2);
	}

	function hs_product_table_head($columns) {
		$columns['product_featured']  = 'Retailer';
		return $columns;
	}

	function hs_product_table_content($column_name, $post_id) {
		$retrailer = new LeafBridge_Retailers();
		$retailers_array = $retrailer->get_retailer_list_db();

		if ($column_name == 'product_featured') {
			$retailer_id = get_post_meta($post_id, '_leafbridge_product_single_meta_retailer_id', true);
			if (isset($retailer_id) && $retailer_id != '') {
				echo $retailers_array[$retailer_id] ?? '-';
			}
		}
	}

	public function registerProduct() {
		register_post_type('product', [
			'description' 			=> 'Products',
			'show_ui' 				=> true,
			'publicly_queryable' 	=> true,
			'show_in_nav_menus' 	=> true,
			'show_in_menu' 			=> 'edit.php?post_type=product',
			//'capability_type'    => 'post',
			// 'menu_position' 		=> 2,
			//'menu_icon' 			=> 'dashicons-products',
			'exclude_from_search' 	=> true,
			'labels' => array('name' => 'Product'),
			'public' => true,
			'capability_type' 		=> 'post',
			'capabilities' 			=> array(
				'create_posts' => true, // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
			),
			'map_meta_cap' 			=> true, // Set to `false`, if users are not allowed to edit/delete existing posts
			'hierarchical' 			=> true,
			'rewrite'          	=> array('slug' => 'product'),
			'has_archive' 			=> true,
			'supports' 				=> array('thumbnail', 'editor', 'title', 'excerpt', 'author', 'page-attributes'),
			'show_in_rest'       	=> true, // To use Gutenberg editor.
			//'taxonomies' 			=> array( 'categories' )
		]);
	}



	function product_categories() {
		register_taxonomy(
			'categories',
			'product',
			array(
				'labels' => array(
					'name' 				=> 'Category',
					'singular_name' 	=> 'Category',
					'search_items' 		=> 'Search Categories',
					'popular_items' 	=> 'Popular Categories',
					'all_items' 		=> 'All Categories',
					'parent_item' 		=> 'Parent Categories',
					'parent_item_colon' => 'Parent Categories:',
					'edit_item' 		=> 'Edit Categories',
					'update_item' 		=> 'Update Categories',
					'add_new_item' 		=> 'Add Categories',
					'new_item_name' 	=> 'New Categories',
				),
				'hierarchical' 		=> true,
				'show_ui' 			=> true,
				'show_in_menu' 		=> 'edit-tags.php?taxonomy=categories&post_type=product',
				'show_tagcloud' 	=> true,
				'show_admin_column' => true,
				'rewrite' 		=> true,
				//'rewrite' 			=> array('slug' => 'categories', 'with_front' => true),
				'public'			=> true,
				//'show_in_rest'      => true, // To use Gutenberg editor.
			) // end array
		);
	}



	function product_retailer_taxonomy() {
		register_taxonomy(
			'retailer',
			'product',
			array(
				'labels' => array(
					'name' 				=> 'Retailer',
					'singular_name' 	=> 'Retailer',
					'search_items' 		=> 'Search Retailer',
					'popular_items' 	=> 'Popular Retailer',
					'all_items' 		=> 'All Retailer',
					'parent_item' 		=> 'Parent Retailer',
					'parent_item_colon' => 'Parent Retailer:',
					'edit_item' 		=> 'Edit Retailer',
					'update_item' 		=> 'Update Retailer',
					'add_new_item' 		=> 'Add Retailer',
					'new_item_name' 	=> 'New Retailer',
				),
				'hierarchical' 		=>	false,
				'show_ui' 			=> false,
				'show_in_menu' 		=> 'edit-tags.php?taxonomy=retailer&post_type=product',
				'show_tagcloud' 	=> false,
				'show_admin_column' => false,
				'rewrite' 			=> true,
				'public'			=> false,
				'show_in_rest'      => true, // To use Gutenberg editor.
			) // end array
		);
	}



	function add_product_box() {
		$instance = new self;
		$screens = ['product'];
		foreach ($screens as $screen) {
			add_meta_box(
				'leafbridge-product_meta',
				'Product Options',
				[$instance, 'leafbridge_product_meta_box_html'],
				$screen
			);
		}
	}

	public static function leafbridge_product_meta_box_html($post) {

		$_leafbridge_product_meta_options_all = get_post_meta($post->ID, '_leafbridge_product_meta_options_all', true);
		$_leafbridge_product_single_meta_retailer_id = get_post_meta($post->ID, '_leafbridge_product_single_meta_retailer_id', true);


		$_leafbridge_product_meta_option = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_option'];
		$_leafbridge_product_meta_priceMed = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_priceMed'];
		$_leafbridge_product_meta_priceRec = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_priceRec'];
		$_leafbridge_product_meta_specialPriceMed = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_specialPriceMed'];
		$_leafbridge_product_meta_specialPriceRec = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_specialPriceRec'];
		$_leafbridge_product_meta_thumbnail = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_thumbnail'];
		$_leafbridge_product_meta_brand = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_brand'];
		$_leafbridge_product_meta_strainType = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_strainType'];
		$_leafbridge_product_meta_potencyCbd = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_potencyCbd'];
		$_leafbridge_product_meta_potencyThc = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_potencyThc'];
		$_leafbridge_product_meta_quantity = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_quantity'];
		//$_leafbridge_product_meta_POS_Categories = $_leafbridge_product_meta_options_all['POS_Categories'];


		$_leafbridge_product_meta_retailer_id = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_retailer_id'];
		$_leafbridge_product_meta_all_product_data = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_all_product_data'];
		$_leafbridge_product_meta_product_id = get_post_meta($post->ID, '_leafbridge_product_meta_product_id', true);
		$_leafbridge_product_meta_menu_type = get_post_meta($post->ID, '_leafbridge_product_meta_menu_type', true);
?>

		<div class="leafbridge_product_meta_options_group">
			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_single_meta_retailer_id">Retailer ID</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_single_meta_retailer_id" id="_leafbridge_product_single_meta_retailer_id" value="<?php echo ($_leafbridge_product_single_meta_retailer_id ? $_leafbridge_product_single_meta_retailer_id : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_product_id">Product ID</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_product_id" id="_leafbridge_product_meta_product_id" value="<?php echo ($_leafbridge_product_meta_product_id ? $_leafbridge_product_meta_product_id : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_menu_type">Menu Types</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_menu_type" id="_leafbridge_product_meta_menu_type" value="<?php echo ($_leafbridge_product_meta_menu_type ? $_leafbridge_product_meta_menu_type : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_brand">Brand</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_brand" id="_leafbridge_product_meta_brand" value="<?php echo ($_leafbridge_product_meta_brand ? $_leafbridge_product_meta_brand : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_strainType">Strain Type</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_strainType" id="_leafbridge_product_meta_strainType" value="<?php echo ($_leafbridge_product_meta_strainType ? $_leafbridge_product_meta_strainType : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_quantity">Quantity</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_quantity" id="_leafbridge_product_meta_quantity" value="<?php echo ($_leafbridge_product_meta_quantity ? $_leafbridge_product_meta_quantity : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_option">Option</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_option" id="_leafbridge_product_meta_option" value="<?php echo ($_leafbridge_product_meta_option ? $_leafbridge_product_meta_option : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_priceMed">MEDICAL Price($)</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_priceMed" id="_leafbridge_product_meta_priceMed" value="<?php echo ($_leafbridge_product_meta_priceMed ? $_leafbridge_product_meta_priceMed : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_priceRec">RECREATIONAL Price($)</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_priceRec" id="_leafbridge_product_meta_priceRec" value="<?php echo ($_leafbridge_product_meta_priceRec ? $_leafbridge_product_meta_priceRec : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_specialPriceMed">MEDICAL Special Price($)</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_specialPriceMed" id="_leafbridge_product_meta_specialPriceMed" value="<?php echo ($_leafbridge_product_meta_specialPriceMed ? $_leafbridge_product_meta_specialPriceMed : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_specialPriceRec">RECREATIONAL Special Price($)</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_specialPriceRec" id="_leafbridge_product_meta_specialPriceRec" value="<?php echo ($_leafbridge_product_meta_specialPriceRec ? $_leafbridge_product_meta_specialPriceRec : ''); ?>" />
			</p>



			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_potencyCbd">CBD Content(mg)</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_potencyCbd" id="_leafbridge_product_meta_potencyCbd" value="<?php echo ($_leafbridge_product_meta_potencyCbd ? $_leafbridge_product_meta_potencyCbd : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_potencyThc">THC Content(mg)</label>
				<input class="lf-readonly-txt" type="text" name="_leafbridge_product_meta_potencyThc" id="_leafbridge_product_meta_potencyThc" value="<?php echo ($_leafbridge_product_meta_potencyThc ? $_leafbridge_product_meta_potencyThc : ''); ?>" />
			</p>

			<p class="leafbridge_product_meta_form-field">
				<label for="_leafbridge_product_meta_thumbnail">Product Thumbnail</label>
				<input type="hidden" name="_leafbridge_product_meta_thumbnail" id="_leafbridge_product_meta_thumbnail" value="<?php echo ($_leafbridge_product_meta_thumbnail ? $_leafbridge_product_meta_thumbnail : ''); ?>" />
				<?php if (isset($_leafbridge_product_meta_thumbnail) && $_leafbridge_product_meta_thumbnail != '') { ?>
					<img src="<?php echo $_leafbridge_product_meta_thumbnail; ?>" width="200" />
				<?php } ?>
			</p>

			<?php  //echo '<pre>'; print_r($_leafbridge_product_meta_all_product_data); echo '</pre>'; 
			?>


		</div>

<?php
	}


	//****************************************************
	/*
			Return products as an array
		*/


	public function fetch_products($retailer_id = NULL) {
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		if ($client) {
			$gql = (new Query('menu'))
				->setArguments([
					'retailerId' => $retailer_id,
					/*'menuType' => new RawObject('RECREATIONAL'),  */
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
									'menuTypes',
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
									(new Query('posMetaData'))
										->setSelectionSet(
											[
												'id',
												'category',
												'sku'
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

	public function add_product_categories() {
		$product_categories = array('Accessories', 'Apparel', 'Cbd', 'Clones', 'Concentrates', 'Edibles', 'Flower', 'Not_Applicable', 'Orals', 'Pre_rolls', 'Seeds', 'Tinctures', 'Topicals', 'Vaporizers');

		foreach ($product_categories as $key => $name) {
			$parent_term = term_exists($name, 'categories');
			if (!is_array($parent_term) && !isset($parent_term['term_id'])) {
				//$parent_term_id = $parent_term['term_id'];
				wp_insert_term(
					$name,   // the term
					'categories', // the taxonomy
					array(
						'description' => $name,
						'slug'        => strtolower($name),
					)
				);
			}
		}
		return __('Success', 'leafbridge');
	}



	public function add_retailer_taxonomy() {
		$loop = new WP_Query(array('post_type' => 'retailer', 'posts_per_page' => -1));

		// go through each of the retrieved ids and get the title
		if ($loop->have_posts()) :
			foreach ($loop->posts as $id) :
				//$post_titles[] = apply_filters('the_title', get_the_title($id));
				$name = get_the_title($id);
				$parent_term = term_exists($name, 'retailer');
				if (!is_array($parent_term) && !isset($parent_term['term_id'])) {
					//$parent_term_id = $parent_term['term_id'];
					wp_insert_term(
						$name,   // the term
						'retailer', // the taxonomy
						array(
							'description' => $name,
							'slug'        => strtolower($name),
						)
					);
				}
			endforeach;
		endif;

		return __('Success', 'leafbridge');
	}



	//****************************************************
	/*
			Get retailer wise all products from API
		*/

	public function fetch_retailer_products($retailerId = NULL, $menutype = NULL, $pagination = NULL, $filter = NULL, $sort = NULL) {
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();

		/*$menutype   = 'RECREATIONAL';
			$pagination = "{ limit: 1 offset: 0 }";
			$filter     = "{ category: FLOWER }";
			$sort       = "{ direction: ASC key: POPULAR }";*/

		//echo $var_pagination." }";



		$setArgs = [];
		if ($menutype != false) {
			$setArgs = [
				'retailerId' => $retailerId,
				'menuType'   => new RawObject($menutype),
				'pagination' => new RawObject($pagination),
				'filter'     => new RawObject($filter),
				'sort'       => new RawObject($sort)
			];
		} else {
			$setArgs = [
				'retailerId' => $retailerId,
				'pagination' => new RawObject($pagination),
				'filter'     => new RawObject($filter),
				'sort'       => new RawObject($sort)
			];
		}



		if ($client) {

			$gql = (new Query('menu'))
				->setArguments($setArgs)
				->setSelectionSet(
					[
						(new Query('weights')),
						(new Query('productsCount')),
						(new Query('products'))
							->setSelectionSet(
								[
									'id',
									'name',
									'image',
									'description',
									'category',
									'strainType',
									'menuTypes',
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
				$productsCount = $results->getData()['menu']['productsCount'];
				$weights = $results->getData()['menu']['weights'];
				$response = array('productsCount' => $productsCount, 'result' => $products, 'weights' => $weights);
				return $response;
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
			Search retailer wise all products from API
		*/

	public function search_retailer_products($retailerId = NULL, $menutype = NULL, $pagination = NULL, $filter = NULL, $sort = NULL) {
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		/*$menutype   = 'RECREATIONAL';
			$pagination = "{ limit: 1 offset: 0 }";
			$filter     = "{ category: FLOWER, search: 'dfdf' }";
			$sort       = "{ direction: ASC key: POPULAR }";*/

		//echo $var_pagination." }";

		//$filter_search     = "{ search: ".$search." }";

		if ($client) {

			$gql = (new Query('menu'))
				->setArguments([
					'retailerId' => $retailerId,
					'menuType'   => new RawObject($menutype),
					'pagination' => new RawObject($pagination),
					'filter'     => new RawObject($filter),
					'sort'       => new RawObject($sort)
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





	//******************************************************************************
	//****************************************************
	/*
			Get retailer wise all products from API without menu type
		*/

	public function fetch_retailer_products_no_menu($retailerId = NULL, $pagination = NULL, $filter = NULL, $sort = NULL) {
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
									'menuTypes',
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



	public function uploadImageByUrl($image_url = NULL, $name = NULL, $alt = NULL) {

		// it allows us to use download_url() and wp_handle_sideload() functions
		require_once(ABSPATH . 'wp-admin/includes/file.php');

		// download to temp dir
		$temp_file = download_url($image_url);

		if (is_wp_error($temp_file)) {
			return false;
		}

		// move the temp file into the uploads directory
		$file = array(
			'name'     => basename($image_url),
			'type'     => mime_content_type($temp_file),
			'tmp_name' => $temp_file,
			'size'     => filesize($temp_file),
		);
		$sideload = wp_handle_sideload(
			$file,
			array(
				'test_form'   => false // no needs to check 'action' parameter
			)
		);

		if (!empty($sideload['error'])) {
			// you may return error message if you want
			return false;
		}

		// it is time to add our uploaded image into WordPress media library
		$attachment_id = wp_insert_attachment(
			array(
				'guid'           => $sideload['url'],
				'post_mime_type' => $sideload['type'],
				'post_title'     => $name, //basename( $sideload[ 'file' ] ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$sideload['file']
		);

		// Set the image Alt-Text
		update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);


		if (is_wp_error($attachment_id) || !$attachment_id) {
			return false;
		}

		// update medatata, regenerate image sizes
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		wp_update_attachment_metadata(
			$attachment_id,
			wp_generate_attachment_metadata($attachment_id, $sideload['file'])
		);

		return $attachment_id;
	}


	//****************************************************
	/*
			Return single product details as an array
		*/


	public function fetch_single_products($retailer_id = NULL, $product_id = NULL) {
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		if ($client) {
			$gql = (new Query('product'))
				->setArguments(['retailerId' => $retailer_id, 'id' => $product_id])
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
						(new Query('posMetaData'))
							->setSelectionSet(
								[
									'id',
									'category',
									'sku'
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



	//****************************************************
	/*
			Get retailer wise all products from API
		*/

	public function fetch_retailer_menu_products($retailerId = NULL, $menutype = NULL) {
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();

		/*$menutype   = 'RECREATIONAL';
			$pagination = "{ limit: 1 offset: 0 }";
			$filter     = "{ category: FLOWER }";
			$sort       = "{ direction: ASC key: POPULAR }";*/



		if ($client) {
			$pagination = "{ limit: 100000 offset: 0 }";
			$gql = (new Query('menu'))
				->setArguments([
					'retailerId' => $retailerId,
					'menuType'   => new RawObject($menutype),
					'pagination' => new RawObject($pagination),
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
									'menuTypes',
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
									(new Query('posMetaData'))
										->setSelectionSet(
											[
												'id',
												'category',
												'sku'
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
			Add products using retailer id
		*/
	public function add_products_new($retailer_id = NULL) {

		$results 			= '';
		$error_message  	= '';
		$reailer_details 	= '';
		$products 			= '';
		$retailer_menuTypes = '';
		$retailer_name 		= '';
		$json = array();


		$product_sync_updated = array();
		$product_sync_new = array();

		$leafbridge_filters_xd = array();

		$previousRetailerids = array();

		$args = array(
			'post_type' => 'retailer',
			'post_status' => 'publish'
		);
		$retailers = new WP_Query($args);

		if ($retailers->have_posts()) {
			while ($retailers->have_posts()) {
				$retailers->the_post();
				$previousRetailerids[] = get_post_meta(get_the_ID(), '_lb_retailer_single_id');
			}
			wp_reset_postdata();
		}

		if (!empty($previousRetailerids)) {
			if (in_array($retailer_id, $previousRetailerids)) {

				//======================= draft all posts =========================
				$product_args = array(
					'post_type'			=> 'product',
					'posts_per_page' 	=> -1,
					'meta_key'         	=> '_leafbridge_product_single_meta_retailer_id',
					'meta_value'       	=> $retailer_id
				);

				$product_query = new WP_Query($product_args);
				if ($product_query->have_posts()) :
					while ($product_query->have_posts()) :
						set_time_limit(1800);
						$product_query->the_post();
						update_post_meta(get_the_ID(), '_leafbridge_product_meta_menu_type', '');
					endwhile;
					wp_reset_postdata();
				else :
				endif;
			}
		}




		$terms = get_terms(array(
			'taxonomy' => 'categories',
			'hide_empty' => false,
		));

		$tax_cat_array = array();

		if (empty($terms) || is_wp_error($terms)) {
			//$json[] = $res;
		} else {

			foreach ($terms as $term) {
				$tax_cat_array[$term->term_id] = strtolower($term->name);
			}
		}

		//======================== get retailer details ================================================

		$args = array(
			//'posts_per_page'   => 1,
			'post_type'        => 'retailer',
			'meta_key'         => '_lb_retailer_single_id',
			'meta_value'       => $retailer_id
		);
		$query = new WP_Query($args);

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$retailer_post_id = get_the_ID();
				//$retailer_menuTypes = get_post_meta( $retailer_post_id, '_lb_retailer_menuTypes', true );
				$retailer_name = get_the_title();
			}
			//wp_reset_postdata();
		}

		//======================================================================================


		//$menu_array = explode(',',$retailer_menuTypes);

		$product_sub_categories = array();
		$categories = array(
			'Accessories' => array(),
			'Apparel' => array(),
			'Cbd' => array(),
			'Clones' => array(),
			'Concentrates' => array(),
			'Edibles' => array(),
			'Flower' => array(),
			'Not_Applicable' => array(),
			'Orals' => array(),
			'Pre_rolls' => array(),
			'Seeds' => array(),
			'Tinctures' => array(),
			'Topicals' => array(),
			'Vaporizers' => array()
		);


		$brands = array();
		$potencyCbd = array();
		$potencyThc = array();


		$leafbridge_filters_xd2 = get_option('leafbridge_filters_xdxs');

		$effects = array();
		$staffPick = array();
		$strainType = array();
		$lb_retailer_posCategories = array();
		/*$prices = array(
							'priceMed' => array(), 
							'priceRec' => array()
						);*/

		$weight = array();

		$lb_retailer_brands1 = array();
		$lb_retailer_brands1 = get_option('lb_product_filter_options');

		//unset($lb_retailer_brands1['brands']);
		$lb_retailer_brands = $lb_retailer_brands1['brands'];

		$lb_retailer_posCategories = $lb_retailer_brands1['POS_Categories'];

		//echo '<pre>......';print_r($lb_retailer_brands1);echo 'sssssssssss</pre>'; die();
		//================ menu type based query =======================================
		//foreach($menu_array as $menu_type_key) {

		//$products = $this->fetch_retailer_menu_products($retailerId=$retailer_id, $menutype=trim($menu_type_key));
		$products = $this->fetch_products($retailer_id = $retailer_id);

		//================ product based query =======================================
		if (is_array($products) && count($products) > 0) {
			$i = 0;

			foreach ($products as $product) {

				//if($product['id']=='62a8e84446c02b0001426cdf') {




				set_time_limit(1800);

				$product['retailer_name'] = $retailer_name;

				$pvariants 	= $product['variants'][0];
				$image 		= ($product['image'] ? $product['image'] : '');
				$brand 		= ($product['brand'] ? $product['brand']['name'] : '');
				$brand_id 		= ($product['brand'] ? $product['brand']['id'] : 0);

				$potencyCbd_formatted = ($product['potencyCbd']['formatted'] ? $product['potencyCbd']['formatted'] : '');
				$potencyCbd_unit = ($product['potencyCbd']['unit'] ? $product['potencyCbd']['unit'] : '');
				$potencyCbd_range = ($product['potencyCbd']['range'] ? $product['potencyCbd']['range'] : '');


				$potencyThc_formatted = ($product['potencyThc']['formatted'] ? $product['potencyThc']['formatted'] : '');
				$p_effects = ($product['effects'] ? $product['effects'] : '');
				$p_staffPick  = ($product['staffPick'] ? $product['staffPick'] : '');
				$p_strainType = ($product['strainType'] ? $product['strainType'] : '');

				$posCategory = ($product['posMetaData']['category'] ? $product['posMetaData']['category'] : '');
				$posMetaDataID = ($product['posMetaData']['id'] ? $product['posMetaData']['id'] : '');

				$product_menu_type_array = ($product['menuTypes'] ? $product['menuTypes'] : array());


				$product_id = $product['id'];
				$product_subcategory = ucfirst(strtolower(trim($product['subcategory'])));
				$product_category = ucfirst(strtolower(trim($product['category'])));


				$weightArray = $product['variants'];


				if (in_array('MEDICAL', $product_menu_type_array) && is_array($weightArray)) {
					foreach ($weightArray as $w) {
						//if(!in_array($w['option'], $leafbridge_filters_xd2['weight'][$retailer_id]['med'])) {
						$weight['med'][] = $w['option'];
						//}
					}
				}
				if (in_array('RECREATIONAL', $product_menu_type_array) && is_array($weightArray)) {
					foreach ($weightArray as $w) {
						//if(!in_array($w['option'], $leafbridge_filters_xd2['weight'][$retailer_id]['rec'])) {
						$weight['rec'][] = $w['option'];
						//}
					}
				}




				//category
				if ($product_subcategory != null && (!in_array($product_subcategory, $categories[$product_category]))) {
					$categories[$product_category][] = $product_subcategory;
				}

				// brand
				//Method 2 - get retailer brands by product query
				if ($brand_id != '') {
					$lb_retailer_brands[$retailer_id][$product['category']][$brand_id] = $brand;
					//array_push($lb_retailer_brands,array([$brand_id] => $brand));
					//echo '<pre>......';print_r($lb_retailer_brands);echo 'sssssssssss</pre>'; die();
				}



				//potencyCbd_formatted
				if ($potencyCbd_formatted != '' && (!in_array($potencyCbd_formatted, $potencyCbd))) {
					$potencyCbd[] = $potencyCbd_formatted;
				}





				//potencyThc_formatted
				if ($potencyThc_formatted != '' && (!in_array($potencyThc_formatted, $potencyThc))) {
					$potencyThc[] = $potencyThc_formatted;
				}

				//effects
				if ($p_effects != '' && (!in_array($p_effects, $effects))) {
					$effects[] = $p_effects;
				}

				//staffPick
				if ($p_staffPick != '' && (!in_array($p_staffPick, $staffPick))) {
					$staffPick[] = $p_staffPick;
				}

				//strainType
				if ($p_strainType != '' && (!in_array($p_strainType, $strainType))) {
					$strainType[] = $p_strainType;
				}

				/*if($pvariants['priceMed'] != null && (!in_array($pvariants['priceMed'], $prices['priceMed']))) {
										$prices['priceMed'][] = $pvariants['priceMed'];
									}
									if($pvariants['priceRec'] != null && (!in_array($pvariants['priceRec'], $prices['priceRec']))) {
										$prices['priceRec'][] = $pvariants['priceRec'];
									}*/

				$postmeta_array = array(
					'_leafbridge_product_meta_option' 			=> $pvariants['option'],
					'_leafbridge_product_meta_retailer_id' 		=> $retailer_id,
					'_leafbridge_product_meta_priceMed' 		=> $pvariants['priceMed'],
					'_leafbridge_product_meta_priceRec' 		=> $pvariants['priceRec'],
					'_leafbridge_product_meta_specialPriceMed' 	=> $pvariants['specialPriceMed'],
					'_leafbridge_product_meta_specialPriceRec' 	=> $pvariants['specialPriceRec'],
					'_leafbridge_product_meta_thumbnail' 		=> $image,
					'_leafbridge_product_meta_strainType' 		=> $product['strainType'],
					'_leafbridge_product_meta_brand' 			=> $brand,
					'_leafbridge_product_meta_potencyCbd' 		=> $product['potencyCbd']['formatted'],
					'_leafbridge_product_meta_potencyThc' 		=> $product['potencyThc']['formatted'],
					'_leafbridge_product_meta_quantity' 		=> $pvariants['quantity'],
					'_leafbridge_product_meta_all_product_data' => $product,
					'_leafbridge_product_posMetaData'			=> $posCategory
				);

				$product_post_id = $this->get_product_post_id($retailer_id, $product_id);
				$product_url = get_site_url() . '/product/' . $product['slug'];

				//usleep( 5 * 1000 );

				$product_sub_categories[] = $product_subcategory;

				if (isset($product_post_id) && $product_post_id != '') {

					// ============= UPDATE EXISTING POST =================


					update_post_meta($product_post_id, '_leafbridge_product_meta_menu_type', implode(",", $product_menu_type_array));

					update_post_meta($product_post_id, '_leafbridge_product_meta_options_all', $postmeta_array);
					update_post_meta($product_post_id, '_leafbridge_product_single_meta_retailer_id', $retailer_id);
					update_post_meta($product_post_id, '_leafbridge_product_meta_product_id', $product_id);


					$post_status_publish = array(
						'ID' 				=> $product_post_id,
						'post_type' 		=> 'product',
						'post_title' 		=> $product['name'],
						'post_name' 		=> $product['slug'],
						'post_content' 		=> $product['description'],
						'post_status' 		=> 'publish',
						'comment_status' 	=> 'closed',
						'ping_status' 		=> 'closed',
					);
					$post_response = wp_update_post($post_status_publish);


					if (isset($post_response) && $post_response > 0) {

						// ASSIGN CATEGORY
						$category_id = array_search(strtolower($product['category']), $tax_cat_array);
						if ($category_id != '') {
							wp_set_object_terms($product_post_id, intval($category_id), 'categories');
						} else {
						}
						//if(isset($product_post_id) && $product_post_id !='') { 
						//$json['product_sync_updated'][] = $product_post_id;
						array_push($product_sync_updated, $product_url);
						//}
						//$json['product_sync_updated'][] = $product_post_id;
					}
				} else {

					// ============= ADD NEW POST ================
					$product_post_id_new = wp_insert_post(array(
						'post_type' 		=> 'product',
						'post_title' 		=> $product['name'],
						'post_name' 		=> $product['slug'],
						'post_content' 		=> $product['description'],
						'post_status' 		=> 'publish',
						'comment_status' 	=> 'closed',
						'ping_status' 		=> 'closed',
					));

					if (isset($product_post_id_new) && $product_post_id_new > 0) {
						add_post_meta($product_post_id_new, '_leafbridge_product_meta_options_all', $postmeta_array);
						add_post_meta($product_post_id_new, '_leafbridge_product_single_meta_retailer_id', $retailer_id);
						add_post_meta($product_post_id_new, '_leafbridge_product_meta_product_id', $product_id);
						add_post_meta($product_post_id_new, '_leafbridge_product_meta_menu_type', implode(",", $product_menu_type_array));

						// ASSIGN CATEGORY
						$category_id = array_search(strtolower($product['category']), $tax_cat_array);
						if ($category_id != '') {
							wp_set_object_terms($product_post_id_new, intval($category_id), 'categories');
						} else {
						}
						array_push($product_sync_new, $product_url);
						//$json['product_sync_new'][] = $product_post_id_new;
					}
					//print_r($json['product_sync_new']); die();


				} // End post create



				$i++;
				//}

			} // end product loop
			// remove duplicates

			if ($product_sync_updated) {
				$product_sync_updated = array_unique($product_sync_updated);
			}
			if ($product_sync_new) {
				$product_sync_new = array_unique($product_sync_new);
			}
		} //else echo 'No products';

		//} // end menu loop


		$product_filter_options_final = array(
			'categories' => $categories,
			'brands' => $lb_retailer_brands,
			'potencyCbd' => $potencyCbd,
			'potencyThc' => $potencyThc,
			'effects' => $effects,
			'staffPick' => $staffPick,
			'strainType' => $strainType,
			'POS_Categories' => $lb_retailer_posCategories
			//'weight' => $weight 							
		);




		$lb_product_filter_options = get_option('lb_product_filter_options');
		if (isset($lb_product_filter_options)) {
			update_option('lb_product_filter_options', $product_filter_options_final);
		} else {
			add_option('lb_product_filter_options', $product_filter_options_final, $deprecated = null,  $autoload = 'true');
		}




		$weightM = isset($weight['med']) ? array_unique($weight['med'], SORT_REGULAR) : array();
		$weightR = isset($weight['rec']) ? array_unique($weight['rec'], SORT_REGULAR) : array();
		$weightFinal = array(
			'med' => $weightM,
			'rec' => $weightR
		);



		$leafbridge_filters_xd2 = get_option('leafbridge_filters_xdxs');
		$leafbridge_filters_xd2['weight'][$retailer_id] = $weightFinal;

		if (isset($leafbridge_filters_xd2)) {
			update_option('leafbridge_filters_xdxs', $leafbridge_filters_xd2);
		} else {
			add_option('leafbridge_filters_xdxs', $leafbridge_filters_xd2, $deprecated = null,  $autoload = 'true');
		}


		$merge = array_merge($product_sync_updated, $product_sync_new);

		return $merge;
	} // End products_new function




	/*
* Return the wordpress product id for the given retailer id
*
*/

	function get_product_post_id($retailer_id, $product_id) {
		// get product from db

		$args = array(
			'posts_per_page'   => 1,
			'post_type'        => 'product',
			'post_status' 	   => array('draft', 'publish'),
			'meta_query' => array(
				array(
					'key' => '_leafbridge_product_meta_product_id',
					'value' => $product_id
				),
				array(
					'key' => '_leafbridge_product_single_meta_retailer_id',
					'value' => $retailer_id
				),
			),
		);
		$query = new WP_Query($args);

		$retailer_post_id = '';

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$retailer_post_id = get_the_ID();
			}
		}


		return $retailer_post_id;
	} // End function




	/**
	 * Setup pages
	 */

	function setup_pages() {

		$page_1 = get_page_by_title('Shop');

		// Check if the page already exists
		if (empty($page_1) || $page_1 == false) {
			$page_id = wp_insert_post(
				array(
					'comment_status' => 'close',
					'ping_status'    => 'close',
					'post_author'    => 1,
					'post_title'     => ucwords('Shop'),
					'post_name'      => strtolower(str_replace(' ', '-', trim('Shop'))),
					'post_status'    => 'publish',
					'post_content'   => '[leafbridge_shop_wizard]',
					'post_type'      => 'page',
				)
			);
		}
		$page_2 = get_page_by_title('Thank You');
		if (empty($page_2) || $page_2 == false) {
			$page_id_2 = wp_insert_post(
				array(
					'comment_status' => 'close',
					'ping_status'    => 'close',
					'post_author'    => 1,
					'post_title'     => ucwords('Thank You'),
					'post_name'      => strtolower(str_replace(' ', '-', trim('Thank You'))),
					'post_status'    => 'publish',
					'post_content'   => '[leafbridge-order-status]',
					'post_type'      => 'page',
				)
			);
		}

		$page_3 = get_page_by_title('Specials');
		if (empty($page_3) || $page_3 == false) {
			$page_id_3 = wp_insert_post(
				array(
					'comment_status' => 'close',
					'ping_status'    => 'close',
					'post_author'    => 1,
					'post_title'     => ucwords('Specials'),
					'post_name'      => strtolower(str_replace(' ', '-', trim('Specials'))),
					'post_status'    => 'publish',
					'post_content'   => '[leafbridge-special-products]',
					'post_type'      => 'page',
				)
			);
		}

		return  __('Success', 'leafbridge');
	}

	function get_page_title_for_slug($page_slug) {

		$page = get_page_by_path($page_slug, OBJECT);

		if (isset($page))
			return $page->post_title;
		else
			return false;
	}


	function get_product_count($retailer_id, $status) {

		global $wpdb;

		$args = array(
			'post_type' => 'product',
			'post_status' => $status,
			'meta_query' => array(
				array(
					'key'     => '_leafbridge_product_single_meta_retailer_id',
					'value'   => $retailer_id,
					'compare' => '=',
				)
			),
		);
		$query = new WP_Query($args);
		$post_count = $query->found_posts;

		return $post_count;
	}



	function getBrands($retailer_id) {
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		if ($client) {
			$gql = (new Query('menu'))
				->setArguments([
					'retailerId' => $retailer_id,
					//'pagination' => new RawObject($pagination),
				])
				->setSelectionSet(
					[
						(new Query('brands'))
							->setSelectionSet(
								[
									'id',
									'name',
								]
							)
					]
				);

			// Run query to get results
			try {
				$results = $client->runQuery($gql);
				$results->reformatResults(true);
				$menu = $results->getData()['menu'];
				return $menu;
				// print_r($specials);
			} catch (QueryError $exception) {
				return $error_message = $exception->getErrorDetails()['message'];
			}
		} else {
			return __('Invalid API Key', 'leafbridge');
		}
	}



	function getSpecials($retailer_id) {
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();

		if ($client) {
			$pagination = "{ limit: 100000 offset: 0 }";
			$gql = (new Query('specials'))
				->setArguments([
					'retailerId' => $retailer_id,
					//'pagination' => new RawObject($pagination),
				])
				->setSelectionSet(
					[
						'id',
						'name',
						'type',
						'redemptionLimit',
						'menuType',
						(new Query('emailConfiguration'))
							->setSelectionSet(
								[
									'description',
									'descriptionHtml',
									'subject',
									'heading',
									'enabled',
								]
							),
						(new Query('scheduleConfiguration'))
							->setSelectionSet(
								[
									'startStamp',
									'endStamp',
									'days',
									'setEndDate',
									'endDate',
									'recurringStartTime',
									'recurringEndTime',
								]
							),
						(new Query('menuDisplayConfiguration'))
							->setSelectionSet(
								[
									'name',
									'description',
									'image'
								]
							)
					]
				);

			// Run query to get results
			try {
				$results = $client->runQuery($gql);
				$results->reformatResults(true);
				$specials = $results->getData()['specials'];
				return $specials;
				// print_r($specials);
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
			
			Parameters
			$retailerId  
			$menutype 
			$pagination 
			$filter  // { category: FLOWER }
			$sort // { direction: ASC key: POPULAR }
		*/

	public function debug_fetch_retailer_products($retailerId = NULL, $menutype = NULL, $pagination = NULL, $filter = NULL, $sort = NULL) {
		$lb_obj = new LeafBridge();
		$client = $lb_obj->get_client();


		/*$menutype   = 'RECREATIONAL';
			$pagination = "{ limit: 1 offset: 0 }";
			$filter     = "{ category: FLOWER }";
			$sort       = "{ direction: ASC key: POPULAR }";*/

		//echo $var_pagination." }"; 
		$sql = '';

		if ($client) {

			$gql = (new Query('menu'))
				->setArguments([
					'retailerId' => $retailerId,
					'menuType'   => new RawObject($menutype),
					'pagination' => new RawObject($pagination),
					'filter'     => new RawObject($filter),
					'sort'       => new RawObject($sort)
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
				//$productsCount = $results->getData()['menu']['productsCount'];
				$response = array('query' => serialize($gql), 'result' => $products);
				return $response;
				// print_r($products);
			} catch (QueryError $exception) {
				return $error_message = $exception->getErrorDetails()['message'];
			}
		} else {
			return __('Invalid API Key', 'leafbridge');
		}
	}
}
?>