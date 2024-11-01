<?php
if ( ! defined('ABSPATH') ) { exit; }

add_action('wp_footer', 'ss2t__floating_mic');

function ss2t__floating_mic() {
  $ss2t = get_option('SS2T') ?: array();
  $position_class = isset($ss2t['floatingpos']) ? strtolower($ss2t['floatingpos']) : 'top';
  $uniqid = uniqid();
?>
<div id="ss2t-flt" class="<?php echo $position_class; ?>"><div id="ss2t-widget-<?php echo $uniqid; ?>" class="ss2t-widget ss2t-flt-inner tooltip-left" data-tooltip="Voice Search"><img src="<?php echo esc_url( plugins_url('/assets/img/ss2t-search-light_small.png', realpath(dirname(__FILE__) . '/..')) ); ?>" id="ss2t-flt-img" class="search-img ss2t-action" alt="Search by Voice" title="Search by Voice" /></div></div>
<?php
}
