function fairsaving_hide_fairaddress(input){
  jQuery(document).ready(function($) {
    address34 = '0000000000000000000000000000000000';
    if( $('input#edd-fairsaving').is(':checked') ) {
      $('input#edd-fairsaving').val(1);
      $('#edd-fairaddress-wrap').hide().find('input').val( address34 );
    } else {
      $('input#edd-fairsaving').val(0);
      $('#edd-fairaddress-wrap').show();
      if( $('#edd-fairaddress-wrap').find('input').val() == address34 ){
        $('#edd-fairaddress-wrap').find('input').val('');
      }
    };
  });
}
jQuery(document).ready(function($) {
  fairsaving_hide_fairaddress($('input#edd-fairsaving'));

  //// Section title currency select change
  $('select.edd-currencies-select').change(function(){
    $('input.edd-currency-save-button').click();
    $('.section-title .edd-icon-spin').css('display', 'inline-block');
  });


  //// Auto price conversion display in product page
  price_str = $('.page-header .download-info span.edd_price').text();
  if(price_str){
    arr = price_str.split(' ');
    fair_eur = arr[2].split(',').join('.') * 1;
    //rest_str = price_str.split(' ').slice(1).join(' ');
    currarr = arr[5].split('');
    cuarr = currarr.slice(0);
    for(a=currarr.length; a>0; a--){
      if( !isNaN(currarr[a]) ){
        curr_rate = currarr.splice(0, a+1).join('')*1;
        curr_str = cuarr.splice(a+1, cuarr.length).join('');
        break;
      }
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
  if(loc.split('/').length < 5){
    $('.home-but').hide();
    //alert(loc.split('/').length);
  }

});
