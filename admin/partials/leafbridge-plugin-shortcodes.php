<div class="lf-action-wrap">
	<div class="lf-ui-sidebar-wrapper">

		<div class="lf-inside">

			<div class="lf-panel">

				<div class="lf-panel-header">
					<h3><?php _e('LeafBridge Plugin Shortcodes', 'leafbridge'); ?></h3>
				</div>

				<div class="lf-panel-content">

					<div>
						<h3>Main Pages Shortcodes</h3>
						<p>Shop/Store Page - <code>[leafbridge_shop_wizard]</code></p>

						<p>Display current selected retailer and page link to change the retailer - <code>[leafbridge-retailer-name-bar]</code></p>

						<p>Order Summary/Thank You Page - <code>[leafbridge-order-status]</code></p>

						<p>Single Product Page (Page Template) - <code>[leafbridge-product-single-page]</code> </p>

						<p>Product Category Page (Page Template) - <code>[leafbridge-product-single-category-page]</code></p>

						<p>Search bar shortcode - <code>[leafbridge-search-bar]</code></p>


						<p>BreadCrumbs shortcode - <code>[leafbridge-breadcrumbs]</code> ( This will be automatically visible on Store, Locations, Categories, Specials and Product pages . For any other pages you will have to add the shortcode manually )</p>



					</div>


					<div>
						<h3>Featured Products</h3>
						<p><code>[leafbridge-featured-products product_count="15"]</code></p>
						<p>
						<ul>
							<li>The product count can be changed as require</li>
							<li>By default, the product count will be 10 if the above is not specified.</li>
						</ul>
						</p>
					</div>


					<div>
						<h3>Product Categories</h3>
						<p><code>[leafbridge-product-categories]</code></p>
						<p>
						<ul>
							<li>The categories that have products available only will be displayed.</li>
							<li>Additional Note: User should upload the category images by editing LeafBridge Product Categories from Wordpress Dashboard. <br />
								(Dashboard > LeafBridge > Categories > Select one Category > You will be able to upload the Category Images. Do not Edit other Category Details)</li>
						</ul>
						</p>
					</div>


					<div>
						<h3>Special Products</h3>
						<p><code>[leafbridge-special-products]</code></p>
						<p>
						<ul>
							<li>You can create the advanced shortcode from <a href="/wp-admin/admin.php?page=leafbridge-shortcode-generater">"Shortcode Generater"</a> page.</li>
						</ul>
						</p>
					</div>

					<div>
						<h3>Special Menu Cards</h3>
						<p><code>[leafbridge-special-menu-cards]</code></p>
						<p>
							<img style="max-width:100%;" src="<?php echo LEAFBRIDGE_ADMIN_PATH . 'leafbridge/admin/images/special-menu-cards.png'; ?>" />
						</p>
					</div>



					<div>
						<h3>Single Product page and Product Category Page</h3>

						<p>
							If the website is using a block theme ( Nrdly Theme ), <a href="https://wordpress.org/plugins/gutenberg/" target="_blank">Guttenberg Plugin</a> should be installed. <br />
							And then two templates have to be created for single product page and Category page as instructed on below video.
						</p>

						<p>
							<iframe src="https://player.vimeo.com/video/746862946?h=801e345788&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="LeafBridge - Creating Page Single Product and Single Categegory page templates"></iframe>
						</p>

						<p>
							If the website is using a regular wordpress theme. It’s not required to follow the above mentioned steps. <br />
							Both Single Product page and Category Page templates will be assigned automatically.
						</p>
					</div>


					<div>
						<h3>Default Store Options</h3>
						<p><code>[leafbridge_selection_wizard_v2]</code></p>
						<p>
						<ul>
							<li>If you have widget area or sidebar you can put this shortcode or you can edit the template files and put this code to display the store default options. </li>
							<li>You can change the settings from LeafBridge > Settings > Default Store Options.</li>
							<li>Please note that, you can see the sticky menu only if you select the wizard as link from settings.</li>
						</ul>
						</p>
					</div>



				</div>




			</div>




		</div>

	</div>