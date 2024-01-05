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

$plugin_WPDutchie = new LeafBridge_Products();
?>

<div class="lf-action-wrap">
	<div class="lf-ui-sidebar-wrapper">

		<div class="lf-inside">

			<div class="lf-panel">

				<div class="lf-panel-header">
					<h3><?php _e('LeafBridge Shortcode Generator', 'leafbridge'); ?></h3>
				</div>

				<div class="lf-panel-content">

					<?php

					//$leafbridge_settings = get_option('leafbridge-settings');
					//echo "<pre>"; print_r($leafbridge_settings) ; echo "</pre>";

					$lb_product_filter_options = get_option('lb_product_filter_options');
					//echo "<pre>"; print_r($lb_product_filter_options) ; echo "</pre>";

					$retailers_array = array();
					$args = array(
						'post_type' => 'retailer',
						'posts_per_page' => -1,
						'post_status' => array('publish')
					);
					$retailer_loop = new WP_Query($args);

					while ($retailer_loop->have_posts()) {
						$retailer_loop->the_post();
						$_lb_retailer_single_id = get_post_meta(get_the_ID(), '_lb_retailer_single_id', true);
						$_lb_retailer_single_title = get_the_title();
						$retailers_array[$_lb_retailer_single_id] = $_lb_retailer_single_title;
					}

					//$r = $plugin_WPDutchie->getSpecials($retailer_id='f0ff5c46-2f0c-4137-941b-b79b71e1d85c');
					//echo "<pre>"; print_r($retailers_array) ; echo "</pre>";

					//echo "<pre>"; print_r($r) ; echo "</pre>";

					?>
					<div class="lf-admin-row">
						<div class="lf-admin-col-3">
							<label>Category</label>
							<select onchange="lb_generate_shortcode();resetSubCat();" id="lb-sc-category">
								<option value="">- Select -</option>
								<option value="ACCESSORIES">ACCESSORIES</option>
								<option value="APPAREL">APPAREL</option>
								<option value="CBD">CBD</option>
								<option value="CLONES">CLONES</option>
								<option value="CONCENTRATES">CONCENTRATES</option>
								<option value="EDIBLES">EDIBLES</option>
								<option value="FLOWER">FLOWER</option>
								<option value="ORALS">ORALS</option>
								<option value="PRE_ROLLS">PRE_ROLLS</option>
								<option value="SEEDS">SEEDS</option>
								<option value="TINCTURES">TINCTURES</option>
								<option value="TOPICALS">TOPICALS</option>
								<option value="VAPORIZERS">VAPORIZERS</option>
								<option value="NOT_APPLICABLE">NOT_APPLICABLE</option>
							</select>
						</div>

						<div class="lf-admin-col-3">
							<label>Sub Category</label>
							<select onchange="lb_generate_shortcode();" id="lb-sc-sub-category">
								<option value="">- Select -</option>
								<?php
								$categories_array = $lb_product_filter_options['categories'];
								//echo "<pre>"; print_r($categories_array) ; echo "</pre>"; 
								foreach ($categories_array as $cat_id => $categories) {
									asort($categories);
									echo '<optgroup label="' . strtoupper($cat_id) . '">';
									foreach ($categories as $sub_category_id => $sub_category) {
										echo '<option value="' . strtoupper($sub_category) . '">' . __($sub_category, 'leafbridge') . '</option>';
									}
									echo '</optgroup>';
								}
								?>
							</select>
						</div>

						<div class="lf-admin-col-3">
							<label>Brand</label>
							<select onchange="lb_generate_shortcode();" id="lb-sc-Brand">
								<option value="">- Select -</option>
								<?php
								$brands_array = $lb_product_filter_options['brands'];
								$brands_array_display = array();

								foreach ($brands_array as $retailer_id => $retailer_node_brands) {
									asort($retailer_node_brands);
									echo '<optgroup label="' . $retailers_array[$retailer_id] . '">';
									foreach ($retailer_node_brands as $retailer_category => $retailer_category_brands) {
										$retailer_category_brands = array_unique($retailer_category_brands);
										asort($retailer_category_brands);
										foreach ($retailer_category_brands as $brand_id => $brand_name) {
											if ($brand_id != '0' || $brand_id != 0) {
												echo '<option value="' . $brand_id . '">' . __($brand_name, 'leafbridge') . '</option>';
											}
										}
									}
									echo '</optgroup>';
								}
								?>

							</select>
						</div>

						<div class="lf-admin-col-3">
							<label>Effect</label>
							<select onchange="lb_generate_shortcode();" id="lb-sc-Effect">
								<option value="">- Select -</option>
								<option value="CALM">CALM</option>
								<option value="CLEAR_MIND">CLEAR_MIND</option>
								<option value="CREATIVE">CALM</option>
								<option value="ENERGETIC">ENERGETIC</option>
								<option value="FOCUSED">FOCUSED</option>
								<option value="HAPPY">HAPPY</option>
								<option value="INSPIRED">INSPIRED</option>
								<option value="RELAXED">RELAXED</option>
								<option value="SLEEPY">SLEEPY</option>
								<option value="UPLIFTED">UPLIFTED</option>
							</select>
						</div>


						<div class="lf-admin-col-3">
							<label>Strain Type</label>
							<select onchange="lb_generate_shortcode();" id="lb-sc-Strain-Type">
								<option value="">- Select -</option>
								<option value="HIGH_CBD">HIGH_CBD</option>
								<option value="HYBRID">INSPIRED</option>
								<option value="INDICA">INDICA</option>
								<option value="SATIVA">SATIVA</option>
								<option value="NOT_APPLICABLE">NOT_APPLICABLE</option>
							</select>
						</div>


						<div class="lf-admin-col-3">
							<label>Special Products</label>
							<select onchange="lb_generate_shortcode();" id="lb-sc-Special">
								<option value="">- Select -</option>
								<option value="true">Yes</option>
							</select>
						</div>

						<div class="lf-admin-col-3 lf-special-names">
							<label>Special Product</label>
							<select onchange="lb_generate_shortcode();" id="lb-sc-special-name">
								<option value="">- Select -</option>
								<?php
								foreach ($retailers_array as $retailer_id => $retailer_name) {
									echo '<optgroup label="' . $retailer_name . '">';
									foreach ($plugin_WPDutchie->getSpecials($retailer_id) as $special_item) {
										if (count($special_item) == 0 || empty($special_item)) {
											echo '<option value="" disabled>No items</option>';
										} else {
											echo '<option value="' . $special_item['id'] . '">' . $special_item['name']  . '</option>';
										}
									}
								}
								?>
							</select>
						</div>
						<div class="lf-admin-col-3 lb-custom-section">
							<label>Custom section</label>
							<select onchange="lb_generate_shortcode();" id="lb-sc-custom-section">
								<option value="">- Select -</option>
								<option value="true">Yes</option>
							</select>
						</div>
						<div class="lf-admin-col-3 lb-custom-menu-name">
							<label>Custom section name</label>
							<input oninput="lb_generate_shortcode()" type="text" name="lb-custom-menu-name" id="lb-custom-menu-name">
						</div>

						<div class="lf-admin-col-3">
							<label>Staff Picks</label>
							<select onchange="lb_generate_shortcode();" id="lb-sc-staff_picks">
								<option value="">- Select -</option>
								<option value="yes">Yes</option>
								<option value="no">No</option>
							</select>
						</div>

						<div class="lf-admin-col-3">
							<label>Sort</label>
							<select onchange="lb_generate_shortcode();" id="lb-sc-Sort">
								<option value="">- Select -</option>
								<optgroup label="NAME">
									<option value="NAME_ASC">Ascending</option>
									<option value="NAME_DESC">Descending</option>
								</optgroup>

								<optgroup label="POPULAR">
									<option value="POPULAR_ASC">Ascending</option>
									<option value="POPULAR_DESC">Descending</option>
								</optgroup>

								<optgroup label="PRICE">
									<option value="PRICE_ASC">Ascending</option>
									<option value="PRICE_DESC">Descending</option>
								</optgroup>

								<optgroup label="POTENCY">
									<option value="POTENCY_ASC">Ascending</option>
									<option value="POTENCY_DESC">Descending</option>
								</optgroup>
							</select>
						</div>

						<div class="lf-admin-col-3">
							<label>Number of Products</label>
							<input type="number" class="" value="20" min="10" id="lb-sc-Products" onchange="lb_generate_shortcode();" onkeyup="lb_generate_shortcode();" />
						</div>

						<div class="lf-admin-col-3">
							<label>Slider Autoplay</label>
							<select onchange="lb_generate_shortcode();" id="lb-autoplay">
								<option value="">- Select -</option>
								<option value="true">Yes</option>
								<option value="false">No</option>
							</select>
						</div>

					</div>


					<div class="lf-admin-row">
						<div class="lf-admin-shortcode-output">
							<span onclick="lb_shortcode_copyText(this)">Copy</span>
							<code>
							</code>
						</div>
					</div>

				</div>




			</div>




		</div>

	</div>
