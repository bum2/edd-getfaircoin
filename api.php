<?php

require_once('../../../wp-config.php');

function fair_price(){
  $parts = explode('/', $_SERVER['REQUEST_URI']);
  if($parts[1] == 'api'){ //$parts[2] == 'ticker' || $parts[2] == 'fair-eur' || $parts[2] == 'eur-fair'){
    global $edd_options;
    if(!isset($edd_options)){
      $price = number_format(1/(get_option('faircoin_price')*1), 4, ',', '')*1;
    } else {
      $price = number_format(1/($edd_options['faircoin_price']*1), 4, '.', '')*1;
    }
    //$price = $edd_options['faircoin_price'];
    //global $wp_query;
    //$price = get_option('faircoin_price');

    if($parts[2] == 'fair-eur'){

      header('Content-type: application/json');
      echo json_encode(array('fair-eur'=>$price*1));

    } else if($parts[2] == 'eur-fair'){

      header('Content-type: application/json');
      echo json_encode(array('eur-fair'=>number_format((1/$price), 8, '.', '')*1));

    } else {
      //$edd_currencies = edd_get_currencies();
      $exchange_rates  = edd_currency_get_exchange_rates();
      $eur2usd = $exchange_rates['EUR'];
      $price_USD = $price * $eur2usd;

      if($parts[2] == 'ticker'){


        $price_GBP = $price_USD / $exchange_rates['GBP'];
        $price_CHF = $price_USD / $exchange_rates['CHF'];
        $price_PLN = $price_USD / $exchange_rates['PLN'];
        $price_MXN = $price_USD / $exchange_rates['MXN'];

	$price_DKK = $price_USD / $exchange_rates['DKK'];
	$price_NOK = $price_USD / $exchange_rates['NOK'];
	$price_SEK = $price_USD / $exchange_rates['SEK'];

	$price_SYP = $price_USD / $exchange_rates['SYP'];

        header('Content-type: application/json');
        echo json_encode(
          array(
              'EUR' => array('last' => number_format((1/$price), 4, '.', '')*1),
              'USD' => array('last' => number_format((1/$price_USD), 4, '.', '')*1),
              'GBP' => array('last' => number_format((1/$price_GBP), 4, '.', '')*1),
              'CHF' => array('last' => number_format((1/$price_CHF), 4, '.', '')*1),
              'PLN' => array('last' => number_format((1/$price_PLN), 4, '.', '')*1),
              'MXN' => array('last' => number_format((1/$price_MXN), 4, '.', '')*1),

	      'DKK' => array('last' => number_format((1/$price_DKK), 4, '.', '')*1),
	      'NOK' => array('last' => number_format((1/$price_NOK), 4, '.', '')*1),
	      'SEK' => array('last' => number_format((1/$price_SEK), 4, '.', '')*1),
	      'SYP' => array('last' => number_format((1/$price_SYP), 4, '.', '')*1)
          )
        );
      } else if($parts[2] == 'fair-pln'){
        header('Content-type: application/json');
        $price_PLN = $price_USD / $exchange_rates['PLN'];
        echo json_encode(array('fair-pln'=>number_format($price_PLN*1, 4, '.', '')*1));
      } else if($parts[2] == 'pln-fair'){
        header('Content-type: application/json');
        $price_PLN = $price_USD / $exchange_rates['PLN'];
        echo json_encode(array('pln-fair'=>number_format((1/$price_PLN), 8, '.', '')*1));
      } else if($parts[2] == 'fair-mxn'){
        header('Content-type: application/json');
        $price_MXN = $price_USD / $exchange_rates['MXN'];
        echo json_encode(array('fair-mxn'=>number_format($price_MXN*1, 4, '.', '')*1));
      } else if($parts[2] == 'mxn-fair'){
        header('Content-type: application/json');
        $price_MXN = $price_USD / $exchange_rates['MXN'];
        echo json_encode(array('mxn-fair'=>number_format((1/$price_MXN), 8, '.', '')*1));
      } else {
        echo 'API: the url var \''.$parts[2].'\' is not understood (try: api/ticker, api/fair-eur, api/eur-fair, etc).';
      }
    }

  } else {
    echo 'No api slug found! (try https://getfaircoin.net/api/ticker)';
  }
}
fair_price();

?>
