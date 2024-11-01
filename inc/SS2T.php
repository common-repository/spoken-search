<?php
if ( ! defined('ABSPATH') ) { exit; }
/**
 * SS2T
**/

require_once( SS2T_PATH_INC . 'ss2t_LifeCycle.php' );

class SS2T extends ss2t_LifeCycle {

  public function getOptionMetaData() {
    return array(
      'apikey'      => array(__('API Key', 'ss2t')),
      'ptmultiple'  => array_merge( array(__('Post Types', 'ss2t')), $this->getAllPostTypes() ),
      'display'     => array(__('Search Results Display', 'ss2t'), 'Default', 'Overlay'),
      'style'       => array(__('Style (For Overlay)', 'ss2t'), 'Dark', 'Light'),
      'floating'    => array(__('Sitewide Floating Microphone', 'ss2t'), 'Off', 'On'),
      'floatingpos' => array(__('Floating Microphone Position', 'ss2t'), 'Top', 'Middle', 'Bottom')
    );
  }

  public function getAllPostTypes() {
    $pt_array = array( 'any', 'post' );
    $post_types = array();
    $args = array(
      'public'   => true,
      '_builtin' => false
    );
    $post_types = get_post_types( $args, 'names' ) ?: array();
    if ( ! empty($post_types) ) {
      foreach ($post_types as $k => $v) {
        $pt_array[] = $v;
      } 
    }
    return $pt_array;
  }

  public function getPluginDisplayName() {
    if ( defined( 'SS2T_NAME' ) ) {
      return SS2T_NAME;
    }
  }

  public function getPlanDisplayName() {
    $name = 'Basic Plan (Chrome Only) <a href="' . esc_url( 'https://www.plnia.com/pricing/' ) . '" target="_blank" rel="noopener">' . esc_html( 'upgrade now' ) . '</a>';
    $ss2t = get_option( 'SS2T' );
    $pro  = isset( $ss2t[ 'pro' ] ) ? $ss2t[ 'pro' ] : false;
    if ( true === $pro ) {
      $name = 'Pro Plan (All Browsers)';
    }
    return $name;
  }

  protected function getMainPluginFileName() {
    return 'spoken-search.php';
  }

  protected function getPluginDir() {
    if ( defined( 'SS2T_PATH_BASE' ) ) {
      return SS2T_PATH_BASE;
    }
  }

  public function ss2t_js_bundle() {
    wp_register_script( 'ss2t-bundle', plugins_url('/assets/js/ss2t-bundle.min.js', dirname(__FILE__)), [], SS2T_VERSION, true );
    wp_enqueue_script('ss2t-bundle');
  }

  public function ss2t_check_for_jquery() {
    if ( ! wp_script_is( 'jquery', 'registered' ) ) {
      wp_enqueue_script( 'jquery' );
    }
  }

  public function ss2t_styles_and_scripts() {
    wp_enqueue_style( 'ss2t-css', plugins_url('/assets/css/ss2t.min.css', dirname(__FILE__)), [], SS2T_VERSION );
  }

  public function ss2t_admin_styles() {
    wp_enqueue_style( 'ss2t-css', plugins_url('/assets/css/ss2t-admin.min.css', dirname(__FILE__)), [], SS2T_VERSION );
  }

