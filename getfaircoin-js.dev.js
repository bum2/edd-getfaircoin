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
  
  price_str = $('.download-info span.edd_price').text();
  fair_eur = price_str.split(' ')[0].split(',').join('.') * 1;
  //rest_str = price_str.split(' ').slice(1).join(' ');
  $('input.edd_cp_price').keyup(function() {
    faircoins = (parseFloat($(this).val() * fair_eur).toFixed(0)+'');//.split('.').join(',');
    //alert(faircoins);
    $('.download-info span.edd_price').html('aprox: &nbsp;'+faircoins+' Fair')
  });

  if( window.location.href.indexOf('confirmation') != -1) {
    $('.edd-remove-from-cart').each(function(){
      $(this).click();
    });
  } else if( $('.edd_cart_item').length > 1 ) {
    window.location = $('.edd_cart_item').first().hide().find('td.edd_cart_actions').find('a').attr('href');
    alert('Two items on cart, only one admited nowadays.../nErasing the oldest! /n');
    //$('.edd-remove-from-cart').first().click();
    //window.location = '/checkout';
  }

});
