<?php
/*
   Plugin Name: Spoken Search
   Plugin URI: https://twicetwomedia.com/wordpress-plugins/
   Description: Give website visitors the option to search your site by voice in any browser, on any device.
   Version: 1.2
   Author: twicetwomedia
   Author URI: https://twicetwomedia.com
   Text Domain: ss2t
   License: GPLv3
  */

$ss2t_version     = '1.2';
$ss2t_name        = 'Spoken Search';
$ss2t_min_php_v   = '5.6';
$ss2t_file        = __FILE__;
$ss2t_basename    = plugin_basename( $ss2t_file );
$ss2t_path_base   = plugin_dir_path( $ss2t_file );
$ss2t_path_inc    = $ss2t_path_base . 'inc/';
$ss2t_path_parts  = $ss2t_path_inc . 'parts/';
$ss2t_path_assets = $ss2t_path_base . 'assets/';

defined( 'SS2T_VERSION' ) or define( 'SS2T_VERSION', $ss2t_version );
defined( 'SS2T_NAME' ) or define( 'SS2T_NAME', $ss2t_name );
defined( 'SS2T_PHPV' ) or define( 'SS2T_PHPV', $ss2t_min_php_v );
defined( 'SS2T_THE_API' ) or define( 'SS2T_THE_API', 'api.plnia.com' );
defined( 'SS2T_FILE' ) or define( 'SS2T_FILE', $ss2t_file );
defined( 'SS2T_BASENAME' ) or define( 'SS2T_BASENAME', $ss2t_basename );
defined( 'SS2T_PATH_BASE' ) or define( 'SS2T_PATH_BASE', $ss2t_path_base );
defined( 'SS2T_PATH_INC' ) or define( 'SS2T_PATH_INC', $ss2t_path_inc );
defined( 'SS2T_PATH_PARTS' ) or define( 'SS2T_PATH_PARTS', $ss2t_path_parts );
defined( 'SS2T_PATH_ASSETS' ) or define( 'SS2T_PATH_ASSETS', $ss2t_path_assets );

require_once( SS2T_PATH_PARTS . 'ss2t_version_check.php' );
if ( ss2t_php_ver_check() ) {
  require_once( SS2T_PATH_INC . 'ss2t_init.php' );
  ss2t_init(__FILE__);
}
