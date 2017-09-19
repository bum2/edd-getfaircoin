<?php
/**
* Plugin Name: EDD GetFaircoin.net Faircoin Price and Fields
* Plugin URI: https://github.com/bum2/edd-getfaircoin
* Description: This plugin adds user FAIR address and FairService checkbox fields in the checkout, shows actual faircoin price at homepage and aprox faircoin as user enters his fiat amount, and now also shows a reference in many other fiat currencies other than euro. Requires edd-currency-converter and edd-custom-prices. Now works with any gateway, excluding all them (at the checkout) except the one setted in the post's sidebar Gateway Options settings. Added the order field for the frontpage's getmethods and a gateway filter on payment history. Now compatible with coopfunding's Faircoin2 Crowdinvestment Campaign.
* Author: Bumbum
* Version: 0.8
* Author URI: https://getfaircoin.net
*/

//// IMPORTANT NOTE:  ////
/*

Has been edited the EDD /includes/payments/functions.php at line 1045, to get the correct customer_id by the email, when there's no payment meta for customer_id, adding this lines:

    if($customer_id < 1){ // bumbum when there's no meta _edd_payment_customer_id, get from user email
        $customer_email = edd_get_payment_meta( $payment_id, '_edd_payment_user_email', true );
        $customer_db = new EDD_DB_Customers();
        $customer = $customer_db->get_customer_by( 'email', $customer_email );
        if( $customer ) {
            $custom_id = $customer->id;
            if( $custom_id != $customer_id ) {
                edd_update_payment_meta( $payment_id, '_edd_payment_customer_id', $custom_id );
                $customer_id = $custom_id;
            }
        }
    } //

*/


### Version
define( 'EDD_GETFAIRCOIN_VERSION', 0.8 );


### Create Text Domain For Translations
add_action( 'plugins_loaded', 'getfaircoin_textdomain' );
function getfaircoin_textdomain() {
  load_plugin_textdomain( 'edd-getfaircoin', false, dirname( plugin_basename( __FILE__ ) ) );
}


### Function: Enqueue JavaScripts/CSS
add_action('wp_enqueue_scripts', 'getfaircoin_scripts');
function getfaircoin_scripts() {
	if(@file_exists(get_stylesheet_directory().'/getfaircoin-css.css')) {

		wp_enqueue_style('edd-getfaircoin', get_stylesheet_directory_uri().'/getfaircoin-css.css', false, EDD_GETFAIRCOIN_VERSION, 'all');
	} else {

    wp_enqueue_style('edd-getfaircoin', plugins_url('edd-getfaircoin/getfaircoin-css.css'), false, EDD_GETFAIRCOIN_VERSION, 'all');
	}
	if( is_rtl() ) {
		if(@file_exists(get_stylesheet_directory().'/getfaircoin-css-rtl.css')) {
			wp_enqueue_style('edd-getfaircoin-rtl', get_stylesheet_directory_uri().'/getfaircoin-css-rtl.css', false, EDD_GETFAIRCOIN_VERSION, 'all');
		} else {
			wp_enqueue_style('edd-getfaircoin-rtl', plugins_url('edd-getfaircoin/getfaircoin-css-rtl.css'), false, EDD_GETFAIRCOIN_VERSION, 'all');
		}
	}

  wp_enqueue_script('edd-getfaircoin', plugins_url('edd-getfaircoin/getfaircoin-js.dev.js'), array('jquery'), EDD_GETFAIRCOIN_VERSION, true); // bumbum .dev.

  //$add_to_cart_text = __('Get Coopshares Now', 'edd-getfaircoin');
  //wp_localize_script( 'edd-getfaircoin', 'edd_cp', array( 'add_to_cart_text' => $add_to_cart_text ) );
}

////
// only send purchase receipt when is paypal, the others are sending their own emails
function getfaircoin_trigger_purchase_receipt( $payment_id ) {
    remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999, 1 );
	// Make sure we don't send a purchase receipt while editing a payment
	if ( isset( $_POST['edd-action'] ) && 'edit_payment' == $_POST['edd-action'] )
		return;

    $gateway = edd_get_payment_gateway( $payment_id );
    if( $gateway == 'paypal'){
        // Send email with secure download link
        edd_email_purchase_receipt( $payment_id );
    } else {
        return;
    }
}
remove_action( 'edd_complete_purchase', 'edd_trigger_purchase_receipt', 999, 1 );
add_action( 'edd_complete_purchase', 'getfaircoin_trigger_purchase_receipt', 1000, 1 );


