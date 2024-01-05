<?php

class LeafBridge_Activator {
 
	public static function activate() {
		
						
		/*$lb_product_filter_options = get_option('lb_product_filter_options');
 
		if(isset($lb_product_filter_options) && count($lb_product_filter_options)>0) {	
			$product_filter_options_final = array(
				'categories' => $lb_product_filter_options['categories'],
				'brands' => $lb_product_filter_options['brands'],
				'potencyCbd' => $lb_product_filter_options['potencyCbd'],
				'potencyThc' => $lb_product_filter_options['potencyThc'],
				'effects' => $lb_product_filter_options['effects'],
				'staffPick' => $lb_product_filter_options['staffPick'],
				'strainType' => $lb_product_filter_options['strainType'],
				'prices' => $lb_product_filter_options['prices'],
				'weights' => $lb_product_filter_options['weights']						
			);		
			update_option( 'lb_product_filter_options', $product_filter_options_final );
		} else {
			$product_filter_options_final = array(
				'categories' => array(),
				'brands' => array(),
				'potencyCbd' => array(),
				'potencyThc' => array(),
				'effects' => array(),
				'staffPick' => array(),
				'strainType' => array(),
				'prices' => array(
							'priceMed' => array(), 
							'priceRec' => array()
						),
				'weights' => ''				
			);
			add_option( 'lb_product_filter_options', $product_filter_options_final, $deprecated = null,  $autoload = 'true' );
		} */

		        // a list of plugin-related capabilities to add to the Editor role
 
		
		global $wp_rewrite; 

		$wp_rewrite->flush_rules( true );
		flush_rewrite_rules();
	}
}
