(function ($) {
  "use strict";

  /**
   * All of the code for admin-facing JavaScript source
   * should reside in this file.
   */
})(jQuery);

var current = 0;
var ajxdata = "";
let ajaxes = [];

jQuery(function () {
  //special names display toggle
  /*var specials = jQuery("#lb-sc-Special");
  var special_names_block = jQuery(".lf-special-names");

  specials.change(function () {
    if (specials.val() == "true") {
      special_names_block.show();
    } else {
      special_names_block.hide();
    }
  });*/
  jQuery(".lf-admin-col-3 select").select2();
  jQuery(".leafbridge-settings-row td select").select2();

  jQuery("form#leafbridge-config-ui-css-editor textarea[data-editor]").each(function () {
    var textarea = jQuery(this);
    var mode = textarea.data("editor");
    var editDiv = jQuery("<div>", {
      position: "absolute",
      width: textarea.width(),
      height: textarea.height(),
      class: textarea.attr("class"),
    }).insertBefore(textarea);
    textarea.css("display", "none");
    var editor = ace.edit(editDiv[0]);
    editor.renderer.setShowGutter(textarea.data("gutter"));
    editor.getSession().setValue(textarea.val());
    editor.getSession().setMode("ace/mode/" + mode);
    //editor.setTheme("ace/theme/xcode");
    editor.renderer.setOption("showLineNumbers", true);
    editor.setOptions({
      fontSize: 14,
      tabSize: 2,
      wrap: true,
      enableBasicAutocompletion: true,
      enableSnippets: true,
      enableLiveAutocompletion: true,
      showGutter: true,
      showPrintMargin: false,
    });

    // copy back to textarea on form submit...
    textarea.closest("form").submit(function () {
      textarea.val(editor.getSession().getValue());
    });
  });

  jQuery(".lb-sync-log").click(function () {
    if (jQuery(this).is(":checked")) {
      jQuery(".leafbridge-settings-row.lf-d-none").fadeIn(300);
      lf_validate_log();
    } else {
      jQuery(".leafbridge-settings-row.lf-d-none").fadeOut(200);
      lf_validate_log();
    }
  });

  //*******************************************
  var default_store = jQuery(".leafbridge-settings-default-store").find(":selected").val();
  //jQuery(".leafbridge-settings-default-store select").val(default_store).change();

  jQuery(".leafbridge-settings-retailer-key").change(function () {
    jQuery("#leafbridge-settings-default-store").html("");
    jQuery(".leafbridge-settings-default-menu-type, .leafbridge-settings-default-order-type").html("");
    jQuery(".leafbridge-settings-retailer-key").each(function () {
      if (this.checked) {
        var selectedRetailer = jQuery(this).val();
        jQuery(".leafbridge-settings-default-menu-type, .leafbridge-settings-default-order-type").html("");
        var rt = jQuery("#lb-settings-retailer_" + selectedRetailer)
          .find(".lb-card__body-header-title")
          .text();
        jQuery("#leafbridge-settings-default-store").append(
          jQuery("<option>", {
            value: selectedRetailer,
            text: rt,
          })
        );
        lb_set_menuType(jQuery(this));
      }
    });
  });

  // ============ MENU TYPE ==================================================================================================
  var selectedRetailer = jQuery("#leafbridge-settings-default-store").last().val();
  jQuery("#lb-settings-retailer_" + selectedRetailer)
    .find(".lb-card-details span")
    .each(function () {
      var optionname = jQuery(this).text();
      jQuery(".leafbridge-settings-default-menu-type").append(
        jQuery("<option>", {
          value: optionname,
          text: optionname,
        })
      );
      //jQuery('.leafbridge-settings-default-menu-type').prepend('<option value="0" selected="selected">-Select-</option>');
    });

  // ============= ORDER TYPE ===================================================================================================
  jQuery("#lb-settings-retailer_" + selectedRetailer)
    .find(".lb-retailer-order-type-hidden span")
    .each(function () {
      var optionname = jQuery(this).text();
      var optionval = jQuery(this).attr("data-val");
      jQuery(".leafbridge-settings-default-order-type").append(
        jQuery("<option>", {
          value: optionval,
          text: optionname,
        })
      );
      //jQuery('.leafbridge-settings-default-order-type').prepend('<option value="0" selected="selected">-Select-</option>');
    });
  // ============= MENU TYPE ===============================================================================================

  // ========================================================================================================================
  if (jQuery("#leafbridge-settings-wizard-type").val() == "link") {
    jQuery("#leafbridge-settings-wizard-type-link-element-row").toggle();
  } else {
    jQuery("#leafbridge-settings-wizard-type-link-element-row").fadeOut();
  }
  //   ================================================================================================

  jQuery(".leafbridge-settings-retailer-key").each(function () {
    if (this.checked) {
      var selectedRetailer = jQuery(this).val();
      jQuery(".leafbridge-settings-default-menu-type, .leafbridge-settings-default-order-type").html("");
      var rt = jQuery("#lb-settings-retailer_" + selectedRetailer)
        .find(".lb-card__body-header-title")
        .text();
      lb_set_menuType(jQuery(this));
    }
  });

  lb_set_menuType2();

  // show wizard modal class boxes
  if (jQuery("#leafbridge-settings-wizard-type").val() == "link") {
    jQuery("#leafbridge-settings-wizard-type-link-element-row").show();
    jQuery("#leafbridge-settings-wizard-type-modal-element-row").hide();
    jQuery("#leafbridge-floating-cart-position-row").hide();
  } else {
    jQuery("#leafbridge-settings-wizard-type-link-element-row").hide();
    jQuery("#leafbridge-settings-wizard-type-modal-element-row").show();
    jQuery("#leafbridge-floating-cart-position-row").show();
  }
});