// To get the new customer id, when there's no meta key, then use the email
// edited in the EDD includes/payments/functions.php line 1045 !!

////

function getfaircoin_price($price){
  global $edd_options;

  if( $price == 0 ) {
    $price = '1 ƒ = '.number_format($edd_options['faircoin_price'], 2, '.', ',').' EUR';
  }
  return $price;
}
add_filter( 'edd_download_price', 'getfaircoin_price', 10);

function getfaircoin_currency_filter($price){
  global $edd_options;
  //if( isset($edd_options['coopshares_fair_checkout_info']) ) {
  //  $price = $edd_options['faircoin_price'].' FAIR';
  //} else
  echo 'Price: '.$price;
  if( $price == 1){//strpos($price, '1 €') !== false ){
    echo 'Price: '.$price;
    $price = $edd_options['faircoin_price'];//.' 1€ = '.number_format($edd_options['faircoin_price'], 0, '.', ',').' fair';
  }
  $price_arr = explode(' ', $price);
  if( count( $price_arr ) > 1 ) {
      $price = str_replace('&euro;', '', $price);
      $price = str_replace('€', '', $price);
  //    if( count( $price_arr ) > 3 ) {
  //        echo 'afterPRICE: '.$price;
  //        $price = 1;
  //    }
  }
  return $price;
}
//add_filter( 'edd_eur_currency_filter_before', 'getfaircoin_currency_filter' ); //edd_eur_currency_filter_after

function getfaircoin_currency_filter_before($formated, $currency, $price){
    //$price_arr = explode(' ', $price);
    //if( count( $price_arr ) > 1 ) {
        echo 'FORMATED: '.$formated.' PRICE: '.$price;
    //}
    //return $price;
}
//add_filter( 'edd_currency_change_before_format', 'getfaircoin_currency_filter_before' );


//// Menu items, not used now
function getfair_currency_menu_item( $item ) {
  if($item->title == 'Your Currency'){
    $item->title = 'from: '.edd_currency_get_stored_currency();
  }
  return $item;
}
//add_filter( 'wp_setup_nav_menu_item', 'getfair_currency_menu_item' );

////

function item_quantities_none() {
  //if( isset($edd_options['coopshares_fair_checkout_info']) ) {
  //   return true;
  //} else {
     return false;
  //}
}
add_filter( 'edd_item_quantities_enabled', 'item_quantities_none' );


////   G E N E R A L   S E T T I N G S   F I E L D S   //

function add_getfaircoin_settings($settings) {
  $getfaircoin_settings = array(
        'faircoin_settings' => array(
          'id' => 'faircoin_settings',
          'name' => '<strong>' . __( 'FAIRCOIN Settings', 'edd-getfaircoin' ) . '</strong>',
          'desc' => __( 'Set the actual Faircoin options', 'edd-getfaircoin' ),
          'type' => 'header'
        ),
        'faircoin_price' => array(
          'id' => 'faircoin_price',
          'name' => __( 'Faircoin-Euro Price', 'edd-getfaircoin' ),
          'desc' => __( 'Put manually the actual Faircoin price related Euro: 1 ƒ = n €', 'edd-getfaircoin' ),
          'type' => 'text',
          'size' => 'medium',
          'std' => '100.01'
        ),
        'general_settings' => array(
          'id' => 'general_settings',
          'name' => '<strong>' . __( 'GENERAL Settings', 'edd-getfaircoin' ) . '</strong>',
          'desc' => '',
          'type' => 'header'
        ),
    );
    return array_merge($getfaircoin_settings, $settings);
}
add_filter( 'edd_settings_general', 'add_getfaircoin_settings' );



////    P O S T   G A T E W A Y   S E T T I N G S    ////

