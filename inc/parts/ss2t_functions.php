<?php
if ( ! defined('ABSPATH') ) { exit; }
/**
 * functions
**/

if ( ! function_exists( 'ss2t_pre_get_posts_search' ) ) {

  function ss2t_pre_get_posts_search($query) {
    $ss2t = get_option('SS2T') ?: null;
    if ( ($ss2t) && isset($ss2t['ptmultiple']) && is_array($ss2t['ptmultiple']) ) {
      if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
        $post_types = $ss2t['ptmultiple'] ?: array( 'any' );
        $query->set( 'post_type', $post_types );
      }
    }
  }

}

if ( ! function_exists( 'ss2t_send_to_search_results' ) ) {

  function ss2t_send_to_search_results() {

    check_ajax_referer( 'ss2t_ajax_nonce', 'security' );

    $query = isset($_POST['query']) ? sanitize_text_field( $_POST['query'] ) : '';
    $base = isset($_POST['base']) ? sanitize_url( $_POST['base'] ) : '';
    $home_url = esc_url( home_url() );

    if ($base === $home_url) {

      if ( '/' != substr($base, -1) ) {
        $param = '/?s=';
      } else {
        $param = '?s=';
      }

      $url = $base . $param . $query;
      echo $url;

    } else {

      return false;

    }

    wp_die();
        
  }

}

if ( ! function_exists( 'ss2t_load_search_results' ) ) {

  function ss2t_load_search_results() {

  	check_ajax_referer( 'ss2t_ajax_nonce', 'security' );

    $query = isset($_POST['query']) ? sanitize_text_field( $_POST['query'] ) : '';
    $paged = isset($_POST['paged']) ? sanitize_text_field( $_POST['paged'] ) : '1';
    $post_types = array( 'any' );
    $ss2t = get_option('SS2T') ?: null;
    if ( ($ss2t) && isset($ss2t['ptmultiple']) ) {
      $post_types = $ss2t['ptmultiple'];
    }  

    if ( ! empty($query) ) {

      $args = array(
        'post_type'      => $post_types,
        'post_status'    => 'publish',
        'posts_per_page' => '10',
        'paged'          => $paged,
        's'              => $query
      );
      $search = new WP_Query( $args );
      
      ob_start();
  ?>

      <header class="ss2t-header">
        <h2 class="ss2t-search-title"><span class="ss2t-search-results">Search Results for:</span> <?php echo esc_attr( $query ); ?></h2>
      </header>

  <?php
      
      if ( $search->have_posts() ) : 

  		  while ( $search->have_posts() ) : $search->the_post();
  ?>

  			<?php the_title( sprintf( '<h3 class="ss2t-search-title"><a href="%s">', esc_url( get_permalink() ) ), '</a></h3>' ); ?>

  <?php
  		  endwhile;

        ss2t_results_pagination($search, $paged);
        
      else :
  ?>

    	<p class="ss2t-no-results">Sorry, no result found.</p>

  <?php
      endif;
  	
      $content = ob_get_clean();
  	
      echo $content;

      wp_reset_postdata();

    }

  	wp_die();
  			
  }

}

if ( ! function_exists( 'ss2t_results_pagination' ) ) {
  
  function ss2t_results_pagination($search, $paged) {

    $temp_query = $wp_query;
    $wp_query = null;
    $wp_query = $search;
    $total = $wp_query->max_num_pages;
    $s = $wp_query->query['s'];
    $big = 999999999999;
    if ($total > 1) {
      $format = '';
      echo paginate_links( 
        array(
          'base'      => home_url() . '/?s=' . urlencode($s),
          'format'    => $format,
          'current'   => $paged,
          'total'     => $total,
          'mid_size'  => 3,
          'type'      => 'list',
          'prev_next' => false,
        ) 
      );
?>
  <script>
    jQuery('#ss2t-search-results .page-numbers a.page-numbers').on('click', function(e) {
      e.preventDefault();
      var $content   = jQuery('#ss2t-search-results');
      var paged      = jQuery(this).html();
      var ss2tquery  = '<?php echo $s; ?>';
      var ss2t_ajax  = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
      var ss2t_nonce = '<?php echo wp_create_nonce( "ss2t_ajax_nonce" ); ?>'; 
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
          $content.html( response );
        }
      });
    });
  </script>