  public function ss2t_add_settings_link($links) {
    $settings_link = '<a href="tools.php?page=' . $this->getSettingsSlug() . '">' . __( 'Settings' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
  }

  protected function addSettingsSubMenuPageNav() {
    $displayName = $this->getPluginDisplayName();
    add_management_page(
      $displayName,
      $displayName,
      'manage_options',
      $this->getSettingsSlug(),
      array(&$this, 'settingsPage')
    );
  }

  public function addActionsAndFilters() {
    add_action( 'admin_menu', array(&$this, 'addSettingsSubMenuPage') );
    add_action( 'init', array(&$this, 'ss2t_check_for_jquery') );
    add_action( 'wp_enqueue_scripts', array(&$this, 'ss2t_styles_and_scripts') );
    add_action( 'admin_enqueue_scripts', array(&$this, 'ss2t_admin_styles') );
    add_action( 'pre_get_posts', 'ss2t_pre_get_posts_search' );
    add_action( 'wp_ajax_ss2t_search_history', 'ss2t_search_history' );
    add_action( 'wp_ajax_nopriv_ss2t_search_history', 'ss2t_search_history' );
    add_action( 'wp_ajax_ss2t_send_to_search_results', 'ss2t_send_to_search_results' );
    add_action( 'wp_ajax_nopriv_ss2t_send_to_search_results', 'ss2t_send_to_search_results' );
    add_action( 'wp_ajax_ss2t_load_search_results', 'ss2t_load_search_results' );
    add_action( 'wp_ajax_nopriv_ss2t_load_search_results', 'ss2t_load_search_results' );
    add_action( 'wp_ajax_ss2t_get_spoken_token', 'ss2t_get_spoken_token' );
    add_action( 'wp_ajax_nopriv_ss2t_get_spoken_token', 'ss2t_get_spoken_token' );
    add_action( 'wp_ajax_ss2t_exclude_include', 'ss2t_exclude_include' );
    add_filter( 'plugin_action_links_' . SS2T_BASENAME, array(&$this, 'ss2t_add_settings_link') );
    if ( false === $this->ss2t_capability() ) {
      add_action( 'init', array(&$this, 'ss2t_js_bundle') );
    }
  }

  public function ss2t_floating_mic_check($ss2t) {
    $floating = isset($ss2t['floating']) ? $ss2t['floating'] : 'Off';
    return $floating;
  }

  public function ss2t_pro_check($ss2t) {
    $pro = isset($ss2t['pro']) ? $ss2t['pro'] : false;
    return $pro;
  }

  public function ss2t_chrome_check($user_agent) {
    $is__chrome = (strpos($user_agent, 'Chrome') !== FALSE) ? true : false;
    return $is__chrome;
  }

  public function ss2t_capability() {

    $ss2t = get_option('SS2T');
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $floating__mic_check = $this->ss2t_floating_mic_check($ss2t) ?: '';
    $floating__mic = ('On' == $floating__mic_check) ? true : false;
    $is__pro = $this->ss2t_pro_check($ss2t);
    $is__chrome = $this->ss2t_chrome_check($user_agent);

    $browsers_spchrc  = array( "Chrome" );
    $browsers_chrome  = array( "Chrome" );
    $browsers_safari  = array( "Safari" );
    $browsers_firefox = array( "Firefox" );
    $browsers_opera   = array( "Opera" );
    $browsers_msiedge = array( "Edge", "MSIE", "Trident" );
    $ipad   = (strpos($user_agent, 'iPad') !== FALSE) ? true : false;
    $iphone = (strpos($user_agent, 'iPhone') !== FALSE) ? true : false;
    $ipod   = (strpos($user_agent, 'iPod') !== FALSE) ? true : false;

    if (true === $is__chrome) {

      require_once( SS2T_PATH_PARTS . 'ss2t_audio_all.php' );
      require_once( SS2T_PATH_PARTS . 'ss2t_audio_chrome.php' );

      if (true === $floating__mic) {
        require_once( SS2T_PATH_PARTS . 'ss2t_floating_mic.php' );
      }

    } else {

      if (true === $is__pro) {

        require_once( SS2T_PATH_PARTS . 'ss2t_audio_all.php' );
        require_once( SS2T_PATH_PARTS . 'ss2t_audio_other.php' );
        
        if (true === $floating__mic) {
          require_once( SS2T_PATH_PARTS . 'ss2t_floating_mic.php' );
        }

      }

    }

    return $is__chrome;

  }

  public function settingsPage() {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have sufficient permissions to access this page.', 'ss2t'));
    }

    $optionMetaData = $this->getOptionMetaData();

    if ($optionMetaData != null) {
      foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
        if (isset($_POST[$aOptionKey])) {
          $this->updateOption( $aOptionKey, $_POST[$aOptionKey] );
        }
      }
    }

