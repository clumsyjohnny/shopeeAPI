# shopee API v2.1
2025-08-24 update the code to accomodate version 2 of SHOPEE open API, and document the code better.

# shopee API V2.0
2024-08-24 update the code to accomodate version 2 of SHOPEE open API.

# shopeeAPI
wrote the code for shopeeAPI using PHP and hope somebody might find it useful.

//1. example of <span style='color:red;'>getting items Detail</span><br/>
$item_id = 123; #{some id of the shopee porudcts}<br/>
$shopee = new shopeeAPI($shopeeShopId, $shopeePartnerId, $shopeeSecretKey, $shopeeUrl);<br/>
$product = $shopee->retrieveItemDetail($item_id);<br/>
var_dump($product); #output the product in JSON format<br/><br/>

//2. example of <span style='color:red;'>update product quantity</span><br/>
$item_id = 123; #{some id of the shopee porudcts}<br/>
$qty = 100; #{quantity of the shopee porudcts}<br/>
$shopee = new shopeeAPI($shopeeShopId, $shopeePartnerId, $shopeeSecretKey, $shopeeUrl);<br/>
$shopee->updateItemStock($item_id, $qty);<br/>
//when successfully updated it will show u modified time and item_id in JSON format<br/><Br/>

//3. retrieve order list<br/>
$shopee = new shopeeAPIOrder($shopeeShopId, $shopeePartnerId, $shopeeSecretKey, $shopeeUrl);<br/>
$ordersList = $shopee->getOrderList(null);<br/>
$ordersListJSON = json_decode($ordersList, true);<br/>
//returns orderList in JSON with attributes "ordersn", "order_status", "order_datetime"<br/><br/>

//4. retrieve order Details<br/>
$shopee = new shopeeAPIOrder($shopeeShopId, $shopeePartnerId, $shopeeSecretKey, $shopeeUrl);<br/>
$ordersn[] = array(123, 456, 789);<br/>
$ordersList = $shopee->getOrderDetail($ordersn);<br/>
$ordersListJON = json_decode($ordersList, true);<br/>
//returns to you order Detail like recipient info., items ordered, status of order, etc.
