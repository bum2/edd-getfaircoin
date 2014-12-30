<?php
/**
* Plugin Name: EDD GetFaircoin.net Fields and Rates
* Plugin URI: http://getfaircoin.net/
* Description: This plugin adds user FAIR address field in the checkout and shows actual faircoin price calling exchanges api's in realtime
* Author: Bumbum
* Version: 0.1
* Author URI: http://github.com/bum2/
*/

### Version
define( 'EDD_GETFAIRCOIN_VERSION', 0.1 );

### Create Text Domain For Translations
add_action( 'plugins_loaded', 'getfaircoin_textdomain' );
function getfaircoin_textdomain() {
  load_plugin_textdomain( 'edd-getfaircoin', false, dirname( plugin_basename( __FILE__ ) ) );
}





/**
* Display fairaddress number field at checkout
* Add more here if you need to
*/
function getfaircoin_edd_display_checkout_fields() { // get user's fairaddress number if they already have one stored
  if ( is_user_logged_in() ) {
    $user_id = get_current_user_id();
    $fairaddress = get_the_author_meta( '_edd_user_fairaddress', $user_id );
  }
  $fairaddress = isset( $fairaddress ) ? esc_attr( $fairaddress ) : '';
  ?>
  <p id="edd-fairaddress-wrap">
  <label class="edd-label" for="edd-fairaddress">
  <?php echo _e('Faircoin Address', 'edd-getfaircoin'); ?>
  </label>
  <span class="edd-description">
  <?php echo _e('Enter your FAIR Address so we can send your Faircoins to your wallet.', 'edd-getfaircoin'); ?>
  </span>
  <input class="edd-input" type="text" name="edd_fairaddress" id="edd-fairaddress" placeholder="<?php echo _e('Faircoin Address', 'edd-getfaircoin'); ?>" value="<?php echo $fairaddress; ?>" />
  </p>
  <?php
}
add_action( 'edd_purchase_form_user_info', 'getfaircoin_edd_display_checkout_fields' );


/**
* Make fairaddress number required
* Add more required fields here if you need to
*/
function getfaircoin_edd_required_checkout_fields( $required_fields ) {
  $required_fields = array(
    'edd_fairaddress' => array(
      'error_id' => 'invalid_fairaddress',
      'error_message' => __('Please enter a valid Faircoin Address', 'edd-getfaircoin')
    ),
  );
  return $required_fields;
}
add_filter( 'edd_purchase_form_required_fields', 'getfaircoin_edd_required_checkout_fields' );


/**
* Set error if fairaddress number field is empty
* You can do additional error checking here if required
*/
function getfaircoin_edd_validate_checkout_fields( $valid_data, $data ) {
  if ( empty( $data['edd_fairaddress'] ) ) {
    edd_set_error( 'invalid_fairaddress', __('Please enter your Faircoin Address.', 'edd-getfaircoin') );
  }
  if ( strlen( $data['edd_fairaddress'] ) != 34 ) {
    edd_set_error( 'invalid_fairaddress', __('Your Faircoin Address must have 34 digits', 'edd-getfaircoin') );
  }
}
add_action( 'edd_checkout_error_checks', 'getfaircoin_edd_validate_checkout_fields', 10, 2 );


/**
* Store the custom field data into EDD's payment meta
*/
function getfaircoin_edd_store_custom_fields( $payment_meta ) {
  $payment_meta['fairaddress'] = isset( $_POST['edd_fairaddress'] ) ? sanitize_text_field( $_POST['edd_fairaddress'] ) : '';
  return $payment_meta;
}
add_filter( 'edd_payment_meta', 'getfaircoin_edd_store_custom_fields');


/**
* Add the fairaddress number to the "View Order Details" page
*/
function getfaircoin_edd_view_order_details( $payment_meta, $user_info ) {
  $fairaddress = isset( $payment_meta['fairaddress'] ) ? $payment_meta['fairaddress'] : 'none';
  ?>
  <div class="column-container">
  <div class="column">
  <strong><?php echo _e('Faircoin Address: ', 'edd-getfaircoin'); ?></strong>
  <input type="text" name="edd_fairaddress" value="<?php esc_attr_e( $fairaddress ); ?>" class="medium-text" />
  <p class="description"><?php _e( 'Customer Faircoin address', 'edd-getfaircoin' ); ?></p>
  </div>
  </div>
  <?php
}
add_action( 'edd_payment_personal_details_list', 'getfaircoin_edd_view_order_details', 10, 2 );


/**
* Save the fairaddress field when it's modified via view order details
*/
function getfaircoin_edd_updated_edited_purchase( $payment_id ) {
  // get the payment meta
  $payment_meta = edd_get_payment_meta( $payment_id );
  // update our fairaddress number
  $payment_meta['fairaddress'] = isset( $_POST['edd_fairaddress'] ) ? $_POST['edd_fairaddress'] : false;
  // update the payment meta with the new array
  update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );
}
add_action( 'edd_updated_edited_purchase', 'getfaircoin_edd_updated_edited_purchase' );


