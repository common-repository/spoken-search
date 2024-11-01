<?php
if ( ! defined('ABSPATH') ) { exit; }
/**
 * version check
**/

if ( ! function_exists( 'ss2t_php_ver_wrong' ) ) {

  function ss2t_php_ver_wrong() {
    echo '<div class="updated fade">' .
      __('Error: The plugin "' . SS2T_NAME . '" requires a newer version of PHP.',  'ss2t').
            '<br/>' . __('Minimum version of PHP required: ', 'ss2t') . '<strong>' . SS2T_PHPV . '</strong>' .
            '<br/>' . __('Your server\'s PHP version: ', 'ss2t') . '<strong>' . phpversion() . '</strong>' .
         '</div>';
  }

}

if ( ! function_exists( 'ss2t_php_ver_check' ) ) {

  function ss2t_php_ver_check() {
    if ( version_compare(phpversion(), SS2T_PHPV) < 0 ) {
      add_action('admin_notices', 'ss2t_php_ver_wrong');
      return false;
    }
    return true;
  }

}
