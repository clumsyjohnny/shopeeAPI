<?php
// shopee_get_shopinfo.php
require_once 'shopeeAPI.php';

// Configuration
$config = array(
    'partner_id' => 'partner_id',
    'secret_key' => 'secret_key',
    'shop_id' => 'shop_id',
    'is_sandbox' => false
);

// Your access token obtained from authorization flow
$accessToken = 'your_access_token_here'; 

try {
    // Initialize client
    $shopee = new ShopeeApiClient($config, $accessToken, false);
    
    // Optionally update timestamp (auto-updated in request)
    $shopee->updateTimestamp();
    
    // Get shop information
    $shopInfo = $shopee->getShopInfo();
    
    // Check if response is valid
    if (isset($shopInfo['error'])) {
        throw new Exception("API Error: " . $shopInfo['message']);
    }
    
    // Display shop information
    echo "<h1>Shop Information</h1>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    
    $fieldsToShow = array(
        'shop_name', 
        'region', 
        'status',
        'auth_time',
        'expire_time',
        'shop_logo'
    );
    
    foreach ($fieldsToShow as $field) {
        if (isset($shopInfo[$field])) {
            $value = is_array($shopInfo[$field]) ? 
                json_encode($shopInfo[$field]) : 
                htmlspecialchars($shopInfo[$field]);
                
            echo "<tr><td>{$field}</td><td>{$value}</td></tr>";
        }
    }
    echo "</table>";
    
    // Show raw response for debugging
    echo "<h2>Raw Response</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($shopInfo, JSON_PRETTY_PRINT)) . "</pre>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color:red'>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    // Handle specific error cases
    if (strpos($e->getMessage(), 'access_token') !== false) {
        echo "<p>Your access token may be expired. Please re-authenticate.</p>";
        // Generate new auth URL
        $authUrl = (new ShopeeApiClient($config))->getAuthUrl('https://yourdomain.com/shopee_callback.php');
        echo "<a href='".htmlspecialchars($authUrl)."'>Re-authorize with Shopee</a>";
    }
}
?>
