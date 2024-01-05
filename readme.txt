===  LeafBridge ===
Contributors: Surge Global
Donate link: https://surge.global/
Tags: dutchie api, dutchie, wp dutchie, surge global, leafbridge
Requires at least: 5.0.0
Tested up to: 6.0
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

An Ecommerce plugin, create your own store using dutchie store.

== Description ==

An Ecommerce plugin, create your own store using dutchie store.

== Installation ==


1. Upload `leafbridge.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set your Dutchie API keys and setup your store.

== Frequently Asked Questions ==



= What about foo bar? =



== Changelog ==

= 1.0 =
* Initial Version

= 1.0.1
* Phase 1 Developments

= 1.1.1
* Phase 2 Developments
* Bug Fixes
* Added Style Changes

= 1.1.2
* Phase 2 Developments 

= 1.1.21
* Phase 2 Developments
* Shortcode issue fixed for sticky menu
* Allow to Select store default option if only one retailer

= 1.1.3 
* Product sorting option
* Number of products per row increased upto 4
* Bug fixes

= 1.1.4 
* Set location / menu / collection via link / URL query (will also combine with filter queries), Highlighting special prices and product pages/ boxes."
* Additional filtering options: "subcategories", "weight", "type", "potency", "brand", "effects" (Category pages)
* Bug fixes

= 1.1.5 
* Display specials cards via specialID=
* Shortcode / slider for specific product filters (to embed products carousel based on query combos, eg flower + indica + special). 
* Shortcode generater page added to plugin settings
* Bug fixes

= 1.1.6 
* Allowing an option for the floating cart buttons somewhere at the top of the page (eg top-right) rather than just floating.
* Adding a "list view" option to the product pages and defaulting to that on mobile (we have "card view" only right now) with Add to Cart button?
* Menu Type Array on the Product Object type (Improvement)
* Validation when a customer buys more than the intended grams they should purchase.
* Custom CSS saving issue resolved from plugin settings
* Floating cart settings added to plugin settings page
* Product synchronization optimized.
* Bug fixes

= 1.1.7 
* Improvements
* Bugo fixes

= 1.1.8 
* Improvements
* Bugo fixes

= 1.1.9 
* Improvements
* Bugo fixes

= 1.1.94
* Changed owl carousel to SwiperJS
* this is only for demo 

= 2.0.0 
* License Feature

= 2.0.12
* Changed owl carousel to SwiperJS and bug fixes

= 2.0.12
* Changed owl carousel to SwiperJS and bug fixes

= 2.0.13
* Hide weights on product page, product boxes for Edibles > Drinks subcategory
* Shrinked Product Boxes on sliders and archive pages for mobile view ( below 480px width )
* mobile - the "view filters" and "view categories" menu label set font-family to inherit

= 2.0.131
* mobile css Changes
* added shortcode to show selected retailer and link to a location page

= 2.0.132
* fixed showing some array warnings on error log
* seperate search bar not showing correct products randomly issue fixed

= 2.0.134
* added loading animation for shop, specials and category pages
* added feature : users can toggle if they don't want to use the provided product single page template. 
* added spans to show selected retailer location , menu type and collection type
* mobile view . specials page colomns set to 2 
* product carousels showing empty boxes issue fixed. 

= 2.0.135
* hiding available label from prod pages
* show sale percentage for product boxes with variants
* elementor - now single product page and category page templates can be edited. elementor page builder editor breaking down issue is fixed
* added extra div to product single page html "lb_single_prod_page_custom_script"

= 2.0.136
* added shortcode for retailer pages with force changing wizard selection
* styling bug in block theme on category pages
* breadcrumb change site name with "Home"

= 2.0.137 
* shortcode for retailer pages bug fixes and show/hide products via shortcode attributes
* showing all retailers on retailer page shortcode generator

= 2.0.138
* shortcode for retailer Improvements. now onwards, if some one lands on retailer page, that retailer will be set as the retailer sitewide.
    if user goes to another retailer wizard will automatically change itself. but if there's products in the cart,
    user will be asked if they want to reset the cart. if they reset, new retailer will be set and cart will be reset. 
    if they cancel, cart will remain same and will be able to view products from new retailer. 
    but if tried to add to the cart, again popup will ask for confirmation. 
    if pressed ok cart will be reset and new retailer and product will be added. 
    if cancelled, it will remain at previous state.
* product slider : when adding product to the cart from a different retailer, error will be shown instead of confirming to reset cart
* single product : when adding product to the cart from a different retailer, error text is changed .

= 2.0.139
* Breadcrumbs bug fixed & Last visited loaction page will be shown before category page on breadcrumbs. 
* Autoscrolling to product showcase when specials box is clicked 
* cache : false on all front-end ajax requests
* On mobile - Tap outside filter menu to close the filter menu slide in out.
* Backend - All retailers will be shown in shortcode generator and default retailer saving on setting


= 2.0.140
* Shortcode for showing products : when brand is set from url attributes, triggering click for a brand is not coupled with selected retailer. It reduces recurring clicks on brand checkbox on filter.
* Add to cart . condition to create a new cart is update in the code. (logic is still same)

= 2.0.141
* * removed .min.js extension from /public/js/leafbridge-public.js /public/js/leafbridge-public-ajax.js on /public/class-leafbridge-public.php

= 2.0.143
* can sync single retailes from settings
* single product page : has .lb_single_prod_page_custom_script element with product id and it's retailer id to inject custom data

= 2.0.144
* Added Google Tag Manager support ( need to manually add tag manager scripts )
* retailer details shortcode - issue with Elementor fixed
* Pre_roll will be displayed as Pre Rolls on category pages and document Title 
* retailers open hours and today open close times will be shown dynamically
* Option to select nonce expiration time from 12 or 24 or completely disable nonce checking. ( only disable nonce if the nonces are being cached by front-end)

= 2.0.145
* fixed order confirmation / thank you page crashing

= 2.0.146
* fixed check_ajax_referer causing issue when cached .

= 2.0.147
* added specials menus to shortcode generator
* single product page - show testing data in accordion

= 2.0.148
* Quick Sync for individual retailers
* User role access for sync option(LB Contributor)
* Display Cron Details