/**
* Add a {fairaddress} tag for use in either the purchase receipt email or admin notification emails
*/
if ( function_exists( 'edd_add_email_tag' ) ) {
  edd_add_email_tag( 'fairaddress', 'Customer\'s Faircoin Address', 'getfaircoin_edd_email_tag_fairaddress' );
}


/**
* The {fairaddress} email tag
*/
function getfaircoin_edd_email_tag_fairaddress( $payment_id ) {
  $payment_data = edd_get_payment_meta( $payment_id );
  return $payment_data['fairaddress'];
}


/**
* Update user's fairaddress number in the wp_usermeta table
* This fairaddress number will be shown on the user's edit profile screen in the admin
*/
function getfaircoin_edd_store_usermeta( $payment_id ) {
  // return if user is not logged in
  if ( ! is_user_logged_in() )
    return;
  // get the user's ID
  $user_id = get_current_user_id();
  // update fairaddress number
  update_user_meta( $user_id, '_edd_user_fairaddress', $_POST['edd_fairaddress'] );
}
add_action( 'edd_complete_purchase', 'getfaircoin_edd_store_usermeta' );


/**
* Save the field when the values are changed on the user's WP profile page
*/
function getfaircoin_edd_save_extra_profile_fields( $user_id ) {
  if ( ! current_user_can( 'edit_user', $user_id ) )
    return false;
  update_user_meta( $user_id, '_edd_user_fairaddress', $_POST['fairaddress'] );
}
add_action( 'personal_options_update', 'getfaircoin_edd_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'getfaircoin_edd_save_extra_profile_fields' );


/**
* Save the field when the value is changed on the EDD profile editor
*/
function getfaircoin_edd_pre_update_user_profile( $user_id, $userdata ) {
  $fairaddress = isset( $_POST['edd_fairaddress'] ) ? $_POST['edd_fairaddress'] : '';
  // Make sure user enters a fairaddress number
  if ( ! $fairaddress ) {
    edd_set_error( 'fairaddress_required', __( 'Please enter a Faircoin Address', 'edd-getfaircoin' ) );
  }
  // update fairaddress number
  update_user_meta( $user_id, '_edd_user_fairaddress', $fairaddress );
}
add_action( 'edd_pre_update_user_profile', 'getfaircoin_edd_pre_update_user_profile', 10, 2 );


/**
* Add the FairAddress to the "Contact Info" section on the user's WP profile page
*/
function getfaircoin_user_contactmethods( $methods, $user ) {
  $methods['_edd_user_fairaddress'] = 'Faircoin Address';
  return $methods;
}
add_filter( 'user_contactmethods', 'getfaircoin_user_contactmethods', 10, 2 );






/*
class RecentGroupDocsWidget extends WP_Widget
{
  function RecentGroupDocsWidget()
  {
    $widget_ops = array('classname' => 'RecentGroupDocsWidget', 'description' => 'Displays a recent group docs list (if any)' );
    $this->WP_Widget('RecentGroupDocsWidget', 'Recent Group Docs', $widget_ops);
  }

  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'max' => 10 ) );
    $title = $instance['title'];
    $max = $instance['max'];
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
  <p><label for="<?php echo $this->get_field_id('max'); ?>">How many? (maximum): &nbsp; <input class="widefat" id="<?php echo $this->get_field_id('max'); ?>" name="<?php echo $this->get_field_name('max'); ?>" type="text" style="text-align:center;width:60px;" maxlength="2" value="<?php echo $max ?>" /></label></p>
<?php
  }

  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    $instance['max'] = $new_instance['max'];
    return $instance;
  }

  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);

    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
    $max = empty($instance['max']) ? 10 : $instance['max'];

    global $bp, $wpdb;

    if (isset($bp->groups->current_group->id)) {
      $output = "

        <ul class='recent-group-docs'>";
        $doc_list = $wpdb->get_results( "
        SELECT ID, post_name
        FROM wp_posts
        WHERE post_type = 'bp_doc' and ID
        IN (
                SELECT object_id
                FROM wp_term_relationships
                WHERE term_taxonomy_id = (
                        SELECT term_taxonomy_id
                        FROM wp_term_taxonomy
                        WHERE term_id = (
                                SELECT term_id FROM wp_terms WHERE slug = 'bp_docs_associated_group_".$bp->groups->current_group->id."'
                        )
                )
        )
        ORDER BY post_modified DESC
        LIMIT 0 , ".$max );

        foreach ($doc_list as $doc){
          $output .= "
          <li class='bp_group type-bp_group bp_doc type-bp_doc no-post-thumbnail'><a href=".get_permalink($doc->ID) . " title=”" . get_the_title($doc->ID) . "”><div class='title'>" . get_the_title($doc->ID) . "
          </div></a></li>
          ";
        }
        $output .= "</ul>

      ";
      if (count($doc_list) > 0){
        if (!empty($title))
          echo $before_title . $title . $after_title;;
        echo $output;
      }
   }

    echo $after_widget;
  }

}
add_action( 'widgets_init', create_function('', 'return register_widget("RecentGroupDocsWidget");') );
*/
