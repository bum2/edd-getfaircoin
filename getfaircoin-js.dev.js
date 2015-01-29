function fairsaving_hide_fairaddress(input){
  jQuery(document).ready(function($) {
    address34 = '0000000000000000000000000000000000';
    if( $('input#edd-fairsaving').is(':checked') ) {
      $('#edd-fairaddress-wrap').hide().find('input').val( address34 );
    } else {
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
  $('.edd_price_options :input').change(function() {
    faircoins = parseFloat($(this).val() * fair_eur).toFixed(2);
    $('.download-info span.edd_price').text('approx. '+faircoins+' Fair')
  });
});
