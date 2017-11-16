if( typeof curr_rate == 'undefined') var curr_rate = false;
if( typeof fair_eur == 'undefined') var fair_eur = false;

var QueryString = function () {
  // This function is anonymous, is executed immediately and
  // the return value is assigned to QueryString!
  var query_string = {};
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
        // If first entry with this name
    if (typeof query_string[pair[0]] === "undefined") {
      query_string[pair[0]] = decodeURIComponent(pair[1]);
        // If second entry with this name
    } else if (typeof query_string[pair[0]] === "string") {
      var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
      query_string[pair[0]] = arr;
        // If third or later entry with this name
    } else {
      query_string[pair[0]].push(decodeURIComponent(pair[1]));
    }
  }
    return query_string;
}();

if(typeof(CFdata) != "undefined" && CFdata !== null) {
  alert('init:'+CFdata);
} else {
  var CFdata = {};
}

if(typeof(FMdata) != "undefined" && FMdata !== null) {
  alert('init:'+FMdata);
} else {
  var FMdata = {};
}

if(typeof(OCPdata) != "undefined" && OCPdata !== null) {
  alert('init:'+OCPdata);
} else {
  var OCPdata = {};
}

function fairsaving_hide_fairaddress(input){
  jQuery(document).ready(function($) {
    address34 = '0000000000000000000000000000000000';
    if( $('input#edd-fairsaving').is(':checked') ) {//val() == '0' ) { //alert($('input#edd-fairsaving').val());//is(':checked') ) {
      $('input#edd-fairsaving').val(1).attr('checked','checked');
      $('#edd-fairaddress-wrap').hide().find('input').val( address34 );
    } else {
      $('input#edd-fairsaving').val(0).removeAttr('checked');
      $('#edd-fairaddress-wrap').show();
      if( $('#edd-fairaddress-wrap').find('input').val() == address34 ){
        $('#edd-fairaddress-wrap').find('input').val('');
      }
    };
  });
}

function coopfunding_fill_fields(){
  jQuery(document).ready(function($) {
    if(typeof cF_email != 'undefined' && typeof cF_first != 'undefined'){
        $('input#edd-email').val(cF_email);
        $('input#edd-first').val(cF_first);
        $('input#edd-last').val(cF_last);
    };
  });
}

function fairmarket_fill_fields(){
  jQuery(document).ready(function($) {
    if(typeof fM_email != 'undefined' && typeof fM_first != 'undefined'){
        $('input#edd-email').val(fM_email);
        $('input#edd-first').val(fM_first);
        $('input#edd-last').val(fM_last);
    };
  });
}

function ocpfairbill_fill_fields(){
  jQuery(document).ready(function($) {
    if(typeof oCP_email != 'undefined' && typeof oCP_first != 'undefined'){
        $('input#edd-email').val(oCP_email);
        $('input#edd-first').val(oCP_first);
        $('input#edd-last').val(oCP_last);
    };
  });
}