/**
 * Render Gateway Specific
 *
 * @since 1.0
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_gateway_choice( $post_id ) {
  $post_gate = get_post_meta( $post_id, '_edd_gateway', true );
  $gateways = edd_get_payment_gateways();
  $gateway_options = array();
  foreach($gateways as $key => $arr){
	   $gateway_options[$key] = $arr['admin_label'];
  }
  $fiat_fee = get_post_meta( $post_id, '_edd_fiat_fee') ? get_post_meta( $post_id, '_edd_fiat_fee', true ) : '0';
  $fair_fee = get_post_meta( $post_id, '_edd_faircoin_fee') ? get_post_meta( $post_id, '_edd_faircoin_fee', true ) : '1';
  $gateway_order = get_post_meta( $post_id, '_edd_order') ? get_post_meta( $post_id, '_edd_order', true) : '0';
?>
	<p><strong><?php _e( 'Gateway Options:', 'edd-getfaircoin' ); ?></strong></p>
	<p>
		<label for="_edd_gateway">
			<?php echo EDD()->html->select( array(
				'name'    => '_edd_gateway',
				'options' => $gateway_options,
				'show_option_all'  => null,
				'show_option_none' => null,
				'selected' => $post_gate
			) ); ?>
			<?php _e( 'Specific gateway', 'edd-getfaircoin' ); ?>
		</label>
        </p>
        <p>
        <label for="_edd_order">
			<?php echo EDD()->html->text( array(
				'name'    => '_edd_order',
				'value' => $gateway_order,
				'placeholder'  => '0',
				'class' => 'small-text'
			) ); ?>
			<?php _e( 'Order', 'edd-getfaircoin' ); ?>
		</label>
	</p>
  <p>
    <label for="_edd_fiat_fee">
      <?php echo EDD()->html->text( array(
			   'name'        => '_edd_fiat_fee',
			   'value'       => $fiat_fee,
			   'placeholder' => '0',
			   'class'       => 'small-text'
	    ) ); ?>
      <?php _e( '% Fiat Fee', 'edd' ); ?>
    </label>
    <label for="_edd_faircoin_fee">
      <?php echo EDD()->html->text( array(
         'name'        => '_edd_faircoin_fee',
         'value'       => $fair_fee,
         'placeholder' => '1',
         'class'       => 'small-text'
      ) ); ?>
      <?php _e( '% Fairc. Fee', 'edd-getfaircoin' ); ?>
    </label>
  </p>
<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_gateway_choice', 30 );

function edd_gateway_metabox_field($fields){
  $fields[] = '_edd_gateway';
  $fields[] = '_edd_order';
  $fields[] = '_edd_fiat_fee';
  $fields[] = '_edd_faircoin_fee';
  return $fields;
}
add_filter('edd_metabox_fields_save', 'edd_gateway_metabox_field');


////    H O M E   O R D E R    ////

function getfaircoin_edd_downloads_query( $query, $atts ) {
    global $pagenow;
	if( 'index.php' != $pagenow ) return $query;
    $query[ 'meta_key' ] = '_edd_order';
    $query[ 'orderby' ]  = 'meta_value_num';
    $query[ 'order' ] = 'ASC';
	return $query;
}
add_filter( 'edd_downloads_query', 'getfaircoin_edd_downloads_query', 20, 2 );



////    P A Y M E N T   H I S T O R Y   /////

function payments_table_gateway_column( $columns ){
  $columns['gateway'] = __('Gateway', 'edd-getfaircoin');
  return $columns;
}
add_filter('edd_payments_table_columns', 'payments_table_gateway_column');

function payments_table_gateway_column_sortable( $columns ){
  //$columns['gateway'] = array('gateway', false);
  //$columns['status'] = array('status', false);
  return $columns;
}
//add_filter('edd_payments_table_sortable_columns', 'payments_table_gateway_column_sortable');

function getfaircoin_payments_table_views ( $views ){

    $gateways = edd_get_payment_gateways();
    $current        = isset( $_GET['gateway'] ) ? $_GET['gateway'] : '';
    $views['gateways'] = '</ul><span class="subsubsub">'.sprintf('<a href="%s"%s>%s</a> ', add_query_arg( array('gateway'=>FALSE) ), $current=='' ? ' class="current"' : '', 'All gateways');
    foreach($gateways as $key => $arr){
       $views[ $key ] = sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'gateway' => $key, 'paged' => FALSE ) ), $current === $key ? ' class="current"' : '', $key );//__('Failed', 'edd') . $failed_count );
    }
    return $views;
}
add_filter( 'edd_payments_table_views', 'getfaircoin_payments_table_views');


function gateway_filter( $query ) {
	global $pagenow;//, $post_type;
	if( 'edit.php' != $pagenow || $query->query_vars['post_type'][0] != 'edd_payment' || !isset( $_GET['gateway'] ) )//|| !is_main_query())
		return;

	$query->set('meta_key', '_edd_payment_gateway');
	$query->set('meta_value', $_GET['gateway'] );
    //$query->query['meta_key'] = '_edd_payment_gateway';
    //$query->query['meta_value'] = $_GET['gateway'];
}
add_action( 'pre_get_posts', 'gateway_filter', 9999 );

////

function getfaircoin_purchase_link_defaults( $defaults ){
  $defaults['price'] = 'no';
  return $defaults;
}
add_filter('edd_purchase_link_defaults', 'getfaircoin_purchase_link_defaults');


////    C H E C K O U T    ////

function getfaircoin_edd_unset_other_gateways( $gateway_list ) {
  $download_ids = edd_get_cart_contents();
  if ( ! $download_ids )
    return $gateway_list;
  $download_ids = wp_list_pluck( $download_ids, 'id' );

  if ( $download_ids ) {
    foreach ( $download_ids as $id ) {
      $gatoWay = get_post_meta( $id, '_edd_gateway', true);
      //echo ':'.$gatoWay.':';//print $gatoWay;
      foreach ( $gateway_list as $key => $val) {
          if ( $key !== $gatoWay) {
              if ( $gatoWay == 'coopshares_mixed' && $key == 'coopshares_transfer' ) {
                  // to let choose at checkout one or the other
              } elseif ( ($gatoWay == 'fc2invest_mixed' && $key == 'fc2invest_transfer') || ($gatoWay == 'fc2invest_mixed' && $key == 'localnode') ) {

                  // to let choose at checkout one or the other

              } elseif ( ($gatoWay == 'fairmarket_mixed' && $key == 'fairmarket_transfer') ) { //|| ($gatoWay == 'fairmarket_mixed' && $key == 'localnode') ) {

                  // to let choose at checkout one or the other

              } else {
                  unset( $gateway_list[ $key ] );
              }
          }
      }
    }
  }
  return $gateway_list;
}
add_filter( 'edd_enabled_payment_gateways', 'getfaircoin_edd_unset_other_gateways' );



function getfaircoin_coopshares_posts(){
  $urlparts = explode('/', $_SERVER['REQUEST_URI']);
  //print_r($urlparts);
  if( isset( $_SERVER['HTTP_HOST'] ) && $_SERVER['HTTP_HOST'] === 'getfaircoin.net' && !empty($urlparts[1]) && empty($_POST) && empty($_GET) &&
	 $urlparts[1] !== 'api' && $urlparts[1] !== 'wp-admin' && $urlparts[1] !== 'index.php'){ // only in the frontend

    global $wpdb;

    // crowdinvestment
    if(!isset($fc2_cats)) $fc2_cats = $wpdb->get_results("SELECT term_id FROM wp_terms WHERE slug LIKE 'fc2crowdinvest%'", OBJECT);
    $fc2_qry = '';
    foreach( $fc2_cats as $fcat){
        $fc2_qry .= $fcat->term_id.',';
    }
    $fc2_qry = rtrim($fc2_qry, ',');
    if(!isset($fc2_posts)) $fc2_posts = strlen($fc2_qry) > 1 ? $wpdb->get_results("SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id IN ($fc2_qry)", OBJECT) : false;
    $fc2_ids = array();
    if($fc2_posts){
        foreach( $fc2_posts as $fpost ) {
           $fc2_ids[] = $fpost->object_id;
        }
    }
    $fc2_str = implode($fc2_ids, ',');

    //echo "<script type='text/javascript'>  var CS_ids = [$cs_str]; var FIF_ids = [$fif_str]; var FC2_ids = [$fc2_str];  </script>"; // bumbm was "/* <![CDATA[ */" before var and " /* ]] */" after
    echo '<script type="text/javascript">  var FC2_ids = ['.$fc2_str.'];  </script>';

    // coopfunding data
    $cFdata = EDD()->session->get('cfData');
    if(isset($cFdata) && $cFdata['email']){
	echo '<script type="text/javascript">  var cF_email = "'.$cFdata['email'].'"; var cF_first = "'.$cFdata['first'].'"; var cF_last = "'.$cFdata['last'].'"; var cF_order = "'.$cFdata['order'].'"; ';
	//print_r($cFdata);
	echo "</script>";
    }


    // fairmarket
    if(!isset($fm_cats)) $fm_cats = $wpdb->get_results("SELECT term_id FROM wp_terms WHERE slug LIKE 'fairmarket%'", OBJECT);
    $fm_qry = '';
    foreach( $fm_cats as $fcat){
        $fm_qry .= $fcat->term_id.',';
    }
    $fm_qry = rtrim($fm_qry, ',');
    if(!isset($fm_posts)) $fm_posts = strlen($fm_qry) > 1 ? $wpdb->get_results("SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id IN ($fm_qry)", OBJECT) : false;
    $fm_ids = array();
    if($fm_posts){
        foreach( $fm_posts as $fpost ) {
           $fm_ids[] = $fpost->object_id;
        }
    }
    $fm_str = implode($fm_ids, ',');

    echo '<script type="text/javascript">  var FM_ids = ['.$fm_str.'];  </script>';

    // fairmarket data
    $fMdata = EDD()->session->get('fmData');
    if(isset($fMdata) && $fMdata['first']){
            echo '<script type="text/javascript">  var fM_email = "'.$fMdata['email'].'"; var fM_first = "'.$fMdata['first'].'"; var fM_last = "'.$fMdata['last'].'"; var fM_order = "'.$fMdata['order'].'"; ';
            //print_r($fMdata);
            echo "</script>";
    }

  }

  do_action( 'edd_getfaircoin_init' );

  if( !empty($_GET) && isset($_GET['order']) ){
	$cFdata = array(
		'order' => $_GET['order'],
		'email' => $_GET['email'],
		'first' => $_GET['first_name'],
		'last' => $_GET['last_name'],
		'amount' >= $_GET['amount']
	);
        /*$fMdata = array(
                  'order' => $_GET['order'],
                  'email' => isset($_GET['email']) ? $_GET['email'] : '',
                  'first' => isset($_GET['first_name']) ? $_GET['first_name'] : '',
                  'last' => isset($_GET['last_name']) ? $_GET['last_name'] : '',
                  'amount' >= isset($_GET['amount']) ? $_GET['amount'] : ''
         );*/
	edd_empty_cart();

	$_GET = array();
	$_REQUEST = array();

	EDD()->session->set( 'cfData', $cFdata );
	//EDD()->session->set( 'fmData', $fMdata );

//	http://dev.getfaircoin.net/downloads/faircoin-2-crowdinvestment/?cart_item=0&edd_action=remove

	echo "<script type='text/javascript'> var CForder_id = ".$cFdata['order']."; "."  </script>";
        //echo "<script type='text/javascript'> var FMorder_id = ".$fMdata['order']."; "."  </script>";
	//wp_redirect('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REDIRECT_URL']);
  }
}
//add_action('init', 'getfaircoin_coopshares_posts'); // now fires from marketify-child/header.php

