<?php
/**
* Plugin Name: EDD GetFaircoin.net Fields and Rates
* Plugin URI: https://getfaircoin.net/
* Description: This plugin adds user FAIR address and FairService checkbox fields in the checkout, shows actual faircoin price at homepage and aprox faircoin as user enters his fiat amount, and now also shows a reference in many other fiat currencies other than euro. Requires edd-currency-converter and edd-custom-prices.
* Author: Bumbum
* Version: 0.3
* Author URI: https://github.com/bum2/
*/

### Version
define( 'EDD_GETFAIRCOIN_VERSION', 0.3 );


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
		if(@file_exists(get_stylesheet_directory().'/polls-css-rtl.css')) {
			wp_enqueue_style('edd-getfaircoin-rtl', get_stylesheet_directory_uri().'/getfaircoin-css-rtl.css', false, EDD_GETFAIRCOIN_VERSION, 'all');
		} else {
			wp_enqueue_style('edd-getfaircoin-rtl', plugins_url('edd-getfaircoin/getfaircoin-css-rtl.css'), false, EDD_GETFAIRCOIN_VERSION, 'all');
		}
	}

  wp_enqueue_script('edd-getfaircoin', plugins_url('edd-getfaircoin/getfaircoin-js.dev.js'), array('jquery'), EDD_GETFAIRCOIN_VERSION, true); // bumbum .dev.
	//wp_localize_script('edd-getfaircoin', 'getfaircoinL10n', array(
	//	'ajax_url' => admin_url('admin-ajax.php'),
	//	'text_wait' => __('Your last request is still being processed. Please wait a while ...', 'edd-getfaircoin'),
		//'text_valid' => __('Please choose a valid poll answer.', 'fair-polls'),
		//'text_multiple' => __('Maximum number of choices allowed: ', 'fair-polls'),
		//'show_loading' => intval($poll_ajax_style['loading']),
		//'show_fading' => intval($poll_ajax_style['fading'])
	//));
}



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
      /*
        {
        	"success" : true,
        	"message" : "",
        	"result" : {
        		"Bid" : 2.05670368,
        		"Ask" : 3.35579531,
        		"Last" : 3.35579531
        	}
        }
      */
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
	       /*
          [{
             "market_id":"25",
             "code":"AUR",
             "exchange":"BTC",
             "last_price":"0.04600001",
             "yesterday_price":"0.04300000",
             "change":"+6.98",
             "24hhigh":"0.04980000",
             "24hlow":"0.04000050",
             "24hvol":"21.737"
             "top_bid":"0.04590000"
             "top_ask":"0.04600005"
          }]
        */
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
         /*
         {"mrc2_ltc": [
          {"pair": "MRC2_LTC","time": "2014-12-30 22:19:38","price": 0.00000011,"volume": 1119923.93618182,"type": "Buy"},
          {"pair": "MRC2_LTC","time": "2014-12-30 22:19:27","price": 0.00000010,"volume": 4000000.00000000,"type": "Buy"},
          ...
          ]
         }
        */
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
add_filter( 'edd_purchase_link_top', 'getfaircoin_show_rate' );


////

function getfaircoin_price($price){
  global $edd_options;
  if( $price == 0 ) {
    //echo '<span class="edd_price">'.number_format($edd_options['faircoin_price'], 0, '.', ',').' faircoins =</span>';
    $price = 1;
    return '1€ = '.number_format($edd_options['faircoin_price'], 0, '.', ',').' fair';
  } //else if( count( split(' ', $price) ) > 1) {
    //echo "count( split(' ', $price) ) > 1 !! ".$price;
    //return 1;//edd_format_amount( $price );//;
  //} else {
  //  return count( split(' ', $price) );//$price;//edd_format_amount($edd_options['faircoin_price']).' faircoins = 1';
  //}
}
add_filter( 'edd_download_price', 'getfaircoin_price', 10);

function getfaircoin_currency_filter($price){
  //print count( split(' ', $price) );
  if( count( split(' ', $price) ) > 1){//return number_format(1, 0, '.', ',');
    $price = str_replace('&euro;', '', $price);
    //$price = str_replace('eur', '', $price);
    //print($price);
  }
  return $price;
}
add_filter( 'edd_eur_currency_filter_after', 'getfaircoin_currency_filter' );

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
  return false;
}
add_filter( 'edd_item_quantities_enabled', 'item_quantities_none' );