    $settingsGroup = get_class($this) . '-settings-group';
    ?>
        <img src="<?php echo esc_url( plugins_url('/assets/img/icon-256x256.png', dirname(__FILE__)) ); ?>" alt="<?php echo esc_html ( $this->getPluginDisplayName() ); ?>" class="ss2t-h1-img" /><h1 class="ss2t-h1"><?php echo esc_html ( $this->getPluginDisplayName() ) . ' '; _e('Settings', 'ss2t'); ?></h1>
        <hr class="ss2t-hr" />
        <p><a href="<?php echo esc_url( 'https://www.plnia.com/pricing/' ); ?>" target="_blank" rel="noopener" style="font-size:14px;"><?php echo esc_html( 'Need an API key?' ); ?></a></p>
        <form id="ss2t-settings" method="post" action="">
        <?php settings_fields($settingsGroup); ?>
            <table class="ss2t-plugin form-table"><tbody>
              <tr valign="top">
                <td class="td-th" scope="row">
                  <p>
                    <label><?php echo esc_html( 'Plan Name' ); ?></label>
                  </p>
                </td>
                <td>
                  <p>
                    <span><?php echo $this->getPlanDisplayName(); ?></span>
                  </p>
                </td>
              </tr>
            <?php
            if ($optionMetaData != null) {
                foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                    $displayText = is_array($aOptionMeta) ? $aOptionMeta[0] : $aOptionMeta;
                    ?>
                        <tr valign="top">
                            <td class="td-th" scope="row"><p><label for="<?php echo esc_html( $aOptionKey ); ?>"><?php echo esc_html( $displayText ); ?></label></p></td>
                            <td>
                            <?php $this->createFormControl($aOptionKey, $aOptionMeta, $this->getOption($aOptionKey)); ?>
                            </td>
                        </tr>
                    <?php
                }
            }
            ?>
            </tbody></table>
            <p class="submit">
              <input type="submit" id="submit-ss2t-options" class="button-primary"
                     value="<?php _e('Save Changes', 'ss2t') ?>"/>
            </p>
        </form>
        <br />
        <hr />
        <div id="ss2t-implementation">
          <br />
          <h2>Spoken Search Options</h2>
          <p><b>API KEY</b> - This is only needed if you <a href="<?php echo esc_url( 'https://www.plnia.com/pricing/' ); ?>" target="_blank" rel="noopener">upgrade to a paid plan</a>. All paid plans include a <a href="<?php echo esc_url( 'https://www.plnia.com/pricing/' ); ?>" target="_blank" rel="noopener">free 7-day trial</a>.</p>
          <p><b>POST TYPES</b> - Here you can select all of the Post Types that you wish to show in Spoken Search results. If you have a standard WordPress site, you may not have any additional Post Types, and "any" may be the only choice. Leave "any" checked to search all Post Types. <a href="<?php echo esc_url('https://wordpress.org/support/article/post-types/'); ?>" target="_blank" rel="noopener">Read this</a> for more information about post types.</p>
          <p><b>SEARCH RESULTS DISPLAY</b> - Choose "Default" to have search results displayed on the default search results page of your WordPress theme. Choose "Overlay" to have search results displayed in an overlay that appears atop the page after a search.</p>
          <p><b>STYLE (FOR OVERLAY)</b> - This is only needed if you choose "Overlay" for "Search Results Display" as described above.</p>
          <p><b>SITEWIDE FLOATING MICROPHONE</b> - Turn this on to display a floating microphone that will appear sitewide allowing users to use voice search whenever they wish.</p>
          <p><b>FLOATING MICROPHONE POSITION</b> - This is only needed if you choose "On" for the "Sitewide Floating Microphone" option above. The floating microphone will follow users as they scroll. You can choose top, middle, or bottom for the vertical positioning of the floating microphone.</p>
          <br />
          <hr />
          <h2>Basic Plan</h2>
          <h3>About the Spoken Search Basic Plan</h3>
          <p>If you opt to use the Basic Plan (Chrome Only) version of this plugin, your website visitors will be able to search your site by voice <strong>if they are using a Chrome browser</strong>, but not if they are using any other browser. You can <a href="<?php echo esc_url( 'https://www.plnia.com/pricing/' ); ?>" target="_blank" rel="noopener">upgrade to a paid plan</a> to use the Pro Version of Spoken Search.</p>
          <h2>Pro Plan</h2>
          <h3>About the Spoken Search Pro Plan</h3>
          <p>If you <a href="<?php echo esc_url( 'https://www.plnia.com/pricing/' ); ?>" target="_blank" rel="noopener">purchase a paid plan</a> and upgrade to the Pro Version of the Spoken Search plugin, voice search will be available to the vast majority of your website visitors. We cannot guarantee that 100% of your website visitors will be able to search by voice, but the coverage is close to 100%. Some visitors could still be using outdated browsers that are no longer updated or supported.</p>
          <h2>Why Chrome?</h2>
          <h3>Why is Chrome the only browser in the Basic Plan?</h3>
          <p>Not all browsers <a href="<?php echo esc_url( 'https://caniuse.com/#feat=speech-recognition' ); ?>" target="_blank" rel="noopener">include the Speech Recognition API</a>. Chrome is the one browser that does include it most of the time. For visitors using non-Chrome browsers, the Spoken Search plugin must operate in a completely different way. This adds complexity and cost to adding voice search capability to a website.</p>
          <br />
          <hr />
          <h2>Implementation</h2>
          <p>Implementation of Spoken Search is accomplished via a WordPress shortcode or with our Spoken Search widget.</p>
          <br />
          <h3>Widget</h3>
          <p>Widgets can be added to any sidebar of your website dynamically. Learn more: <a href="<?php echo esc_url( 'https://wordpress.org/support/article/wordpress-widgets/' ); ?>" target="_blank" rel="noopener">About WordPress Widgets</a></p>
          <p>
            1) Navigate to Appearance -> Widgets.<br />
            2) Find the Spoken Search widget &amp; drag or add it to the sidebar/area of choice.<br />
            3) Select options in the Spoken Search widget &amp; click save.
          </p>
          <h4>Available options to set in a widget:</h4>
          <p>
            <b>style</b>: dark, light <br />
            <b>format</b>: html, image<br />
            <b>size</b>: small, medium, large<br />
            <b>tooltip</b>: yes, no<br />
          </p>
          <br />
          <h3>Shortcode</h3>
          <p>Shortcodes can be added directly into WordPress theme files. Learn more: <a href="<?php echo esc_url( 'https://en.support.wordpress.com/shortcodes/' ); ?>" target="_blank" rel="noopener">About WordPress Shortcodes</a></p>
          <h4>Available options to set in a shortcode:</h4>
          <p>
            <b>style</b>: dark, light <br />
            <b>format</b>: html, image<br />
            <b>size</b>: small, medium, large<br />
            <b>tooltip</b>: yes, no<br />
          </p>
          <h4>Example 1</h4>
          <p><code>[ss2t style="dark" format="image" size="large" tooltip="yes"]</code></p>
          <h4>Example 2</h4>
          <p><code>[ss2t style="light" format="html" size="medium" tooltip="no"]</code></p>
          <br />
          <h2>HTTPS / SSL</h2>
          <p>It is highly recommended to have an SSL certificate in place and serve pages via HTTPS when using this plugin. The vast majority of modern browsers require a secure connection via HTTPS in order to allow use of the microphone. Because of this, a website that does not have an SSL certificate in place and does not serve HTTPS pages will not be able to make use of this plugin completely.</p>
          <br />
          <br />
          <p>
            Powered by <a href="<?php echo esc_url( 'https://www.plnia.com' ); ?>" target="_blank" rel="noopener" title="plnia"><img src="<?php echo esc_url( plugins_url('/assets/img/plnia-logo.png', dirname(__FILE__)) ); ?>" alt="plnia" style="width:67px;height:auto;margin-left:2px;vertical-align:bottom" /></a>
          </p>
        </div>
        <br />
        <hr />
        <script>
          jQuery(document).on( "click", "#submit-ss2t-options", function(e) {
            e.preventDefault();
            var akey = jQuery("#apikey").val();
            var ajaxurl = '<?php echo admin_url("admin-ajax.php"); ?>';   
            var ss2t_nonce = '<?php echo wp_create_nonce( "ss2t_ajax_nonce" ); ?>';  
            var the_ajax_action = 'ss2t_exclude_include';
            var kdata = {
              'action'   : the_ajax_action,
              'security' : ss2t_nonce,
              'akey'     : akey
            };
            jQuery.get(ajaxurl, kdata, function(res){ 
              jQuery("#ss2t-settings").submit();
            });
          });
        </script>
    <?php

  }

  protected function createFormControl($aOptionKey, $aOptionMeta, $savedOptionValue) {

    if ( is_array($aOptionMeta) && (strpos($aOptionKey, 'multiple') !== false) ) {
        $choices = array_slice($aOptionMeta, 1);
  ?>
        <p>
        <?php
          foreach ($choices as $aChoice) {
            $checked = ( in_array($aChoice, $savedOptionValue) ) ? ' checked' : '';
            ?>
              <input type="checkbox" name="<?php echo esc_html( $aOptionKey ); ?>[]" value="<?php echo esc_html( $aChoice ); ?>"<?php echo $checked; ?> /> <?php echo esc_html( $aChoice ); ?><br />
            <?php
        }
        ?>
        </p>
  <?php
    } elseif ( is_array($aOptionMeta) && (count($aOptionMeta) >= 2) ) {
        $choices = array_slice($aOptionMeta, 1);
  ?>
        <p><select name="<?php echo esc_html( $aOptionKey ); ?>" id="<?php echo esc_html( $aOptionKey ); ?>">
        <?php
          foreach ($choices as $aChoice) {
            $selected = ($aChoice == $savedOptionValue) ? 'selected' : '';
            ?>
                <option value="<?php echo esc_html( $aChoice ); ?>" <?php echo $selected ?>><?php echo esc_html( $this->getOptionValueI18nString($aChoice) ); ?></option>
            <?php
        }
        ?>
        </select></p>
  <?php
    }
    elseif (strpos($aOptionKey, 'key') !== false) {
  ?>
        <p><input type="password" name="<?php echo esc_html( $aOptionKey ); ?>" id="<?php echo esc_html( $aOptionKey ); ?>"
                  value="<?php echo esc_attr( base64_decode($savedOptionValue) ); ?>" size="50"/></p>
  <?php
    }
    else {
  ?>
        <p><input type="text" name="<?php echo esc_html( $aOptionKey ); ?>" id="<?php echo esc_html( $aOptionKey ); ?>"
                  value="<?php echo esc_attr( $savedOptionValue ); ?>" size="50"/></p>
  <?php

    }

  }

}