function getfaircoin_only_one_item_on_cart($download_id) {
    //echo 'CART CONTENTS: ';
    //print_r($cart_contents);
    //$cart_contents = edd_get_cart_contents();
    //if($cart_contents) return false;
    //else return $download_id;
    edd_empty_cart();
}
add_action('edd_pre_add_to_cart', 'getfaircoin_only_one_item_on_cart');


////  N E W   U S E R   F I E L D S  ////

/**
* Display fairaddress number field at checkout
* Add more here if you need to
*/
function getfaircoin_edd_display_checkout_fields() { // get user's fairaddress number if they already have one stored
  if ( is_user_logged_in() ) {
    $user_id = get_current_user_id();
    $fairaddress = get_the_author_meta( '_edd_user_fairaddress', $user_id );
    $fairsaving = get_the_author_meta( '_edd_user_fairsaving', $user_id );
  }
  $fairaddress = isset( $fairaddress ) ? esc_attr( $fairaddress ) : '';
  $fairsaving = isset( $fairsaving ) ? esc_attr( $fairsaving ) : '0';
  ?>
  <p id="edd-fairsaving-wrap">
    <label class="edd-label" for="edd_fairsaving">
      <?php echo _e('FairSaving Service', 'edd-getfaircoin'); ?>
    </label>
    <span class="edd-description">
      <?php echo _e('Check this if you\'re not managing your own wallet yet, and want the FairSaving service team to take care of it.', 'edd-getfaircoin'); ?>
    </span>
    <input class="edd-checkbox" type="checkbox" name="edd_fairsaving" id="edd-fairsaving" value="<?php echo $fairsaving; ?>" onclick="fairsaving_hide_fairaddress(this)" />
    <br />
  </p>
  <p id="edd-fairaddress-wrap">
    <label class="edd-label" for="edd_fairaddress">
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
  $required_fields['edd_fairaddress'] = array(
    'error_id' => 'invalid_fairaddress',
    'error_message' => __('Please enter a valid Faircoin Address', 'edd-getfaircoin')
  );
  return $required_fields;
}
add_filter( 'edd_purchase_form_required_fields', 'getfaircoin_edd_required_checkout_fields' );