//==========================================================================================================

function lf_uploadSelectedFile(obj) {
  var custom_uploader;

  var btnObj = jQuery(obj);

  //If the uploader object has already been created, reopen the dialog
  if (custom_uploader) {
    custom_uploader.open();
    return;
  }

  //Extend the wp.media object
  custom_uploader = wp.media.frames.file_frame = wp.media({
    title: "Upload Image",
    button: {
      text: "Upload Image",
    },
    //library: { type: 'image/jpg' },
    multiple: false,
  });

  //When a file is selected, grab the URL and set it as the text field's value
  custom_uploader.on("select", function () {
    attachment = custom_uploader.state().get("selection").first().toJSON();

    jQuery("#lf_category_upload_image").val(attachment.url);
    jQuery(".lf_category_image_prev").attr("src", attachment.url);
  });

  //Open the uploader dialog
  custom_uploader.open();
}
//================================== Quick sync retailer =========================================
function leafbridge_retailer_quick_sync(e, element) {
  e.preventDefault();
  var retailerId = jQuery(element).parent().parent().find(".lb-card__body-cover-checkbox .lb-form-control input").val();
  var retailerName = jQuery(element).parent().parent().find("h2.lb-card__body-header-title").text();

  if (confirm(`Are you sure want to sync ${retailerName}`)) {
    var data = {
      action: "leafbridge_retailer_quick_sync",
      nonce_ajax: ajaxurl,
      form_data: {
        lfstatus: "TRUE",
        retailerId: retailerId,
      },
      nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
    };
    jQuery.ajax({
      url: ajaxurl,
      dataType: "json",
      data: data,
      type: "POST",
      proccessData: false,
      beforeSend: function () {
        jQuery("#leafbridge-settings-modal .leafbridge-settings-modal-warpper h3").html("Syncing inprogress. Please wait!");
        jQuery("#leafbridge-settings-modal-close-btn").hide();
        jQuery("#leafbridge-settings-modal").fadeIn();
        jQuery(".lf-admin-loading-gif").show();
        jQuery(".leafbridge-settings-modal-warpper ul li#lf-admin-modal-li-retailer").fadeIn();
        var modalcontents = `<li id="lf-admin-modal-li-retailer">Syncing retailer details and products of ${retailerName}.</li>`;
        jQuery("#leafbridge-settings-modal ul").html(modalcontents);
      },
      success: function (serverResponse) {
        jQuery(".leafbridge-settings-modal-warpper ul").append(`<li id="lf-admin-modal-li-store-0">Successfully synchronized <strong><span></span></strong> products of ${retailerName}. </li>`).fadeIn();
      },
      complete: function () {
        run_stat(retailerId);
        jQuery(".lf-admin-loading-gif").hide();
        jQuery("#leafbridge-settings-modal-close-btn").show();
      },
    });
  }
}
//================================== Remove retailer ========================================================
function leafbridge_remove_retailer(element) {
  if (confirm("Are you sure to delete this retailer ?")) {
    var retailer = jQuery(element);
    var retailerId = retailer.parent().find(".lb-card__body-cover-checkbox .lb-form-control input").val();
    var data = {
      action: "leafbridge_remove_retailer",
      nonce_ajax: ajaxurl,
      form_data: {
        lfstatus: "TRUE",
        retailerId: retailerId,
      },
      nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
    };
    jQuery.ajax({
      url: ajaxurl,
      dataType: "json",
      data: data,
      type: "POST",
      proccessData: false,
      success: function (serverResponse) {
        retailer.parent().find(".lb-card__body-cover-checkbox .lb-form-control input").prop("checked", false);
        retailer.parent().find(".lb-card__body-cover-checkbox .lb-form-control input").removeClass("selected");
        retailer.find("svg").css("display", "none");
        alert(serverResponse.data);
      },
    });
  }
}
//==================================Setup store for new retailers==========================================================================================

