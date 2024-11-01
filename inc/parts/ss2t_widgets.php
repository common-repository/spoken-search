<?php
if ( ! defined('ABSPATH') ) { exit; }
/**
 * widgets
**/

add_action( 'widgets_init', function(){ register_widget( 'SS2T_Widget' ); } );

class SS2T_Widget extends WP_Widget {

  public function __construct() {
    $ss2t_widget_ops = array( 
      'classname' => 'ss2t_widget',
      'description' => 'Voice Search Widget.',
    );
    parent::__construct( 'ss2t_widget', 'Spoken Search', $ss2t_widget_ops );
  }
  
  public function widget( $args, $instance ) {
    $style = isset($instance['style']) ? $instance['style'] : 'light';
    $format = isset($instance['format']) ? $instance['format'] : 'html';
    $size = isset($instance['size']) ? $instance['size'] : 'large';
    $tooltip = isset($instance['tooltip']) ? $instance['tooltip'] : 'yes';
    $shortcode = '[ss2t style="' . $style . '" format="' . $format . '" size="' . $size . '" tooltip="' . $tooltip . '"]';
    echo do_shortcode( $shortcode );
  }

  public function form( $instance ) {
    $style = ! empty( $instance['style'] ) ? $instance['style'] : esc_html__( 'style', 'ss2t' );
?>
    <p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>">
    <b><?php esc_attr_e( 'Display Style', 'ss2t' ); ?></b>
      <select class='widefat' id="<?php echo $this->get_field_id('style'); ?>"
              name="<?php echo $this->get_field_name('style'); ?>" type="text">
        <option value='light'<?php echo ($style=='light') ? ' selected' : ''; ?>>
          light
        </option>
        <option value='dark'<?php echo ($style=='dark') ? ' selected' : ''; ?>>
          dark
        </option>
      </select>                
    </label>
    </p>
<?php
    $tooltip = ! empty( $instance['tooltip'] ) ? $instance['tooltip'] : esc_html__( 'tooltip', 'ss2t' );
?>
    <p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'tooltip' ) ); ?>">
    <b><?php esc_attr_e( 'Use Tooltip?', 'ss2t' ); ?></b>
    <br />
    This will display a small tooltip on hover.
      <select class='widefat' id="<?php echo $this->get_field_id('tooltip'); ?>"
              name="<?php echo $this->get_field_name('tooltip'); ?>" type="text">
        <option value='yes'<?php echo ($tooltip=='yes') ? ' selected' : ''; ?>>
          yes
        </option>
        <option value='no'<?php echo ($tooltip=='no') ? ' selected' : ''; ?>>
          no
        </option>
      </select>                
    </label>
    </p>
<?php
    $format = ! empty( $instance['format'] ) ? $instance['format'] : esc_html__( 'format', 'ss2t' );
?>
    <p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'format' ) ); ?>">
    <b><?php esc_attr_e( 'Display Format', 'ss2t' ); ?></b>
    <br />
    Choose the "image" option for <b>format</b> if you are experiencing style / format issues with your theme.
      <select class='widefat' id="<?php echo $this->get_field_id('format'); ?>"
              name="<?php echo $this->get_field_name('format'); ?>" type="text">
        <option value='html'<?php echo ($format=='html') ? ' selected' : ''; ?>>
          html
        </option>
        <option value='image'<?php echo ($format=='image') ? ' selected' : ''; ?>>
          image
        </option>
      </select>                
    </label>
    </p>
<?php
    $size = ! empty( $instance['size'] ) ? $instance['size'] : esc_html__( 'size', 'ss2t' );
?>
    <p>
    <label for="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>">
    <b><?php esc_attr_e( 'Image Size', 'ss2t' ); ?></b>
    <br />
    * Display size is only applicable if "image" option for <b>format</b> is chosen above.
      <select class='widefat' id="<?php echo $this->get_field_id('size'); ?>"
              name="<?php echo $this->get_field_name('size'); ?>" type="text">
        <option value='medium'<?php echo ($size=='medium') ? ' selected' : ''; ?>>
          medium
        </option>
        <option value='large'<?php echo ($size=='large') ? ' selected' : ''; ?>>
          large
        </option>
        <option value='small'<?php echo ($size=='small') ? ' selected' : ''; ?>>
          small
        </option>
      </select>                
    </label>
    </p>
<?php
  }

  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['style'] = ( ! empty( $new_instance['style'] ) ) ? strip_tags( $new_instance['style'] ) : '';
    $instance['format'] = ( ! empty( $new_instance['format'] ) ) ? strip_tags( $new_instance['format'] ) : '';
    $instance['size'] = ( ! empty( $new_instance['size'] ) ) ? strip_tags( $new_instance['size'] ) : '';
    $instance['tooltip'] = ( ! empty( $new_instance['tooltip'] ) ) ? strip_tags( $new_instance['tooltip'] ) : '';
    return $instance;
  }

}
