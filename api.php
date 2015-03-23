<?php

require_once('../../../wp-config.php');

function fair_price(){
  $parts = explode('/', $_SERVER['REQUEST_URI']);
  if($parts[2] == 'fair-eur' || $parts[2] == 'eur-fair'){
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
      echo json_encode(array('fair-eur'=>$price));

    } else if($parts[2] == 'eur-fair'){

      header('Content-type: application/json');
      echo json_encode(array('eur-fair'=>number_format((1/$price), 8, '.', '')*1));

    }
  } else {
    echo 'No var requested! (try api/fair-eur or api/eur-fair) the var '.$parts[2].' is not understood.';
  }
}
fair_price();

?>
