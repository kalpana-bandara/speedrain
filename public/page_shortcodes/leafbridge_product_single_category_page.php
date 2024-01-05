<?php

/**
 * The Template for displaying all single posts.
 *
 * @package Genesis Block Theme
 */

?>
<div id="primary" class="content-area">
	<main id="main" class="site-main">
		<div class="" style="white-space:pre;max-width:100vw;display:none; ">
			<?php
			// print_r(get_queried_object());
			$queried_obj = get_queried_object();
			print_r($queried_obj);

			$LeafBridge_Products = new LeafBridge_Products();
			$category_enum = strtoupper($queried_obj->name);
			$products_list = $LeafBridge_Products->fetch_retailer_products("f0ff5c46-2f0c-4137-941b-b79b71e1d85c", "MEDICAL", "{ limit: 1000 offset: 1 }", "{ category : " . $category_enum . "}", "{ direction: ASC key: NAME }");

			// print_r($products_list);
			?>
		</div>
		<div class="leafbridge_shop_wizard_wrapper" id="leafbridge_category_page" ff>
			<div class="leafbridge_shop_wizard_container">
				<div class="show_products_based_on_retailer" id="leafbridge_shop_wizard_view_products" style="display:none;">
					<div class="wizard_box_header" style="display:none;">
						<h2><?php _e('Select your products', 'leafbridge') ?></h2>
						<p><?php _e('Filter products from category', 'leafbridge') ?></p>
					</div>
					<div class="wizard_box_container">
						<div class="wizard_box_container_wrapper product_collection" id="product_collection">
							<div class="wizard_prods_wrapper wizard_prods_categories_wrapper">
								<?php echo do_shortcode('[leafbridge-breadcrumbs]'); ?>
								<div class="wizard_prods_inner">
									<div class="wizard_prods_categories">
										<div class="class_toggle_category open_cat_close">
											<i class="fa-solid fa-xmark"></i>
										</div>
										<div class="lb_search_products">
											<div class="lb_search_products_input_wrapper">
												<input type="text" name="" value="" id="products_search_input" value="" placeholder="Search Products">
												<button type="button" name="button" id="products_clear_search_button" title="Clear product search keywords"><i class="fa-solid fa-rotate-left"></i></button>
												<button type="button" name="button" class="lb_prod_filter_btn" id="products_search_button" title="Search products with matching keywords"><i class="fa-solid fa-magnifying-glass"></i></button>
											</div>
										</div>
										<div class="lb_prod_filter_attrs">
											<?php
											$filter_btn = '<div class="lb_prod_filter_button_wrapper" style="display:none !important;"><button class="lb_prod_filter_btn" title="Apply search filters" type="button" name="button"><label style="display:inline-block;margin-right:5px;">Filter</label><i class="fa-solid fa-filter"></i></button></div>';
											echo $filter_btn;

											$leafbridge_settings = get_option('leafbridge-settings');
											$leafbridge_product_categories = $leafbridge_settings['leafbridge-product-categories'];

											$lb_product_filter_options = get_option('lb_product_filter_options');
											echo "<pre style='display:none;'>";
											print_r($lb_product_filter_options);
											echo "</pre>";

											$terms = get_terms(array(
												'taxonomy' => 'categories',
												'hide_empty' => true,
											));
											?>
											<!-- Specials -->
											<div class="lb_prod_filter_attr_box open_attr_box" filter_selected_val="" filter_attr="MenuSectionFilter">
												<?php
												echo '<p class="lb_prod_filter_attr_title"><strong>Featured</strong></p>';
												echo '<div class="prod_filter_ul_wrapper " >';
												echo '<ul id="staff_picks" class="prod_filter_ul">';
												echo '<li class="" attr_value="STAFF_PICKS">';
												echo '<div class="prod_cat_select_icon">';
												echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
												echo '</div>';
												echo '<div class="prod_cat_select_lable lf-common">';
												echo '<span class="">';
												echo __('Staff Picks', 'leafbridge');
												echo '</span>';
												echo '</div>';
												echo '</li>';

												echo '<li class="" attr_value="SPECIALS">';
												echo '<div class="prod_cat_select_icon">';
												echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
												echo '</div>';
												echo '<div class="prod_cat_select_lable lf-common">';
												echo '<span class="">';
												echo __('Specials', 'leafbridge');
												echo '</span>';
												echo '</div>';
												echo '</li>';

												echo '</ul>';
												echo '</div>';
												?>
											</div>
											<!-- categories -->
											<div style="display:none !important;" class="lb_prod_filter_attr_box" filter_selected_val="<?php echo strtoupper($category_enum) ?>" filter_attr="categories">
												<p class="lb_prod_filter_attr_title"><strong>Category</strong></p>
												<?php
												echo '<div class="prod_filter_ul_wrapper" >';
												echo '<ul id="prods_categories" class="prod_filter_ul" >';
												foreach ($terms as $key => $term) {
													$term_name = strtoupper($term->name);
													echo '<li class="" attr_value="' . $term_name . '">';
													echo '<div class="prod_cat_select_icon">';
													echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
													echo '</div>';
													echo '<div class="prod_cat_select_lable lf-common">';
													echo '<span class="">';
													echo __(strtolower(str_replace("_", " ", $term_name)), 'leafbridge');
													echo '</span>';
													echo '</div>';
													echo '</li>';
												}
												echo '</ul>';
												echo '</div>';
												?>
											</div>

											<?php

											$subcategoreis_array = $lb_product_filter_options['categories'][$queried_obj->name];

											if (count($subcategoreis_array) > 0) :
											?>
												<!-- subcategory-->
												<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="subcategory">
													<p class="lb_prod_filter_attr_title"><strong>Sub Categories</strong></p>
													<?php

													echo '<div class="prod_filter_ul_wrapper" >';
													echo '<ul id="prods_categories" class="prod_filter_ul" >';
													foreach ($subcategoreis_array as $subcategoreis_array_key => $subcategory_name) {
														$term_name = strtoupper($subcategory_name);
														echo '<li class="" attr_value="' . $term_name . '">';
														echo '<div class="prod_cat_select_icon">';
														echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
														echo '</div>';
														echo '<div class="prod_cat_select_lable lf-common">';
														echo '<span class="">';
														echo __(strtolower(str_replace("_", " ", $term_name)), 'leafbridge');
														echo '</span>';
														echo '</div>';
														echo '</li>';
													}
													echo '</ul>';
													echo '</div>';
													?>
												</div>
											<?php
											endif;
											?>
											<!-- brands -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="brands">
												<?php
												// brands
												echo '<p class="lb_prod_filter_attr_title"><strong>Brands</strong></p>';
												echo '<div class="prod_filter_ul_wrapper" >';
												echo '<ul id="prods_brands" class="prod_filter_ul">';
												$brands_array = $lb_product_filter_options['brands'];

												foreach ($brands_array as $retailer_id => $retailer_node_brands) {

													foreach ($retailer_node_brands as $retailer_category => $retailer_category_brands) {

														asort($retailer_category_brands);
														foreach ($retailer_category_brands as $brand_id => $brand_name) {

															if ($brand_id != '0' || $brand_id != 0) {
																if ($retailer_category === $category_enum) {
																	echo '<li class="" brand_retailer="' . $retailer_id . '" checkcat = "' . $category_enum . '" category="' . $retailer_category . '" attr_value="' . $brand_id . '">';
																	echo '<div class="prod_cat_select_icon">';
																	echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
																	echo '</div>';
																	echo '<div class="prod_cat_select_lable lf-common">';
																	echo '<span class="">';
																	echo __($brand_name, 'leafbridge');
																	echo '</span>';
																	echo '</div>';
																	echo '</li>';
																}
															}
														}
													}
												}
												echo '</ul>';
												echo '</div>';
												?>
											</div>
											<!-- potency CBD -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="potency_cbd">
												<button class="lb_prod_filter_attr_title"><strong>Potency : CBD</strong></button>
												<div class="prod_filter_ul_wrapper">
													<ul id="prods_categories" class="prod_filter_ul">
														<li class="" tch_retailer="" thc_menu_type="" attr_value="0_1_%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">0 - 1%</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="1.0001_100%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">1% +</span></div>
														</li>

														<li class="" tch_retailer="" thc_menu_type="" attr_value="0_100_mg">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">0mg - 100 mg</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="100.0001_100000_mg">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">100mg +</span></div>
														</li>

													</ul>
												</div>
											</div>
											<!-- potency THC -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="potency_thc">
												<button class="lb_prod_filter_attr_title"><strong>Potency : THC</strong></button>
												<div class="prod_filter_ul_wrapper">
													<ul id="prods_categories" class="prod_filter_ul">
														<li class="" tch_retailer="" thc_menu_type="" attr_value="0_20_%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">0-20%</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="20.001_30_%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">20-30%</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="30.001_40_%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">30-40%</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="40.001_1000_%">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">40%+</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="0_100_mg">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">0-100mg</span></div>
														</li>
														<li class="" tch_retailer="" thc_menu_type="" attr_value="100.001_10000_mg">
															<div class="prod_cat_select_icon"><svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle>
																	<path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path>
																</svg></div>
															<div class="prod_cat_select_lable lf-common"><span class="">100mg+</span></div>
														</li>
													</ul>
												</div>
											</div>
											<!-- effects -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="effects">
												<?php
												// Effects
												echo '<p class="lb_prod_filter_attr_title"><strong>Effects</strong></p>';
												$effects_array = array();
												foreach ($lb_product_filter_options['effects'] as $key => $effects_main) {
													foreach ($effects_main as $key => $effect) {
														(!in_array($effect, $effects_array)) ?  array_push($effects_array, $effect) : '';
													}
												}
												echo '<div class="prod_filter_ul_wrapper" >';
												echo '<ul id="prods_effects" class="prod_filter_ul">';
												sort($effects_array);
												foreach ($effects_array as $key => $effects_array_node) {
													echo '<li class="" attr_value="' . $effects_array_node . '">';
													echo '<div class="prod_cat_select_icon">';
													echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
													echo '</div>';
													echo '<div class="prod_cat_select_lable lf-common">';
													echo '<span class="">';
													echo __(strtolower(str_replace("_", " ", $effects_array_node)), 'leafbridge');
													echo '</span>';
													echo '</div>';
													echo '</li>';
												}
												echo '</ul>';
												echo '</div>';

												?>
											</div>
											<!-- strainType -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="strainType">
												<?php
												echo '<p class="lb_prod_filter_attr_title"><strong>Strain Type</strong></p>';

												echo '<div class="prod_filter_ul_wrapper" >';
												echo '<ul id="prods_strainType" class="prod_filter_ul">';
												sort($effects_array);
												foreach ($lb_product_filter_options['strainType'] as $key => $strain_node) {
													echo '<li class="" attr_value="' . $strain_node . '">';
													echo '<div class="prod_cat_select_icon">';
													echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
													echo '</div>';
													echo '<div class="prod_cat_select_lable lf-common">';
													echo '<span class="">';
													echo __(strtolower(str_replace("_", " ", $strain_node)), 'leafbridge');
													echo '</span>';
													echo '</div>';
													echo '</li>';
												}
												echo '</ul>';
												echo '</div>';

												?>
											</div>

											<!-- weight -->
											<div class="lb_prod_filter_attr_box" filter_selected_val="" filter_attr="weight">
												<button class="lb_prod_filter_attr_title"><strong>Weight</strong></button>
												<div class="prod_filter_ul_wrapper">
													<ul id="prods_strainType" class="prod_filter_ul">
														<?php
														// leafbridge_filters_xdx
														$leafbridge_filters_xdx = get_option('leafbridge_filters_xdxs');
														$leafbridge_filters_weights = $leafbridge_filters_xdx['weight'];
														echo '<pre style="display:none;">';
														print_r($leafbridge_filters_xdx);
														echo '</pre>';

														foreach ($leafbridge_filters_weights  as $retailer_id_weight => $retailer_weight_node) {
															foreach ($retailer_weight_node as $retailer_menu_type => $r_m_weights) {
																echo '<div class="weight_retailer" retailer_id="' . $retailer_id_weight . '" menu_type="' . $retailer_menu_type . '">';
																sort($r_m_weights);
																echo '<div class="weight_buttons_wrapper">';
																foreach ($r_m_weights as $r_m_weight) {
																	echo '<li class="" attr_value="' . $r_m_weight . '">';
																	echo '<div class="prod_cat_select_icon">';
																	echo '<svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="11" fill="#F4F2EC"></circle><path fill-rule="evenodd" clip-rule="evenodd" d="M5 11.0665L7.37441 8.57501L9.51291 11.0125L15.2778 6L17 7.83508L9.46049 15.75L5 11.0665Z" fill="white"></path></svg>';
																	echo '</div>';
																	echo '<div class="prod_cat_select_lable lf-common">';
																	echo '<span class="">';
																	echo __(strtolower(str_replace("_", " ", $r_m_weight)), 'leafbridge');
																	echo '</span>';
																	echo '</div>';
																	echo '</li>';
																}
																echo '</div>';
																echo '</div>';
															}
														}

														?>
													</ul>
												</div>
											</div>

											<!--other categories -->
											<div style="" class="other_cats_wrapper">
												<p class="lb_prod_filter_attr_title"><strong>Other Categories</strong></p>
												<?php
												echo '<div class="other_cats_inner" >';
												echo '<ul id="" class="" >';
												foreach ($terms as $key => $term) {
													if ($queried_obj->name !== $term->name) {
														echo '<li class="">';
														echo '<a href="' . get_term_link($term->term_id) . '">' . str_replace('_', ' ', $term->name) . '</a>';
														echo '</li>';
													}
												}
												echo '</ul>';
												echo '</div>';
												?>
											</div>

											<?php
											echo $filter_btn;
											?>
										</div>
									</div>
									<div class="wizard_prods_view">

										<div class="class_toggle_category">
											<i class="fa-solid fa-bars"></i>
											<p><?php echo __('View Filters', 'leafbridge') ?></p>
										</div>
										<div id="wizard_prods_view">
											<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto; animation-play-state: running; animation-delay: 0s;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
												<path fill="none" stroke="#f4bd33" stroke-width="8" stroke-dasharray="42.76482137044271 42.76482137044271" d="M24.3 30C11.4 30 5 43.3 5 50s6.4 20 19.3 20c19.3 0 32.1-40 51.4-40 C88.6 30 95 43.3 95 50s-6.4 20-19.3 20C56.4 70 43.6 30 24.3 30z" stroke-linecap="round" style="transform: scale(0.8); transform-origin: 50px 50px; animation-play-state: running; animation-delay: 0s;">
													<animate attributeName="stroke-dashoffset" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0;256.58892822265625" style="animation-play-state: running; animation-delay: 0s;" />
												</path>
											</svg>
										</div>
										<section class="wizard_product_section" id="prod_show_cat_all" data_category="all" style="">
											<!-- <h3><?php //echo __( 'All Products', 'leafbridge' )
														?></h3> -->
											<div class="wizard_sort_wrapper" id="wizard_sort_wrapper">
												<h1><?php echo ($queried_obj->name == "Cbd") ? "CBD" : ($queried_obj->name == "Pre_rolls" ? "Pre Rolls" : $queried_obj->name); ?></h1>
												<div class="wizard_controls_wrapper">
													<div class="wizard_sort_by">
														<select class="" name="" id="leafbridge_products_sort_by" title="Select the sorting order">
															<?php
															$sort_dropdown_array = array(
																array('NAME_ASC', 'Name A - Z'),
																array('NAME_DESC', 'Name Z - A'),
																// array('POPULAR_ASC', 'Popularity Low - High'),
																array('POPULAR_DESC', 'Top Sellers'),
																array('PRICE_ASC', 'Price Low - High'),
																array('PRICE_DESC', 'Price High - Low'),
																array('POTENCY_ASC', 'Potency Low - High'),
																array('POTENCY_DESC', 'Potency High - Low'),
															);

															$selected_element = (isset($_GET['sort'])) ? $_GET['sort'] : 'NAME_ASC';

															foreach ($sort_dropdown_array as $key => $sort_link) {
																$mark_selected = ($selected_element == $sort_link[0]) ? 'selected' : '';
																echo '<option value="' . $sort_link[0] . '" ' . $mark_selected . ' >' . $sort_link[1] . '</option>';
															}
															?>
														</select>
													</div>
													<div class="wizard_toggle_list_grid">
														<div class="list_grid_buttons_wrapper">
															<button type="button" value="grid" name="button" value="grid" class="" title="Toggle grid view"><i class="fa-solid fa-grip"></i></button>
															<button type="button" value="list" name="button" value="list" title="Toggle list view"><i class="fa-solid fa-list"></i></button>
														</div>
													</div>
												</div>
											</div>
											<div class="wizard_category_products_showcase">

											</div>
											<div class="wizard_category_products_pagination allow_pages">
												<button type="button" name="button" class="leaf_bridge_btn prev" data_page="0" style="display:none;"><?php _e('back', 'leafbridge') ?></button>
												<p id="pagination_text"></p>
												<button type="button" name="button" class="leaf_bridge_btn next" gg data_page="2" style="display:none;"><?php _e('next', 'leafbridge') ?></button>
											</div>
										</section>

										<?php
										foreach ($leafbridge_product_categories as $key => $leafbridge_product_category) {
											// if($leafbridge_product_category !== "NOT_APPLICABLE"):
											if (false) :
										?>
												<section class="wizard_product_section" id="prod_show_cat_<?php echo $leafbridge_product_category ?>" data_category="<?php echo $leafbridge_product_category ?>" style="display:none;">
													<h3><?php echo __(strtolower(str_replace("_", " ", $leafbridge_product_category)), 'leafbridge') ?></h3>
													<div class="wizard_category_products_showcase">

													</div>
													<div class="wizard_category_products_pagination">
														<button type="button" name="button" class="leaf_bridge_btn prev" data_page="-1"><?php _e('back', 'leafbridge') ?></button>
														<button type="button" name="button" class="leaf_bridge_btn next" data_page="1"><?php _e('next', 'leafbridge') ?></button>
													</div>
												</section>
										<?php
											endif;
										}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</main><!-- #main -->
</div><!-- #primary -->

<?php  ?>