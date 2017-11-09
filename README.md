# shopeeAPI
wrote the code for shopeeAPI using PHP and hope somebody might find it useful.

1. example of <span style='color:red;'>getting items Detail</span><br/>
$item_id = 123; #{some id of the shopee porudcts}<br/>
$shopee = new shopeeAPI($shopeeShopId, $shopeePartnerId, $shopeeSecretKey, $shopeeUrl);<br/>
$product = $shopee->retrieveItemDetail($item_id);<br/>
var_dump($product); #output the product in JSON format<br/><br/>

2. example of <span style='color:red;'>update product quantity</span><br/>
$item_id = 123; #{some id of the shopee porudcts}<br/>
$qty = 100; #{quantity of the shopee porudcts}<br/>
$shopee = new shopeeAPI($shopeeShopId, $shopeePartnerId, $shopeeSecretKey, $shopeeUrl);<br/>
$shopee->updateItemStock($item_id, $qty);<br/>
when successfully updated it will show u modified time and item_id in JSON format<br/><Br/>