</div>

<div class="lf-action-wrap">
	<div class="lf-ui-sidebar-wrapper">

		<div class="lf-inside">

			<div class="lf-panel">

				<div class="lf-panel-header">
					<h3><?php _e('Retailer Based  Shortcode Generator', 'leafbridge'); ?></h3>
				</div>

				<div class="lf-panel-content">
					<div class="lf-admin-row">
						<div class="lf-admin-col-3">
							<label>Retailer name</label>

							<select onchange="lb_retailer_shortcode()" id="lb-sc-retailers">
								<option value="">-Select-</option>
								<?php
								foreach ($retailers_array as $retailer_id => $retailer_name) {
									echo '<option value="' . $retailer_id . '">' . $retailer_name . '</option>';
								}
								?>
							</select>
						</div>
						<div class="lf-admin-col-3">
							<label>Show filter</label>
							<select onchange="lb_retailer_shortcode()" id="lb-sc-filter">
								<option value="on">On</option>
								<option value="off">Off</option>
							</select>
						</div>
						<div class="lf-admin-col-3">
							<label>Menu type</label>
							<select onchange="lb_retailer_shortcode()" id="lb-sc-menu-type">
								<option value="">-Select-</option>
								<option value="MEDICAL">Medical</option>
								<option value="RECREATIONAL">Recreational</option>
							</select>
						</div>
						<div class="lf-admin-col-3">
							<label>Order type</label>
							<select onchange="lb_retailer_shortcode()" id="lb-sc-order-type">
								<option value="">-Select-</option>
								<option value="PICKUP">Pickup</option>
								<option value="DELIVERY">Delivery</option>
							</select>
						</div>
						<div class="lf-admin-col-3">
							<label>Force wizard</label>
							<select onchange="lb_retailer_shortcode()" id="lb-sc-force-type">
								<option value="">-Select-</option>
								<option value="on">On</option>
								<option value="off">Off</option>
							</select>
						</div>
						<div class="lf-admin-col-3">
							<label>Show products</label>
							<select onchange="lb_retailer_shortcode()" id="lb-sc-show-products">
								<option value="">-Select-</option>
								<option value="on">On</option>
								<option value="off">Off</option>
							</select>
						</div>
					</div>
					<div class="lf-admin-row">
						<div class="lf-admin-retailer-shortcode-output">
							<span onclick="lb_retailer_shortcode_copyText(this)">Copy</span>
							<code>
							</code>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>

<div class="lf-action-wrap">
	<div class="lf-ui-sidebar-wrapper">

		<div class="lf-inside">

			<div class="lf-panel">

				<div class="lf-panel-header">
					<h3><?php _e('Retailer Details Shortcode Generator', 'leafbridge'); ?></h3>
				</div>

				<div class="lf-panel-content">
					<div class="lf-admin-row">
						<div class="lf-admin-col-3">
							<label>Retailer name</label>

							<select onchange="lb_retailer_details_shortcode()" id="lb-sc-retailer">
								<option value="">-Select-</option>
								<?php
								foreach ($retailers_array as $retailer_id => $retailer_name) {
									echo '<option value="' . $retailer_id . '">' . $retailer_name . '</option>';
								}
								?>
							</select>
						</div>
					</div>
					<div class="lf-admin-row">
						<div class="lf-admin-retailer-details-shortcode-output">
							<span onclick="lb_retailer_details_shortcode_copyText(this)">Copy</span>
							<code>
							</code>
						</div>
					</div>

				</div>

			</div>

		</div>
	</div>
</div>