/**
* Set error if fairaddress number field is empty
* You can do additional error checking here if required
*/
function getfaircoin_edd_validate_checkout_fields( $valid_data, $data ) {
  if( !isset($data['edd_fairsaving']) ) $data['edd_fairsaving'] = '0';
  if ( is_user_logged_in() ) {
    $user_id = get_current_user_id();
    update_user_meta( $user_id, '_edd_user_fairsaving', sanitize_text_field($data['edd_fairsaving']) );
  } else {

  }
  $fairsaving = sanitize_text_field($data['edd_fairsaving']);
  if( $fairsaving === '0') {
    if ( empty( $data['edd_fairaddress'] ) ) {
      edd_set_error( 'invalid_fairaddress', __('Please enter your Faircoin Address.', 'edd-getfaircoin') );
    } else if ( strlen( $data['edd_fairaddress'] ) != 34 ) {
      edd_set_error( 'invalid_fairaddress', __('Your Faircoin Address must have 34 digits', 'edd-getfaircoin') );
    } else {

    }
  } else if( $fairsaving == '1'){
    //
  } else {
    edd_set_error( 'invalid_fairaddress', 'FAIRSAVING = '+$fairsaving+' ?' );
  }
}
add_action( 'edd_checkout_error_checks', 'getfaircoin_edd_validate_checkout_fields', 10, 2 );


