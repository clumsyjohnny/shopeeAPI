<?php
// assume this page is callback.php
// shopee_generate_auth.php
require_once 'shopeeAPI.php';

// Configuration - replace with your actual credentials
$config = array(
    'partner_id' => 'partner_id',
    'secret_key' => 'secret_key',
    'shop_id' => 'shop_id',
    'is_sandbox' => false //for sandbox testing
);

// Initialize client
$shopee = new ShopeeApiClient($config, null, true); // true enables debug mode

// The URL where Shopee will redirect after authorization
// Must match exactly what you registered in Shopee Partner Center
$redirectUrl = 'https://'.$_SERVER['HTTP_HOST'].'/shopee_callback.php';

// Generate the authorization URL
$authUrl = $shopee->getAuthUrl($redirectUrl);

// Display the link
echo '<h1>Shopee Authorization</h1>';
echo '<p>Click this link to authorize your application:</p>';
echo '<a href="'.htmlspecialchars($authUrl).'" target="_blank">';
echo 'Authorize with Shopee';
echo '</a>';

// For debugging - show the raw URL
echo '<div style="margin-top:20px; padding:10px; background:#f5f5f5;">';
echo '<strong>Raw Authorization URL:</strong><br>';
echo '<input type="text" value="'.htmlspecialchars($authUrl).'" style="width:100%;">';
echo '</div>';
?>
