(function ($) {
  "use strict";
  /**
   * All of the code for your public-facing ajax JavaScript source
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

  /*
   * Selecting retailer and update the next step based on retailer selection.
   */
  $(document).on("click", ".wizardbox_retailer_box button", function () {
    var menu_type = $(".wizardbox_tab_head.leaf_bridge_btn.active_btn").attr("data_menu_type");
    var data_retailer_id = $(this).attr("data_retailer_id");

    $(".selected_retailer").removeClass("selected_retailer");
    $(this).closest(".wizardbox_retailer_box").addClass("selected_retailer");

    $(this).closest(".leafbridge_shop_wizard_step").find('.leaf_bridge_btn[data_direction="#leafbridge_shop_wizard_set_delivery"]').fadeIn("250");

    var leafbridge_wizard_data = JSON.parse(
      localStorage.getItem("leafbridge_wizard_data")
    );

    if (leafbridge_wizard_data == null) {
      var leafbridge_wizard_data = {
        age_confirm: true,
        created_time: new Date(),
        collection_method: $(this).val(),
        retailer_id: data_retailer_id,
        menu_type: menu_type,
      };
    } else {
      leafbridge_wizard_data.collection_method = $(this).val();
      leafbridge_wizard_data.retailer_id = data_retailer_id;
      leafbridge_wizard_data.menu_type = menu_type;
    }
    localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data));
    set_delivery_pickup(data_retailer_id);
  });

  // ajax function associated with checking if retailer has pickup or deliver or both and
  // make the ui depending the retailer
  function set_delivery_pickup(retailer_id) {
    var data = {
      action: "show_delivery_pickup_ajax",
      retailer_id: retailer_id,
      nonce_ajax: leafbridge_public_ajax_obj.nonce,
    };

    $.ajax({
      url: leafbridge_public_ajax_obj.ajaxurl,
      dataType: "json",
      cache: false,
      data: data,
      type: "POST",
      proccessData: false,
      success: function (json) {
        var capturehtml = json.data.returnHTML;
        $(".product_collection").html(capturehtml);
      },
      error: function (json) {
        $("#leafbridge_shop_wizard_set_location")
          .find('button[data_direction="#leafbridge_shop_wizard_set_delivery"]')
          .hide();
        console.error("set_delivery_pickup err", json);
      },
      complete: function (json) { },
    });
  }

  /*
   * Validate ZIP CODE
   */
  $(document).on("click", "#wizardbox_zipcode_validation", function (event) {
    event.preventDefault();
    var wizardbox_zipcode = $("#wizardbox_zipcode").val();

    var data = {
      action: "delivery_validate_zip_code",
      zipcode: wizardbox_zipcode,
      nonce_ajax: leafbridge_public_ajax_obj.nonce,
    };

    $.ajax({
      url: leafbridge_public_ajax_obj.ajaxurl,
      dataType: "json",
      cache: false,
      data: data,
      type: "POST",
      proccessData: false,
      success: function (json) { },
      error: function (json) {
        $(".error_zip_validation").fadeIn();
      },
      complete: function (json) { },
    });
  });

  // new show products on store page
  function show_products_from_filter() {
    $("#leafbridge_shop_wizard_view_products").show();
    var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));

    // override leafbridge_wizard_data if it's empty or in retailer_based_store
    if ($("div").hasClass("retailer_based_store")) {
      leafbridge_wizard_data = {
        retailer_id: $(".retailer_based_store").attr("retailer_id"),
        menu_type: $(".retailer_based_store").attr("menu_type"),
        collection_method: $(".retailer_based_store").attr("order_type"),
      };
    }
    var force_wizard = $("div").hasClass("retailer_based_store") ? $(".retailer_based_store").attr("force_wizard") : $(".retailer_based_store").attr("force_wizard") ? "off" : "off";

    // var selected_retailer = (leafbridge_wizard_data) ? leafbridge_wizard_data.retailer_id : false;
    var selected_retailer = $("div").hasClass("retailer_based_store") ? $(".retailer_based_store").attr("retailer_id") : leafbridge_wizard_data ? leafbridge_wizard_data.retailer_id : false;
    var selected_menu_type = $("div").hasClass("retailer_based_store") ? $(".retailer_based_store").attr("menu_type") : leafbridge_wizard_data ? leafbridge_wizard_data.menu_type : false;

    selected_menu_type = selected_menu_type == "" ? leafbridge_wizard_data.menu_type : selected_menu_type;

    if (selected_retailer) {
      //  hide brands from other retailers
      $("ul#prods_brands li").hide();
      $("ul#prods_brands li").removeClass("show_brand");
      $("ul#prods_brands li[brand_retailer=" + selected_retailer + "]").show();
      $("ul#prods_brands li[brand_retailer=" + selected_retailer + "]").addClass("show_brand");

      // hide weight from other retailers
      $(".weight_retailer").hide();
      if (selected_menu_type) {
        var show_mt = selected_menu_type == "RECREATIONAL" ? "rec" : selected_menu_type == "MEDICAL" ? "med" : "";
        $('.weight_retailer[retailer_id="' + selected_retailer + '"][menu_type="' + show_mt + '"]').show();
      }
    }

    var searchParams = new URLSearchParams(window.location.search);
    var pagination = searchParams.get("pagination") ? parseInt(searchParams.get("pagination")) : 1;
    var params_counter = 0;

    searchParams.forEach((value, key) => {
      params_counter++;
    });

    $("#leafbridge_products_sort_by").val() == null ? $("#leafbridge_products_sort_by").val("NAME_ASC") : "";

    if (leafbridge_wizard_data) {
      searchParams.forEach((search_attr_val, search_attr) => {
        if (search_attr != "potency_cbd" && search_attr != "potency_thc" && search_attr != "weight" && search_attr != "products_search") {
          if (search_attr == "brands") {
            $(".lb_prod_filter_attr_box[filter_attr=" + search_attr + "] ul").find("li[attr_value=" + search_attr_val + "][brand_retailer=" + leafbridge_wizard_data.retailer_id + "]").trigger("click");
            $(".lb_prod_filter_attr_box[filter_attr=" + search_attr + "] ul").find("li[attr_value=" + search_attr_val + "][brand_retailer=" + leafbridge_wizard_data.retailer_id + "]").addClass("selected");
          } else {
            $(".lb_prod_filter_attr_box[filter_attr=" + search_attr + "] ul").find("li[attr_value=" + search_attr_val + "]").trigger("click");
            $(".lb_prod_filter_attr_box[filter_attr=" + search_attr + "] ul").find("li[attr_value=" + search_attr_val + "]").addClass("selected");
          }
        }

        // set potency_cbd
        if (search_attr == "potency_cbd") {
          var split_search_attr_val = search_attr_val.split("_");
          var metric = split_search_attr_val[2] == "%" ? "percentage" : split_search_attr_val[2];

          $(".lb_prod_filter_attr_box[filter_attr=" + search_attr.toLowerCase() + "]").find("select").val(split_search_attr_val[2]).trigger("change"); //selects metric dropdown
          $("#prods_cbd_" + metric + "_min").val(split_search_attr_val[0]).trigger("input"); //set min value
          $("#prods_cbd_" + metric + "_max").val(split_search_attr_val[1]).trigger("input"); //set max value

          $(".lb_prod_filter_attr_box[filter_attr=" + search_attr.toLowerCase() + "]").addClass("attr_set");
        }

        // set potency_thc
        if (search_attr == "potency_thc") {
          var split_search_attr_val = search_attr_val.split("_");
          var metric = split_search_attr_val[2] == "%" ? "percentage" : split_search_attr_val[2];
          $(".lb_prod_filter_attr_box[filter_attr=" + search_attr.toLowerCase() + "]").find("select").val(split_search_attr_val[2]).trigger("change"); //selects metric dropdown
          $("#prods_thc_" + metric + "_min").val(split_search_attr_val[0]).trigger("input"); //set min value
          $("#prods_thc_" + metric + "_max").val(split_search_attr_val[1]).trigger("input"); //set max value

          $(".lb_prod_filter_attr_box[filter_attr=" + search_attr.toLowerCase() + "]").addClass("attr_set");
        }

        // set search keyword
        if (search_attr == "products_search") {
          $("#products_search_input").val(search_attr_val).trigger("input");
        }

        // set filter weight
        if (search_attr == "weight") {
          $("#prod_weight").val(search_attr_val.replace("%2F", "/")).trigger("input");
        }

        if (search_attr == "sort") {
          // console.log('show_products_from_filter sort',search_attr_val);
        }
      });

      if (pagination == 1) {
        $(".wizard_category_products_pagination button.prev").hide();
        $(".wizard_category_products_pagination button.next").show();
      } else {
        $(".wizard_category_products_pagination button.prev").show();
        $(".wizard_category_products_pagination button.next").show();
      }
      $(".wizard_category_products_pagination button.next").attr("data_page", pagination + 1);
      $(".wizard_category_products_pagination button.prev").attr("data_page", pagination - 1);

      // $('.wizard_category_products_pagination button.next').trigger('click');
      // $("#leafbridge_shop_wizard_view_products").fadeIn("250").delay("250");
      // leafbridge_shop_wizard_view_products

      setTimeout(function () {
        $(".lb_prod_filter_btn").first().trigger("click");
      }, 2000);
    } else {
      // $('.leafbridge_shop_wizard_wrapper').html('<p>You are required to select retailer, Menutype and Collection opiton</p>');
    }

    hide_if_no_retailer_brands();
  }

  function hide_if_no_retailer_brands() {
    if ($("ul#prods_brands li.show_brand").length == 0) {
      $('.lb_prod_filter_attr_box[filter_attr="brands"]').hide();
    }
  }

  /*
   * show products based on retailer selection - ajax
   */
  $(document).on("click", "#wizard_show_products", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const param_pagination = parseInt(urlParams.get("pagination"));
    const page_number = param_pagination ? param_pagination : 0;
    const trigger_event = param_pagination ? "" : "initial";

    if ($("div").hasClass("show_products_based_on_retailer")) {
      show_products_from_filter();
      location.reload();
    }
    get_featured_products_ajax();
    single_prod_page_fetch();
    show_category_products();
    var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
  });

  /*
   * Show filtered results - Phase 2 function ! - start
   */

  // for selecting attributes
  $(document).on("click", ".lb_prod_filter_attr_box ul li", function (event) {
    var target_attr = $(this).closest(".lb_prod_filter_attr_box");
    // var attribute_val = $(this).find('.prod_cat_select_lable span').text();
    var attribute_val = $(this).attr("attr_value");
    $(this).siblings("li").removeClass("selected");

    if ($(this).hasClass("selected")) {
      $(this).removeClass("selected");
      target_attr.attr("filter_selected_val", "");
      target_attr.removeClass("attr_set");
    } else {
      $(this).addClass("selected");
      target_attr.attr("filter_selected_val", attribute_val);
      target_attr.addClass("attr_set");
    }

    setTimeout(function (attribute_val) {
      $(".lb_prod_filter_btn").first().trigger("click");
    }, 500);
  });

  // for typing attributes - multiple textboxes
  $(document).on("input change keypress", ".lb_prod_filter_attr_range input", function (event) {
    var target_attr = $(this).closest(".lb_prod_filter_attr_box");
    var input_val = parseFloat($(this).val());
    var sibling_val = parseFloat($(this).val());
    var input_wrapper = $(this).closest(".lb_prod_filter_attr_range");
    var prod_filter_ul_wrapper = $(this).closest(".prod_filter_ul_wrapper");
    var this_input_id = $(this).attr("id");
    var this_min = parseFloat($(this).attr("min"));
    var this_max = parseFloat($(this).attr("max"));

    var this_sibling_val = parseFloat($(this).siblings("input").val());

    var step = parseFloat($(this).attr("step"));

    // set default min max values if one field is not entered by mistake
    if (!input_wrapper.attr("max_val")) {
      input_wrapper.attr("max_val", $(this).attr("max"));
    }
    if (!input_wrapper.attr("min_val")) {
      input_wrapper.attr("min_val", $(this).attr("min"));
    }

    // prepaer min max values to be sent to graphql and url params
    if ($(this).attr("id").includes("min")) {
      input_wrapper.attr("min_val", input_val);

      if (input_val >= this_sibling_val) {
        lb_range_error_label(prod_filter_ul_wrapper);
      } else {
        $(".lb_range_error_label").remove();
      }
    }

    if ($(this).attr("id").includes("max")) {
      input_wrapper.attr("max_val", input_val);
      // do not entering value less than min amount
      if (input_val <= this_sibling_val) {
        lb_range_error_label(prod_filter_ul_wrapper);
      } else {
        $(".lb_range_error_label").remove();
      }
    }

    if (input_val > this_max || input_val < this_min) {
      lb_range_error_label(prod_filter_ul_wrapper);
    } else {
      // $(this).removeClass('lb_error');
      $(".lb_range_error_label").remove();
    }

    var min_val = input_wrapper.attr("min_val");
    var max_val = input_wrapper.attr("max_val");
    var metric_ref = input_wrapper.attr("metric_ref");

    $(this).closest(".lb_prod_filter_attr_box").attr("filter_selected_val", min_val + "_" + max_val + "_" + metric_ref);
    $(this).closest(".lb_prod_filter_attr_box").addClass("attr_set");

    setTimeout(function () {
      $(".lb_prod_filter_btn").first().trigger("click");
    }, 100);
  }
  );

  $(document).on("click", ".set_filter_attr_range", function () {
    setTimeout(function () {
      $("#products_search_button").trigger("click"), 100;
    });
  });

  // for typing attributes - single textbox
  $(document).on("input change keypress", ".lb_prod_filter_attr_input input", function (e) {
    var this_val = $(this).val();
    this_val = this_val.replace("/", "%2F");
    $(this).closest(".lb_prod_filter_attr_box").attr("filter_selected_val", this_val);
    $(this).closest(".lb_prod_filter_attr_box").addClass("attr_set");

    // trigger auto filter
    setTimeout(function () {
      $(".lb_prod_filter_btn").first().trigger("click");
    }, 100);
  }
  );

  function lb_range_error_label(prod_filter_ul_wrapper) {
    // (!$('span').hasClass('lb_range_error_label')) ? $('.lb_prod_filter_attrs').append('<span class="lb_range_error_label">min < max</span>') : '';
    // $('.lb_prod_filter_attrs').append('<span class="lb_range_error_label">min < max</span>');
  }

  // clear typing attribute inputs
  $(document).on("click", ".clear_filter_attr_range", function () {
    $(this).siblings("input").val("");
    $(this).closest(".lb_prod_filter_attr_range").attr("min_val", "");
    $(this).closest(".lb_prod_filter_attr_range").attr("max_val", "");
    $(this).closest(".lb_prod_filter_attr_box").attr("filter_selected_val", "");
    $(this).closest(".lb_prod_filter_attr_box").removeClass("attr_set");
    update_page_url(1);
  });

  // function to update the url based on attribute selection
  function update_page_url(page_number) {
    var page_url = window.location.href;
    var url = new URL(page_url);
    var params = new URLSearchParams(url.search);
    var updated_url = "";

    page_number = page_number != null ? parseInt(page_number) : 1;
    params.delete("products_search");
    params.set("pagination", page_number);
    params.set("sort", $("#leafbridge_products_sort_by").val());

    var leafbridge_wizard_data = JSON.parse(
      localStorage.getItem("leafbridge_wizard_data")
    );

    if (leafbridge_wizard_data != null) {
      var selected_retailer = leafbridge_wizard_data.retailer_id;
      var menu_type = leafbridge_wizard_data.menu_type;
      var collection_type = leafbridge_wizard_data.collection_method;

      !params.get("retailer_id") ? params.append("retailer_id", selected_retailer) : "";
      !params.get("menu_type") ? params.append("menu_type", menu_type) : "";
      !params.get("collection_type") ? params.append("collection_type", collection_type) : "";
    }
    $("#products_search_input").val() ? params.append("products_search", $("#products_search_input").val()) : "";

    $(".lb_prod_filter_attrs .lb_prod_filter_attr_box").each(function (index, el) {
      var this_attr = $(this).attr("filter_attr");
      var this_attr_val = $(this).attr("filter_selected_val");
      if (this_attr_val) {
        params.delete(this_attr);
        params.append(this_attr, this_attr_val);
      } else {
        params.delete(this_attr);
      }
    });

    updated_url = url.origin + url.pathname + "?" + params.toString();
    window.history.pushState({}, "", updated_url);

    // update page number in the paginations
    $(".wizard_category_products_pagination button.prev").attr("data_page", page_number - 1);
    $(".wizard_category_products_pagination button.next").attr("data_page", page_number + 1);

    page_number == 1
      ? $(".wizard_category_products_pagination button.prev").hide()
      : $(".wizard_category_products_pagination button.prev").show();
  }

  /*
   * call the ajax function to filter on filter button click
   */

  $(document).on("click", ".lb_prod_filter_btn", function () {
    lb_prod_filter(false);
  });

  $(document).on("change", "#leafbridge_products_sort_by", function () {
    // update local storage with selected sorting. this selected sorting will be set when page is being loaded
    var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    leafbridge_wizard_data.sort = $(this).val();
    localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data));
    lb_prod_filter(true);
  });

  function lb_prod_filter(triggered_from_sort) {
    var default_sorting = "POPULAR_DESC";
    //get pagination number
    var page_url = window.location.href;
    var url = new URL(page_url);
    var params = new URLSearchParams(url.search);
    var page_number = params.get("pagination") ? params.get("pagination") : 1;
    page_number = triggered_from_sort ? 1 : page_number;

    var leafbridge_wizard_data = localStorage.getItem("leafbridge_wizard_data") ? JSON.parse(localStorage.getItem("leafbridge_wizard_data")) : { sort: null };

    // get all filter attributes
    var filter_attributes = {};
    filter_attributes.search_keyword = $("#products_search_input").val();
    filter_attributes.sort_order = leafbridge_wizard_data.sort != null ? leafbridge_wizard_data.sort : default_sorting;

    $(".lb_prod_filter_attrs .lb_prod_filter_attr_box").each(function (index, el
    ) {
      var this_filter_attr = $(this).attr("filter_attr");
      var this_filter_attr_val = $(this).attr("filter_selected_val");
      this_filter_attr_val ? (filter_attributes[this_filter_attr] = this_filter_attr_val) : "";
    });

    // get_products_ajax(filter_attributes, "", page_number);
    // update_page_url(page_number);

    promise_get_products_ajax(filter_attributes, "", page_number).then(function (json) {
      update_page_url(page_number);
      filter_attributes.sort_order == default_sorting ? $("#leafbridge_products_sort_by").val(default_sorting) : "";
    })
      .catch(function (err) {
        console.error("get_products_ajax", err);
      });
  }
  /*
   * Show filtered results - Phase 2 function ! - End
   */

  /*
   * Product Category pages search
   */
  $(document).on("click", "#products_category_search_button", function (event) {
    $("#products_clear_search_button").fadeIn();
    var get_category = $(this).closest(".wizard_product_section").attr("data_category");
    // get_products_ajax(get_category, "", 1);
    promise_get_products_ajax(get_category, "", 1).then(function (json) { }).catch(function (err) {
      console.error("products_category_search_button : get_products_ajax", err);
    });
  });

  /*
   * Clear Product Category pages search
   */
  $(document).on("click", "#products_clear_search_button", function (event) {
    $("#products_search_input").val("");
    $("#products_search_button").trigger("click");
    $("#products_category_search_button").trigger("click");
  });

  // ajax function to load products
  function get_products_ajax(category, triggering_event, prods_pageNumber) {
    // if it's retailer location page, it will not pass wizard data . instead it will only send the retailer id.
    var get_leafbridge_wizard_data = {};
    var retailer_based_store = false;
    var localStorage_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    var retailer_name = $(".retailer_based_store").attr("retailer_name");

    get_leafbridge_wizard_data = localStorage_wizard_data;

    if (triggering_event == "initial") {
      $("#wizard_prods_view").show();
      var data = {
        action: "wizard_show_products",
        wizard_data: get_leafbridge_wizard_data,
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
        prods_pageNumber: prods_pageNumber,
        prod_categories: "all",
        search_key_word: $("#products_search_input").val(),
        filter_attributes: JSON.stringify(category),
      };

      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        beforeSend: function () {
          $(".lb_prod_filter_btn").css("pointer-events", "none");
        },
        success: function (json) {
          $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all .wizard_category_products_showcase").html(json.data.products_html);
          $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all").fadeIn("250");
          location_pages_addtocart(retailer_based_store, localStorage_wizard_data, retailer_name);
          // $('.wizard_category_products_showcase').css('display','inline-grid');
          setTimeout(function () {
            $(".leafbridge_shop_wizard_wrapper .leafbridge_shop_wizard_container").css("opacity", "1");
            $(".leafbridge_shop_wizard_wrapper").addClass("loaded");
          }, 500);
        },
        error: function (json) { },
        complete: function (json) {
          $(".lb_prod_filter_btn").css("pointer-events", "all");
          $(".select2_init").select2();
          $("#wizard_prods_view").hide();
          $("#prods_categories li.selected").each(function (index, el) {
            $(this).removeClass("selected");
          });
        },
      });
    } else {
      $("#wizard_prods_view").fadeIn();
      var data = {
        action: "wizard_show_products",
        wizard_data: get_leafbridge_wizard_data,
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
        prods_pageNumber: prods_pageNumber,
        prod_categories: category,
        search_key_word: $("#products_search_input").val(),
        filter_attributes: category,
      };

      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        beforeSend: function () {
          $(".lb_prod_filter_btn").css("pointer-events", "none");
        },
        success: function (json) {
          $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view section.wizard_product_section").hide();
          $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all .wizard_category_products_showcase").html(json.data.products_html);
          $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all").fadeIn("250");

          // show / hide pagination next button
          $("#pagination_text").text(json.data.pagination_show_current_page_text);

          if (parseInt(json.data.current_products_set) - parseInt(json.data.products_count) < 0) {
            $(".wizard_category_products_pagination button.next").show();
          } else {
            $(".wizard_category_products_pagination button.next").hide();
          }

          // show/hide pagination prev button
          if (json.data.current_products_set == json.data.prods_per_page) {
            $(".wizard_category_products_pagination button.prev").hide();
          } else {
            $(".wizard_category_products_pagination button.prev").show();
          }
          location_pages_addtocart(retailer_based_store, localStorage_wizard_data, retailer_name);
          setTimeout(function () {
            $(".leafbridge_shop_wizard_wrapper .leafbridge_shop_wizard_container").css("opacity", "1");
            $(".leafbridge_shop_wizard_wrapper").addClass("loaded");
          }, 500);
        },
        error: function (json) {
          $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all").hide();
          $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all").fadeIn("250");
          // $('#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_'+data.prod_categories).find('.wizard_category_products_pagination').hide();
          // $('#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_'+data.prod_categories+' .wizard_category_products_showcase').html('<p>Could not find more products</p>');
        },
        complete: function (json) {
          $(".lb_prod_filter_btn").css("pointer-events", "all");
          $(".select2_init").select2();
          $("#prods_categories").css("opacity", "1");
          $("#prods_categories").css("pointer-events", "auto");
          $("#wizard_prods_view").hide();
        },
      });
    }
  }

  // ajax function promise to load products
  function promise_get_products_ajax(category, triggering_event, prods_pageNumber) {
    return new Promise(function (resolve, reject) {
      // if it's retailer location page, it will not pass wizard data . instead it will only send the retailer id.
      var get_leafbridge_wizard_data = {};
      var retailer_based_store = false;
      var localStorage_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
      var retailer_name = $(".retailer_based_store").attr("retailer_name");

      get_leafbridge_wizard_data = localStorage_wizard_data;

      if (triggering_event == "initial") {
        $("#wizard_prods_view").show();
        var data = {
          action: "wizard_show_products",
          wizard_data: get_leafbridge_wizard_data,
          nonce_ajax: leafbridge_public_ajax_obj.nonce,
          prods_pageNumber: prods_pageNumber,
          prod_categories: "all",
          search_key_word: $("#products_search_input").val(),
          filter_attributes: JSON.stringify(category),
        };

        $.ajax({
          url: leafbridge_public_ajax_obj.ajaxurl,
          dataType: "json",
          cache: false,
          data: data,
          type: "POST",
          proccessData: false,
          beforeSend: function () {
            $(".lb_prod_filter_btn").css("pointer-events", "none");
          },
          success: function (json) {
            $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all .wizard_category_products_showcase").html(json.data.products_html);
            $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all").fadeIn("250");
            location_pages_addtocart(retailer_based_store, localStorage_wizard_data, retailer_name);
            // $('.wizard_category_products_showcase').css('display','inline-grid');
            setTimeout(function () {
              $(".leafbridge_shop_wizard_wrapper .leafbridge_shop_wizard_container").css("opacity", "1");
              $(".leafbridge_shop_wizard_wrapper").addClass("loaded");
            }, 500);
            resolve(json);
          },
          error: function (json) {
            reject(json);
          },
          complete: function (json) {
            $(".lb_prod_filter_btn").css("pointer-events", "all");
            $(".select2_init").select2();
            $("#wizard_prods_view").hide();
            $("#prods_categories li.selected").each(function (index, el) {
              $(this).removeClass("selected");
            });
          },
        });
      } else {
        $("#wizard_prods_view").fadeIn();
        var data = {
          action: "wizard_show_products",
          wizard_data: get_leafbridge_wizard_data,
          nonce_ajax: leafbridge_public_ajax_obj.nonce,
          prods_pageNumber: prods_pageNumber,
          prod_categories: category,
          search_key_word: $("#products_search_input").val(),
          filter_attributes: category,
        };

        $.ajax({
          url: leafbridge_public_ajax_obj.ajaxurl,
          dataType: "json",
          cache: false,
          data: data,
          type: "POST",
          proccessData: false,
          beforeSend: function () {
            $(".lb_prod_filter_btn").css("pointer-events", "none");
          },
          success: function (json) {
            $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view section.wizard_product_section").hide();
            $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all .wizard_category_products_showcase").html(json.data.products_html);
            $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all").fadeIn("250");

            // show / hide pagination next button
            $("#pagination_text").text(json.data.pagination_show_current_page_text);

            if (parseInt(json.data.current_products_set) - parseInt(json.data.products_count) < 0) {
              $(".wizard_category_products_pagination button.next").show();
            } else {
              $(".wizard_category_products_pagination button.next").hide();
            }

            // show/hide pagination prev button
            if (json.data.current_products_set == json.data.prods_per_page) {
              $(".wizard_category_products_pagination button.prev").hide();
            } else {
              $(".wizard_category_products_pagination button.prev").show();
            }
            location_pages_addtocart(retailer_based_store, localStorage_wizard_data, retailer_name);
            setTimeout(function () {
              $(".leafbridge_shop_wizard_wrapper .leafbridge_shop_wizard_container").css("opacity", "1");
              $(".leafbridge_shop_wizard_wrapper").addClass("loaded");
            }, 500);
            resolve(json);
          },
          error: function (json) {
            $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all").hide();
            $("#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_all").fadeIn("250");
            // $('#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_'+data.prod_categories).find('.wizard_category_products_pagination').hide();
            // $('#leafbridge_shop_wizard_view_products .wizard_box_container #product_collection .wizard_prods_view #prod_show_cat_'+data.prod_categories+' .wizard_category_products_showcase').html('<p>Could not find more products</p>');
            reject(json);
          },
          complete: function (json) {
            $(".lb_prod_filter_btn").css("pointer-events", "all");
            $(".select2_init").select2();
            $("#prods_categories").css("opacity", "1");
            $("#prods_categories").css("pointer-events", "auto");
            $("#wizard_prods_view").hide();
          },
        });
      }
    });
  }

  function location_pages_addtocart(retailer_based_store, localStorage_wizard_data, retailer_name) {
    if (retailer_based_store && localStorage_wizard_data && $(".retailer_based_store").attr("force_wizard") == "off") {
      var location_retailer_id = $(".retailer_based_store").attr("retailer_id");
      if (location_retailer_id != localStorage_wizard_data.retailer_id) {
        $(".leafbridge_product_card").each(function (index, el) {
          $(this).find(".leafbridge_product_modal_add_to_cart").css("display", "block");
          $(this).find(".leafbridge_product_modal_add_to_cart").css("width", "100%");
          $(this).find(".leafbridge_product_modal_add_to_cart").html('<div class="add_to_cart_warning"><i class="fa-sharp fa-solid fa-triangle-exclamation"></i><p style="font-size:12px;">To add this item to your cart, please change your location using the options at the top of the page.</div>');
        });
      }
    }
  }

  // product sections pagination
  $(document).on("click", ".wizard_category_products_pagination button", function () {
    $("html, body").animate({ scrollTop: $("#product_collection").offset().top }, 750);

    // extract any filter attributes
    var get_category = $(this).closest(".wizard_product_section").attr("data_category");
    var filter_attributes = {};
    filter_attributes.search_keyword = $("#products_search_input").val();
    filter_attributes.sort_order = $("#leafbridge_products_sort_by").val();

    $(".lb_prod_filter_attrs .lb_prod_filter_attr_box").each(function (index, el) {
      var this_filter_attr = $(this).attr("filter_attr");
      var this_filter_attr_val = $(this).attr("filter_selected_val");
      this_filter_attr_val ? (filter_attributes[this_filter_attr] = this_filter_attr_val) : "";
    });
    get_category = filter_attributes;
    var get_page = parseInt($(this).attr("data_page"));
    update_page_url(get_page);
    // get_products_ajax(get_category, "", get_page);
    promise_get_products_ajax(get_category, "", get_page).then(function (json) { }).catch(function (err) {
      console.error("get_products_ajax", err);
    });
  }
  );

  // ajax function to view featured products
  function get_featured_products_ajax() {
    $("#leafbridge_featured_products_wrapper").fadeIn("250");
    var data = {
      action: "show_featured_products_func",
      wizard_data: JSON.parse(localStorage.getItem("leafbridge_wizard_data")),
      nonce_ajax: leafbridge_public_ajax_obj.nonce,
      prod_categories: "all",
      product_count: $("#leafbridge_featured_products_wrapper").attr(
        "data_product_count"
      ),
    };
    $.ajax({
      url: leafbridge_public_ajax_obj.ajaxurl,
      dataType: "json",
      cache: false,
      data: data,
      type: "POST",
      proccessData: false,
      success: function (json) {
        $("#leafbridge_featured_products_wrapper").addClass("has_prods");
        $("#leafbridge_featured_products_wrapper .leafbridge_featured_products_inner").html(json.data.products_html);
        $("#leafbridge_featured_products_wrapper").fadeIn("250");
      },
      error: function (json) {
        $("#leafbridge_featured_products_wrapper .leafbridge_featured_products_inner").html("error fetching products");
      },
      complete: function (json) {
        $(".select2_init").select2();
        $("#wizard_prods_view").hide();
        $("#prods_categories li.selected").each(function (index, el) {
          $(this).removeClass("selected");
        });
      },
    });
  }

  // opening product modals
  $(document).on("click", ".open_prod_modal", function (event) {
    event.preventDefault();
    var data_modal_id = $(this).attr("data_modal_id");
    var capture_modal = $("#" + data_modal_id).html();
    // $('#'+data_modal_id).fadeIn('250');
    $("#popup_container").addClass("open_popup");
    $("#popup_container").html(capture_modal);

    //GTM view_item push to data layer - START
    var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    var leafbridge_recent_location = JSON.parse(localStorage.getItem("leafbridge_recent_location"));

    var json = {};
    var item_data = null;
    var prod_card = $(this).closest(".leafbridge_product_card");
    item_data = JSON.parse(prod_card.attr("filter_data"));

    var cart_prod = {
      product_id: item_data.product_id,
      variation: item_data.variants[0].option,
      prod_count: 1,
      prod_name: item_data.item_name,
    };

    if (item_data != null) {
      json.moreDataLayerPush = {
        item_data: item_data,
        cart_prod: cart_prod,
        location_id: leafbridge_wizard_data.retailer_id,
        location_name: leafbridge_recent_location.retailer_name,
        menu_type: leafbridge_wizard_data.menu_type,
      };
      gtm_push_single_item_data(json, "LB_ViewItem");
    }
    //GTM view_item push to data layer - END
  });

  // change limit of add to cart per variant
  $(document).on("change", ".add_to_cart_variant", function (event) {
    var variation_qty_list = $(this).closest(".leafbridge_product_modal_add_to_cart").attr("quantities");
    var variations_qty_obj = JSON.parse(variation_qty_list);
    var selected_variation = $(this).val();
    $(this).closest(".leafbridge_product_modal_add_to_cart").find(".add_to_cart_count").attr("max", variations_qty_obj[selected_variation]);
    $(this).closest(".leafbridge_product_modal_add_to_cart").find(".add_to_cart_count").val(1);

    //GTM view_item push to data layer - START
    var variation_type = $(this).val();
    var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    var leafbridge_recent_location = JSON.parse(localStorage.getItem("leafbridge_recent_location"));

    var json = {};
    var item_data = null;

    if ($(this).hasClass("variant_prod_box")) {
      var prod_card = $(this).closest(".leafbridge_product_card");
      item_data = JSON.parse(prod_card.attr("filter_data"));
      item_data = null;
    } else if ($(this).hasClass("variant_prod_modal")) {
      var modal_prod_id = $(this).closest(".leafbridge_product_modal_add_to_cart").attr("data_product_id");
      var prod_card = $(".leafbridge_product_card[data_product_id=" + modal_prod_id + "]");
      item_data = JSON.parse(prod_card.attr("filter_data"));
    } else if ($(this).hasClass("variant_prod_page")) {
      item_data = JSON.parse($(".lb_prod_single_add_to_cart").attr("filter_data"));
    }

    var cart_prod = {
      product_id: item_data.product_id,
      variation: variation_type,
      prod_count: 1,
      prod_name: item_data.item_name,
    };

    if (item_data != null) {
      json.moreDataLayerPush = {
        item_data: item_data,
        cart_prod: cart_prod,
        location_id: leafbridge_wizard_data.retailer_id,
        location_name: leafbridge_recent_location.retailer_name,
        menu_type: leafbridge_wizard_data.menu_type,
      };
      gtm_push_single_item_data(json, "LB_ViewItem");
    }
    //GTM view_item push to data layer - END
  });

  // popup add product to cart
  $(document).on("click", ".leafbridge_product_modal_add_to_cart button", function () {
    var this_button = $(this);
    // $(this).prop("disabled",true);
    var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    var preserve_leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    var leafbridge_recent_location = JSON.parse(localStorage.getItem("leafbridge_recent_location"));
    var force_leafbridge_wizard_data = {};
    var retailer_based_store = $("div").hasClass("retailer_based_store") ? true : false;

    var cart_creation_utc = new Date().toUTCString();

    if ($("div").hasClass("retailer_based_store")) {
      if ($(".retailer_based_store").attr("force_wizard") == "on") {
        force_leafbridge_wizard_data = {
          retailer_id: $(".retailer_based_store").attr("retailer_id"),
          menu_type: $(".retailer_based_store").attr("menu_type"),
          collection_method: $(".retailer_based_store").attr("order_type"),
        };
      }
    }

    if (!localStorage.getItem("leafbridge_wizard_data")) {
      leafbridge_wizard_data = {
        age_confirm: true,
        collection_method: $(".retailer_based_store").attr("order_type"),
        created_time: new Date(),
        default_set: true,
        event: "EMPTY CART",
        menu_type: $(".retailer_based_store").attr("menu_type"),
        retailer_id: $(".retailer_based_store").attr("retailer_id"),
      };
    } else {
      leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));

      if ($("div").hasClass("retailer_based_store")) {
        if ($(".retailer_based_store").attr("force_wizard") == "on") {
          // inside retailer based store page, check if there's already wizard data and cart data is in local storage
          if (localStorage.getItem("leafbridge_cart_data")) {
            var check_leafbridge_cart_data = JSON.parse(localStorage.getItem("leafbridge_cart_data"));
            if (check_leafbridge_cart_data.retailer_id != force_leafbridge_wizard_data.retailer_id || check_leafbridge_cart_data.menutype != force_leafbridge_wizard_data.menu_type) {
              jQuery.toast({
                heading: "Error",
                text: "Please set your location to " + $(".retailer_based_store").attr("retailer_name"),
                showHideTransition: "slide",
                position: "top-right",
                icon: "error",
                bgColor: "#fd3c51",
                textColor: "white",
                hideAfter: 2000,
              });

              return null;
            }
          } else {
          }
        }
      }
    }

    var leafbridge_cart_data = localStorage.getItem("leafbridge_cart_data") !== null ? JSON.parse(localStorage.getItem("leafbridge_cart_data")) : [];

    // again check if cart is mutated before adding products to cart in shop
    var flag_reset_cart = localStorage.getItem("leafbridge_cart_data") ? false : true;

    if (localStorage.getItem("leafbridge_cart_data")) {
      if (leafbridge_cart_data.retailer_id != leafbridge_wizard_data.retailer_id || leafbridge_cart_data.menutype != leafbridge_wizard_data.menu_type) {
        var confirm_change_cart = confirm("There are products from another retailer / menutype so adding this product will reset your current cart. Procced?");

        if (confirm_change_cart == true) {
          flag_reset_cart = true;
          localStorage.removeItem("leafbridge_cart_data");
        }
      }
    }

    var prod_name = $(this).closest(".leafbridge_product_modal_descr_inner").find(".leafbridge_product_name").text();
    var product_id = $(this).closest(".leafbridge_product_modal_add_to_cart").attr("data_product_id");
    var checkout_id = typeof leafbridge_cart_data.new_checkout_id != "undefined" ? leafbridge_cart_data.new_checkout_id : 0;
    var variation = $(this).closest(".leafbridge_product_modal_add_to_cart").find(".add_to_cart_variant").length > 0 ? $(this).closest(".leafbridge_product_modal_add_to_cart").find(".add_to_cart_variant").val() : "N/A";
    var prod_count = $(this).closest(".leafbridge_product_modal_add_to_cart").find(".add_to_cart_count").val();
    var prod_available_amount = parseInt($(this).closest(".leafbridge_product_modal_add_to_cart").find(".add_to_cart_count").attr("max"));

    var retailer_id = retailer_based_store ? force_leafbridge_wizard_data.retailer_id : leafbridge_wizard_data.retailer_id;
    var menutype = retailer_based_store ? force_leafbridge_wizard_data.menu_type : leafbridge_wizard_data.menu_type;
    var collection_type = retailer_based_store ? force_leafbridge_wizard_data.collection_method : leafbridge_wizard_data.collection_method;

    leafbridge_wizard_data.created_time = new Date();

    var cart_prod = {
      product_id: product_id,
      variation: variation,
      prod_count: prod_count,
      prod_name: prod_name,
    };

    if (flag_reset_cart) {
      leafbridge_cart_data = {
        retailer_id: retailer_id,
        menutype: menutype,
        collection_type: collection_type,
        // products_count : [],
        products: [],
        cart_creation_utc: cart_creation_utc,
      };
    }

    leafbridge_cart_data.products.push(cart_prod);
    localStorage.setItem("leafbridge_cart_data", JSON.stringify(leafbridge_cart_data));
    // (!$('div').hasClass('retailer_based_store')) ? localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data)) : '';
    localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data));

    // wait until ajax request is completed
    $(this).prop("disabled", false);

    // true if more than available quantity is added.
    if (check_prod_count(product_id, prod_count, prod_available_amount)) {
      // console.log('%c%s%s','color:yellow','checkout ID : '+checkout_id);
      ajax_add_to_cart(retailer_id, checkout_id, menutype, collection_type, cart_prod).then(function (json) {
        //GTM add to cart push to data layer - START
        var capture_prod_id = this_button.closest(".leafbridge_product_modal_add_to_cart").attr("data_product_id");
        var item_data = null;

        if ($(".leafbridge_product_card[data_product_id=" + capture_prod_id + "]").length != 0) {
          item_data = JSON.parse($(".leafbridge_product_card[data_product_id=" + capture_prod_id + "]").attr("filter_data"));
        }
        else {
          if (item_data == null && $("div").hasClass("lb_prod_single_add_to_cart")) {
            item_data = JSON.parse($(".lb_prod_single_add_to_cart").attr("filter_data"));
          }
          else {
            item_data = null;
          }
        }

        if (item_data != null) {
          json.moreDataLayerPush = {
            item_data: item_data,
            cart_prod: cart_prod,
            location_id: retailer_id,
            location_name: leafbridge_recent_location.retailer_name,
            menu_type: menutype,
          };
          gtm_push_single_item_data(json, "LB_AddToCart");
        }
        //GTM add to cart push to data layer - END

        if (checkout_id == 0) {
          var leafbridge_cart_data_2 = localStorage.getItem("leafbridge_cart_data") !== null ? JSON.parse(localStorage.getItem("leafbridge_cart_data")) : [];
          leafbridge_cart_data_2.new_checkout_id = json.data.new_checkout_id;
          localStorage.setItem("leafbridge_cart_data", JSON.stringify(leafbridge_cart_data_2));
        }

        if (json.data.cart_html) {
          jQuery.toast({
            heading: "Success",
            text: "Added to the cart.",
            showHideTransition: "slide",
            position: "top-right",
            icon: "success",
          });
        } else {
          if (json.data.add_to_cart_error) {
            var hide_after = 20000;
            if (json.data.add_to_cart_error == "Not Found") {
              // this_button.trigger('click');
              hide_after = 3000;
              json.data.add_to_cart_error = "Could not add to cart. retrying.."
              localStorage.removeItem('leafbridge_cart_data');
            }
            jQuery.toast({
              heading: "Error adding to cart",
              text: json.data.add_to_cart_error,
              showHideTransition: "slide",
              position: "top-right",
              icon: "error",
              bgColor: "#fd3c51",
              textColor: "white",
              hideAfter: hide_after,
            });
            // alert("json.data.add_to_cart_error");
          } else {
            jQuery.toast({
              heading: "Error",
              text: "Could not add product to the cart. Please retry.",
              showHideTransition: "slide",
              position: "top-right",
              icon: "error",
              bgColor: "#fd3c51",
              textColor: "white",
              hideAfter: 20000,
            });
          }
        }
        set_cart_prod_count(json.data.update_cart.items);
        render_cart(json.data);
      })
        .catch(function (err) {
          console.error("add to cart error", err);
        });
    }
  }
  );

  function set_cart_prod_count(cart_items) {
    var get_local_cart = JSON.parse(localStorage.getItem("leafbridge_cart_data"));
    get_local_cart.cart_items_count = cart_items;
    localStorage.setItem("leafbridge_cart_data", JSON.stringify(get_local_cart));
  }

  function check_prod_count(prod_id, prod_count, prod_available_amount) {
    var flag_over_packed = true;
    var get_local_cart = JSON.parse(localStorage.getItem("leafbridge_cart_data"));
    var cart_items_count = get_local_cart.cart_items_count;

    if (cart_items_count) {
      for (let cic_index = 0; cic_index < cart_items_count.length; cic_index++) {
        var cart_item = cart_items_count[cic_index];
        var attempting_qty = parseInt(cart_item.quantity) + parseInt(prod_count);
        if (cart_item.productId == prod_id && attempting_qty > prod_available_amount) {
          flag_over_packed = false;
        }
      }
    }

    if (!flag_over_packed) {
      jQuery.toast({
        heading: "Error",
        text: "Added quantity exceeds the available quantity",
        showHideTransition: "slide",
        position: "top-right",
        icon: "error",
        bgColor: "#fd3c51",
        textColor: "white",
        hideAfter: 20000,
      });
    }
    return flag_over_packed;
  }

  // ajax fuction to add products to cart
  function ajax_add_to_cart(retailerId, checkoutId, menutype, collection_type, products_list) {
    return new Promise(function (resolve, reject) {
      var data = {
        retailerId: retailerId,
        checkoutId: checkoutId,
        collection_type: collection_type,
        pricingType: menutype,
        products_list: products_list,
        init_cart_data: JSON.parse(localStorage.getItem("leafbridge_cart_data")),
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
        action: "leafbridge_shop_add_products_to_cart",
      };
      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        beforeSend: function () {
          $(".leafbridge_product_modal_add_to_cart button").prop("disabled", true);
          $(".leafbridge_product_modal_add_to_cart").css("opacity", "0.5");
        },
        success: function (json) {
          $(".leafbridge_product_modal_add_to_cart button").prop("disabled", false);
          $(".leafbridge_product_modal_add_to_cart").css("opacity", "1");
          resolve(json);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) { },
      });
    });
  }

  // check and prepare cart items on page load
  function check_and_prepare_cart_items() {
    if (localStorage.getItem("leafbridge_cart_data")) {
      var leafbridge_cart_data = JSON.parse(localStorage.getItem("leafbridge_cart_data"));
      var retailer_id = leafbridge_cart_data.retailer_id;
      var new_checkout_id = leafbridge_cart_data.new_checkout_id;
      var item_id = leafbridge_cart_data.products[0].product_id;
      var item_quantity = leafbridge_cart_data.products[0].prod_count;

      ajax_get_cart(retailer_id, new_checkout_id, item_id, item_quantity)
        .then(function (json) {
          render_cart(json.data);
        })
        .catch(function (err) {
          console.error("ajax get cart error ", err);
        });
    } else {
      render_empty_cart();
    }
  }

  // ajax function to send a product count update and retrieve the updated cart details
  function ajax_get_cart(retailer_id, new_checkout_id, item_id, item_quantity) {
    return new Promise(function (resolve, reject) {
      var data = {
        action: "leafbridge_get_cart_items",
        retailer_id: retailer_id,
        new_checkout_id: new_checkout_id,
        item_id: item_id,
        item_quantity: item_quantity,
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
      };
      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        beforeSend: function () { },
        success: function (json) {
          resolve(json);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) {
          // jQuery('.floating_cart_items_inner').slimScroll({
          // 	height: '65vh'
          // });
        },
      });
    });
  }

  // remove items from the cart
  $(document).on("click", ".remove_cart_item", function () {
    var leafbridge_cart_data = JSON.parse(localStorage.getItem("leafbridge_cart_data"));
    var gtm_data = JSON.parse($(this).closest('.floating_cart_item_box').attr('gtm_prod_data'));
    var item_key = $(this).closest(".floating_cart_item_box").attr("cart_item_id");
    var retailerId = leafbridge_cart_data.retailer_id;
    var checkoutId = leafbridge_cart_data.new_checkout_id;
    var cartItem_qty = parseInt($(this).closest('.floating_cart_item_box').find('.cart_item_count_val').val());
    ajax_remove_item_from_cart(item_key, retailerId, checkoutId)
      .then(function (json) {
        render_cart(json.data);
        //GTM view_item push to data layer - START
        var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
        var leafbridge_recent_location = JSON.parse(localStorage.getItem("leafbridge_recent_location"));

        var item_data = {
          item_name: gtm_data.product.name,
          item_id: gtm_data.product.id,
          category: gtm_data.product.category,
          subcategory: gtm_data.product.subcategory,
          brand_name: (gtm_data.product.brand) ? gtm_data.product.brand.name : null,
          brand: (gtm_data.product.brand) ? gtm_data.product.brand.id : null,
          potencyCbd: (gtm_data.product.potencyCbd != null) ? gtm_data.product.potencyCbd.formatted : null,
          potencyThc: (gtm_data.product.potencyThc != null) ? gtm_data.product.potencyThc.formatted : null,
          strainType: gtm_data.product.strainType,
          variants: gtm_data.product.variants
        };

        var cart_prod = {
          product_id: gtm_data.productId,
          variation: gtm_data.option,
          prod_count: cartItem_qty,
          prod_name: item_data.item_name,
        };

        if (item_data != null) {
          json.moreDataLayerPush = {
            item_data: item_data,
            cart_prod: cart_prod,
            location_id: leafbridge_wizard_data.retailer_id,
            location_name: leafbridge_recent_location.retailer_name,
            menu_type: leafbridge_wizard_data.menu_type,
          };
          gtm_push_single_item_data(json, "LB_RemoveCartItem");
        }
        //GTM view_item push to data layer - END
      })
      .catch(function (err) {
        console.error("remove cart item err", err);
      });
  });

  // ajax function to remove items from the cart
  function ajax_remove_item_from_cart(item_key, retailerId, checkoutId) {
    return new Promise(function (resolve, reject) {
      var data = {
        action: "leafbridge_remove_cart_item",
        item_key: item_key,
        retailerId: retailerId,
        checkoutId: checkoutId,
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
      };
      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        beforeSend: function () { },
        success: function (json) {
          resolve(json);
          jQuery.toast({
            heading: "Success",
            text: "Successfully removed from cart.",
            showHideTransition: "slide",
            position: "top-right",
            icon: "success",
          });
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) { },
      });
    });
  }

  //edit cart item quantity - single cart item button
  $(document).on("click", "#update_cart_qts", function () {
    $(".floating_cart_wrapper .floating_cart_items_wrapper .floating_cart_items_inner").addClass("updating_cart");

    var gtm_cart_items = [];
    $(".floating_cart_wrapper .floating_cart_items_wrapper .floating_cart_items_inner .floating_cart_item_box").each(function (index, el) {
      var leafbridge_cart_data = JSON.parse(localStorage.getItem("leafbridge_cart_data"));
      var leafbridge_cart_data_prods = leafbridge_cart_data.products;
      var retailerId = leafbridge_cart_data.retailer_id;
      var checkoutId = leafbridge_cart_data.new_checkout_id;
      var itemId = $(this).attr("cart_item_id");
      var prod_id = $(this).attr("prod_id");
      var quantity = $(this).find(".cart_item_count").find(".cart_item_count_val").val();

      gtm_cart_items[prod_id] = JSON.parse($(this).attr('gtm_prod_data'));
      ajax_update_cart_item_qunatity(retailerId, checkoutId, itemId, quantity)
        .then(function (json) {
          if (json.data.cart_html) {
            render_cart(json.data);
            jQuery.toast({
              heading: "Success",
              text: "Cart updated!",
              showHideTransition: "slide",
              position: "top-right",
              icon: "success",
            });

            // Get checkout for GTM - Start
            var GTM_leafbridge_cart_data = JSON.parse(localStorage.getItem("leafbridge_cart_data"));
            var gtm_retailer_id = GTM_leafbridge_cart_data.retailer_id;
            var gtm_new_checkout_id = GTM_leafbridge_cart_data.new_checkout_id;
            var gtm_item_id = GTM_leafbridge_cart_data.products[0].product_id;
            var gtm_item_quantity = GTM_leafbridge_cart_data.products[0].prod_count;

            ajax_get_cart(gtm_retailer_id, gtm_new_checkout_id, gtm_item_id, gtm_item_quantity)
              .then(function (json) {
                // console.log('retrieve cart after update', json);
                //GTM view_item push to data layer - START
                var GTM_leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
                var GTM_leafbridge_recent_location = JSON.parse(localStorage.getItem("leafbridge_recent_location"));

                var order_details = json.data.update_cart;
                json.data.order_details = json.data.update_cart;

                if (order_details != null) {
                  json.moreDataLayerPush = {
                    order_details: order_details,
                    location_id: GTM_leafbridge_wizard_data.retailer_id,
                    location_name: GTM_leafbridge_recent_location.retailer_name,
                    menu_type: GTM_leafbridge_wizard_data.menu_type,
                  };
                  // console.log('calling LB_UpdateCart', json.data);
                  gtm_push_items_data(json, "LB_UpdateCart");
                }
                else {
                  console.log('LB_UpdateCart failed');
                }
                //GTM view_item push to data layer - END

              })
              .catch(function (err) {
                console.error("ajax get cart error ", err);
              });
            // Get checkout for GTM - End
          } else if (json.data.update_cart_error) {
            jQuery.toast({
              heading: "Error",
              text: json.data.update_cart_error,
              showHideTransition: "slide",
              position: "top-right",
              icon: "error",
              bgColor: "#fd3c51",
              textColor: "white",
              hideAfter: 20000,
            });
            // alert("json.data.add_to_cart_error");
          } else {
            jQuery.toast({
              heading: "Error",
              text: "Update cart failed",
              showHideTransition: "slide",
              position: "top-right",
              icon: "error",
              bgColor: "#fd3c51",
              textColor: "white",
              hideAfter: 20000,
            });
          }
        })
        .catch(function (err) {
          console.error("ajax_update_cart_item_qunatity err", err);
        });
    });



    setTimeout(function () {
      $(".floating_cart_wrapper .floating_cart_items_wrapper .floating_cart_items_inner").removeClass("updating_cart");
    }, 3000);
  });

  //edit cart item quantity - single cart item button
  $(document).on("click", ".floating_cart_item_box .cart_item_count .cart_item_count_val_save", function () {
    var leafbridge_cart_data = JSON.parse(localStorage.getItem("leafbridge_cart_data"));
    var retailerId = leafbridge_cart_data.retailer_id;
    var checkoutId = leafbridge_cart_data.new_checkout_id;
    var itemId = $(this).closest(".floating_cart_item_box").attr("cart_item_id");
    var quantity = $(this).closest(".cart_item_count").find(".cart_item_count_val").val();

    ajax_update_cart_item_qunatity(retailerId, checkoutId, itemId, quantity)
      .then(function (json) {
        if (json.data.cart_html) {
          render_cart(json.data);
          jQuery.toast({
            heading: "Success",
            text: "Cart updated!",
            showHideTransition: "slide",
            position: "top-right",
            icon: "success",
          });
        } else if (json.data.update_cart_error) {
          jQuery.toast({
            heading: "Error",
            text: json.data.update_cart_error,
            showHideTransition: "slide",
            position: "top-right",
            icon: "error",
            bgColor: "#fd3c51",
            textColor: "white",
            hideAfter: 20000,
          });
          // alert("json.data.add_to_cart_error");
        } else {
          jQuery.toast({
            heading: "Error",
            text: "Update cart failed",
            showHideTransition: "slide",
            position: "top-right",
            icon: "error",
            bgColor: "#fd3c51",
            textColor: "white",
            hideAfter: 20000,
          });
        }
      })
      .catch(function (err) {
        console.error("ajax_update_cart_item_qunatity err", err);
      });
  }
  );

  //ajax function to edit cart item qunantity
  function ajax_update_cart_item_qunatity(retailerId, checkoutId, itemId, quantity) {
    return new Promise(function (resolve, reject) {
      var data = {
        action: "leafbridge_update_cart_item_quantity",
        retailerId: retailerId,
        checkoutId: checkoutId,
        itemId: itemId,
        quantity: quantity,
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
      };

      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        beforeSend: function () { },
        success: function (json) {
          resolve(json);
          set_cart_prod_count(json.data.update_cart.items);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) { },
      });
    });
  }

  //Reset reatiler , menu type and delivery selection
  // AKA reset checkout
  // reset selection
  $(document).on("click", "#leafbridge_shop_wizard_reset_selection_confirm .wizard_reset", function (e) {
    var wizard_reset = $(this).val();
    if (wizard_reset == "no") {
      $("#leafbridge_shop_wizard_popup").hide();
    }
    else {
      e.preventDefault();
      // var leafbridge_wizard_data = JSON.parse(localStorage.getItem('leafbridge_wizard_data'));
      // leafbridge_wizard_data.reset_flag = true;
      // localStorage.setItem('leafbridge_wizard_data',JSON.stringify(leafbridge_wizard_data));

      // remove local storage items so it will create new checkout when adding first product to chekcout
      // localStorage.removeItem("leafbridge_wizard_data");
      localStorage.removeItem("leafbridge_cart_data");
      render_empty_cart();
      // location.reload();
      // $('#leafbridge_shop_wizard_set_location button[data_direction="#leafbridge_shop_wizard_set_age"]').hide();
    }
  }
  );

  // ajax function to reset checkout
  function ajax_reset_checkout(wizard_data) {
    return new Promise(function (resolve, reject) {
      var data = {
        action: "leafbridge_reset_checkout",
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
        wizard_data: wizard_data,
      };

      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        beforeSend: function () { },
        success: function (json) {
          resolve(json);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) { },
      });
    });
  }

  // function to render the cartitems
  function render_cart(cart_data) {
    // console.log('render_cart', cart_data);
    $("#floating_cart").attr('cart_data', JSON.stringify(cart_data));
    $("#floating_cart .floating_cart_footer_wrapper").fadeIn();
    $("#floating_cart .floating_cart_items_inner").html(cart_data.cart_html);

    if (cart_data.update_cart.redirectUrl) {
      var checkout_url = cart_data.update_cart.redirectUrl;
      checkout_url += "?r=" + cart_data.order_details_url;
      $("#floating_cart .floating_cart_footer a").attr("href", checkout_url);
    }

    var subtotal = convert_and_format_numbers(cart_data.update_cart.priceSummary.subtotal);
    var discounts = convert_and_format_numbers(cart_data.update_cart.priceSummary.discounts);
    var fees = convert_and_format_numbers(cart_data.update_cart.priceSummary.fees);
    var mixAndMatch = convert_and_format_numbers(cart_data.update_cart.priceSummary.mixAndMatch);
    var rewards = convert_and_format_numbers(cart_data.update_cart.priceSummary.rewards);
    var taxes = convert_and_format_numbers(cart_data.update_cart.priceSummary.taxes);
    var total = convert_and_format_numbers(cart_data.update_cart.priceSummary.total);

    // show cart expenses
    var cart_total_html = "";
    cart_total_html += cart_data.update_cart.priceSummary.subtotal > 0 ? '<span class="cart_amnt_parent"><span class="cart_amnt_label"> subtotal </span><span class="cart_amnt_val"> $' + subtotal + "</span></span>" : "";
    cart_total_html += cart_data.update_cart.priceSummary.discounts >= 0 ? '<span class="cart_amnt_parent"><span class="cart_amnt_label"> discounts </span><span class="cart_amnt_val"> ($' + discounts + ")</span></span>" : "";
    cart_total_html += cart_data.update_cart.priceSummary.fees > 0 ? '<span class="cart_amnt_parent"><span class="cart_amnt_label"> fees </span><span class="cart_amnt_val"> $' + fees + "</span></span>" : "";
    cart_total_html += cart_data.update_cart.priceSummary.mixAndMatch > 0 ? '<span class="cart_amnt_parent"><span class="cart_amnt_label"> mixAndMatch </span><span class="cart_amnt_val"> $' + mixAndMatch + "</span></span>" : "";
    cart_total_html += cart_data.update_cart.priceSummary.rewards > 0 ? '<span class="cart_amnt_parent"><span class="cart_amnt_label"> rewards </span><span class="cart_amnt_val"> $' + rewards + "</span></span>" : "";
    cart_total_html += cart_data.update_cart.priceSummary.taxes > 0 ? '<span class="cart_amnt_parent"><span class="cart_amnt_label"> taxes </span><span class="cart_amnt_val"> $' + taxes + "</span></span>" : "";
    cart_total_html += cart_data.update_cart.priceSummary.total > 0 ? '<span class="cart_amnt_parent"><span class="cart_amnt_label"> total </span><span class="cart_amnt_val"> $' + total + "</span></span>" : "";

    $(".floating_cart_notes").html(cart_total_html);
    $("#floating_cart .floating_cart_footer a span.cart_total").html(" - $" + total);

    // show cart quantity
    var cart_quantity = 0;
    var cart_items = cart_data.update_cart.items;
    for (const [key, value] of Object.entries(cart_items)) {
      cart_quantity = cart_quantity + value.quantity;
    }

    if (cart_quantity > 0) {
      $(".cart_count").text(cart_quantity);
      $(".open_the_cart_button_icon").find(".cart_count").length == 0 ? $(".open_the_cart_button_icon").append('<span class="cart_count otcbi">' + cart_quantity + "</span>") : "";
    } else {
      render_empty_cart();
    }
  }

  function render_empty_cart() {
    $("#floating_cart .floating_cart_items_inner").html('<p style="text-align:center;width:100%">Your cart is empty</p>');
    $("#floating_cart .floating_cart_footer_wrapper").hide();
    $(".cart_count").text("");
  }

  function convert_and_format_numbers(number) {
    var float_number_in_cents = parseFloat(number);
    var float_number_in_usd = float_number_in_cents / 100;
    var float_number_in_decimals = float_number_in_usd.toFixed(2);
    var float_number_formatted = float_number_in_decimals.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return float_number_formatted;
  }

  // ajax function to load oder details
  function ajax_get_order_details() {
    return new Promise(function (resolve, reject) {
      var url_string = window.location.href;
      var url = new URL(url_string);
      var orderNumber = url.searchParams.get("orderNumber");

      var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
      var retailer_id = leafbridge_wizard_data.retailer_id;

      var data = {
        retailerId: retailer_id,
        orderId: orderNumber,
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
        action: "leafbridge_order_details",
      };

      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: true,
        beforeSend: function () {
          //$('#lb_order_preloader').fadeIn();
        },
        success: function (json) {
          //$('#lb_order_preloader').fadeOut();
          resolve(json);
          //$('.lb-order-inner').fadeIn();
        },
        error: function (json) {
          //$('#lb_order_preloader').fadeOut();
          reject(json);
          //$('.lb-order-inner').fadeIn();
        },
        complete: function (json) {
          //$('#lb_order_preloader').fadeOut();
          //$('.lb-order-inner').fadeIn();
        },
      });
    });
  }

  function single_prod_page_fetch() {
    if ($("div").hasClass("lb_single_prod_page_wrapper")) {
      var error_add_to_cart = "This Item is not available with the selected retailer. Please reset the selection if you wish to buy this item.";
      ajax_get_product_page()
        .then(function (json) {
          $(".lb_prod_single_add_to_cart.lb_prod_single_section").html(json.data.render_html);

          if (json.data.render_html.includes("prod_real_retailer")) {
            ajax_get_retailer_details($(".lb_single_prod_page_wrapper").attr("data_real_retailer"))
              .then(function (json) {
                $("#prod_real_retailer").html(json.data.lb_retailer_custom_name);
              })
              .catch(function (json) {
                console.error("single_prod_page_fetch err", json);
              });
          }

          //GTM view_item push to data layer - START
          var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
          var leafbridge_recent_location = JSON.parse(localStorage.getItem("leafbridge_recent_location"));

          var json = {};
          var item_data = null;
          item_data = JSON.parse($(".lb_prod_single_add_to_cart").attr("filter_data"));

          var cart_prod = {
            product_id: item_data.product_id,
            variation: item_data.variants[0].option,
            prod_count: 1,
            prod_name: item_data.item_name,
          };

          if (item_data != null) {
            json.moreDataLayerPush = {
              item_data: item_data,
              cart_prod: cart_prod,
              location_id: leafbridge_wizard_data.retailer_id,
              location_name: leafbridge_recent_location.retailer_name,
              menu_type: leafbridge_wizard_data.menu_type,
            };
            gtm_push_single_item_data(json, "LB_ViewItem");
          }
          //GTM view_item push to data layer - END
        })
        .catch(function (json) {
          console.error("ajax-get_product_page err", json);
          $(".lb_prod_single_add_to_cart.lb_prod_single_section").html(error_add_to_cart);
        });
    }
  }

  // ajax promise to load retailer details by passing retalier id
  function ajax_get_retailer_details(retailer_id) {
    return new Promise(function (resolve, reject) {
      var data = {
        action: "load_retailer_name",
        retailer_id: retailer_id,
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
      };
      $.ajax({
        type: "POST",
        dataType: "json",
        cache: false,
        proccessData: false,
        url: leafbridge_public_ajax_obj.ajaxurl,
        data: data,
        success: function (json) {
          resolve(json);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) { },
      });
    });
  }

  // ajax function to load product detail on single product page
  function ajax_get_product_page() {
    return new Promise(function (resolve, reject) {
      var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
      var retailer_id = leafbridge_wizard_data.retailer_id;
      var menu_type = leafbridge_wizard_data.menu_type;
      var product_id = $(".lb_single_prod_page_wrapper").attr("data_product_id");

      var data = {
        retailerId: retailer_id,
        product_id: product_id,
        menu_type: menu_type,
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
        action: "leafbridge_single_product",
      };
      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        beforeSend: function () {
          var svg_animation =
            '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"> <circle cx="50" cy="50" fill="none" stroke="#c8c8c8" stroke-width="10" r="35" stroke-dasharray="164.93361431346415 56.97787143782138"> <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50" keyTimes="0;1"></animateTransform> </circle> </svg>';
          $(".lb_prod_single_add_to_cart.lb_prod_single_section").html(
            svg_animation
          );
        },
        success: function (json) {
          resolve(json);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) { },
      });
    });
  }

  function show_category_products() {
    // function to show category pages
    setTimeout(function () {
      if ($("div").hasClass("lb_categories_wrapper")) {
        const urlParams = new URLSearchParams(window.location.search);
        const param_pagination = parseInt(urlParams.get("pagination"));

        if (param_pagination) {
          $(".wizard_category_products_pagination button.next").attr("data_page", param_pagination - 1);
          $(".wizard_category_products_pagination button.next").trigger("click");
          $(".wizard_category_products_pagination button.next").attr("data_page", param_pagination);
          $(".wizard_category_products_pagination button.prev").attr("data_page", param_pagination - 2);
        } else {
          ajax_get_category_products()
            .then(function (json) {
              $(".wizard_category_products_showcase").html(json.data.products_html);
              $("#wizard_prods_view").fadeOut();
            })
            .catch(function (json) {
              console.error("ajax get category products err", json);
              $(".wizard_category_products_showcase").html("<p>No Available Products</p>");
              $("#wizard_prods_view").fadeOut();
              // $('.wizard_category_products_pagination').fadeOut();
            });
        }
      }
    }, 200);
  }

  // ajax function to load category products
  function ajax_get_category_products() {
    return new Promise(function (resolve, reject) {
      var this_category = $(".lb_categories_wrapper").attr("data_category");

      var data = {
        action: "wizard_show_products",
        wizard_data: JSON.parse(localStorage.getItem("leafbridge_wizard_data")),
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
        prod_categories: this_category,
      };
      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        success: function (json) {
          resolve(json);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) { },
      });
    });
  }

  /*
   * new sticky wizard functionalities
   */
  // select store and show store's menu type selector
  $(document).on("change", "#select_store_sticky_wizard", function (event) {
    var this_val = $(this).val();

    $("#select_collection_type_sticky_wizard").prop("disabled", true);
    $("#select_collection_type_sticky_wizard option").remove();
    $("#select_collection_type_sticky_wizard").append('<option value="">Please Select</option>');

    if (this_val !== "") {
      // show the menutype dropdown depending on retailer selection
      var target_menutype_selector_wrapper = $(
        ".menu_type_selector[retailer_id=" + this_val + "]"
      );
      target_menutype_selector_wrapper.siblings(".menu_type_selector").hide();
      target_menutype_selector_wrapper.find("select").prop("disabled", true);
      target_menutype_selector_wrapper.show();
      target_menutype_selector_wrapper.find("select").prop("disabled", false);
    } else {
      $(".menu_type_selector select").prop("disabled", true);
      $("#set_floating_wizard_data").prop("disabled", true);
    }
    reset_cart();
  });

  // select menu type after selecting the retailer
  $(document).on("change", ".menu_type_selector select", function () {
    $("#set_floating_wizard_data").prop("disabled", true);
    $(this).closest("#menu_types_selector_wrapper").attr("selected_menu_type", $(this).val());
    var retailer_id = $(this).closest(".menu_type_selector").attr("retailer_id");
    populate_delivery_pickup_dropdown(retailer_id, "");
    reset_cart();
  });

  function populate_delivery_pickup_dropdown(retailer_id, collection_method) {
    if (collection_method == "") {
      // var url_string  = window.location.href;
      // var url = new URL(url_string);
      // var leafbridge_wizard_data = JSON.parse(localStorage.getItem('leafbridge_wizard_data'));
      // collection_method = (url.searchParams.get("collection_type")) ? url.searchParams.get("collection_type") : leafbridge_wizard_data.collection_method;
    }

    $(".loading_collection_methods").addClass("show");
    get_delivery_pickup(retailer_id)
      .then(function (json) {
        if ($("div").hasClass("retailer_based_store")) {
          var disp_order_type_e = $(".retailer_based_store").attr("order_type");
          var disp_order_type = disp_order_type_e.toLowerCase();
          $(".header_stiky_wizard_inner select").prop("disabled", "disabled");
          $("#select_collection_type_sticky_wizard").html('<option value="' + disp_order_type_e + '" >' + disp_order_type.charAt(0).toUpperCase() + disp_order_type.slice(1) + "</option>");
          $("#select_collection_type_sticky_wizard").val(disp_order_type_e);
          $("#select_collection_type_sticky_wizard").prop("disabled", true);
        }
        else {
          $("#select_collection_type_sticky_wizard").prop("disabled", false);
          json.data.data.fulfillmentOptions.delivery ? $('#select_collection_type_sticky_wizard option[value="DELIVERY"]').length == 0 ? $("#select_collection_type_sticky_wizard").append('<option value="DELIVERY">Delivery</option>') : "" : "";
          json.data.data.fulfillmentOptions.pickup ? $('#select_collection_type_sticky_wizard option[value="PICKUP"]').length == 0 ? $("#select_collection_type_sticky_wizard").append('<option value="PICKUP">Pickup</option>') : "" : "";
          json.data.data.fulfillmentOptions.curbsidePickup ? $('#select_collection_type_sticky_wizard option[value="curbsidePickup"]').length == 0 ? $("#select_collection_type_sticky_wizard").append('<option value="curbsidePickup">Curbside Pickup</option>') : "" : "";
          json.data.data.fulfillmentOptions.driveThruPickup ? $('#select_collection_type_sticky_wizard option[value="driveThruPickup"]').length == 0 ? $("#select_collection_type_sticky_wizard").append('<option value="driveThruPickup">DriveThru Pickup</option>') : "" : "";
        }

        // set collection type
        // $('#select_collection_type_sticky_wizard').val(collection_method.toUpperCase());
        $("#select_collection_type_sticky_wizard").val(collection_method);
        $(".loading_collection_methods").removeClass("show");
      })
      .catch(function (json) {
        console.error("get_delivery_pickup error", json);
      });
  }

  // get raw data for pickup / delivery depending on retailer
  function get_delivery_pickup(retailer_id) {
    return new Promise(function (resolve, reject) {
      var data = {
        action: "show_delivery_pickup_ajax",
        retailer_id: retailer_id,
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
      };

      var return_data = null;
      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        success: function (json) {
          resolve(json);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) {
          // console.log('get_delivery_pickup complete');
        },
      });
    });
  }

  // enable SET button after selecting collection method
  $(document).on("change", "#select_collection_type_sticky_wizard", function () {
    var selected_retailer_id = $("#select_store_sticky_wizard").val();
    var selected_menu_type = $("#select_menu_type_sticky_wizard_" + selected_retailer_id).val();
    $("#menu_types_selector_wrapper").attr("selected_menu_type", selected_menu_type);

    var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));

    if (leafbridge_wizard_data != null) {
      leafbridge_wizard_data.menu_type = selected_menu_type;
      leafbridge_wizard_data.event = "on change select_collection_type_sticky_wizard";
    }

    localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data));

    $("#set_floating_wizard_data").prop("disabled", false);

    setTimeout(function () {
      $("#set_floating_wizard_data").trigger("click");
    }, 100);
  }
  );

  // set button
  $(document).on("click", "#set_floating_wizard_data", function () {
    var leafbridge_wizard_data = {
      age_confirm: true,
      created_time: new Date(),
      collection_method: $("#select_collection_type_sticky_wizard").val(),
      retailer_id: $("#select_store_sticky_wizard").val(),
      menu_type: $("#menu_types_selector_wrapper").attr("selected_menu_type"),
      event: "on change set_floating_wizard_data",
    };

    localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data));

    // reset filter url paremeters
    reset_url_params();

    setTimeout(function () {
      location.reload();
      // show_products_from_filter();
    }, 100);
  });

  function load_default_retailer_settings() {
    return new Promise(function (resolve, reject) {
      var data = {
        action: "get_default_retailer",
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
      };
      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        success: function (json) {
          resolve(json);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) { },
      });
    });
  }
  // sticky wizard reset button
  $(document).on("click", "#reset_retailer_selection , #reset_retailer_selection_v2", function () {
    $("#reset_notice_header").fadeIn();
    $("#floating_cart").removeClass("open_the_cart");
  }
  );

  // on confirm box button click
  $(document).on("click", ".new_reset_buttons_wrapper button", function (e) {
    var reset_button_val = $(this).val();
    if (reset_button_val == "no") {
      $("#reset_notice_header").hide();
    } else {
      e.preventDefault();
      localStorage.removeItem("leafbridge_wizard_data");
      reset_cart();
      location.reload();
    }
  });

  // function to reset cart data
  function reset_cart() {
    var get_local_cart = localStorage.getItem("leafbridge_cart_data");
    if (get_local_cart) {
      let text = "You have added products to the cart. Changing retailer or menu type will remove items from your cart.";
      if (confirm(text) == true) {
        localStorage.removeItem("leafbridge_cart_data");
        render_empty_cart();
      } else {
        var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));

        var selected_retailer = leafbridge_wizard_data.retailer_id;
        var menu_type = leafbridge_wizard_data.menu_type;
        var collection_type = leafbridge_wizard_data.collection_method;

        $("#select_store_sticky_wizard").val(selected_retailer);
        $('.menu_type_selector[retailer_id="' + selected_retailer + '"]').show();
        $('.menu_type_selector[retailer_id="' + selected_retailer + '"]').siblings("div").hide();
        $('.menu_type_selector[retailer_id="' + selected_retailer + '"] select').val(menu_type);
        $('#select_collection_type_sticky_wizard option[value="' + collection_type + '"]').attr("selected", "selected");
      }
    } else {
      localStorage.removeItem("leafbridge_cart_data");
      render_empty_cart();
    }
  }

  // reset url params
  function reset_url_params() {
    var page_url = window.location.href;
    var url = new URL(page_url);

    var updated_url = "";
    updated_url = url.origin + url.pathname + "?pagination=1";
    window.history.pushState({}, "", updated_url);
  }

  //main function to show specials page content
  function specials_page_get_specials_data() {
    // if ($('div').hasClass('leafbridge_special_products_wrapper')) {
    if ($("div").hasClass("leafbridge_special_products_wrapper specials_page_wrapper")) {
      // remove pagination url param from url
      var page_url = window.location.href;
      var url = new URL(page_url);
      var params = new URLSearchParams(url.search);
      var updated_url = "";

      params.delete("pagination");
      var special_id_to_click = params.get("specials_id");

      updated_url = url.origin + url.pathname + "?" + params.toString();
      window.history.pushState({}, "", updated_url);

      // check selected retailer , menutype and collection if same on url and localstorage. if different, show a message to select them manually
      var leafbridge_wizard_data = {};
      if ($("div").hasClass("retailer_based_store")) {
        var force_leafbridge_wizard_data = {
          retailer_id: $(".retailer_based_store").attr("retailer_id"),
          menu_type: $(".retailer_based_store").attr("menu_type"),
          collection_method: $(".retailer_based_store").attr("order_type"),
        };

        leafbridge_wizard_data = {
          age_confirm: true,
          collection_method: force_leafbridge_wizard_data.collection_method,
          created_time: new Date(),
          default_set: true,
          event: "FORCE WIZARD",
          menu_type: force_leafbridge_wizard_data.menu_type,
          retailer_id: force_leafbridge_wizard_data.retailer_id,
        };
      } else {
        leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
      }

      var selected_retailer = leafbridge_wizard_data.retailer_id;
      var menu_type = leafbridge_wizard_data.menu_type;
      var collection_type = leafbridge_wizard_data.collection_method;

      var url_retailer = params.get("retailer_id") ? params.get("retailer_id") : selected_retailer;
      var url_menutype = params.get("menu_type");
      var url_collection_type = params.get("collection_type");
      var url_viewmore = params.get("viewmore") == "true" ? true : false;

      if (url_retailer !== selected_retailer || url_menutype !== menu_type) {
        // var retailer_name = $('#select_store_sticky_wizard').find('option[value='+url_retailer+']').text();
        // var show_menu_type = url_menutype;
        // var collection_type = url_collection_type;
        // var show_combination = '<b>Retailer : '+retailer_name+', Menu Type : '+show_menu_type+', Collection type: '+collection_type+'</b>';
        // $('.specials_tab_notice').html("<b>You have selected a different retailer.</b><p>In order to view the specials mentioned in the url, please change the retailer, menutype and collection type combination to, <br/>"+show_combination+". <br/> or you can select a special menu given by the retailer you have already selected.</p>");
        // $('#select_store_sticky_wizard').val(url_retailer);
        // $('#select_menu_type_sticky_wizard_'+url_retailer).val(url_menutype);
        // $('#select_collection_type_sticky_wizard').val(url_collection_type);
      } else {
      }

      ajax_get_retailer_specials()
        .then(function (json) {
          if (json.data.getSpecials_html == "") {
            $("#leafbridge_special_products_wrapper .specials_tabs").html('<p style="text-align:center;">No Specials found with the selected retailer. Please select another retailer.</p>');
            $("#leafbridge_special_products_wrapper .specials_tabs").css("display", "block");
          } else {
            $("#leafbridge_special_products_wrapper .specials_tabs").css("display", "inline-grid");
            $("#leafbridge_special_products_wrapper .specials_tabs").html(json.data.getSpecials_html);

            //check if url has viewmore attribute
            if (url_viewmore) {
              $(".leafbridge_special_products_wrapper .specials_tabs .specials_tab").fadeIn();
              $("#specials_view_more_wrapper").hide();
            } else {
              if (json.data.getSpecials.length > 4) {
                $("#specials_view_more_wrapper").fadeIn();
              } else {
                $("#specials_view_more_wrapper").hide();
              }
            }

            // check if specials id is selected or not
            if (special_id_to_click != null) {
              $("#leafbridge_special_products_wrapper .specials_tabs .specials_tab[specials_id=" + special_id_to_click + "]").trigger("click");
            } else {
              // if specials_id is not assigned, click the 1st specials box
              $("#leafbridge_special_products_wrapper .specials_tabs .specials_tab").first().trigger("click");
            }
          }
        })
        .catch(function (json) {
          console.error(json);
        });
    }
  }

  // ajax function to get retailer's specials data
  function ajax_get_retailer_specials() {
    return new Promise(function (resolve, reject) {
      var data = {
        action: "get_retailer_specials",
        wizard_data: JSON.parse(localStorage.getItem("leafbridge_wizard_data")),
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
      };
      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        success: function (json) {
          resolve(json);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) { },
      });
    });
  }

  // on click specials page view more button
  $(document).on("click", ".special_cards_view_more_btn", function (e) {
    e.preventDefault();
    $(this).closest(".special_cards_view_more_btn").hide();
    $(".leafbridge_special_products_wrapper .specials_tabs .specials_tab").fadeIn();
  });

  // on click of special id button
  $(document).on("click", ".specials_tab", function () {
    var specials_id = $(this).attr("specials_id");

    var filter_attributes = {};
    filter_attributes.MenuSectionFilter = "SPECIALS";
    filter_attributes.special_id = specials_id;
    filter_attributes.sort_order = "NAME_ASC";
    filter_attributes.search_key_word = "";

    $(this).siblings(".specials_tab").removeClass("active_tab");
    $(".specials_tab_content").addClass("loading_animation");
    $(this).addClass("active_tab");
    $(".specials_tab_content_inner").hide();
    $(".specials_tab_content_inner").html("");

    $(".specials_tab_notice").html("");
    // set url params
    var page_url = window.location.href;
    var url = new URL(page_url);
    var params = new URLSearchParams(url.search);
    var updated_url = "";

    params.delete("pagination");

    // var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    var leafbridge_wizard_data = {};
    if ($("div").hasClass("retailer_based_store")) {
      var force_leafbridge_wizard_data = {
        retailer_id: $(".retailer_based_store").attr("retailer_id"),
        menu_type: $(".retailer_based_store").attr("menu_type"),
        collection_method: $(".retailer_based_store").attr("order_type"),
      };

      leafbridge_wizard_data = {
        age_confirm: true,
        collection_method: force_leafbridge_wizard_data.collection_method,
        created_time: new Date(),
        default_set: true,
        event: "FORCE WIZARD",
        menu_type: force_leafbridge_wizard_data.menu_type,
        retailer_id: force_leafbridge_wizard_data.retailer_id,
      };
    } else {
      leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    }

    if (leafbridge_wizard_data != null) {
      var selected_retailer = leafbridge_wizard_data.retailer_id;
      var menu_type = leafbridge_wizard_data.menu_type;
      var collection_type = leafbridge_wizard_data.collection_method;

      !params.get("retailer_id") ? params.append("retailer_id", selected_retailer) : params.set("retailer_id", selected_retailer);
      !params.get("menu_type") ? params.append("menu_type", menu_type) : params.set("menu_type", menu_type);
      !params.get("collection_type") ? params.append("collection_type", collection_type) : params.set("collection_type", collection_type);
      !params.get("specials_id") ? params.append("specials_id", specials_id) : params.set("specials_id", specials_id);
    }

    updated_url = url.origin + url.pathname + "?" + params.toString();
    window.history.pushState({}, "", updated_url);

    ajax_get_filtered_products(filter_attributes)
      .then(function (json) {
        $(".specials_tab_content").removeClass("loading_animation");
        $(".specials_tab_content_inner").html(json.data.products_html);
        $(".specials_tab_content_inner").fadeIn();

        prepare_specials_filter(json.data.specials_filter_html);
        var scroll_offset = (window.screen.width > 768) ? parseInt(parseInt(window.screen.height)) / 2 : parseInt(parseInt(window.screen.height) / 3.3);
        $("html, body").animate({ scrollTop: $("#specials_tab_content").offset().top - scroll_offset }, 350);
      })
      .catch(function (json) {
        console.error("get filtered prods ", json);
      });
  });

  function prepare_specials_filter(products_filter_attrs) {
    $(".specials_tab_filter .specials_tab_filter_inner").html("");
    products_filter_attrs.category.includes("li") ? $(".specials_tab_filter .specials_tab_filter_inner").append(products_filter_attrs.category) : "";
    products_filter_attrs.subcategory.includes("li") ? $(".specials_tab_filter .specials_tab_filter_inner").append(products_filter_attrs.subcategory) : "";
    products_filter_attrs.brand.includes("li") ? $(".specials_tab_filter .specials_tab_filter_inner").append(products_filter_attrs.brand) : "";
    products_filter_attrs.effects.includes("li") ? $(".specials_tab_filter .specials_tab_filter_inner").append(products_filter_attrs.effects) : "";
    products_filter_attrs.strainType.includes("li") ? $(".specials_tab_filter .specials_tab_filter_inner").append(products_filter_attrs.strainType) : "";
    products_filter_attrs.potencyCbd.includes("li") ? $(".specials_tab_filter .specials_tab_filter_inner").append(products_filter_attrs.potencyCbd) : "";
    products_filter_attrs.potencyThc.includes("li") ? $(".specials_tab_filter .specials_tab_filter_inner").append(products_filter_attrs.potencyThc) : "";
  }

  //main function to show specials cards when it's shortcode is called
  function get_specials_menus_cards() {
    if ($("div").hasClass("leafbridge_special_cards_wrapper")) {
      ajax_get_special_menu_cards()
        .then(function (json) {
          if (json.data.getSpecials_html == "") {
            $("#leafbridge_special_products_wrapper .specials_tabs_new").html('<p style="text-align:center;">No Specials found with the selected retailer. Please select another retailer.</p>');
            $("#leafbridge_special_products_wrapper .specials_tabs_new").css("display", "block");
          } else {
            $("#leafbridge_special_products_wrapper .specials_tabs_new").css("display", "inline-grid");
            $("#leafbridge_special_products_wrapper .specials_tabs_new").html(json.data.getSpecials_html);
            json.data.getSpecials.length > 4 ? $(".specials_view_more_wrapper").fadeIn() : $(".specials_view_more_wrapper").hide();
          }
        })
        .catch(function (json) {
          console.error(json);
        });
    }
  }

  // show selected retailer name and link to locations page
  function show_retailer_name_bar() {
    var leafbridgeData = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
    if (leafbridgeData) {
      var menu_type = leafbridgeData.menu_type.toLowerCase();
      var name_bar_collection = leafbridgeData.collection_method.toLowerCase();
    }

    var data = {
      action: "load_retailer_name",
      retailer_id: leafbridgeData.retailer_id,
      nonce_ajax: leafbridge_public_ajax_obj.nonce,
    };

    $.ajax({
      type: "POST",
      dataType: "json",
      cache: false,
      proccessData: false,
      url: leafbridge_public_ajax_obj.ajaxurl,
      data: data,
      success: function (response) {

        $(".retailer_name").html(response.data.lb_retailer_custom_name);
        $(".name_bar_menutype").html(menu_type);
        $(".name_bar_collection").html(name_bar_collection);
        $(".name_bar_address").html(response.data.lb_retailer_address);
        $(".name_bar_phone").html(response.data.lb_retailer_phone);
        $(".name_bar_day_monday").html(response.data.lb_retailer_day_monday);
        $(".name_bar_day_monday_start").html(response.data.lb_retailer_day_monday_start);
        $(".name_bar_day_monday_end").html(response.data.lb_retailer_day_monday_end);
        $(".name_bar_day_tuesday").html(response.data.lb_retailer_day_tuesday);
        $(".name_bar_day_tuesday_start").html(response.data.lb_retailer_day_tuesday_start);
        $(".name_bar_day_tuesday_end").html(response.data.lb_retailer_day_tuesday_end);
        $(".name_bar_day_wednesday").html(response.data.lb_retailer_day_wednesday);
        $(".name_bar_day_wednesday_start").html(response.data.lb_retailer_day_wednesday_start);
        $(".name_bar_day_wednesday_end").html(response.data.lb_retailer_day_wednesday_end);
        $(".name_bar_day_thursday").html(response.data.lb_retailer_day_thursday);
        $(".name_bar_day_thursday_start").html(response.data.lb_retailer_day_thursday_start);
        $(".name_bar_day_thursday_end").html(response.data.lb_retailer_day_thursday_end);
        $(".name_bar_day_friday").html(response.data.lb_retailer_day_friday);
        $(".name_bar_day_friday_start").html(response.data.lb_retailer_day_friday_start);
        $(".name_bar_day_friday_end").html(response.data.lb_retailer_day_friday_end);
        $(".name_bar_day_saturday").html(response.data.lb_retailer_day_saturday);
        $(".name_bar_day_saturday_start").html(response.data.lb_retailer_day_saturday_start);
        $(".name_bar_day_saturday_end").html(response.data.lb_retailer_day_saturday_end);
        $(".name_bar_day_sunday").html(response.data.lb_retailer_day_sunday);
        $(".name_bar_day_sunday_start").html(response.data.lb_retailer_day_sunday_start);
        $(".name_bar_day_sunday_end").html(response.data.lb_retailer_day_sunday_end);
        $(".name_bar_today_closing_time").html(response.data.lb_retailer_today_closing_time);


        $('.seperate_today_opening_time').html(response.data.lb_retailer_today_open_time);
        $('.seperate_today_closing_time').html(response.data.lb_retailer_today_closing_time);
      },
    });
  }

  function ajax_get_special_menu_cards() {
    return new Promise(function (resolve, reject) {
      var leafbridge_wizard_data = {};
      if ($("div").hasClass("retailer_based_store")) {
        var force_leafbridge_wizard_data = {
          retailer_id: $(".retailer_based_store").attr("retailer_id"),
          menu_type: $(".retailer_based_store").attr("menu_type"),
          collection_method: $(".retailer_based_store").attr("order_type"),
        };

        leafbridge_wizard_data = {
          age_confirm: true,
          collection_method: force_leafbridge_wizard_data.collection_method,
          created_time: new Date(),
          default_set: true,
          event: "FORCE WIZARD",
          menu_type: force_leafbridge_wizard_data.menu_type,
          retailer_id: force_leafbridge_wizard_data.retailer_id,
        };
      } else {
        leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
      }

      var data = {
        action: "get_retailer_special_menus",
        wizard_data: leafbridge_wizard_data,
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
      };
      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        success: function (json) {
          resolve(json);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) { },
      });
    });
  }

  // ajax function to get selected specials products
  function ajax_get_filtered_products(filter_attributes) {
    return new Promise(function (resolve, reject) {
      var wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));

      if ($("div").hasClass("retailer_based_store")) {
        wizard_data = {
          retailer_id: $(".retailer_based_store").attr("retailer_id"),
          menu_type: $(".retailer_based_store").attr("menu_type"),
          collection_method: $(".retailer_based_store").attr("order_type"),
        };
      }

      var data = {
        action: "wizard_show_products",
        wizard_data: wizard_data,
        nonce_ajax: leafbridge_public_ajax_obj.nonce,
        prods_pageNumber: 1,
        prod_categories: filter_attributes,
        search_key_word: "",
        filter_attributes: filter_attributes,
      };

      $.ajax({
        url: leafbridge_public_ajax_obj.ajaxurl,
        dataType: "json",
        cache: false,
        data: data,
        type: "POST",
        proccessData: false,
        success: function (json) {
          resolve(json);
        },
        error: function (json) {
          reject(json);
        },
        complete: function (json) { },
      });
    });
  }

  function specific_product_filter_slider() {
    if ($("div").hasClass("leafbridge_specific_product_filter_wrapper")) {
      $(".leafbridge_specific_product_filter_wrapper").each(function (index, el) {
        var this_slider_wrapper = $(this);
        var this_slider_wrapper_animation = $(this).find("#loading_animation");
        var lb_specific_prods_slider_inner = $(this).find(".lb_specific_prods_slider_inner");
        var filter_attributes_html = JSON.parse($(this).find(".lb_specific_prods_slider_wrapper").attr("filter_attributes"));
        var filter_attributes = {};
        filter_attributes.search_key_word = "";

        // filter_attributes.fetch_prod_count = 1000;
        filter_attributes.fetch_prod_count = filter_attributes_html.count ? filter_attributes_html.count : 1000;
        filter_attributes_html.special == "true" ? (filter_attributes.MenuSectionFilter = "SPECIALS") : "";
        filter_attributes_html.staff_picks == "yes" ? (filter_attributes.MenuSectionFilter = "STAFF_PICKS") : "";
        filter_attributes_html.custom_selection == "true" ? (filter_attributes.MenuSectionFilter = "CUSTOM_SECTION") : ""; // set if custom selection menu
        filter_attributes_html.special_id ? (filter_attributes.special_id = filter_attributes_html.special_id) : "";
        filter_attributes_html.custom_selection == "true" ? (filter_attributes.special_id = filter_attributes_html.custom_menu_name) : ""; //set custom selection menu name
        filter_attributes_html.category ? (filter_attributes.categories = filter_attributes_html.category.toUpperCase()) : "";
        filter_attributes_html.sub_category ? (filter_attributes.subcategory = filter_attributes_html.sub_category.toUpperCase()) : "";
        filter_attributes_html.effect ? (filter_attributes.effects = filter_attributes_html.effect.toUpperCase()) : "";
        filter_attributes_html.strain_type ? (filter_attributes.strainType = filter_attributes_html.strain_type.toUpperCase()) : "";
        filter_attributes_html.brand ? (filter_attributes.brands = filter_attributes_html.brand) : "";
        filter_attributes.sort_order = filter_attributes_html.sort ? filter_attributes_html.sort.toUpperCase() : "NAME_ASC";
        filter_attributes.prod_slider = true;

        ajax_get_filtered_products(filter_attributes)
          .then(function (json) {
            if (json.data.products_count > 0) {
              this_slider_wrapper_animation.hide();
              lb_specific_prods_slider_inner.show();
              this_slider_wrapper.find(".lb_specific_prods_slider_inner").find(".swiper-wrapper").html(json.data.products_html);
              // this_slider_wrapper.find('.leafbridge_product_modal_outer').remove();
              prepare_owl_carousel_filter(this_slider_wrapper);
            } else {
              this_slider_wrapper.find(".lb_specific_prods_slider_wrapper").html("We can't find what you're looking for. Please browse our other products using the filters on this page.");
            }
          })
          .catch(function (json) {
            console.error("filtered prods error ", json);
          });
      });
    }
  }

  // prepare owl carousel after loading products for filtered slider content
  function prepare_owl_carousel_filter(this_slider_wrapper) {
    var swiper_autoplay = this_slider_wrapper.attr("data_autoplay") == "true" ? true : false;

    var this_slider_wrapper_id = $("#" + this_slider_wrapper.attr("id"));
    var this_slider_wrapper_idstr = "#" + this_slider_wrapper.attr("id");

    this_slider_wrapper_id.find(".swipernav_wrapper").show(); // show slider nav after initializing the slider

    var swiper = new Swiper(
      this_slider_wrapper_idstr + " .lb_specific_prods_slider_inner.swiper", {
      spaceBetween: 20,
      loop: false,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: this_slider_wrapper_idstr + " .swiper-button-next",
        prevEl: this_slider_wrapper_idstr + " .swiper-button-prev",
      },
      centeredSlides: false,
      breakpoints: {
        0: {
          slidesPerView: "auto",
          spaceBetween: 8,
        },
        481: {
          slidesPerView: "auto",
        },
      },
    }
    );
  }

  // promise to check
  function check_wizard_attributes() {
    return new Promise(function (resolve, reject) {
      var leafbridge_wizard_data = localStorage.getItem("leafbridge_wizard_data") ? JSON.parse(localStorage.getItem("leafbridge_wizard_data")) : {};
      var leafbridge_cart_data = localStorage.getItem("leafbridge_cart_data") ? JSON.parse(localStorage.getItem("leafbridge_cart_data")) : {};

      var force_leafbridge_wizard_data = {};
      var retailer_based_store = false;

      if ($("div").hasClass("retailer_based_store")) {
        retailer_based_store = true;

        force_leafbridge_wizard_data.retailer_id = $(".retailer_based_store").attr("retailer_id");
        force_leafbridge_wizard_data.menu_type = $(".retailer_based_store").attr("menu_type");
        force_leafbridge_wizard_data.collection_method = $(".retailer_based_store").attr("order_type");

        if (Object.keys(leafbridge_cart_data).length != 0 && Object.keys(leafbridge_wizard_data).length != 0) {
          if (leafbridge_wizard_data.retailer_id != force_leafbridge_wizard_data.retailer_id || leafbridge_wizard_data.menu_type != force_leafbridge_wizard_data.menu_type || leafbridge_cart_data.retailer_id != force_leafbridge_wizard_data.retailer_id || leafbridge_cart_data.menutype != force_leafbridge_wizard_data.menu_type) {
            var confirm_change_cart = confirm("There are products from another retailer / menutype so adding this product will reset your current cart. Procced?");
            if (confirm_change_cart) {
              leafbridge_cart_data = {};
              localStorage.removeItem("leafbridge_cart_data");

              leafbridge_wizard_data.retailer_id = force_leafbridge_wizard_data.retailer_id;
              leafbridge_wizard_data.menu_type = force_leafbridge_wizard_data.menu_type;
              leafbridge_wizard_data.collection_method = force_leafbridge_wizard_data.collection_method;
            } else {
            }
          }

        } else {
          leafbridge_wizard_data.retailer_id = force_leafbridge_wizard_data.retailer_id;
          leafbridge_wizard_data.menu_type = force_leafbridge_wizard_data.menu_type;
          leafbridge_wizard_data.collection_method = force_leafbridge_wizard_data.collection_method;
        }
      }

      var urlParams = new URLSearchParams(window.location.search);

      var page_url = window.location.href;
      var url = new URL(page_url);

      var url_par_retailer = urlParams.get("retailer_id");
      var url_par_menu_type = urlParams.get("menu_type");
      var url_par_collection_type = urlParams.get("collection_type");
      var url_par_sort = urlParams.get("sort");

      // filter params
      var leafbridge_filter_params = {};

      urlParams.get("products_search") ? (leafbridge_filter_params.products_search = urlParams.get("products_search")) : "";
      urlParams.get("MenuSectionFilter") ? (leafbridge_filter_params.MenuSectionFilter = urlParams.get("MenuSectionFilter")) : "";
      urlParams.get("categories") ? (leafbridge_filter_params.categories = urlParams.get("categories")) : "";
      urlParams.get("subcategory") ? (leafbridge_filter_params.subcategory = urlParams.get("subcategory")) : "";
      urlParams.get("brands") ? (leafbridge_filter_params.brands = urlParams.get("brands")) : "";
      urlParams.get("potency_cbd") ? (leafbridge_filter_params.potency_cbd = urlParams.get("potency_cbd")) : "";
      urlParams.get("potency_thc") ? (leafbridge_filter_params.potency_thc = urlParams.get("potency_thc")) : "";
      urlParams.get("effects") ? (leafbridge_filter_params.effects = urlParams.get("effects")) : "";
      urlParams.get("strainType") ? (leafbridge_filter_params.strainType = urlParams.get("strainType")) : "";
      urlParams.get("weight") ? (leafbridge_filter_params.weight = urlParams.get("weight")) : "";

      localStorage.setItem("leafbridge_filter_params", JSON.stringify(leafbridge_filter_params));

      if (localStorage.getItem("leafbridge_wizard_data") != null) {
        // var collection_method = leafbridge_wizard_data.collection_method.toUpperCase();
        var collection_method = leafbridge_wizard_data.collection_method;
        var created_time = leafbridge_wizard_data.created_time;
        var menu_type = leafbridge_wizard_data.menu_type;
        var retailer_id = leafbridge_wizard_data.retailer_id;

        $("#leafbridge_products_sort_by").val(url_par_sort).trigger("change");

        if (url_par_retailer && url_par_menu_type && url_par_collection_type) {
          if (leafbridge_cart_data) {
            if (url_par_retailer != retailer_id || url_par_menu_type != menu_type || url_par_collection_type != collection_method) {
              var text = "You have added products to the cart. Changing retailer or menu type will remove items from your cart.";

              if (confirm(text) == true) {
                leafbridge_wizard_data.created_time = new Date();
                leafbridge_wizard_data.collection_method = url_par_collection_type;
                leafbridge_wizard_data.menu_type = url_par_menu_type;
                leafbridge_wizard_data.retailer_id = url_par_retailer;
                leafbridge_wizard_data.sort = url_par_sort;
                leafbridge_wizard_data.event = "reset cart";

                link_wizard_set_values(url_par_collection_type, url_par_retailer, url_par_menu_type, url_par_sort);
                localStorage.removeItem("leafbridge_cart_data");
                render_empty_cart();
              } else {
                leafbridge_wizard_data.created_time = new Date();
                leafbridge_wizard_data.collection_method = collection_method;
                leafbridge_wizard_data.menu_type = menu_type;
                leafbridge_wizard_data.retailer_id = retailer_id;
                leafbridge_wizard_data.sort = url_par_sort;
                leafbridge_wizard_data.event = "DONT reset cart";

                link_wizard_set_values(collection_method, retailer_id, menu_type, url_par_sort);
                urlParams.set("collection_type", collection_method);
                urlParams.set("menu_type", menu_type);
                urlParams.set("retailer_id", retailer_id);
                var updated_url = url.origin + url.pathname + "?" + urlParams.toString();
                window.history.pushState({}, "", updated_url);
              }
            } else {
              leafbridge_wizard_data.created_time = new Date();
              leafbridge_wizard_data.collection_method = collection_method;
              leafbridge_wizard_data.menu_type = menu_type;
              leafbridge_wizard_data.retailer_id = retailer_id;
              leafbridge_wizard_data.sort = url_par_sort;
              leafbridge_wizard_data.event = "CART DATA - url and localStorage data MATCH";

              link_wizard_set_values(collection_method, retailer_id, menu_type, url_par_sort);
            }
          }
          else {
            leafbridge_wizard_data.created_time = new Date();
            leafbridge_wizard_data.collection_method = url_par_collection_type;
            leafbridge_wizard_data.menu_type = url_par_menu_type;
            leafbridge_wizard_data.retailer_id = url_par_retailer;
            leafbridge_wizard_data.sort = url_par_sort;
            leafbridge_wizard_data.event = "EMPTY CART";

            link_wizard_set_values(url_par_collection_type, url_par_retailer, url_par_menu_type, url_par_sort);
          }
        }
        else {
          leafbridge_wizard_data.created_time = new Date();
          leafbridge_wizard_data.collection_method = collection_method;
          leafbridge_wizard_data.menu_type = menu_type;
          leafbridge_wizard_data.retailer_id = retailer_id;
          leafbridge_wizard_data.sort = url_par_sort;
          leafbridge_wizard_data.event = "NO URL DATA";
          link_wizard_set_values(collection_method, retailer_id, menu_type, url_par_sort);
        }

        localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data));
        resolve();
      }
      else {
        load_default_retailer_settings()
          .then(function (json) {
            var leafbridge_wizard_data = {};

            const urlParams = new URLSearchParams(window.location.search);
            const url_par_retailer = urlParams.get("retailer_id");
            const url_par_menu_type = urlParams.get("menu_type");
            const url_par_collection_type = urlParams.get("collection_type");

            if (url_par_retailer && url_par_menu_type && url_par_collection_type) {
              var collection_method = url_par_collection_type;
              var created_time = new Date();
              var menu_type = url_par_menu_type;
              var retailer_id = url_par_retailer;

              leafbridge_wizard_data.age_confirm = json.data.leafbridge_default_settings.age_confirmation == "1" ? false : true; // if age_confirmation 1 means age modal is enabled. so age_confirmation should be kept false otherwise it won't show the age confirmation modal
              leafbridge_wizard_data.created_time = new Date();
              leafbridge_wizard_data.default_set = true;
              leafbridge_wizard_data.collection_method = retailer_based_store ? force_leafbridge_wizard_data.collection_method : url_par_collection_type;
              leafbridge_wizard_data.menu_type = retailer_based_store ? force_leafbridge_wizard_data.menu_type : url_par_menu_type;
              leafbridge_wizard_data.retailer_id = retailer_based_store ? force_leafbridge_wizard_data.retailer_id : url_par_retailer;
              leafbridge_wizard_data.sort = url_par_sort;
              leafbridge_wizard_data.event = "LOCAL STORAGE NULL - have URL DATA";

              localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data));
              link_wizard_set_values(collection_method, retailer_id, menu_type, url_par_sort);

              resolve();
            } else {
              leafbridge_wizard_data.age_confirm = json.data.leafbridge_default_settings.age_confirmation == "1" ? false : true; // if age_confirmation 1 means age modal is enabled. so age_confirmation should be kept false otherwise it won't show the age confirmation modal
              leafbridge_wizard_data.created_time = new Date();
              leafbridge_wizard_data.default_set = true;

              leafbridge_wizard_data.collection_method = retailer_based_store ? force_leafbridge_wizard_data.collection_method : json.data.leafbridge_default_settings.default_order_type.toUpperCase();
              leafbridge_wizard_data.menu_type = retailer_based_store ? force_leafbridge_wizard_data.menu_type : json.data.leafbridge_default_settings.default_menu_type;
              leafbridge_wizard_data.retailer_id = retailer_based_store ? force_leafbridge_wizard_data.retailer_id : json.data.leafbridge_default_settings.default_store;

              leafbridge_wizard_data.sort = url_par_sort;
              leafbridge_wizard_data.event = "LOCAL STORAGE NULL - CALL AJAX DEFAULTS";

              var collection_method = leafbridge_wizard_data.collection_method;
              var created_time = leafbridge_wizard_data.created_time;
              var menu_type = leafbridge_wizard_data.menu_type;
              var retailer_id = leafbridge_wizard_data.retailer_id;

              localStorage.setItem("leafbridge_wizard_data", JSON.stringify(leafbridge_wizard_data));
              link_wizard_set_values(collection_method, retailer_id, menu_type, url_par_sort);

              resolve();
            }

            // popup wizard only
            if (!leafbridge_wizard_data.age_confirm) {
              $("#leafbridge_shop_wizard_popup").show();
              $(".leafbridge_popup_box").show();
            }
          })
          .catch(function (json) {
            console.error("load_default_retailer_settings", json);
            reject(json);
          });
      }

      // get saved filter attrs and append to url again.
      var localstorage_leafbridge_filter_params = JSON.parse(localStorage.getItem("leafbridge_filter_params"));

      for (const filter_attr in localstorage_leafbridge_filter_params) {
        var filter_val = localstorage_leafbridge_filter_params[filter_attr];
        urlParams.set(filter_attr, filter_val);
      }

      var updated_url = url.origin + url.pathname + "?" + urlParams.toString();
      window.history.pushState({}, "", updated_url);

      // if cart is expired remove it
      var newCart = localStorage.getItem("leafbridge_cart_data") ? JSON.parse(localStorage.getItem("leafbridge_cart_data")) : null;
      if (newCart != null) {
        if (newCart.cart_creation_utc) {
          var current_utc = new Date();
          var cart_creation_utc = new Date(newCart.cart_creation_utc);

          // To calculate the time difference of two dates
          var Difference_In_Time = current_utc.getTime() - cart_creation_utc.getTime();

          // To calculate the no. of days between two dates
          var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);

          if (Difference_In_Days > 21) {
            localStorage.removeItem('leafbridge_cart_data');
          }
        }
      }
      else {
        // console.log('missing newCart.cart_creation_utc');
        localStorage.removeItem('leafbridge_cart_data');
      }

    });
  }

  function link_wizard_set_values(collection_method, retailer_id, menu_type, set_sort) {
    // console.log('%c%s%s','color:yellow',collection_method+' '+retailer_id+' '+menu_type+' '+set_sort);
    // prepare dropdown
    populate_delivery_pickup_dropdown(retailer_id, collection_method);

    // set retailer
    $("#select_store_sticky_wizard").val(retailer_id);
    $("#select_store_sticky_wizard").prop("disabled", false);

    // set menu type
    $(".header_stiky_wizard_select_wrapper[retailer_id=" + retailer_id + "]").siblings(".header_stiky_wizard_select_wrapper").hide();
    $(".header_stiky_wizard_select_wrapper[retailer_id=" + retailer_id + "]").show();
    $("#select_menu_type_sticky_wizard_" + retailer_id).val(menu_type);
    $("#select_menu_type_sticky_wizard_" + retailer_id).prop("disabled", false);

    // set sort
    set_sort ? $("#leafbridge_products_sort_by").val(set_sort) : "";
  }

  // breadcrumbs_modifications
  function breadcrumbs_modifications() {
    if ($(".pagination_button").hasClass("lb_recent_location")) {
      var lb_recent_location = JSON.parse(localStorage.getItem("leafbridge_recent_location"));
      if (localStorage.getItem("leafbridge_recent_location")) {
        $(".pagination_button.lb_recent_location").find("a").attr("href", lb_recent_location.retailer_page);
        $(".pagination_button.lb_recent_location").find("a").text(lb_recent_location.retailer_name);
      } else {
        $(".pagination_button.lb_recent_location").remove();
      }
    } else {
    }
  }

  // GTM dataLayer push functions
  function gtm_push_single_item_data(pushData, eventName) {
    var prepped_pushData = {};

    var get_price = 0;
    var get_special_price = 0;
    var get_discounted_price = 0;
    var item_variants = pushData.moreDataLayerPush.item_data.variants;
    var selected_menu_type = pushData.moreDataLayerPush.menu_type;
    var item_qty = parseFloat(pushData.moreDataLayerPush.cart_prod.prod_count);

    for (var i = 0; i < item_variants.length; i++) {
      var this_item_variant = item_variants[i];
      if (this_item_variant.option == pushData.moreDataLayerPush.cart_prod.variation) {
        get_price = selected_menu_type == "MEDICAL" ? parseFloat(this_item_variant.priceMed) * item_qty : parseFloat(this_item_variant.priceRec) * item_qty;
        get_special_price = selected_menu_type == "MEDICAL" ? this_item_variant.specialPriceMed != null ? parseFloat(this_item_variant.specialPriceMed) * item_qty : null : this_item_variant.specialPriceRec != null ? parseFloat(this_item_variant.specialPriceRec) * item_qty : null;
        get_discounted_price = get_special_price != null ? get_price - get_special_price : 0;
      }
    }

    // prepped_pushData.all = (pushData) ? pushData : "N/A";
    prepped_pushData.event = eventName;
    prepped_pushData.item_id = pushData.moreDataLayerPush.item_data.item_id ? pushData.moreDataLayerPush.item_data.item_id : "N/A";
    prepped_pushData.item_name = pushData.moreDataLayerPush.item_data.item_name ? pushData.moreDataLayerPush.item_data.item_name : "N/A";
    prepped_pushData.currency = "USD";
    prepped_pushData.quantity = pushData.moreDataLayerPush.cart_prod.prod_count ? pushData.moreDataLayerPush.cart_prod.prod_count : "N/A";
    prepped_pushData.item_value = get_price;
    prepped_pushData.item_discount = parseFloat(get_discounted_price.toFixed(3));
    prepped_pushData.item_discounted_value = get_special_price;
    prepped_pushData.item_brand = pushData.moreDataLayerPush.item_data.brand_name ? pushData.moreDataLayerPush.item_data.brand_name : "N/A";
    prepped_pushData.item_category = pushData.moreDataLayerPush.item_data.category ? pushData.moreDataLayerPush.item_data.category : "N/A";
    prepped_pushData.item_subcategory = pushData.moreDataLayerPush.item_data.subcategory ? pushData.moreDataLayerPush.item_data.subcategory : "N/A";
    prepped_pushData.item_variant = pushData.moreDataLayerPush.cart_prod.variation ? pushData.moreDataLayerPush.cart_prod.variation : "N/A";
    prepped_pushData.location_id = pushData.moreDataLayerPush.location_id ? pushData.moreDataLayerPush.location_id : "N/A";
    prepped_pushData.location_name = pushData.moreDataLayerPush.location_name ? pushData.moreDataLayerPush.location_name : "N/A";
    prepped_pushData.menu_type = pushData.moreDataLayerPush.menu_type ? pushData.moreDataLayerPush.menu_type : "N/A";
    prepped_pushData.item_strain_type = pushData.moreDataLayerPush.item_data.strainType ? pushData.moreDataLayerPush.item_data.strainType : "N/A";
    prepped_pushData.item_potency_thc = pushData.moreDataLayerPush.item_data.potencyThc ? pushData.moreDataLayerPush.item_data.potencyThc : "N/A";
    prepped_pushData.item_potency_cbd = pushData.moreDataLayerPush.item_data.potencyCbd ? pushData.moreDataLayerPush.item_data.potencyCbd : "N/A";

    if (eventName == "LB_ViewItem") {
      delete prepped_pushData.quantity;
    }

    window.dataLayer = window.dataLayer || [];
    dataLayer.push(prepped_pushData);
    // console.log("gtm_push_single_item_data", dataLayer);

  }

  function gtm_push_items_data(pushData, eventName) {
    var prepped_pushData = {};

    // prep items related data 
    var prep_items = {};
    var pushDataItems = pushData.moreDataLayerPush.order_details.items;

    // prep items data 
    var prep_items = {};
    for (var i = 0; i < pushDataItems.length; i++) {
      var selected_menu_type = pushData.moreDataLayerPush.menu_type;
      var pushDataItem = pushDataItems[i];
      var item_qty = parseInt(pushDataItem.quantity);
      var item_price_total = item_qty * (parseFloat(pushDataItem.price) / 100);
      var item_discount = null;
      var item_discounted_value = null;
      var item_varient = pushDataItem.option;


      var item_variants = pushDataItem.product.variants;
      for (var vrnts = 0; vrnts < item_variants.length; vrnts++) {
        var current_variant = item_variants[vrnts];
        if (current_variant.option == item_varient) {
          item_discounted_value = (selected_menu_type == "RECREATIONAL") ? current_variant.specialPriceRec : current_variant.specialPriceMed;
          item_discounted_value = (item_discounted_value != null) ? parseFloat((parseFloat(item_discounted_value) * item_qty).toFixed(3)) : null;
          item_discount = (item_discounted_value != null) ? parseFloat((item_price_total - item_discounted_value).toFixed(3)) : null;
        }
      }

      prep_items[pushDataItem.productId] = {};
      prep_items[pushDataItem.productId].index = pushDataItem.productId;
      prep_items[pushDataItem.productId].item_id = pushDataItem.productId;
      prep_items[pushDataItem.productId].item_name = pushDataItem.product.name;
      prep_items[pushDataItem.productId].quantity = pushDataItem.quantity;
      prep_items[pushDataItem.productId].item_value = parseFloat(item_price_total.toFixed(2));
      prep_items[pushDataItem.productId].item_discount = item_discount;
      prep_items[pushDataItem.productId].item_discounted_value = item_discounted_value;
      prep_items[pushDataItem.productId].item_brand_id = (pushDataItem.product.brand != null) ? pushDataItem.product.brand.id : "N/A";
      prep_items[pushDataItem.productId].item_brand_name = (pushDataItem.product.brand != null) ? pushDataItem.product.brand.name : "N/A";
      prep_items[pushDataItem.productId].item_category = pushDataItem.product.category;
      prep_items[pushDataItem.productId].item_subcategory = pushDataItem.product.subcategory;
      prep_items[pushDataItem.productId].item_variant = item_varient;
      prep_items[pushDataItem.productId].item_strain_type = pushDataItem.product.strainType;
      prep_items[pushDataItem.productId].item_potency_thc = (pushDataItem.product.potencyThc.formatted != null) ? pushDataItem.product.potencyThc.formatted : "N/A";
      prep_items[pushDataItem.productId].item_potency_cbd = (pushDataItem.product.potencyCbd.formatted != null) ? pushDataItem.product.potencyCbd.formatted : "N/A";
    }

    // localStorage.setItem('purchase_checkout_id', '12312312');

    // prepped_pushData.allData = pushData.moreDataLayerPush.order_details ? pushData.moreDataLayerPush.order_details : {};

    prepped_pushData.event = eventName;
    prepped_pushData.currency = "USD";
    prepped_pushData.items = prep_items;
    prepped_pushData.location_id = pushData.moreDataLayerPush.location_id ? pushData.moreDataLayerPush.location_id : "N/A";
    prepped_pushData.location_name = pushData.moreDataLayerPush.location_name ? pushData.moreDataLayerPush.location_name : "N/A";
    prepped_pushData.menu_type = pushData.moreDataLayerPush.menu_type ? pushData.moreDataLayerPush.menu_type : "N/A";
    prepped_pushData.transaction_id = (pushData.moreDataLayerPush.order_details.orderNumber) ? pushData.moreDataLayerPush.order_details.orderNumber : "N/A";
    prepped_pushData.transaction_total = (pushData.moreDataLayerPush.order_details.total) ? pushData.moreDataLayerPush.order_details.total : "N/A";
    prepped_pushData.transaction_subtotal = (pushData.moreDataLayerPush.order_details.subtotal) ? pushData.moreDataLayerPush.order_details.subtotal : "N/A";
    prepped_pushData.transaction_tax = (pushData.moreDataLayerPush.order_details.tax) ? pushData.moreDataLayerPush.order_details.tax : "N/A";
    // prepped_pushData.transaction_discount = (pushData.moreDataLayerPush.order_details) ? pushData.moreDataLayerPush.order_details.orderNumber : "N/A";
    // prepped_pushData.transaction_fee = (pushData.moreDataLayerPush.order_details) ? pushData.moreDataLayerPush.order_details.orderNumber : "N/A";
    // prepped_pushData.transaction_mixmatch = (pushData.moreDataLayerPush.order_details) ? pushData.moreDataLayerPush.order_details.orderNumber : "N/A";
    // prepped_pushData.transaction_rewards = (pushData.moreDataLayerPush.order_details) ? pushData.moreDataLayerPush.order_details.orderNumber : "N/A";
    prepped_pushData.transaction_type = (pushData.moreDataLayerPush.order_details.pickup) ? ((pushData.moreDataLayerPush.order_details.pickup) ? "PICKUP" : "DELIVER") : "N/A";
    prepped_pushData.transaction_user = (pushData.moreDataLayerPush.order_details.customer) ? pushData.moreDataLayerPush.order_details.customer : "N/A";
    prepped_pushData.transaction_date = (pushData.moreDataLayerPush.order_details.createdAt) ? pushData.moreDataLayerPush.order_details.createdAt : "N/A";

    window.dataLayer = window.dataLayer || [];
    dataLayer.push(prepped_pushData);
    // console.log("gtm_push_items_data", dataLayer);
  }


  // Run all the functions that is necessary to run after page load
  function page_load_functions() {
    // use a promise to check the attributes then call the related functions
    check_wizard_attributes()
      .then(function () {
        //show products based on retailer selection - ajax
        if ($("div").hasClass("show_products_based_on_retailer")) {
          show_products_from_filter();
        }

        if ($("div").hasClass("leafbridge_featured_products_wrapper")) {
          var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
          if (leafbridge_wizard_data) {
            get_featured_products_ajax();
            $("#leafbridge_shop_wizard_view_products").fadeIn("250");
          }
        }

        //load order details
        if ($("div").hasClass("leafbridge_order_details_wrapper")) {
          ajax_get_order_details()
            .then(function (json) {
              $("#leafbridge_order_details_wrapper").html(json.data.order_html);
              $("#loading_animation").hide();
              localStorage.removeItem("leafbridge_cart_data");

              //GTM view_item push to data layer - START
              var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
              var leafbridge_recent_location = JSON.parse(localStorage.getItem("leafbridge_recent_location"));

              var order_details = json.data.order_details[0];

              if (order_details != null) {
                json.moreDataLayerPush = {
                  order_details: order_details,
                  location_id: leafbridge_wizard_data.retailer_id,
                  location_name: leafbridge_recent_location.lb_retailer_custom_name,
                  menu_type: leafbridge_wizard_data.menu_type,
                };
                gtm_push_items_data(json, "LB_Purchase");
              }
              //GTM view_item push to data layer - END
            })
            .catch(function (err) {
              console.error("ajax get order details err", err);
              $("#leafbridge_order_details_wrapper").html(err);
              $("#loading_animation").hide();
            });
        }
        else {
          // check and prepare cart items on page load
          check_and_prepare_cart_items();
        }

        //product single page load prodct data
        single_prod_page_fetch();

        //categories page show products
        show_category_products();

        // specials shortcode get specials data
        specials_page_get_specials_data();

        // specific product filter slider
        specific_product_filter_slider();

        // show special cards
        get_specials_menus_cards();

        // show selected retailer name and link to locations page
        show_retailer_name_bar();

        // force showing custom name instead of actual name of retailer
        if ($("div").hasClass("retailer_based_store")) {
          ajax_get_retailer_details($(".retailer_based_store").attr("retailer_id"))
            .then(function (json) {
              $(".retailer_based_store").attr("retailer_name", json.data.lb_retailer_custom_name);
            })
            .catch(function (json) {
              console.error("ajax_get_retailer_details", json);
            });
        } else {
          var leafbridge_wizard_data = JSON.parse(localStorage.getItem("leafbridge_wizard_data"));
          ajax_get_retailer_details(leafbridge_wizard_data.retailer_id)
            .then(function (json) {
              var leafbridge_recent_location = {
                page_id: "000000000",
                retailer_id: leafbridge_wizard_data.retailer_id,
                retailer_name: json.data.lb_retailer_custom_name,
                retailer_page: window.location.href,
              };
              localStorage.setItem("leafbridge_recent_location", JSON.stringify(leafbridge_recent_location));
            })
            .catch(function (json) {
              console.error("get retailer data  err", json);
            });
        }

        // breadcrumbs modifications
        breadcrumbs_modifications();
      })
      .catch(function (json) {
        console.error("check wizard attrs error", json);
      });
  }

  // run all required functions at page load
  $(window).on("load", function () {
    page_load_functions();
  });
})(jQuery);
