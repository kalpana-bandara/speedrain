<?php

/**
 * The Template for displaying all single posts.
 *
 * @package Genesis Block Theme
 */


?>
<div class="data" style="white-space:pre;max-width:100vw; display:none;" gg>
  <?php
  // while ( have_posts() ) :
  // the_post();

  $post_id = get_queried_object_id();
  $post_meta = get_post_meta($post_id);

  $_leafbridge_product_meta_options_all = get_post_meta($post_id, '_leafbridge_product_meta_options_all', true);
  $_leafbridge_product_meta_all_product_data = $_leafbridge_product_meta_options_all['_leafbridge_product_meta_all_product_data'];

  // print_r($post_meta);
  // print_r($post_meta['_leafbridge_product_single_meta_retailer_id']);
  $prod_real_retailer = $post_meta['_leafbridge_product_single_meta_retailer_id'][0];
  // print_r($post_id);
  // echo $prod_real_retailer ;
  // print_r($_leafbridge_product_meta_all_product_data);

  // endwhile;
  ?>
</div>
<div id="primary" class="content-area">
  <main id="main" class="site-main">
    <div class="lb_single_prod_page_wrapper" data_product_id="<?php echo $_leafbridge_product_meta_all_product_data['id']; ?>" data_real_retailer="<?php echo  $prod_real_retailer ?>">
      <div class="lb_single_prod_page_container">
        <?php echo do_shortcode('[leafbridge-breadcrumbs]'); ?>
        <div class="lb_prod_single_grid gg2">
          <div class="lb_prod_single_img">
            <img src="<?php echo $_leafbridge_product_meta_all_product_data['image'] ?>" alt="">
          </div>
          <div class="lb_prod_single_details">
            <?php echo (isset($_leafbridge_product_meta_all_product_data['brand']['name'])) ? '<span class="lb_prod_single_brand">Brand: ' . $_leafbridge_product_meta_all_product_data['brand']['name'] . '</span>' : '' ?>
            <h1><?php echo $_leafbridge_product_meta_all_product_data['name'] ?></h1>
            <?php
            $pass_prod_meta = array(
              'item_name' => $_leafbridge_product_meta_all_product_data['name'],
              'item_id' => $_leafbridge_product_meta_all_product_data['id'],
              'category' => $_leafbridge_product_meta_all_product_data['category'],
              'subcategory' => $_leafbridge_product_meta_all_product_data['subcategory'],
              'brand_name' => (isset($_leafbridge_product_meta_all_product_data['brand']['name'])) ? $_leafbridge_product_meta_all_product_data['brand']['name'] : "NO BRAND",
              'brand' => (isset($_leafbridge_product_meta_all_product_data['brand']['id']) && ($_leafbridge_product_meta_all_product_data['brand']['id'] != null)) ? $_leafbridge_product_meta_all_product_data['brand']['id'] : '',
              'effects' => $_leafbridge_product_meta_all_product_data['effects'],
              // 'effects' => $_leafbridge_product_meta_all_product_data['effects'],
              'potencyCbd' => $_leafbridge_product_meta_all_product_data['potencyCbd']['formatted'],
              'potencyThc' => $_leafbridge_product_meta_all_product_data['potencyThc']['formatted'],
              'strainType' => $_leafbridge_product_meta_all_product_data['strainType'],
              'variants' => $_leafbridge_product_meta_all_product_data['variants'],
            );
            $prod_meta_json_html = (json_encode($pass_prod_meta));

            ?>
            <div class="lb_prod_single_add_to_cart gg lb_prod_single_section" filter_data="<?php echo htmlentities($prod_meta_json_html, ENT_QUOTES, 'UTF-8') ?> ">
              <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                <circle cx="50" cy="50" fill="none" stroke="#c8c8c8" stroke-width="10" r="35" stroke-dasharray="164.93361431346415 56.97787143782138">
                  <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50" keyTimes="0;1"></animateTransform>
                </circle>
              </svg>
            </div>
            <div class="lb_prod_single_thc_cbd lb_prod_single_section">
              <?php
              $formatted_label = ($_leafbridge_product_meta_all_product_data['strainType'] == "HIGH_CBD") ? "High CBD" : ucwords(strtolower(str_replace("_", " ", $_leafbridge_product_meta_all_product_data['strainType'])));

              echo ($_leafbridge_product_meta_all_product_data['strainType'] != "NOT_APPLICABLE") ? '<span>' .  $formatted_label . '</span>' : '';

              echo ($_leafbridge_product_meta_all_product_data['potencyThc']['formatted'] != "") ? '<span><strong>THC:</strong>' . $_leafbridge_product_meta_all_product_data['potencyThc']['formatted'] . '</span>' : '';
              echo ($_leafbridge_product_meta_all_product_data['potencyCbd']['formatted'] != "") ? '<span><strong>CBD:</strong>' . $_leafbridge_product_meta_all_product_data['potencyCbd']['formatted'] . '</span>' : '';
              ?>
            </div>
            <div class="lb_prod_single_descr lb_prod_single_section">
              <p><?php echo $_leafbridge_product_meta_all_product_data['description']; ?></p>
            </div>

            <?php if (count($_leafbridge_product_meta_all_product_data['effects']) > 0) : ?>
              <div class="lb_prod_single_effects lb_prod_single_section">
                <h2>Effects</h2>
                <?php
                foreach ($_leafbridge_product_meta_all_product_data['effects'] as $key => $effect) {
                  $formatted_effect =  ucwords(strtolower(str_replace("_", " ", $effect)));
                  echo '<span>' . $formatted_effect . '</span>';
                }
                ?>
              </div>
            <?php endif; ?>

            <div id="lb_single_prod_page_custom_script" data-product-id="<?php echo $_leafbridge_product_meta_all_product_data['id']; ?>" data-retailer_id="<?php echo $prod_real_retailer ?>"></div>

            <?php if (is_array($_leafbridge_product_meta_all_product_data['terpenes']) || is_array($_leafbridge_product_meta_all_product_data['cannabinoids'])) : ?>
              <?php if ((count($_leafbridge_product_meta_all_product_data['terpenes']) > 0) || (count($_leafbridge_product_meta_all_product_data['cannabinoids']) > 0)) : ?>
                <div class="lb_prod_single_tabs_wrapper lb_prod_single_section">
                  <div class="lb_prod_single_accordion_wrapper">
                    <div class="lb_prod_single_accordion_title">
                      <span>View Product Testing Data</span>
                    </div>
                    <div class="lb_prod_single_accordion_content">
                      <div class="lb_prod_single_tabs_inner">
                        <div class="lb_prod_single_tabs_header">
                          <?php if (count($_leafbridge_product_meta_all_product_data['terpenes']) > 0) : ?>
                            <div class="tab_toggler active_tab" ff data_toggle="#lb_prod_single_terpenes">Terpenes</div>
                          <?php endif; ?>

                          <?php if (count($_leafbridge_product_meta_all_product_data['cannabinoids']) > 0) : ?>
                            <div class="tab_toggler <?php echo (count($_leafbridge_product_meta_all_product_data['terpenes']) == 0) ? 'active_tab' : ''; ?>" data_toggle="#lb_prod_single_cannabinoids">Cannabinoids</div>
                          <?php endif; ?>

                        </div>
                        <div class="lb_prod_single_tabs_container">
                          <?php if (count($_leafbridge_product_meta_all_product_data['terpenes']) > 0) : ?>
                            <div id="lb_prod_single_terpenes" class="lb_prod_single_terpenes lb_prod_single_tab_content active_tab">

                              <div class="info_grid">
                                <?php
                                // print_r($_leafbridge_product_meta_all_product_data['terpenes']);

                                foreach ($_leafbridge_product_meta_all_product_data['terpenes'] as $key => $terpene) {
                                  $this_terpene = $terpene['terpene'];
                                ?>
                                  <div class="terpene_box sub_info_box">
                                    <i class="fa-solid fa-circle-info info_icon"></i>
                                    <span class="terpene_name sub_info_box_title"><?php echo $this_terpene['name']; ?></span>
                                    <span class="terpene_percentage sub_info_box_field"><?php echo $terpene['value'] . $terpene['unitSymbol']; ?></span>
                                    <div class="sub_info_box_inner_collapse">
                                      <span class="terpene_name sub_info_box_title"><?php echo $this_terpene['name']; ?></span>
                                      <span class="terpene_percentage sub_info_box_field"><?php echo $terpene['value'] . $terpene['unitSymbol']; ?></span>
                                      <span class="terpene_descr sub_info_box_field"><?php echo $this_terpene['description']; ?></span>
                                      <i class="fa-solid fa-xmark close_info"></i>

                                      <?php if (count($this_terpene['aromas']) > 0) { ?>
                                        <span class="terpene_aromas sub_info_box_field">
                                          <p class="sub_info_box_title">Aromas</p>
                                          <?php
                                          foreach ($this_terpene['aromas'] as $key => $aroma) {
                                            echo '<span>' . $aroma . '</span>';
                                          }
                                          ?>
                                        </span>
                                      <?php } ?>

                                      <?php if (count($this_terpene['effects']) > 0) { ?>
                                        <span class="terpene_effects sub_info_box_field">
                                          <p class="sub_info_box_title">Effects</p>
                                          <?php
                                          foreach ($this_terpene['effects'] as $key => $effect) {
                                            echo '<span>' . $effect . '</span>';
                                          }
                                          ?>
                                        </span>
                                      <?php } ?>

                                      <?php if (count($this_terpene['potentialHealthBenefits']) > 0) { ?>
                                        <span class="terpene_benefits sub_info_box_field">
                                          <p class="sub_info_box_title">Potential Health Benefits</p>
                                          <?php
                                          foreach ($this_terpene['potentialHealthBenefits'] as $key => $benefit) {
                                            echo '<span>' . $benefit . '</span>';
                                          }
                                          ?>
                                        </span>
                                      <?php } ?>
                                    </div>
                                  </div>
                                <?php
                                }
                                ?>
                              </div>
                            </div>
                          <?php endif; ?>

                          <?php if (count($_leafbridge_product_meta_all_product_data['cannabinoids']) > 0) : ?>
                            <div id="lb_prod_single_cannabinoids" class="lb_prod_single_cannabinoids lb_prod_single_tab_content <?php echo (count($_leafbridge_product_meta_all_product_data['terpenes']) == 0) ? 'active_tab' : ''; ?>">
                              <div class="info_grid">
                                <?php
                                foreach ($_leafbridge_product_meta_all_product_data['cannabinoids'] as $key => $cannabinoid) {
                                  $this_cannabinoid = $cannabinoid['cannabinoid'];
                                  // print_r($cannabinoid);
                                ?>
                                  <div class="cannabinoid_box sub_info_box">
                                    <span class="cannabinoid_name sub_info_box_title"><?php echo $this_cannabinoid['name']; ?></span>
                                    <span class="cannabinoid_name "><?php echo $cannabinoid['value'];
                                                                    echo ($cannabinoid['unit'] == "PERCENTAGE") ? '%' : '' ?></span>
                                    <i class="fa-solid fa-circle-info info_icon"></i>
                                    <div class="sub_info_box_inner_collapse">
                                      <i class="fa-solid fa-xmark close_info"></i>
                                      <span class="cannabinoid_name sub_info_box_title"><?php echo $this_cannabinoid['name'] . '-' . $cannabinoid['value'];
                                                                                        echo ($cannabinoid['unit'] == "PERCENTAGE") ? '%' : ''; ?></span>
                                      <span class="cannabinoid_description sub_info_box_field"><?php echo $this_cannabinoid['description']; ?></span>
                                    </div>
                                  </div>
                                <?php
                                }
                                ?>
                              </div>
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            <?php endif ?>

            <?php if (isset($_leafbridge_product_meta_all_product_data['brand']) && ($_leafbridge_product_meta_all_product_data['brand']['description'] !== "")) : ?>
              <div class="lb_prod_single_about_brand lb_prod_single_section">
                <h2>About the brand : <br /> <?php echo $_leafbridge_product_meta_all_product_data['brand']['name']; ?></h2>
                <img src="<?php echo $_leafbridge_product_meta_all_product_data['brand']['imageUrl']; ?>" alt="">
                <p><?php echo $_leafbridge_product_meta_all_product_data['brand']['description']; ?></p>
              </div>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </main><!-- #main -->
</div><!-- #primary -->

<?php

?>