function leafbridge_new_setup_store() {
  //var allRetailerids = checkedRetailerIds.concat(uncheckedRetailerIds);

  var retailersArr = jQuery('input[name="leafbridge-settings-retailer-key[]"]:checked')
    .not('input[name="leafbridge-settings-retailer-key[]"].selected')
    .map(function () {
      return jQuery(this).val();
    })
    .get();
  if (retailersArr.length <= 0) {
    alert("Please select at least one new retailer to be synced");
  } else {
    var data = {
      action: "leafbridge_admin_setup_retailers",
      nonce_ajax: ajaxurl,
      form_data: {
        lfstatus: "TRUE",
        retailers: retailersArr,
      },
      nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
    };

    // ***********   1. SAVING RETAILERS    *****************
    jQuery.ajax({
      url: ajaxurl,
      dataType: "json",
      data: data,
      type: "POST",
      proccessData: false,
      beforeSend: function () {
        jQuery(".lf-admin-loading-gif").fadeIn();
        jQuery("#leafbridge-settings-modal").fadeIn();
        jQuery("#leafbridge-settings-modal-close-btn").hide();
        jQuery("#leafbridge-settings-modal .leafbridge-settings-modal-warpper h3").html("Preparing your store. Please wait!");
        jQuery(".leafbridge-settings-modal-warpper ul li#lf-admin-modal-li-retailer").fadeIn();
        var modalcontents = '<li id="lf-admin-modal-li-retailer" style="display:none">Saving retailers...</li><li id="lf-admin-modal-li-category" style="display:none">Saving categories...</li><li class="lf-admin-modal-li-product" style="display:none"></li>';
        jQuery("#leafbridge-settings-modal ul").html(modalcontents);

        var ask = true;
        window.onbeforeunload = function (e) {
          if (!ask) return null;
          e = e || window.event;
          //old browsers
          if (e) {
            e.returnValue = "Are you sure?";
          }
          //safari, chrome(chrome ignores text)
          return "Are you sure?";
        };
      },
      success: function (serverResponse) {
        jQuery(".leafbridge-settings-modal-warpper ul li#lf-admin-modal-li-retailer").html(serverResponse.data.retailers_status).fadeIn();
        var data = {
          action: "leafbridge_admin_setup_categories",
          nonce_ajax: ajaxurl,
          form_data: { lfstatus: "TRUE" },
          nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
        };

        // *************   2. SAVING CATEGORIES    **********
        jQuery.ajax({
          url: ajaxurl,
          dataType: "json",
          data: data,
          type: "POST",
          proccessData: false,
          beforeSend: function (serverResponse) {
            jQuery(".leafbridge-settings-modal-warpper ul li#lf-admin-modal-li-category").fadeIn();
          },
          success: function (serverResponse) {
            jQuery(".leafbridge-settings-modal-warpper ul li#lf-admin-modal-li-category").html(serverResponse.data.categories_status).fadeIn();
            //let allRetailers = jQuery.merge(checkedRetailers, uncheckedRetailers);
            ajaxes = jQuery('input[name="leafbridge-settings-retailer-key[]"]:checked')
              .not('input[name="leafbridge-settings-retailer-key[]"].selected')
              .map(function () {
                var rname = jQuery(this).parent().parent().parent().find("h2.lb-card__body-header-title").text();
                var rid = jQuery(this).val();
                return { retailer_name: rname, retailer_id: rid };
              })
              .get();
            // *************   3. SAVING STORE'S PRODUCTS    *****************
            leafbridge_setup_store_process(ajaxes);
          },
          error: function (json) {},
          complete: function () {},
        });
      },
      error: function (json) {},
      complete: function () {},
    });
  }
}

//================================setup all retailers============================================================================================