jQuery(document).ready(function($) {
  if( $('input#edd-fairsaving').val() == '1'){
    $('input#edd-fairsaving').attr('checked','checked');
  } else {
    $('input#edd-fairsaving').val(0).removeAttr('checked');
  }
  fairsaving_hide_fairaddress($('input#edd-fairsaving'));

  var query = window.location.search.substring(1);

  coopfunding_fill_fields();
  fairmarket_fill_fields();
  ocpfairbill_fill_fields();

  //// Coopshares hide FairAddress and FairSaving
  if( $('label.coopshares-mixed').length > 0 || $('label.fairmarket-mixed').length > 0 ) {
    $('input#edd-fairsaving').attr('checked','checked').val('1');
    $('#edd-fairsaving-wrap').hide();
  }
  // bind a gateway_changed event, needs to add .trigger('gateway_changed') in edd's assets/js/edd-ajax.js last lines, after inserting html content to the div // no needed, now is 'edd_gateway_loaded'
  $('body').bind('edd_gateway_loaded', function(pay_mode){
    if( $('label.coopshares-mixed').length > 0 ) {
      $('input#edd-fairsaving').attr('checked','checked').val('1');
      fairsaving_hide_fairaddress();
      $('#edd-fairsaving-wrap').hide();
    };

    if( $('label.fc2invest-mixed').length > 0 ) {
      //$('input#edd-fairsaving').attr('checked','checked').val('1');
      fairsaving_hide_fairaddress();
      //$('#edd-fairsaving-wrap').hide();
    };

    if( $('label.fairmarket-mixed').length > 0 ) {
      $('input#edd-fairsaving').attr('checked','checked').val('1');
      fairsaving_hide_fairaddress();
      $('#edd-fairsaving-wrap').hide();
    };

    if( $('label.ocp-mixed').length > 0 ) {
      $('input#edd-fairsaving').attr('checked','checked').val('1');
      fairsaving_hide_fairaddress();
      $('#edd-fairsaving-wrap').hide();
    };

    if( $('input[name=amount]').length > 0 ) {
        val = $('.edd_cart_amount').attr('data-total');
        $('input[name=amount]').val(val);
        $('span.limit_amount').text(val);
    }

    coopfunding_fill_fields();
    fairmarket_fill_fields();
    ocpfairbill_fill_fields();

    if (typeof render_localnode_checkout === "function") render_localnode_checkout();
    $( "#localnode-menu" ).menu();

    /*if(typeof cF_email != 'undefined' && typeof cF_first != 'undefined'){
	$('input#edd-email').val(cF_email);
	$('input#edd-first').val(cF_first);
	$('input#edd-last').val(cF_last);
    };*/

  });

  //// Section title currency select change autosave
  $('select.edd-currencies-select').change(function(){
    $('input.edd-currency-save-button').click();
    $('.section-title .edd-icon-spin').css('display', 'inline-block');
  });

  //// If not required indicator, put them (chromium)
  if($(' #edd-email-wrap span.edd-required-indicator').length < 1){
    $('#edd-email-wrap label').append('<span class="edd-required-indicator">*</span>');
  }
  if($(' #edd-fairaddress-wrap span.edd-required-indicator').length < 1){
    $('#edd-fairaddress-wrap label').append('<span class="edd-required-indicator">*</span>');
  }
  if($(' #edd-first-name-wrap span.edd-required-indicator').length < 1){
    $('#edd-first-name-wrap label').append('<span class="edd-required-indicator">*</span>');
  }

  var mini_price = $('.edd-cp-container small').first().text();
  if(mini_price.indexOf(') )') > -1){
    mini_price = mini_price.split(' ( ').join(' ≈ ').split(') )').join(')');
    $('.edd-cp-container small').first().text( mini_price );
  }
  //// Auto price conversion display in product page
  var price_str = $('.page-header .download-info span.edd_price').text();
  if(price_str){
    var arr = price_str.split(' ');
    if(arr.length <= 3){
      //alert('price_str?: '+price_str);
    } else {
      fair_eur = arr[3].split(',').join('.') * 1;
    }
    //alert('fair_eur:'+fair_eur+' arr: '+arr);
    if(arr.length > 8){ // currency codes
      //alert('rate: '+parseFloat( (arr[7]*1) / (('1'+fair_eur)*1) ).toFixed(8));
      arr[7] = parseFloat( fair_eur * parseFloat( (arr[7]*1) / (('1'+fair_eur)*1) ).toFixed(8)*1 ).toFixed(2); // when zero custom price is not interpreted as 1 (edd v2.3.9), the converted uses '1'+faircoin_price, so divide with that
      price_str = arr.join(' ');
      price_str = price_str.split(' EUR ').join(' ');
      //price_str = price_str.split('FAIRP').join('FAIR P');
      $('.page-header .download-info span.edd_price').text( price_str );
      arr = price_str.split(' ');
    } else if(arr.length == 6){ //alert(arr);
      price_str = price_str.split('EUR ').join('');
      price_str = price_str.split('fairP').join('fair <br>P');
      $('.page-header .download-info span.edd_price').html( price_str );
      arr = price_str.split(' '); //alert(arr);
    } else if(arr.length > 6 && arr.length < 9){ // coming soon
        price_str = arr.join(' ');
        price_str = price_str.split(' EUR').join('<br>');
        if(arr[4] == 'EUR'){
            arr[4] == ' <br> ';
        }; //alert(price_str);
	      $('.page-header .download-info span.edd_price').html( price_str );
    } else if(arr.length < 5){ // coming soon
       //alert(arr.join(' '));
    }

    //var fair_eur = arr[2].split(',').join('.') * 1;
    //rest_str = price_str.split(' ').slice(1).join(' ');

    if(!isNaN(arr[6]*1)){ // currency codes option
      curr_rate = arr[6] * 1;
      curr_str = ' '+arr[7];
      //alert('curr_rate: '+curr_rate+' curr_str:'+curr_str);
    } else if(arr[6]){ // curreny symbol option
      currarr = arr[6].split('');
      cuarr = currarr.slice(0);
      for(a=currarr.length; a>0; a--){
        if( !isNaN(currarr[a]) ){
          curr_rate = currarr.splice(0, a+1).join('')*1;
          curr_str = cuarr.splice(a+1, cuarr.length).join('');
          break;
        }
      }
      //alert('sym currarr: '+currarr+' cuarr: '+cuarr);
    }
    //alert('curr_rate: '+curr_rate+' curr_str: '+curr_str);
  }
  $('input.edd_cp_price').keyup(function() {
    faircoins = (parseFloat($(this).val() / fair_eur).toFixed(4)+'');//.split('.').join(',');
    //alert(faircoins);
    if ( curr_rate ){
      curr_aprox = parseFloat($(this).val() * (curr_rate / fair_eur)).toFixed(2)+'';
      $('.download-info span.edd_price').html( 'aprox: &nbsp;' + faircoins + ' Fair ( ' + curr_aprox + curr_str + ' )' );
    } else {
      $('.download-info span.edd_price').html( 'aprox: &nbsp;' + faircoins + ' Fair' );
    }
  });

  //// Remove search forms
  $('form.search-form').remove();

  //// close the currency footer
  var refreshId = setInterval(function() {
    $('.edd-currency-bottom-popup a.edd-currency-close-popup').click();
  }, 5000);

  //// Remove cart items if confirmation or i>1 ...
  if( window.location.href.indexOf('confirmation') != -1) {
    $('.edd-remove-from-cart').each(function(){
      $(this).click();
    });
  } else if( $('.edd_cart_item').length > 1 ) {
    window.location = $('.edd_cart_item').first().hide().find('td.edd_cart_actions').find('a').attr('href');
    alert('Two items on cart, only one admited nowadays... Erasing the oldest!');
  };

  //// if at home, hide menu 'home'
  //alert(window.location);
  loc = window.location+'';
  if(loc.indexOf('/es/') != -1 || loc.indexOf('/pt-pt/') != -1){
    if(loc.split('/').length < 6){
      $('.home-but').hide();
    }
  } else {
    if(loc.split('/').length < 5){
      $('.home-but').hide();
      //alert(loc.split('/').length);
    }
  }

  //// FIF change price
  //var idFIF = '1012';
  //var idFEHIF = '1005';
  //var idFEHIFt = '1053';
  var cs_cont = 0;
  var fif_cont = 0;
 if(typeof CS_ids != 'undefined'){
  for( var i=0; i < CS_ids.length; i++ ) {
    cs_cont += $('form#edd_purchase_'+CS_ids[i]).length;
  }
  for( var i=0; i < FIF_ids.length; i++ ) {
    fif_cont += $('form#edd_purchase_'+FIF_ids[i]).length;
  }
  if( cs_cont > 0) { // Coopshares Posts
    $('span.edd-add-to-cart-label').text('Invest in Coopshares Now');
    edd_cp['add_to_cart_text'] = 'Get Coopshares Now';
    if( fif_cont > 0 ){ // Coopshares FIF
        $('input.edd_cp_price').val( $('span.edd_price').text().split(' ')[2] ).parent().hide();
        //$('span.edd_price').text('1 FAIR = 1 fair');
    }
  }
  //// FIF auto set price
  $('input#edd-faircoins').keyup(function() {
    var fairs = parseFloat($(this).val()).toFixed(2)+'';
    if(fairs == 'NaN') fairs = '0';
    //if ( curr_rate ){
      //curr_aprox = parseFloat($(this).val() * curr_rate).toFixed(2)+'';
      $('span.edd_cart_amount').attr('data-subtotal',fairs).attr('data-total', fairs).html( fairs + '&nbsp; FAIR' );// + faircoins + ' Fair ( ' + curr_aprox + curr_str + ' )' );
    //} else {
      //$('.download-info span.edd_price').html( 'aprox: &nbsp;' + fairs + ' Fair' );
    //}
  });

  var fif_chk_cont = 0;
  for( var i=0; i < FIF_ids.length; i++){
    var fiftr = $('tr[data-download-id='+ FIF_ids[i] +']');
    if( fiftr.length ){
      fif_chk_cont += fiftr.length;
      break;
    }
  }
  if( fif_chk_cont > 0) { // Coopshares FIF in the Checkout
    var item_price_arr = fiftr.find('td.edd_cart_item_price').text().split(' ');
    item_price_arr[1] = 'Fair/Eur';
    if( item_price_arr.length > 3){
       item_price_arr[3] = curr_rate ? curr_rate : parseFloat( item_price_arr[0] * (item_price_arr[0]/item_price_arr[3]) ).toFixed(2);
       item_price_arr[4] = 'Fair/'+item_price_arr[4];

       //alert(item_price_arr.join(' '));
    }
    fiftr.find('td.edd_cart_item_price').text( item_price_arr.join(' ') );//[0]+' '+item_price_arr[1] ); //.join(' ') );
    if( $('span.edd_cart_amount').text().indexOf('FAIR') == -1) $('span.edd_cart_amount').attr('data-subtotal', 0).attr('data-total', 0).html( '0 FAIR' );
    $('fieldset#edd_currency_checkout_message').hide();
  }
 }
  if( $('table#edd_purchase_receipt').length > 0 && $('h3').first().text().indexOf('FIF') != -1 ) { // Coopshares FIF in the Receipt
    var total_arr = $('table#edd_purchase_receipt tr').eq(5).find('td').last().text().split(' ');
    if(total_arr[1] != 'FAIR') total_arr[1] = 'FAIR';
    var total_str = total_arr.join(' ');//$('table#edd_purchase_receipt tr').eq(5).find('td').last().text();
    var price_arr = $('table#edd_purchase_receipt_products tr').last().find('td').last().text().split(' ');
    if(price_arr.length > 3){
      alert(price_arr);
    }
    //$('table#edd_purchase_receipt tr').eq(3).find('td').last().text( total_str );
    $('table#edd_purchase_receipt tr').eq(4).find('td').last().text( total_str );
    var item_arr = $('table#edd_purchase_receipt_products tr').eq(1).find('td').last().text().split(' ');
    //$('table#edd_purchase_receipt_products tr').eq(1).find('td').last().text( item_arr[0]+' Fair/Eur' );
    $('table#edd_purchase_receipt_products tr').last().find('td').last().text( total_str );
  }


  // coopfunding FC2 campaign
  var query = window.location.search.substring(1);

  if(CFdata.length || FMdata.length || OCPdata.length){

     alert('CFdata+FMdata+OCPdata: '+CFdata+FMdata+OCPdata);

  } else if(query && typeof cliket == 'undefined'){

        //alert('QUERY: '+query+' PRICE:'+QueryString.amount);

        $('input.edd_cp_price').focus().val(QueryString.amount).keyup();//.blur();//.prop('disabled', true);//change();

        //$('input.edd_cp_price').trigger($.Event( 'keydown', {which:37, keyCode:37}));

        /*CFdata.email = QueryString.email;
        CFdata.first = QueryString.first_name;
        CFdata.last = QueryString.last_name;
        CFdata.order = QueryString.order;
        CFdata.amount = QueryString.amount;*/

        if($('a.edd-add-to-cart').css('display')!='none' && $('a.edd-add-to-cart span.edd-loading').css('opacity') == 0 &&
$('li.current-cart span.edd-cart-quantity').text()*1 == 0 && (typeof CForder_id != 'undefined' || typeof FMorder_id != 'undefined')){

                setTimeout(function(){
			$('a.edd-add-to-cart').click();//.prop('disabled', true);
		}, 1000);

                //cliket = true;
        }

  }


});
