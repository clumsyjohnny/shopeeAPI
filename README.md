# shopeeAPI
wrote the code for shopeeAPI using PHP and hope somebody might find it useful.

1. example of getting items Detail<br/>
$item_id = 123; #{some id of the shopee porudcts}<br/>
$shopee = new shopeeAPI($shopeeShopId, $shopeePartnerId, $shopeeSecretKey, $shopeeUrl);<br/>
$shopee->retrieveItemDetail($item_id);<br/>