function leafbridge_setup_store() {
  var retailersArr = jQuery('input[name="leafbridge-settings-retailer-key[]"]:checked')
    .map(function () {
      return jQuery(this).val();
    })
    .get();

  //var default_store = jQuery('#leafbridge-settings-default-store').find(":selected").val();
  //var default_menu_type = jQuery('#leafbridge-settings-default-menu-type').find(":selected").val();
  //var default_order_type = jQuery('#leafbridge-settings-default-order-type').find(":selected").val();

  var data = {
    action: "leafbridge_admin_setup_retailers",
    nonce_ajax: ajaxurl,
    form_data: {
      lfstatus: "TRUE",
      retailers: retailersArr,
      //default_store: default_store,
      //default_menu_type: default_menu_type,
      //default_order_type: default_order_type
    },
    nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
  };

  //console.log(data);

  // ****************************   1. SAVING RETAILERS    ***********************************
  jQuery.ajax({
    url: ajaxurl,
    dataType: "json",
    data: data,
    type: "POST",
    proccessData: false,
    beforeSend: function () {
      jQuery(".lf-admin-loading-gif").fadeIn();
      jQuery("#leafbridge-settings-modal").fadeIn();
      jQuery("#leafbridge-settings-modal-close-btn").hide();
      jQuery("#leafbridge-settings-modal .leafbridge-settings-modal-warpper h3").html("Preparing your store. Please wait!");
      jQuery(".leafbridge-settings-modal-warpper ul li#lf-admin-modal-li-retailer").fadeIn();
      var modalcontents = '<li id="lf-admin-modal-li-retailer" style="display:none">Saving retailers...</li><li id="lf-admin-modal-li-category" style="display:none">Saving categories...</li><li class="lf-admin-modal-li-product" style="display:none"></li>';
      jQuery("#leafbridge-settings-modal ul").html(modalcontents);

      var ask = true;
      window.onbeforeunload = function (e) {
        if (!ask) return null;
        e = e || window.event;
        //old browsers
        if (e) {
          e.returnValue = "Are you sure?";
        }
        //safari, chrome(chrome ignores text)
        return "Are you sure?";
      };
    },
    success: function (serverResponse) {
      jQuery(".leafbridge-settings-modal-warpper ul li#lf-admin-modal-li-retailer").html(serverResponse.data.retailers_status).fadeIn();
      var data = {
        action: "leafbridge_admin_setup_categories",
        nonce_ajax: ajaxurl,
        form_data: { lfstatus: "TRUE" },
        nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
      };

      // ****************************   2. SAVING CATEGORIES    ***********************************
      jQuery.ajax({
        url: ajaxurl,
        dataType: "json",
        data: data,
        type: "POST",
        proccessData: false,
        beforeSend: function (serverResponse) {
          jQuery(".leafbridge-settings-modal-warpper ul li#lf-admin-modal-li-category").fadeIn();
        },
        success: function (serverResponse) {
          jQuery(".leafbridge-settings-modal-warpper ul li#lf-admin-modal-li-category").html(serverResponse.data.categories_status).fadeIn();
          ajaxes = jQuery('input[name="leafbridge-settings-retailer-key[]"]:checked')
            .map(function () {
              var rname = jQuery(this).parent().parent().parent().find("h2.lb-card__body-header-title").text();
              var rid = jQuery(this).val();
              return { retailer_name: rname, retailer_id: rid };
            })
            .get();

          // ****************************   3. SAVING STORE'S PRODUCTS    ***********************************
          leafbridge_setup_store_process(ajaxes);
        },
        error: function (json) {},
        complete: function () {},
      });
    },
    error: function (json) {},
    complete: function () {},
  });
}

// ****************************   GET PRODUCT COUNTS    ***********************************
function run_stat(retailer_id) {
  var data_stat = {
    action: "leafbridge_admin_product_stat",
    nonce_ajax: ajaxurl,
    form_data: { retailer_id: retailer_id },
    nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
  };
  jQuery.ajax({
    url: ajaxurl,
    dataType: "json",
    data: data_stat,
    type: "POST",
    proccessData: false,
    beforeSend: function () {
      //jQuery('#lf-admin-modal-li-store-'+current).append(serverResponse.data.product_stat);
    },
    success: function (serverResponse) {
      jQuery("#lf-admin-modal-li-store-" + current + " span").html(serverResponse.data.product_stat);
    },
    error: function (json) {
      //alert(json);
    },
    complete: function () {},
  });
}

// ****************************   STOP TIMEOUT AJAX FOR PRODUCT COUNT   ***********************************
function stopTimer(timer) {
  clearInterval(timer);
}

var ajax_timer = "";

