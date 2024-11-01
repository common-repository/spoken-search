<?php
if ( ! defined('ABSPATH') ) { exit; }

add_action('wp_footer', 'ss2t__audio_all');

function ss2t__audio_all() {
  $ss2t = get_option('SS2T') ?: array();
  $display = isset($ss2t['display']) ? strtolower($ss2t['display']) : 'default';
  $style = isset($ss2t['style']) ? strtolower($ss2t['style']) : 'dark';
?>
<div class="ss2t-overlay <?php echo $style; ?>" id="ss2t-overlay"><div id="ss2t-search-results" class="<?php echo $style; ?>"></div><div id="ss2t-close-btn" class="<?php echo $style; ?>"></div></div>
<form id="ss2t-voice-search"><input type="hidden" value="" name="ss2t-query" id="ss2t-query"></form>
<script>
function ss2t_change_val(ss2tID,ss2tVal) {
  jQuery('#' + ss2tID).val(ss2tVal).trigger('change');
}
jQuery(document).ready(function() {
  jQuery('#ss2t-close-btn').on('click', function() {
  	var $overlay = jQuery('.ss2t-overlay');
  	$overlay.hide();
  	jQuery('body').removeClass('ss2t-no-body-scroll');
  });
  jQuery('#ss2t-query').on('change', function() {
  	var ss2tquery  = jQuery('#ss2t-query').val();
    var $content   = jQuery('#ss2t-search-results');
    var $overlay   = jQuery('.ss2t-overlay');
    var paged      = '1';
    var ss2t_ajax  = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
    var ss2t_nonce = '<?php echo wp_create_nonce( "ss2t_ajax_nonce" ); ?>'; 
    jQuery.ajax({
      type : 'post',
      url  : ss2t_ajax,
      data : {
        action   : 'ss2t_search_history',
        security : ss2t_nonce,
        query    : ss2tquery
      },
      success : function( response ) {
<?php 
  if ('default' === $display) : 
?>
        var results_base = "<?php echo esc_url( home_url() ); ?>";
        jQuery.ajax({
          type : 'post',
          url  : ss2t_ajax,
          data : {
            action   : 'ss2t_send_to_search_results',
            security : ss2t_nonce,
            base     : results_base,
            query    : ss2tquery
          },
          success : function( response ) {
            if (false == response) {
              console.log( 'ss2t error' );
            } else {
              window.location.href = encodeURI(response);
            }
          }
        });
<?php 
  endif;
  if ('overlay' === $display) : 
?>
        jQuery.ajax({
          type : 'post',
          url  : ss2t_ajax,
          data : {
            action   : 'ss2t_load_search_results',
            security : ss2t_nonce,
            paged    : paged,
            query    : ss2tquery
          },
          success : function( response ) {
            $overlay.fadeIn();
            $content.html( response );
            jQuery('body').addClass('ss2t-no-body-scroll');
          }
        });
<?php 
  endif; 
?>
      }
    });
    return false;
  });
});
</script>
<?php
}
