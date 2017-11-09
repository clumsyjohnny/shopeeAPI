# shopeeAPI
wrote the code for shopeeAPI using PHP and hope somebody might find it useful.

1. example of getting items Detail
$item_id = 123; #{some id of the shopee porudcts}
$shopee = new shopeeAPI($shopeeShopId, $shopeePartnerId, $shopeeSecretKey, $shopeeUrl);
$shopee->retrieveItemDetail($item_id);