// ****************************   DYNAMIC AJAX LOOP FUNCTION  FOR PRODUCTS  ***********************************
function leafbridge_setup_store_process(ajaxes) {
  //check to make sure there are more requests to make
  if (current < ajaxes.length) {
    var data = {
      action: "leafbridge_admin_setup_store",
      nonce_ajax: ajaxurl,
      form_data: { retailer_id: ajaxes[current].retailer_id, retailer_name: ajaxes[current].retailer_name },
      nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
    };

    //make the AJAX request with the given info from the array of objects
    jQuery.ajax({
      url: ajaxurl,
      dataType: "json",
      data: data,
      type: "POST",
      proccessData: false,
      beforeSend: function () {
        jQuery(".leafbridge-settings-modal-warpper ul")
          .append('<li id="lf-admin-modal-li-store-' + current + '">Saving <strong><span></span></strong> products of ' + ajaxes[current].retailer_name + "...</li>")
          .fadeIn();

        // ****************************   GET PRODUCT COUNT STEP - 1 ***********************************

        var data_stat = {
          action: "leafbridge_admin_product_stat",
          nonce_ajax: ajaxurl,
          form_data: { retailer_id: ajaxes[current].retailer_id },
          nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
        };
        jQuery.ajax({
          url: ajaxurl,
          dataType: "json",
          data: data_stat,
          type: "POST",
          proccessData: false,
          xhr: function () {
            var xhr = new window.XMLHttpRequest();
            //Download progress
            xhr.addEventListener(
              "progress",
              function (evt) {
                ajax_timer = setInterval(function () {
                  run_stat(ajaxes[current].retailer_id); // LOOP
                }, 4000);
                //stopTimer(ajax_timer);
              },
              false
            );
            return xhr;
          },
          beforeSend: function () {
            //jQuery('#lf-admin-modal-li-store-'+current).append(serverResponse.data.product_stat);
          },
          success: function (serverResponse) {
            jQuery("#lf-admin-modal-li-store-" + current + " span").html("<strong>" + serverResponse.data.product_stat + "</strong>");
          },
          error: function (json) {
            //alert(json);
          },
          complete: function () {},
        });

        //--------------------------------
      },
      success: function (serverResponse) {
        jQuery(".leafbridge-settings-modal-warpper ul li#lf-admin-modal-li-store-" + current).html(serverResponse.data.product_status);
      },
      error: function (json) {
        //alert(json);
      },
      complete: function () {
        stopTimer(ajax_timer); // <<<<<< Stop Timer
        current++;
        leafbridge_setup_store_process(ajaxes);

        if (ajaxes.length == current) {
          current = 0;
          //ajaxes = '';

          // ****************************  DELETE ALL DRAFT PRODUCTS ***********************************
          var data_final = {
            action: "leafbridge_admin_optimizing_store",
            nonce_ajax: ajaxurl,
            form_data: { store_status: 1 },
            nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
          };
          jQuery.ajax({
            url: ajaxurl,
            dataType: "json",
            data: data_final,
            type: "POST",
            proccessData: false,
            beforeSend: function () {
              //jQuery('.leafbridge-settings-modal-warpper ul').append('<li id="lf-admin-modal-li-store-'+current+'">Saving products of '+ ajaxes[current].retailer_name+'...</li>').fadeIn();
            },
            success: function (serverResponse) {
              //jQuery('.leafbridge-settings-modal-warpper ul li#lf-admin-modal-li-store-'+current).html(serverResponse.data.product_status);
            },
            error: function (json) {},
            complete: function () {
              // ****************************  CREATE STORE PAGES ***********************************
              var data_final = {
                action: "leafbridge_admin_setup_pages",
                nonce_ajax: ajaxurl,
                form_data: { store_status: 1 },
                nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
              };
              jQuery.ajax({
                url: ajaxurl,
                dataType: "json",
                data: data_final,
                type: "POST",
                proccessData: false,
                beforeSend: function () {
                  //jQuery('.leafbridge-settings-modal-warpper ul').append('<li id="lf-admin-modal-li-store-'+current+'">Saving products of '+ ajaxes[current].retailer_name+'...</li>').fadeIn();
                },
                success: function (serverResponse) {
                  //jQuery('.leafbridge-settings-modal-warpper ul li').last().after('<li>'+serverResponse.data.pages_status+'<li>');
                  jQuery(".leafbridge-settings-modal-warpper ul li").last().after('<li>Store synchronization  successfully completed. <i class="dashicons dashicons-saved"></i><li>');
                },
                error: function (json) {},
                complete: function () {
                  jQuery(".lf-admin-loading-gif").fadeOut();
                  jQuery(".leafbridge-settings-modal-warpper h3").html("Setup completion successful!");
                  jQuery(".leafbridge-settings-modal-warpper h3").after('<b style="color: #088608;margin-bottom: 10px;">Please wait until reloading your settings. After reload, Go to "Default Store Options" and save your default store settings.</b>');
                  jQuery("#leafbridge-settings-modal-close-btn").fadeIn();
                  window.onbeforeunload = null;
                  setTimeout(function () {
                    window.location.reload();
                  }, 5000);
                },
              });
            },
          });

          //====================================
        }
        //note that the "success" callback will fire
        //before the "complete" callback
      },
    });
  }
}