/**
* Store the custom field data into EDD's payment meta
*/
function getfaircoin_edd_store_custom_fields( $payment_meta ) {
  $payment_meta['fairsaving'] = isset( $_POST['edd_fairsaving'] ) ? sanitize_text_field( $_POST['edd_fairsaving'] ) : '0';
  $payment_meta['fairaddress'] = isset( $_POST['edd_fairaddress'] ) ? sanitize_text_field( $_POST['edd_fairaddress'] ) : '';
  return $payment_meta;
}
add_filter( 'edd_payment_meta', 'getfaircoin_edd_store_custom_fields');


/**
* Add the fairaddress number to the "View Order Details" page
*/
function getfaircoin_edd_view_order_details( $payment_meta, $user_info ) {
  $fairaddress = isset( $payment_meta['fairaddress'] ) ? $payment_meta['fairaddress'] : 'none';
  $fairsaving = isset( $payment_meta['fairsaving'] ) ? $payment_meta['fairsaving'] : '0';
  ?>
  <div class="column-container">
    <div class="column">
      <strong><?php echo _e('FairSaving: ', 'edd-getfaircoin'); ?></strong>
      <input type="text" name="edd_fairsaving" value="<?php esc_attr_e( $fairsaving ); ?>" class="small-text" />
      <p class="description"><?php _e( 'Customer FairSaving choice', 'edd-getfaircoin' ); ?></p>
    </div>
    <div class="column">
      <strong><?php echo _e('FaircoinAddress: ', 'edd-getfaircoin'); ?></strong>
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
  $payment_meta['fairsaving'] = isset( $_POST['edd_fairsaving'] ) ? $_POST['edd_fairsaving'] : false;
  // update the payment meta with the new array
  update_post_meta( $payment_id, '_edd_payment_meta', $payment_meta );
}
add_action( 'edd_updated_edited_purchase', 'getfaircoin_edd_updated_edited_purchase' );


