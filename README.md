# edd-getfaircoin
Adds the functionality needed on getfaircoin.net, new fields on checkout, filter gateways for each item, etc.

In the getfaircoin.net site, the EDD downloads-products (posts) are called 'GetMethods' and are tied to a specific gateway each. The product is only one, faircoins in this case, and the edd 'downloads' are the payment options. This way, the checkout has no payment options (only the defined one) and also is limited to only one item in the cart.

*Required plugins:*
- edd-currency-converter
- edd-custom-prices

*Features / Changelog:*
v0.2:
- adds user Faircoin Address and FairService checkbox fields in the checkout.
- adds new js to hide faircoin_address on checkout when fairsaving checked.
- adds new Gateway Settings metabox in every 'getmethod' (edd 'download' post) with fields to define the gateway and the expected fees.
- adds new EDD Settings field to set the faircoin price.
- show actual faircoin price at homepage.
- show actual price and the aprox. faircoins, as the user enter his fiat amount (js).
v0.3:
- added integration with edd-currency-converter and show aprox. rates in many fiat currencies, using openexchangerates.com service.
v0.4:
- added an API to retrieve values from anywhere:
  https://getfaircoin.net/api/ticker
  returns a JSON object like:
  {"EUR":{"last":0.00666667},"USD":{"last":0.00721775},"GBP":{"last":0.00482847},"CHF":{"last":0.007055}}
- also you can call https://getfaircoin.net/api/fair-eur and get {"fair-eur":150}
- or call https://getfaircoin.net/api/eur-fair and get {"eur-fair":0.00666667}

*Contribute donating:*
faircoin:fThesXCU7FfekYNNui2MtELfCNoa9pctJk
bitcoin:13f5TfiYgWeqTFxfzwyraA1LUV6RMFjxnq

