<?php
if ( ! defined('ABSPATH') ) { exit; }
/**
 * shortcodes
**/

if ( ! function_exists( 'ss2t_shortcode' ) ) {

  add_shortcode( 'ss2t', 'ss2t_shortcode' );

  function ss2t_shortcode( $atts ) {
    $a = shortcode_atts( array(
      'style'   => '',
      'format'  => '',
      'size'    => '',
      'tooltip' => ''
    ), $atts );
    $style = $a['style'];
    $style = ( ! empty($style) ) ? strtolower($style) : 'light';
    $format = $a['format'];
    $format = ( ! empty($format) ) ? strtolower($format) : 'html';
    $size = $a['size'];
    $size = ( ! empty($size) ) ? strtolower($size) : 'medium';
    $tooltip = $a['tooltip'];
    $tooltip = ( ! empty($tooltip) ) ? strtolower($tooltip) : 'yes';
    $tooltip_display = ' data-tooltip="Voice Search"';
    if ('no' === $tooltip) {
      $tooltip_display = '';
    }

    $ss2t_display = '<!-- ss2t // browser not supported -->';

    $is__chrome = ss2t_chrome_check();
    $is__pro = ss2t_pro_check();

    if ( (true === $is__chrome) || (true === $is__pro) ) {

      $ss2t_display = '<!-- ss2t // configuration error -->';
      $uniqid = uniqid();

      if ('html' === $format) {
        $mimg = plugins_url("/spoken-search/assets/img/ss2t-microphone-for-" . $style . ".png");
        $ss2t_display = '<!--ss2t format=' . $format . ' style=' . $style . '-->';
        $ss2t_display .= '<section id="ss2t-widget-' . $uniqid .'" class="ss2t-widget widget widget_spoken_search ' . $style . '"'. $tooltip_display . '><form role="search" method="get" class="search-form" action="' . home_url() . '"><label for="search-form-' . $uniqid .'"><span class="screen-reader-text">Search for:</span></label><input type="search" id="search-form-' . $uniqid . '" class="search-field ss2t-action" placeholder="Search by Voice &hellip;" value="" name="s" style="background-image:url(' . $mimg . ')" autocomplete="off" readonly /></form></section>';
      }

      if ('image' === $format) {
        $fimg = plugins_url("/spoken-search/assets/img/ss2t-search-" . $style . "_" . $size . ".png");
        $ss2t_display = '<!--ss2t format=' . $format . ' style=' . $style . '-->';
        $ss2t_display .= '<section id="ss2t-widget-' . $uniqid .'" class="ss2t-widget widget widget_spoken_search ' . $style . '"'. $tooltip_display . '><img src="' . $fimg . '" id="ss2t-fimg-' . $uniqid . '" class="search-img ss2t-action" alt="Voice Search" /></section>';
      }

    }

    return $ss2t_display;
    
  }

}