function getfaircoin_actual_price() {
  return array(
				'test_mode' => array(
					'id' => 'test_mode',
					'name' => __( 'Test Mode', 'edd' ),
					'desc' => __( 'While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.', 'edd' ),
					'type' => 'checkbox'
				),
				'purchase_page' => array(
					'id' => 'purchase_page',
					'name' => __( 'Checkout Page', 'edd' ),
					'desc' => __( 'This is the checkout page where buyers will complete their purchases. The [download_checkout] short code must be on this page.', 'edd' ),
					'type' => 'select',
					'options' => edd_get_pages()
				),
				'success_page' => array(
					'id' => 'success_page',
					'name' => __( 'Success Page', 'edd' ),
					'desc' => __( 'This is the page buyers are sent to after completing their purchases. The [edd_receipt] short code should be on this page.', 'edd' ),
					'type' => 'select',
					'options' => edd_get_pages()
				),
				'failure_page' => array(
					'id' => 'failure_page',
					'name' => __( 'Failed Transaction Page', 'edd' ),
					'desc' => __( 'This is the page buyers are sent to if their transaction is cancelled or fails', 'edd' ),
					'type' => 'select',
					'options' => edd_get_pages()
				),////
        'faircoin_settings' => array(
          'id' => 'faircoin_settings',
          'name' => '<strong>' . __( 'FAIRCOIN Settings', 'edd' ) . '</strong>',
          'desc' => __( 'Set actual Faircoin options', 'edd' ),
          'type' => 'header'
        ),
        'faircoin_price' => array(
          'id' => 'faircoin_price',
          'name' => __( 'Faircoin Price', 'edd' ),
          'desc' => __( 'Put manually the actual Faircoin price from your base currency: 1€ = n Fair\'s.', 'edd' ),
          'type' => 'text',
          'size' => 'medium',
          'std' => '100.01'
        ),////
				'currency_settings' => array(
					'id' => 'currency_settings',
					'name' => '<strong>' . __( 'Currency Settings', 'edd' ) . '</strong>',
					'desc' => __( 'Configure the currency options', 'edd' ),
					'type' => 'header'
				),
				'currency' => array(
					'id' => 'currency',
					'name' => __( 'Currency', 'edd' ),
					'desc' => __( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'edd' ),
					'type' => 'select',
					'options' => edd_get_currencies()
				),
				'currency_position' => array(
					'id' => 'currency_position',
					'name' => __( 'Currency Position', 'edd' ),
					'desc' => __( 'Choose the location of the currency sign.', 'edd' ),
					'type' => 'select',
					'options' => array(
						'before' => __( 'Before - $10', 'edd' ),
						'after' => __( 'After - 10$', 'edd' )
					)
				),
				'thousands_separator' => array(
					'id' => 'thousands_separator',
					'name' => __( 'Thousands Separator', 'edd' ),
					'desc' => __( 'The symbol (usually , or .) to separate thousands', 'edd' ),
					'type' => 'text',
					'size' => 'small',
					'std' => ','
				),
				'decimal_separator' => array(
					'id' => 'decimal_separator',
					'name' => __( 'Decimal Separator', 'edd' ),
					'desc' => __( 'The symbol (usually , or .) to separate decimal points', 'edd' ),
					'type' => 'text',
					'size' => 'small',
					'std' => '.'
				),
				'api_settings' => array(
					'id' => 'api_settings',
					'name' => '<strong>' . __( 'API Settings', 'edd' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'api_allow_user_keys' => array(
					'id' => 'api_allow_user_keys',
					'name' => __( 'Allow User Keys', 'edd' ),
					'desc' => __( 'Check this box to allow all users to generate API keys. Users with the \'manage_shop_settings\' capability are always allowed to generate keys.', 'edd' ),
					'type' => 'checkbox'
				),
				'tracking_settings' => array(
					'id' => 'tracking_settings',
					'name' => '<strong>' . __( 'Tracking Settings', 'edd' ) . '</strong>',
					'desc' => '',
					'type' => 'header'
				),
				'allow_tracking' => array(
					'id' => 'allow_tracking',
					'name' => __( 'Allow Usage Tracking?', 'edd' ),
					'desc' => __( 'Allow Easy Digital Downloads to anonymously track how this plugin is used and help us make the plugin better. Opt-in and receive a 20% discount code for any purchase from the <a href="https://easydigitaldownloads.com/extensions" target="_blank">Easy Digital Downloads store</a>. Your discount code will be emailed to you.', 'edd' ),
					'type' => 'checkbox'
				),
				'uninstall_on_delete' => array(
					'id' => 'uninstall_on_delete',
					'name' => __( 'Remove Data on Uninstall?', 'edd' ),
					'desc' => __( 'Check this box if you would like EDD to completely remove all of its data when the plugin is deleted.', 'edd' ),
					'type' => 'checkbox'
				)
		);
}
add_filter( 'edd_settings_general', 'getfaircoin_actual_price' );

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
  $fair_fee = get_post_meta( $post_id, '_edd_faircoin_fee', true );
?>
	<p><strong><?php _e( 'Gateway Options:', 'edd' ); ?></strong></p>
	<p>
		<label for="_edd_gateway">
			<?php echo EDD()->html->select( array(
				'name'    => '_edd_gateway',
				'options' => $gateway_options,
				'show_option_all'  => null,
				'show_option_none' => null,
				'selected' => $post_gate
			) ); ?>
			<?php _e( 'Specific gateway', 'edd' ); ?>
		</label>
	</p>
  <p>
    <label for="_edd_fiat_fee">
      <?php echo EDD()->html->text( array(
			   'name'        => '_edd_fiat_fee',
			   'value'       => $fiat_fee,
			   'placeholder' => __( '0', 'edd' ),
			   'class'       => 'small-text'
	    ) ); ?>
      <?php _e( '% Fiat Fee', 'edd' ); ?>
    </label>
    <label for="_edd_faircoin_fee">
      <?php echo EDD()->html->text( array(
         'name'        => '_edd_faircoin_fee',
         'value'       => $fair_fee,
         'placeholder' => __( '1', 'edd' ),
         'class'       => 'small-text'
      ) ); ?>
      <?php _e( '% Fairc. Fee', 'edd' ); ?>
    </label>
  </p>
<?php
}
add_action( 'edd_meta_box_settings_fields', 'edd_render_gateway_choice', 30 );

function edd_gateway_metabox_field($fields){
  $fields[] = '_edd_gateway';
  $fields[] = '_edd_fiat_fee';
  $fields[] = '_edd_faircoin_fee';
  return $fields;
}
add_filter('edd_metabox_fields_save', 'edd_gateway_metabox_field');

////


function payments_table_gateway_column( $columns ){
  $columns['gateway'] = __('Gateway', 'edd');
  return $columns;
}
add_filter('edd_payments_table_columns', 'payments_table_gateway_column');

function payments_table_gateway_column_sortable( $columns ){
  $columns['gateway'] = array('gateway', false);
  $columns['status'] = array('status', false);
  return $columns;
}
add_filter('edd_payments_table_sortable_columns', 'payments_table_gateway_column_sortable');

////

function getfaircoin_purchase_link_defaults( $defaults ){
  $defaults['price'] = 'no';
  return $defaults;
}
add_filter('edd_purchase_link_defaults', 'getfaircoin_purchase_link_defaults');

////

function getfaircoin_edd_unset_other_gateways( $gateway_list ) {
  $download_ids = edd_get_cart_contents();
  if ( ! $download_ids )
    return $gateway_list;
  $download_ids = wp_list_pluck( $download_ids, 'id' );

  if ( $download_ids ) {
    foreach ( $download_ids as $id ) {
      if ( has_term( 'Paypal', 'download_category', $id ) ) {
	      //var_dump($gateway_list);
	      $gateway_manual = $gateway_list['transfer'];
        unset( $gateway_list['transfer'] );
	      unset( $gateway_list['localnode'] );
      }
      if ( has_term( 'Transfer', 'download_category', $id ) ) {
	      $gateway_paypal = $gateway_list['paypal'];
        unset( $gateway_list['paypal'] );
	      unset( $gateway_list['localnode'] );
      }
      if ( has_term( 'LocalNode', 'download_category', $id ) ) {
	      $gateway_localnode = $gateway_list['localnode'];
        unset( $gateway_list['paypal'] );
	      unset( $gateway_list['transfer'] );
      }
    }
  }
  return $gateway_list;
}
add_filter( 'edd_enabled_payment_gateways', 'getfaircoin_edd_unset_other_gateways' );



////  N E W   F I E L D S  ////

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
    update_user_meta( $user_id, '_edd_user_fairsaving', $data['edd_fairsaving'] );
  } else {

  }
  $fairsaving = $data['edd_fairsaving'];
  if( $fairsaving === '0') {
    if ( empty( $data['edd_fairaddress'] ) ) {
      edd_set_error( 'invalid_fairaddress', __('Please enter your Faircoin Address.', 'edd-getfaircoin') );
    } else if ( strlen( $data['edd_fairaddress'] ) != 34 ) {
      edd_set_error( 'invalid_fairaddress', __('Your Faircoin Address must have 34 digits', 'edd-getfaircoin') );
    }
  } else if( $fairsaving == '1'){
    //
  } else {
    edd_set_error( 'invalid_fairaddress', $fairsaving+'' );
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
  update_user_meta( $user_id, '_edd_user_fairaddress', $_POST['fairaddress'] );
  update_user_meta( $user_id, '_edd_user_fairsaving', $_POST['fairsaving'] );
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