/**
* Add a {fairaddress} tag for use in either the purchase receipt email or admin notification emails
*/
if ( function_exists( 'edd_add_email_tag' ) ) {
  edd_add_email_tag( 'fairaddress', 'Customer\'s Faircoin Address', 'getfaircoin_edd_email_tag_fairaddress' );
  edd_add_email_tag( 'fairsaving', 'Customer\'s FairSaving Choice', 'getfaircoin_edd_email_tag_fairsaving' );
  edd_add_email_tag( 'fM_order', 'Customer\'s FairMarket payment id', 'getfaircoin_edd_email_tag_fairmarket_id' );
}


/**
* The {fairaddress} email tag
*/
function getfaircoin_edd_email_tag_fairaddress( $payment_id ) {
  $payment_data = edd_get_payment_meta( $payment_id );
  return $payment_data['fairaddress'];
}

/**
* The {fairsaving} email tag
*/
function getfaircoin_edd_email_tag_fairsaving( $payment_id ) {
  $payment_data = edd_get_payment_meta( $payment_id );
  if($payment_data['fairsaving'] == '1') {
    return __('FairSaving Active', 'edd-getfaircoin'); //$payment_data['fairsaving'];
  } else {
    return '';
  }
}

/**
* The {fairmarket_id} email tag
*/
function getfaircoin_edd_email_tag_fairmarket_id( $payment_id ) {
  $payment_data = edd_get_payment_meta( $payment_id );
  if(isset($payment_data['fM_order'])) {
    return urlencode($payment_data['fM_order']);
  } else {
    return '';
  }
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
  update_user_meta( $user_id, '_edd_user_fairsaving', $_POST['edd_fairsaving'] );
}
add_action( 'edd_complete_purchase', 'getfaircoin_edd_store_usermeta' );


