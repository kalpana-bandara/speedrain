<?php
if ( ! current_user_can( 'manage_options' ) ) {
    return;
}

require_once LEAFBRIDGE_PATH . 'admin/includes/class-leafbridge-db.php';
require_once LEAFBRIDGE_PATH . 'admin/includes/class-leafbridge-compatibility.php';

//Get the active tab from the $_GET param
  $default_tab = null;
  $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

  ?>
  <!-- Our admin page content should all be inside .wrap -->
  <div class="wrap">
    <!-- Print the page title -->
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <!-- Here are our tabs -->
    <nav class="nav-tab-wrapper">
      <a href="?page=leafbridge-documentation" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Plugin Setup</a>
      <a href="?page=leafbridge-documentation&tab=shortcode" class="nav-tab <?php if($tab==='shortcode'):?>nav-tab-active<?php endif; ?>">Shortcodes</a>
	  <a href="?page=leafbridge-documentation&tab=classes" class="nav-tab <?php if($tab==='classes'):?>nav-tab-active<?php endif; ?>">CSS Helper Classes</a>
      <a href="?page=leafbridge-documentation&tab=support" class="nav-tab <?php if($tab==='support'):?>nav-tab-active<?php endif; ?>">Support</a>
    </nav>

    <div class="tab-content"> 
    <?php switch($tab) :
      case 'support':
        include plugin_dir_path( dirname( __FILE__ ) ) . 'partials/leafbridge-plugin-support.php';
        break;
      case 'shortcode':
        include plugin_dir_path( dirname( __FILE__ ) ) . 'partials/leafbridge-plugin-shortcodes.php';
        break;
	  case 'classes':
        include plugin_dir_path( dirname( __FILE__ ) ) . 'partials/leafbridge-doc-classes.php';
        break;	
      default:
        include plugin_dir_path( dirname( __FILE__ ) ) . 'partials/leafbridge-plugin-setup.php';
        break;
    endswitch; ?>
    </div>
  </div>
  <?php
 

?>