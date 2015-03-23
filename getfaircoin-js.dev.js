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
jQuery(document).ready(function($) {
  if( $('input#edd-fairsaving').val() == '1'){
    $('input#edd-fairsaving').attr('checked','checked');
  } else {
    $('input#edd-fairsaving').val(0).removeAttr('checked');
  }
  fairsaving_hide_fairaddress($('input#edd-fairsaving'));

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
    if(arr.length > 8){ // currency codes
      price_str = price_str.split('EUR ').join('');
      $('.page-header .download-info span.edd_price').text( price_str );
      arr = price_str.split(' ');
      //alert(price_str);
    } else if(arr.length == 5){
      price_str = price_str.split(' EUR').join('');
      $('.page-header .download-info span.edd_price').text( price_str );
      arr = price_str.split(' ');
    }
    var fair_eur = arr[2].split(',').join('.') * 1;
    //rest_str = price_str.split(' ').slice(1).join(' ');

    if(!isNaN(arr[5]*1)){ // currency codes option
      curr_rate = arr[5] * 1;
      curr_str = ' '+arr[6];

    } else if(arr[5]){ // curreny symbol option
      currarr = arr[5].split('');
      cuarr = currarr.slice(0);
      for(a=currarr.length; a>0; a--){
        if( !isNaN(currarr[a]) ){
          curr_rate = currarr.splice(0, a+1).join('')*1;
          curr_str = cuarr.splice(a+1, cuarr.length).join('');
          break;
        }
      }
    } else {
      curr_rate = false;
    }
  }
  $('input.edd_cp_price').keyup(function() {
    faircoins = (parseFloat($(this).val() * fair_eur).toFixed(0)+'');//.split('.').join(',');
    //alert(faircoins);
    if ( curr_rate ){
      curr_aprox = parseFloat($(this).val() * curr_rate).toFixed(2)+'';
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
});