/**
* Save the field when the values are changed on the user's WP profile page
*/
function getfaircoin_edd_save_extra_profile_fields( $user_id ) {
  if ( ! current_user_can( 'edit_user', $user_id ) )
    return false;
  update_user_meta( $user_id, '_edd_user_fairaddress', $_POST['_edd_user_fairaddress'] );
  update_user_meta( $user_id, '_edd_user_fairsaving', $_POST['_edd_user_fairsaving'] );
}
add_action( 'personal_options_update', 'getfaircoin_edd_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'getfaircoin_edd_save_extra_profile_fields' );


/**
* Save the field when the value is changed on the EDD profile editor
*/
function getfaircoin_edd_pre_update_user_profile( $user_id, $userdata ) {
  $fairaddress = isset( $_POST['edd_fairaddress'] ) ? $_POST['edd_fairaddress'] : '';
  $fairsaving = isset( $_POST['edd_fairsaving'] ) ? $_POST['edd_fairsaving'] : '0';
  // Make sure user enters a fairaddress number
  if ( ! $fairsaving && ! $fairaddress ) {
    edd_set_error( 'fairaddress_required', __( 'Please enter a Faircoin Address', 'edd-getfaircoin' ) );
  }
  // update fairaddress number
  update_user_meta( $user_id, '_edd_user_fairaddress', $fairaddress );
  update_user_meta( $user_id, '_edd_user_fairsaving', $fairsaving );
}
add_action( 'edd_pre_update_user_profile', 'getfaircoin_edd_pre_update_user_profile', 10, 2 );


/**
* Add the FairAddress to the "Contact Info" section on the user's WP profile page
*/
function getfaircoin_user_contactmethods( $methods, $user ) {
  $methods['_edd_user_fairaddress'] = 'Faircoin Address';
  $methods['_edd_user_fairsaving'] = 'FairSaving Service';
  return $methods;
}
add_filter( 'user_contactmethods', 'getfaircoin_user_contactmethods', 10, 2 );



//  AUTO CALCULATE FAIRCOIN PRICE   (not used!)  //
 /*
function json_validate($string) {
        if (is_string($string)) {
            @json_decode($string);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
}
function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = utf8ize($v);
            }
        } else if (is_string ($d)) {
            return utf8_encode($d);
        }
        return $d;
}

function mjson_decode($json) {
	return json_decode(removeTrailingCommas(utf8_encode($json)));
}
function removeTrailingCommas($json) {
        $json=preg_replace('/,\s*([\]}])/m', '$1', $json);
        return $json;
}

function getfaircoin_price_btc() { // to return the multiplier FAIR-BTC... TODO
  // perhaps i do this in ajax? js...
  if(empty($bittrex_btc_fair)){
    $bitObj = json_decode( file_get_contents( 'https://bittrex.com/api/v1.1/public/getticker?market=btc-fair') );
    if($bitObj->success){
      $bittrex_btc_fair = $bitObj->result->Ask;

        //{
        //	"success" : true,
        //	"message" : "",
        //	"result" : {
        //		"Bid" : 2.05670368,
        //		"Ask" : 3.35579531,
        //		"Last" : 3.35579531
        //	}
        //}

      $coinbObj = json_decode( file_get_contents( 'https://api.coinbase.com/v1/prices/spot_rate?currency=EUR' ) );
      if( $coinbObj->currency == 'EUR' ) {
	       $coinb_btc_eur = $coinbObj->amount;
      } else {
	       $coinb_btc_eur = '?';
      }

      if(empty($vaultex_btc_fair)){
	       $vauStr = '?';//file_get_contents( 'https://api.vaultex.io/v1/market/stats/FAIR/BTC' );
         $vauObj = '';//json_decode( $vauStr );
	       if($vauObj){
            $vaultex_btc_fair = $vauStr; //->'24hhigh';
         } else {
	          $vaultex_btc_fair = $vauObj;
	       }
          //[{
          //   "market_id":"25",
          //   "code":"AUR",
          //   "exchange":"BTC",
          //   "last_price":"0.04600001",
          //   "yesterday_price":"0.04300000",
          //   "change":"+6.98",
          //   "24hhigh":"0.04980000",
          //   "24hlow":"0.04000050",
          //   "24hvol":"21.737"
          //   "top_bid":"0.04590000"
          //   "top_ask":"0.04600005"
          //}]
      }

      if(empty($alcurex_btc_fair) or !$alcurex_btc_fair){
	       $alcStr = file_get_contents( 'https://alcurex.org/api/market.php?pair=fair_btc&price=sell');
         $alcObj = json_decode(utf8ize($alcStr));
	       if (json_last_error() === JSON_ERROR_NONE) {
        	//$arr = (array)$alcObj->fair_btc;
		        $alcurex_btc_fair = $alcStr;
		        //foreach($alcObj->fair_btc as $obj){
		        //	$alcurex_btc_fair = $obj->price;
	          //	break;
		        //}
      	 } else {
      	    $alcurex_btc_fair = '? (php '.phpversion().') err:'.json_last_error();
      		  //.' val:'.print_r($alcStr, true);
      	 }

         //{"mrc2_ltc": [
         // {"pair": "MRC2_LTC","time": "2014-12-30 22:19:38","price": 0.00000011,"volume": 1119923.93618182,"type": "Buy"},
         // {"pair": "MRC2_LTC","time": "2014-12-30 22:19:27","price": 0.00000010,"volume": 4000000.00000000,"type": "Buy"},
         // ...
         // ]
         //}
      }

      $euro_out = 1 / floatval($coinb_btc_eur);
      $fair_out = $euro_out / $bittrex_btc_fair;
      $btc_fair = 1 / $bittrex_btc_fair;
      $chk_fair_out = $btc_fair * $euro_out;

      $fair_btc = 'btc/eur: '.number_format($euro_out, 8, ',', '.')
              		.'<br />fair/btc: '.number_format($btc_fair, 8, ',', '.')
              		.'<br /><b>1eur = '.number_format($fair_out, 8, ',', '.').' faircoins'
              		//.'<br />chk: '.number_format($chk_fair_out, 8, ',', '.')
                    		.'</b><br /> bittrex (btc/fair): '.number_format( $bittrex_btc_fair, 8, ",", "." )
              		//.'<br /> vaultex: '.$vaultex_btc_fair //.number_format( $vaultex_btc_fair, 8, ",", ".")
              		//.'<br /> alcurex: '.$alcurex_btc_fair //number_format( $alcurex_btc_fair, 8, ",", ".")
              		.'<br /> coinbase (eur/btc): '.$coinb_btc_eur;
      // end if($bitObj->success)
    } else {
      $fair_btc = $bitObj->error;
    }
  }
  //$fair_btc = 0.00003;
  return $fair_btc;
}

function getfaircoin_show_rate( $download_id ) {
  //echo '<p>'.getfaircoin_price_btc().'</p>';
}
//add_filter( 'edd_purchase_link_top', 'getfaircoin_show_rate' );
*/
