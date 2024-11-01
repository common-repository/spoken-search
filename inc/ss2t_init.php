<?php
if ( ! defined('ABSPATH') ) { exit; }
/**
 * init
**/

function ss2t_init($file) {

  require_once( SS2T_PATH_INC . 'ss2t_extras.php' );
  require_once( SS2T_PATH_INC . 'SS2T.php' );

  $ss2t_plugin = new SS2T();

  if ( ! $ss2t_plugin->isInstalled() ) {
    $ss2t_plugin->install();
  } else {
    $ss2t_plugin->upgrade();
  }

  register_activation_hook( SS2T_FILE, array(&$ss2t_plugin, 'activate') );
  register_deactivation_hook( SS2T_FILE, array(&$ss2t_plugin, 'deactivate') );

  $ss2t_plugin->addActionsAndFilters();
  $ss2t_plugin->ss2t_capability();
  
}
