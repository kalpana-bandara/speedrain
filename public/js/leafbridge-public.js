"use strict";
// on history back , reload the page
(function ($) {
  /**
   * All of the code for your public-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  // this is for showing selected categories on product view - old version
  $(document).on("click", ".leafbridge_category_filter_list_items", function (event) {
    event.preventDefault();
    var selected_filter = $(this).attr("data_category");
    var selected_categories = [];
    // $(this).closest('ul').find('.selected_cat').removeClass('selected_cat');
    if (!$(this).hasClass("selected_cat")) {
      $(this).addClass("selected_cat");
    } else {
      $(this).removeClass("selected_cat");
    }

    // go through all filter clickables and collect selected categories
    $(".leafbridge_category_filter_list .leafbridge_category_filter_list_items").each(function (index, el) {
      if ($(this).hasClass("selected_cat")) {
        selected_categories.push($(this).attr("data_category"));
      }
    });

    if (selected_categories.length > 0) {
      $(".leafbridge_prod_section_wrapper .leafbridge_product_section").each(function (index, el) {
        if (selected_categories.includes($(this).attr("data_category"))) {
          $(this).fadeIn("250");
        } else {
          $(this).hide();
        }
      }
      );
    } else {
      $(".leafbridge_prod_section_wrapper .leafbridge_product_section").fadeIn("250");
    }
  }
  );

  // search bar Enter key
  $("#products_search_input").keyup(function (event) {
    if ($("#products_search_input").is(":focus") && event.key == "Enter") {
      event.preventDefault();
      $("#products_search_button").click();
    }
  });

  // closing product modals
  $(document).on("click", ".leafbridge_product_modal_outer_bg , .leafbridge_product_modal_close", function (event) {
    // $(this).closest('.leafbridge_product_modal_outer').hide();
    $("#popup_container").html("");
    $("#popup_container").removeClass("open_popup");
  }
  );

  // This is for the wizard for customers selecting thier retailer and goes to viewing products

  // enable age confirmation buttons after agreeing terms and conditions
  $(document).on("click", "#LeafBridgeTandC", function (event) {
    // event.preventDefault();
    $(this).closest(".leafbridge_wizard_popup").find(".leafbridge_popup_box_grouped_inputs").toggleClass("disabled-btn-panel");
    if (this.checked) {
      $(".leafbridge_popup_box_grouped_inputs.wizard_box_button_group button").prop("disabled", false).removeClass("disabled-btn");
    } else {
      $(".leafbridge_popup_box_grouped_inputs.wizard_box_button_group button").prop("disabled", true).addClass("disabled-btn");
    }
  });

  // age confirmation
  $(document).on("click", "#leafbridge_shop_wizard_popup .wizard_age", function () {
    var cust_age = $(this).val();
    if (cust_age == "yes") {
      $("#leafbridge_shop_wizard_set_age .wizard_box_container_wrapper .error").hide();
      // $('#leafbridge_shop_wizard_popup').remove();
      var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));

      if (leafbridge_wizard_data == null) {
        // wizard will have this condition only default retailers are not set
        var leafbridge_wizard_data = {
          age_confirm: true,
          created_time: new Date(),
        };
        var data_direction = $(this).attr("data_direction"); // works only when default retailers are not set
        $(this).closest(".leafbridge_shop_wizard_step").hide();
        $(data_direction).fadeIn("250");
      } else {
        leafbridge_wizard_data.age_confirm = true;
        leafbridge_wizard_data.created_time = new Date();
        $("#leafbridge_shop_wizard_popup").hide();
      }
      localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data));
    } else if (cust_age == "no") {
      $("#leafbridge_shop_wizard_set_age .wizard_box_container_wrapper .error").fadeIn();
    }
  }
  );

  // go back and next buttons
  $(document).on("click", ".wizard_box_nav_buttons .wizard_box_button_group button", function () {
    var data_direction = $(this).attr("data_direction");
    if (data_direction) {
      $(this).closest(".leafbridge_shop_wizard_step").hide();
      $(data_direction).fadeIn("250");

      if ($(this).attr("id") == "wizard_show_products") {
        $("#leafbridge_shop_wizard_popup").hide();
      }
    }
  }
  );

  // select menu type
  $(document).on("click", ".wizardbox_tab_head", function () {
    var target_content = $(this).attr("data_tab");
    $(target_content).siblings(".wizardbox_tab_content").hide();
    $(".active_btn").removeClass("active_btn");
    $(target_content).fadeIn("250");
    $(this).addClass("active_btn");
  });

  // select retailer
  /*
      * Since selecting whether delivery or store pickup is based on selection of the
        retailer, it will require an ajax function. so this function is on leafbridge-public-ajax.js file
      */

  // show zipcode validation when delivery is selected
  $(document).on("click", ".prod_collection_btn", function (event) {
    event.preventDefault();
    $(".error_zip_validation").hide();

    $(this).siblings(".leaf_bridge_btn").removeClass("active_btn");
    $(this).addClass("active_btn");

    // if ($(this).val() == 'DELIVERY') {
    // 	$('.wizardbox_zipcode_validation_wrapper').addClass('show_zipcode_validation');
    // 	$(this).closest('.leafbridge_shop_wizard_step').find('button.next').hide();
    // }
    // else if ($(this).val() == 'PICKUP') {
    // 	$('.wizardbox_zipcode_validation_wrapper').removeClass('show_zipcode_validation');
    // 	$(this).closest('.leafbridge_shop_wizard_step').find('button.next').fadeIn('250');
    // }
    // else {
    //
    // }

    // remove this when delivery zip is being validated
    $(this).closest(".leafbridge_shop_wizard_step").find("button.next").fadeIn("250");

    var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    leafbridge_wizard_data.collection_method = $(this).val();
    localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data));
  });

  //  check availability is on ajax file

  /*
   * Reset reatiler , menu type and delivery selection
   */
  $(document).on("click", ".reset_retailer_selection", function () {
    var close_button = '<div class="leafbridge_wizard_popup_close"><svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" class="close-button__StyledSvg-sc-1rp6nt-0 duhkda"><circle cx="18" cy="18" r="17.5" stroke="#1f2b49"></circle><path d="M22.8536 13.8539C23.0496 13.6578 23.0487 13.3407 22.8515 13.1456C22.6542 12.9506 22.3354 12.9516 22.1394 13.1477L13.1464 22.1461C12.9504 22.3422 12.9513 22.6593 13.1485 22.8544C13.3458 23.0494 13.6646 23.0484 13.8606 22.8523L22.8536 13.8539Z" fill="#1f2b49"></path><path d="M13.1464 13.8539C12.9504 13.6578 12.9513 13.3407 13.1485 13.1456C13.3458 12.9506 13.6646 12.9516 13.8606 13.1477L22.8536 22.1461C23.0496 22.3422 23.0487 22.6593 22.8515 22.8544C22.6542 23.0494 22.3354 23.0484 22.1394 22.8523L13.1464 13.8539Z" fill="#1f2b49"></path></svg></div>';

    $("#leafbridge_shop_wizard_popup").fadeIn("250");
    $("#leafbridge_shop_wizard_popup").siblings(".leafbridge_shop_wizard_step").hide();
    $("#leafbridge_shop_wizard_popup .leafbridge_popup_box").fadeIn("250");
    $("#leafbridge_shop_wizard_set_age").hide();
    $("#leafbridge_shop_wizard_reset_selection_confirm").fadeIn("250");
    $('#leafbridge_shop_wizard_reset_selection_confirm button[data_direction="#leafbridge_shop_wizard_set_age"]').hide();
    $("#leafbridge_shop_wizard_set_location	").hide();
    if (!$("#leafbridge_shop_wizard_popup .leafbridge_shop_wizard_container").hasClass("leafbridge_wizard_popup_close")) {
      // $('#leafbridge_shop_wizard_popup .leafbridge_shop_wizard_container').append(close_button);
    }
  });

  // wizardpopup close
  $(document).on("click", ".leafbridge_wizard_popup_close", function () {
    $("#leafbridge_shop_wizard_popup").hide();
  });

  // disallow userss not to add more than available products
  $(document).on("input change keydown keypress keyup", ".add_to_cart_count", function (event) {
    var input_val = parseInt($(this).val());
    var max_val = parseInt($(this).attr("max"));
    if (input_val > max_val) {
      $(this).val(max_val);
    } else {
      $(this).val(input_val);
    }
  });

  // open the cart button
  $(document).on("click", "#open_the_cart , #nav_open_the_cart , .open_the_cart_button , .open_the_cart_button_icon", function (e) {
    e.preventDefault();
    $("#floating_cart").addClass("open_the_cart");
  });

  // close the cart button
  $(document).on("click", "#close_floating_cart , .floating_cart_bg", function () {
    $("#floating_cart").removeClass("open_the_cart");
  });

  // toggle category menu on mobile devices
  $(document).on("click", ".class_toggle_category , #specials_tab_filter_background", function () {
    $(".wizard_prods_categories").toggleClass("open_cats");
    $("#specials_tab_filter_background").toggleClass("open_cats");
    $(".specials_tab_filter").toggleClass("open_cats");
  });

  // open more data boxes on single product page
  $(document).on("click", ".tab_toggler", function () {
    $(this).closest(".lb_prod_single_tabs_inner").find(".lb_prod_single_tab_content").removeClass("active_tab");
    var target_tab = $(this).attr("data_toggle");
    $(target_tab).addClass("active_tab");
    $(this).siblings(".tab_toggler").removeClass("active_tab");
    $(this).addClass("active_tab");
  });

  $(document).on("click", ".info_icon", function (event) {
    $(".sub_info_box_inner_collapse").removeClass("show_info");
    $(this).closest(".sub_info_box").find(".sub_info_box_inner_collapse").addClass("show_info");
    $(this).closest(".lb_prod_single_accordion_content").toggleClass("overflow_nope");
  });
  $(document).on("click", ".close_info", function (event) {
    $(this).closest(".sub_info_box").find(".sub_info_box_inner_collapse").removeClass("show_info");
  });

  $(document).on("click", ".lb_categories_list p", function () {
    $(this).closest(".lb_categories_list").toggleClass("expand_list");
  });

  $(document).on("change", ".prods_filter_metric_selector_wrapper select", function (event) {
    var selected_metric = $(this).val();
    var target_metric = $(this).closest(".lb_prod_filter_attr_box").find('.lb_prod_filter_attr_range[metric_ref="' + selected_metric + '"]');
    target_metric.siblings(".lb_prod_filter_attr_range").hide();
    target_metric.css("display", "inline-flex");
  });

  $(document).on("click", ".wizard_prods_inner .wizard_prods_categories .lb_prod_filter_attr_title", function () {
    $(this).closest(".lb_prod_filter_attr_box").toggleClass("open_attr_box");
  });

  // highlihght empty dropdown on floating wizard`
  $(document).on("click", "#header_stiky_wizard select", function () {
    check_empty_wizard_dropdowns_and_hightlight();
  });
  function check_empty_wizard_dropdowns_and_hightlight() {
    $("#header_stiky_wizard select").each(function (index, el) {
      if ($(this).val() == "") {
        $(this).addClass("empty");
      } else {
        $(this).removeClass("empty");
      }
    });
  }

  /*
   * list grid view toggle
   */
  $(document).on("click", ".wizard_toggle_list_grid button", function () {
    var view_type = $(this).val();
    set_products_view_type(view_type);
    localStorage.setItem("view_mode", view_type);
  });

  function set_products_view_type(view_type) {
    $(".wizard_toggle_list_grid button[value=" + view_type + "]").siblings("button").removeClass("selected_view");
    $(".wizard_toggle_list_grid button[value=" + view_type + "]").addClass("selected_view");

    if (view_type == "list") {
      $(".wizard_category_products_showcase").addClass("list");
      $(".wizard_category_products_showcase").removeClass("grid");
    } else if (view_type == "grid") {
      $(".wizard_category_products_showcase").addClass("grid");
      $(".wizard_category_products_showcase").removeClass("list");
    }
  }

  // age popup on ticking t&c checkbox
  $(document).on("change", ".age-popup-box #age-popup-tnc", function () {
    if ($(this).is(":checked")) {
      $(".age-popup-btn").removeAttr("disabled");
    } else {
      $(".age-popup-btn").attr("disabled", "disabled");
    }
  });

  // age popup YES and NO Buttons
  $(document).on("click", ".age-popup-box .age-popup-btn", function () {
    var selected_age_option = $(this).val();
    if (selected_age_option == "yes") {
      var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
      if (leafbridge_wizard_data == null) {
        var leafbridge_wizard_data = {
          age_confirm: true,
          created_time: new Date(),
        };
      } else {
        leafbridge_wizard_data.age_confirm = true;
        leafbridge_wizard_data.created_time = new Date();
      }
      localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data));
      $("#age-popup-box-overlay").hide();
    } else if (selected_age_option == "no") {
      $(".age-popup-warning").fadeIn();
    }
  });

  // Position floating cart buttons
  function position_floating_cart_modal_wizard() {
    var floating_wizard_button = $("#floating_wizard_button");
    var inject_location = floating_wizard_button.attr("data_inject_location");

    if (inject_location) {
      floating_wizard_button.remove();
      var floating_wizard_button_html = '<div id="floating_wizard_button" class="injected">' + floating_wizard_button.html() + "</div>";
      $(inject_location).append(floating_wizard_button_html);
    }
  }

  function change_page_titles() {

    $("h1:contains('Pre_rolls')").html(function (i, text) {
      return text.replace("Pre_rolls", "Pre Rolls");
    });
    $("h2:contains('Pre_rolls')").html(function (i, text) {
      return text.replace("Pre_rolls", "Pre Rolls");
    });
    $("h3:contains('Pre_rolls')").html(function (i, text) {
      return text.replace("Pre_rolls", "Pre Rolls");
    });
    $("h4:contains('Pre_rolls')").html(function (i, text) {
      return text.replace("Pre_rolls", "Pre Rolls");
    });
    $("h5:contains('Pre_rolls')").html(function (i, text) {
      return text.replace("Pre_rolls", "Pre Rolls");
    });
    $("h6:contains('Pre_rolls')").html(function (i, text) {
      return text.replace("Pre_rolls", "Pre Rolls");
    });
    $("h6:contains('Pre_rolls')").html(function (i, text) {
      return text.replace("Pre_rolls", "Pre Rolls");
    });
    $("p:contains('Pre_rolls')").html(function (i, text) {
      return text.replace("Pre_rolls", "Pre Rolls");
    });
    $("div:contains('Pre_rolls')").html(function (i, text) {
      return text.replace("Pre_rolls", "Pre Rolls");
    });
    $("span:contains('Pre_rolls')").html(function (i, text) {
      return text.replace("Pre_rolls", "Pre Rolls");
    });
  }

  // add to cart warning icon
  $(document).on("click", ".add_to_cart_warning", function (e) {
    $(this).toggleClass("open_warning");
  });

  //popup close on 'Escape' key
  $(document).keyup(function (event) {
    if (event.key == "Escape") {
      if ($("#popup_container").hasClass("open_popup")) {
        event.preventDefault();
        $(".leafbridge_product_modal_close").click();
      }
    }
  });

  // on click specials filter
  $(document).on("click", ".specials_filter_attr_box ul li", function () {
    var found_results = 0;

    if ($(this).hasClass("selected")) {
      $(this).removeClass("selected");
    } else {
      $(this).closest("ul").find("li").removeClass("selected");
      $(this).addClass("selected");
    }

    var selection_array = {};
    $(".specials_tab_filter .specials_tab_filter_inner .specials_filter_attr_box").each(function (index, el) {
      var this_attr_box = $(this);
      var this_attr_name = $(this).find("ul").attr("filter_attr");
      this_attr_box.find("ul").find("li").each(function (index, el) {
        if ($(this).hasClass("selected")) {
          selection_array[this_attr_name] = $(this).attr("attr_value");
        }
      });
    });

    if (Object.keys(selection_array).length > 0) {
      // $('.specials_tab_content_inner .leafbridge_product_card').hide();
      $(".specials_tab_content_inner .leafbridge_product_card").each(function (index, el) {
        // inside each prod box
        var this_prod_box = $(this);
        var pbfd = JSON.parse($(this).attr("filter_data")); //prod_box_filter_data
        var flag_show = false;

        // loop through selection array
        for (const selection_array_attr in selection_array) {
          var selected_option = selection_array[selection_array_attr];
          // loop through product box's filter data and check if each selection matches
          var pbfd_selected_attr = pbfd[selection_array_attr];
          if (pbfd_selected_attr != null) {
            if (pbfd_selected_attr.includes(selected_option)) {
              this_prod_box.show();
              this_prod_box.addClass("show_box");
              found_results++;
            } else {
              this_prod_box.hide();
              this_prod_box.removeClass("show_box");
              break;
            }
          } else {
            this_prod_box.hide();
            break;
          }
        }
      });
    } else {
      found_results++;
      $(".specials_tab_content_inner .leafbridge_product_card").show();
      $(".specials_tab_content_inner").css("display", "inline-grid");
    }

    if ($(".specials_tab_content_inner .leafbridge_product_card.show_box").length == 0) {
      if ($(".specials_tab_content_inner .filter_error").length == 0) {
        $(".specials_tab_content_inner").append('<p class="filter_error">Sorry Could not find any matching products </p>');
        $(".specials_tab_content_inner").css("display", "block");
      }
    } else {
      $(".filter_error").remove();
      $(".specials_tab_content_inner").css("display", "inline-grid");
    }
  });

  // specials filter expand
  $(document).on("click", ".specials_filter_attr_title", function (e) {
    e.preventDefault();
    $(this).closest(".specials_filter_attr_box").toggleClass("open_ul");
  });

  // searchbar shortcode functions	
  $(document).on('click', '.products_search_button_shortcode', function () {
    var store_link = $(this).closest('.lb_search_products_input_wrapper_shortcode').attr('store_url');
    var updated_url = store_link + '?products_search=' + $(this).siblings('.products_search_input_shortcode').val();
    ($(this).siblings('.products_search_input_shortcode').val() != '') ? window.location.href = updated_url : '';
  });
  $(document).on('click', '.products_clear_search_button_shortcode', function (e) {
    e.preventDefault();
    $(this).siblings('input.products_search_input_shortcode').val('');
  })
  // search bar Enter key	
  $('.products_search_input_shortcode').keyup(function (event) {
    if ($(".products_search_input_shortcode").is(":focus") && event.key == "Enter") {
      event.preventDefault();
      $(this).siblings('.products_search_button_shortcode').trigger('click');
    }
  });

  // single product page "View Product Testing Data" accordion
  $(document).on('click', '.lb_prod_single_accordion_title', function () {
    $(this).closest('.lb_prod_single_accordion_wrapper').toggleClass('open_accordion');
  });

  $(window).on("load", function () {
    /*
     * Age Modal
     */
    var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    if (leafbridge_wizard_data != null) {
      if (leafbridge_wizard_data.age_confirm == true) {
        $("#age-popup-box-overlay").hide();
      } else {
        $("#age-popup-box").fadeIn();
      }
    } else {
      $("#age-popup-box").fadeIn();
    }
    $(".age-popup-box .age-popup-btn").attr("disabled", "disabled");

    /*
     * Position floating cart buttons
     */
    position_floating_cart_modal_wizard();
    /*
     *  position sticky wizard
     */
    if ($("div").hasClass("header_stiky_wizard")) {
      var wizard_shortcodes_arr = [];
      var final_wizard_element = [];

      // loop through all found wizard elements and store them in an array wizard_shortcodes_arr
      $(".header_stiky_wizard").each(function (index, el) {
        var parent_element = $(this).attr("parent_element");
        var autoshow_wizard = ($(this).find(".header_stiky_wizard_inner").attr("autoshow_wizard") == "true") ? true : false;
        var header_stiky_wizard = $(this).html();
        var this_element = $(this);
        var temp_arr = [];

        temp_arr["element"] = this_element[0];
        temp_arr["parent_element"] = parent_element;
        temp_arr["autoshow_wizard"] = autoshow_wizard;

        wizard_shortcodes_arr.push(temp_arr);
      });

      loop1: for (var i = 0; i < wizard_shortcodes_arr.length; i++) {
        // loop through wizard_shortcodes_arr and find if there's any shortcode wizards.
        var wizard_element_iteration = wizard_shortcodes_arr[i];
        if (wizard_element_iteration["autoshow_wizard"]) {
          // if there's wizard from link
          var inject_location = wizard_element_iteration["parent_element"];
          // before injecting, clear all wizard elements
          $(".header_stiky_wizard").remove();

          if (inject_location.length > 0) {
            $(inject_location).first().append(wizard_element_iteration["element"]);
          } else {
            $("header").first().append(wizard_element_iteration["element"]);
          }
        } else {
          // if there's wizard from shortcode,
          // loop through the wizard shortcodes array again and remove all wizards made by link
          for (var j = 0; j < wizard_shortcodes_arr.length; j++) {
            var inner_wizard_element_iteration = wizard_shortcodes_arr[j];
            if (inner_wizard_element_iteration["autoshow_wizard"]) {
              inner_wizard_element_iteration["element"].remove();
            }
          }
          break loop1;
        }
      }
    }

    var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    if (leafbridge_wizard_data) {
      $("#leafbridge_shop_wizard_popup").fadeOut();
    }

    /*
     *  when page is loaded check the localStorage and remove if they are over expiration time
     */

    // check wizard data and clear

    // var current_time = new Date();
    // if(localStorage.getItem('leafbridge_wizard_data')){
    // 	var leafbridge_wizard_data = JSON.parse(localStorage.getItem('leafbridge_wizard_data'));
    // 	var created_time = new Date(leafbridge_wizard_data.created_time);
    // 	var lc_time_diff = ((current_time.getTime() - created_time.getTime()) / 1000) / 60 ;
    //
    // 	if(lc_time_diff>60){
    // 		localStorage.removeItem('leafbridge_wizard_data');
    // 		localStorage.removeItem('leafbridge_cart_data');
    // 		$('#leafbridge_shop_wizard_popup .leafbridge_popup_box').fadeIn('250');
    // 	}
    //
    // }
    // else {
    // 	$('#leafbridge_shop_wizard_popup .leafbridge_popup_box').fadeIn('250');
    // }

    // check age
    var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    if (leafbridge_wizard_data) {
      if (leafbridge_wizard_data.age_confirm) {
        if (!leafbridge_wizard_data.hasOwnProperty("retailer_id") || leafbridge_wizard_data.collection_method == "" || leafbridge_wizard_data.menu_type == "") {
          $("#leafbridge_shop_wizard_popup").fadeIn("250");
          $("#leafbridge_shop_wizard_popup .leafbridge_popup_box").fadeIn("250");

          $("#leafbridge_shop_wizard_set_age").hide();
          $("#leafbridge_shop_wizard_set_location").fadeIn("250");
        } else {
          $("#leafbridge_shop_wizard_popup").hide();
        }
      } else {
        $("#leafbridge_shop_wizard_popup").show();
        $("#leafbridge_shop_wizard_popup .leafbridge_popup_box").fadeIn("250");
      }
    }

    /*
     * Set view grid / list view
     */
    if ($("div").hasClass("wizard_category_products_showcase")) {
      var view_type = localStorage.getItem("view_mode");
      view_type = view_type != null ? view_type : "grid";
      set_products_view_type(view_type);
    }

    // js function if PHP WP hooks are not supporting any title change
    change_page_titles();
  });
})(jQuery);