// ========================================================================================================================
function lb_close_modal() {
  jQuery("#leafbridge-settings-modal").fadeOut();
}

// ========================================================================================================================
function lf_validate_log() {
  var emailadd = jQuery("#lb-sync-log-email").val().trim();
  if (jQuery(".lb-sync-log").is(":checked")) {
    lf_validate_email(emailadd);
    //jQuery('#lf-submit-ui-log').attr('disabled',true);
  } else {
    //jQuery('#lf-submit-ui-log').attr('disabled',false);
  }
}

// ========================================================================================================================
function lf_validate_email(email) {
  var emailadd = email.trim();
  if (!isEmail(emailadd)) {
    jQuery(".lf-error-email-required").html("Valid email address is required.").css("visibility", "visible");
    jQuery("#lf-submit-ui-log").attr("disabled", true);
  } else {
    jQuery(".lf-error-email-required").css("visibility", "hidden");
    jQuery("#lf-submit-ui-log").attr("disabled", false);
  }
}

// ========================================================================================================================
function isEmail(email) {
  var pattern = new RegExp(
    /^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i
  );
  return pattern.test(email);
}

// ========================================================================================================================
function lb_set_menuType(obj) {
  var selectedRetailer = jQuery(obj).val();
  //alert(selectedRetailer);

  jQuery(".leafbridge-settings-default-menu-type, .leafbridge-settings-default-order-type").html("");

  jQuery("#lb-settings-retailer_" + selectedRetailer)
    .find(".lb-card-details span")
    .each(function () {
      var optionname = jQuery(this).text();

      jQuery(".leafbridge-settings-default-menu-type").append(
        jQuery("<option>", {
          value: optionname,
          text: optionname,
        })
      );
    });

  jQuery("#lb-settings-retailer_" + selectedRetailer)
    .find(".lb-retailer-order-type-hidden span")
    .each(function () {
      var optionname = jQuery(this).text();
      var optionval = jQuery(this).attr("data-val");
      jQuery(".leafbridge-settings-default-order-type").append(
        jQuery("<option>", {
          value: optionval,
          text: optionname,
        })
      );
      //alert(optionname);
    });
}

function lb_set_menuType2() {
  var selectedRetailer = jQuery("#leafbridge-settings-default-store").find(":selected").val();
  //alert(selectedRetailer);

  jQuery(".leafbridge-settings-default-menu-type, .leafbridge-settings-default-order-type").html("");

  jQuery("#lb-settings-retailer_" + selectedRetailer)
    .find(".lb-card-details span")
    .each(function () {
      var optionname = jQuery(this).text();

      jQuery(".leafbridge-settings-default-menu-type").append(
        jQuery("<option>", {
          value: optionname,
          text: optionname,
        })
      );
    });

  jQuery("#lb-settings-retailer_" + selectedRetailer)
    .find(".lb-retailer-order-type-hidden span")
    .each(function () {
      var optionname = jQuery(this).text();
      var optionval = jQuery(this).attr("data-val");
      jQuery(".leafbridge-settings-default-order-type").append(
        jQuery("<option>", {
          value: optionval,
          text: optionname,
        })
      );
    });

  var setmenu = jQuery("#lb-hdn-default-menu-type").val();
  jQuery("#leafbridge-settings-default-menu-type").val(setmenu).change();

  var setorder = jQuery("#lb-hdn-default-order-type").val();
  jQuery("#leafbridge-settings-default-order-type").val(setorder).change();
}

// ========================================================================================================================
function lb_load_menuType(obj) {
  //alert(jQuery(obj).val());
  if (jQuery(obj).val() == "link") {
    jQuery("#leafbridge-settings-wizard-type-link-element-row").show().focus();
    jQuery("#leafbridge-floating-cart-position-row").hide();
    jQuery("#leafbridge-settings-wizard-type-modal-element-row").hide();
  } else {
    jQuery("#leafbridge-settings-wizard-type-link-element-row").hide();
    jQuery("#leafbridge-settings-wizard-type-modal-element-row").show().focus();
    jQuery("#leafbridge-floating-cart-position-row").show();
  }
}