<?php
    }
    $wp_query = $temp_query;
  }
  
}

if ( ! function_exists( 'ss2t_get_spoken_token' ) ) {

  function ss2t_get_spoken_token() {

  	$token = null;

  	check_ajax_referer( 'ss2t_ajax_nonce', 'security' );

    $transient = 'ss2t___token';
    $timeout = 9 * MINUTE_IN_SECONDS;
    $token = get_transient( $transient );

  	if ( $token === false ) {

      $api_url = 'https://' . SS2T_THE_API . '/v1/spoken/';
      $apiKey = isset( get_option('SS2T')['apikey'] ) ? base64_decode( get_option('SS2T')['apikey'] ) : null;
      $args = array(
        'headers' => array( 
          'Authorization' => $apiKey
        ),
        'body' => array()
      );
      $response = wp_remote_get( $api_url, $args );

      if ($response) {

        $decoded = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset($decoded['status']) && ('success' == $decoded['status']) ) {

  				$token = isset($decoded['token']) ? $decoded['token'] : null;

  				if ($token) {

  					set_transient( $transient, $token, $timeout );

  				}

        } 

      }

    }

  	echo $token;

  	wp_die();

  }

}

if ( ! function_exists( 'ss2t_exclude_include' ) ) {

  function ss2t_exclude_include() {

    check_ajax_referer( 'ss2t_ajax_nonce', 'security' );

    $allowed = false;
    $apiKey = null;

    if ( isset($_GET['akey']) && ('' != isset($_GET['akey'])) ) {

      $apiKey = sanitize_text_field( $_GET['akey'] );

    }

    if ($apiKey) {
    
      $api_url = 'https://' . SS2T_THE_API . '/v1/excluded__included/';
      $args = array(
        'headers' => array( 
          'Authorization' => $apiKey
        ),
        'body' => array(
          'akey' => $apiKey
        )
      );
      $response = wp_remote_get( $api_url, $args );

      if ($response) {

        $decoded = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset($decoded['status']) && ('success' == $decoded['status']) ) {

        	$response_array = ( isset($decoded['list']) ) ? $decoded['list'] : array();

        	if ( ! in_array("spoken", $response_array) ) {

        		$allowed = true;

        	}

        }

      }

    }

    $options = get_option('SS2T') ?: array();
    $options['pro'] = $allowed;
    update_option( 'SS2T', $options );

    wp_die();

  }

}

if ( ! function_exists( 'ss2t_search_history' ) ) {

  function ss2t_search_history() {

    check_ajax_referer( 'ss2t_ajax_nonce', 'security' );

    $query = isset($_POST['query']) ? sanitize_text_field( $_POST['query'] ) : '';
    
    if ( ! empty($query) ) {
    
      $history = get_option('SS2T_HISTORY') ?: array();
      $history[] = $query;
      update_option( 'SS2T_HISTORY', $history, false );

    }

    wp_die();
        
  }

}

if ( ! function_exists( 'ss2t_chrome_check' ) ) {

  function ss2t_chrome_check() {

    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $is__chrome = (strpos($user_agent, 'Chrome') !== FALSE) ? true : false;

    return $is__chrome;

  }

}

if ( ! function_exists( 'ss2t_pro_check' ) ) {

  function ss2t_pro_check() {

    $ss2t = get_option('SS2T') ?: array();
    $is__pro = isset($ss2t['pro']) ? $ss2t['pro'] : false;

    return $is__pro;

  }

}
