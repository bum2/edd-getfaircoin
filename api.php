<?php

require_once('../../../wp-config.php');

function fair_price(){
  $parts = explode('/', $_SERVER['REQUEST_URI']);
  if($parts[2] == 'ticker' || $parts[2] == 'fair-eur' || $parts[2] == 'eur-fair'){
    global $edd_options;
    if(!isset($edd_options)){
      $price = get_option('faircoin_price')*1;
    } else {
      $price = $edd_options['faircoin_price']*1;
    }
    //$price = $edd_options['faircoin_price'];
    //global $wp_query;
    //$price = get_option('faircoin_price');

    if($parts[2] == 'fair-eur'){

      header('Content-type: application/json');
      echo json_encode(array('eur-fair'=>$price*1));

    } else if($parts[2] == 'eur-fair'){

      header('Content-type: application/json');
      echo json_encode(array('eur-fair'=>number_format((1/$price), 8, '.', '')*1));

    } else if($parts[2] == 'ticker'){

      //$edd_currencies = edd_get_currencies();
      $exchange_rates  = edd_currency_get_exchange_rates();
      $eur2usd = $exchange_rates['EUR'];
      $price_USD = $price * $eur2usd;
      $price_GBP = $price_USD / $exchange_rates['GBP'];
      $price_CHF = $price_USD / $exchange_rates['CHF'];

      header('Content-type: application/json');
      echo json_encode(
        array(
            'EUR' => array('last' => number_format((1/$price), 8, '.', '')*1),
            'USD' => array('last' => number_format((1/$price_USD), 8, '.', '')*1),
            'GBP' => array('last' => number_format((1/$price_GBP), 8, '.', '')*1),
            'CHF' => array('last' => number_format((1/$price_CHF), 8, '.', '')*1),
        )
      );
    }

  } else {
    echo 'No var requested! (try api/fair-eur or api/eur-fair) the var '.$parts[2].' is not understood.';
  }
}
fair_price();

?>