function lb_show_info_modal() {
  jQuery(".lf-info-modal-wrap").fadeIn();
}
function lb_info_close_modal() {
  jQuery(".lf-info-modal-wrap").fadeOut();
}

//*************************************************************************

function lb_generate_shortcode() {
  var special_id = jQuery("#lb-sc-special-name").val();
  var custom_menu_name = jQuery("#lb-custom-menu-name").val();
  var custom_section = jQuery("#lb-sc-custom-section").val();
  var cat = jQuery("#lb-sc-category").val();
  var sub_cat = jQuery("#lb-sc-sub-category").val();
  var brand = jQuery("#lb-sc-Brand").val();
  var effect = jQuery("#lb-sc-Effect").val();
  var strain = jQuery("#lb-sc-Strain-Type").val();
  var special = jQuery("#lb-sc-Special").val();
  var sort = jQuery("#lb-sc-Sort").val();
  var products = jQuery("#lb-sc-Products").val();
  var autoplay = jQuery("#lb-autoplay").val();
  var special_names_block = jQuery(".lf-special-names");
  var custom_menu_name_block = jQuery(".lb-custom-menu-name");
  var staff_picks = jQuery("#lb-sc-staff_picks").val();

  var shortcodeStr = "[leafbridge_specific_product_filter";
  jQuery(".lf-admin-shortcode-output code").html("");

  if (cat != "") {
    shortcodeStr += ' category="' + cat + '"';
    jQuery("#lb-sc-sub-category optgroup").show();
    jQuery("#lb-sc-sub-category optgroup:not([label='" + cat + "'])").hide();
    //var x = jQuery("#lb-sc-sub-category option:selected").closest('optgroup').prop('label');
    // console.log(x);
    shortcodeStr += "";
  }
  if (sub_cat != "") {
    shortcodeStr += ' sub_category="' + sub_cat + '"';
  } else {
    shortcodeStr += "";
  }
  if (brand != "") {
    shortcodeStr += ' brand="' + brand + '"';
  }
  if (effect != "") {
    shortcodeStr += ' effect="' + effect + '"';
  }
  if (strain != "") {
    shortcodeStr += ' strain_type="' + strain + '"';
  }
  if (sort != "") {
    shortcodeStr += ' sort="' + sort + '"';
  }
  if (products != "" || products > 0) {
    shortcodeStr += ' count="' + products + '"';
  }
  if (autoplay != "" || autoplay > 0) {
    shortcodeStr += ' autoplay="' + autoplay + '"';
  }
  if (special == "true" && custom_section == "") {
    if (special_id != "" || special_id > 0) {
      shortcodeStr += ' special_id="' + special_id + '"';
    }
    if (special != "") {
      shortcodeStr += ' special="' + special + '"';
    }
    special_names_block.show();
  } else {
    special_names_block.hide();
  }

  if (special == "" && custom_section == "true") {
    if (custom_section != "") {
      shortcodeStr += ' custom_selection="' + custom_section + '"';
    }

    if (custom_menu_name != "") {
      shortcodeStr += ' custom_menu_name="' + custom_menu_name + '"';
    }
    custom_menu_name_block.show();
  } else {
    custom_menu_name_block.hide();
  }
  if (special == "true" && custom_section == "true") {
    alert("You cannot select both custom and specials. Please select only one");
    if (special != "" && custom_section != "") {
      jQuery("#lb-sc-Special").val("").trigger("change");
      jQuery("#lb-custom-menu-name").val("").trigger("change");
      jQuery("#lb-sc-special-name").val("").trigger("change");
      jQuery("#lb-sc-custom-section").val("").trigger("change");
      jQuery(".lf-admin-shortcode-output code")
        .html()
        .replace(/\s?special="true"/, "");
      jQuery(".lf-admin-shortcode-output code")
        .html()
        .replace(/\s?custom_selection="true"/, "");
    }
  }
  if (staff_picks != "") {
    shortcodeStr += ' staff_picks="' + staff_picks + '"';
  }

  shortcodeStr += "]";

  //var shortcode = '[leafbridge_specific_product_filter category="'+cat+'" brand="'+brand+'" effect="'+effect+'" strain_type="'+strain+'" special="'+special+'" sort="'+sort+'" count="'+products+'"]';
  jQuery(".lf-admin-shortcode-output code").html(shortcodeStr);
  jQuery(".lf-admin-shortcode-output span").fadeIn();
}

function resetSubCat() {
  jQuery("#lb-sc-sub-category").val("");
  jQuery(".lf-admin-shortcode-output code").html("");
  lb_generate_shortcode();
}

function lb_shortcode_copyText(event) {
  //console.log("hi");
  let div = document.getElementById("div");
  let text = jQuery(".lf-admin-shortcode-output code").text();
  let textArea = document.createElement("textarea");
  textArea.width = "1px";
  textArea.height = "1px";
  textArea.background = "transparents";
  textArea.value = text;
  document.body.append(textArea);
  textArea.select();
  document.execCommand("copy"); //No i18n
  document.body.removeChild(textArea);
  alert("Shortcode copied to clipboard!");
}
function lb_retailer_details_shortcode() {
  var retailer_id = jQuery("#lb-sc-retailer");

  var shortcodeStr = "";

  if (retailer_id != "") {
    shortcodeStr += '[leafbridge-retailer-details retailer_id="' + retailer_id.val() + '"]';
  }

  jQuery(".lf-admin-retailer-details-shortcode-output code").html(shortcodeStr);
  jQuery(".lf-admin-retailer-details-shortcode-output span").fadeIn();
}
function lb_retailer_details_shortcode_copyText(event) {
  let div = document.getElementById("div");
  let text = jQuery(".lf-admin-retailer-details-shortcode-output code").text();
  let textArea = document.createElement("textarea");
  textArea.width = "1px";
  textArea.height = "1px";
  textArea.background = "transparents";
  textArea.value = text;
  document.body.append(textArea);
  textArea.select();
  document.execCommand("copy"); //No i18n
  document.body.removeChild(textArea);
  alert("Shortcode copied to clipboard!");
}

function lb_retailer_shortcode() {
  var retailer_id = jQuery("#lb-sc-retailers");
  var retailer_name = jQuery("#lb-sc-retailers option:selected").text();
  var isFilter = jQuery("#lb-sc-filter").val();
  var menuType = jQuery("#lb-sc-menu-type").val();
  var orderType = jQuery("#lb-sc-order-type").val();
  var forceType = jQuery("#lb-sc-force-type").val();
  var showProducts = jQuery("#lb-sc-show-products").val();

  var shortcodeStr = "[retailer_based_store";

  if (retailer_id != "") {
    shortcodeStr += ' retailer_id="' + retailer_id.val() + '" retailer_name="' + retailer_name + '"';
  }
  if (menuType != "") {
    shortcodeStr += '  menu_type="' + menuType + '"';
  }
  if (isFilter != "") {
    shortcodeStr += '  show_filter="' + isFilter + '"';
  }
  if (orderType != "") {
    shortcodeStr += '  order_type="' + orderType + '"';
  }
  if (forceType != "") {
    shortcodeStr += '  force_wizard="' + forceType + '"';
  }
  if (showProducts != "") {
    shortcodeStr += '  show_products="' + showProducts + '"';
  }

  shortcodeStr += "]";

  jQuery(".lf-admin-retailer-shortcode-output code").html(shortcodeStr);
  jQuery(".lf-admin-retailer-shortcode-output span").fadeIn();
}

function lb_retailer_shortcode_copyText(event) {
  let div = document.getElementById("div");
  let text = jQuery(".lf-admin-retailer-shortcode-output code").text();
  let textArea = document.createElement("textarea");
  textArea.width = "1px";
  textArea.height = "1px";
  textArea.background = "transparents";
  textArea.value = text;
  document.body.append(textArea);
  textArea.select();
  document.execCommand("copy"); //No i18n
  document.body.removeChild(textArea);
  alert("Shortcode copied to clipboard!");
}

setTimeout(() => {
  jQuery(".lb-settings-retailer-submit").click(function (e) {
    e.preventDefault();

    var obj = {};
    var customName = jQuery(this).prev(".leafbridge-settings-retailer-custom-name").val();
    var id = jQuery(this).parent().parent().parent().find("input[type='checkbox']").val();
    obj[id] = customName;

    jQuery.ajax({
      type: "POST",
      url: ajaxurl,
      data: {
        action: "leafbridge_admin_custom_name",
        nonce_ajax: ajaxurl,
        my_data: {
          customNames: obj,
        },
        nonce_ajax: jQuery("#leafbridge-admin-ajax-nonce").val(),
      },
      complete: function () {
        alert("Retailer name has been updated.");
      },
    });
    return false;
  });
}, 2000);

function isSpecial() {
  if (jQuery("#lb-sc-Special").val() == "true") {
    jQuery(".lf-special-names").fadeIn();
  } else {
    jQuery(".lf-special-names").fadeOut();
  }
}
