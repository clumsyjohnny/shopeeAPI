<?php
/**
 * Shopee API Client (OOP Version)
 * 
 * A secure, well-documented PHP client for Shopee's Open API compatible with PHP 5.6+
 * 
 * @package ShopeeApi
 * @version 2.0.0
 */
class ShopeeApiClient
{
    /**
     * @var array $config Configuration settings for the API client
     */
    private $config;
    
    /**
     * @var string $accessToken Current access token for API calls
     */
    private $accessToken;
    
    /**
     * @var bool $debug Debug mode flag
     */
    private $debug = false;
    
    /**
     * Constructor with integrated access token
     * 
     * @param array $config Configuration including:
     *   - partner_id
     *   - secret_key
     *   - shop_id
     *   - is_sandbox
     * @param string|null $accessToken Existing access token (optional)
     * @param bool $debug Debug mode
     */
    public function __construct(array $config, $accessToken = null, $debug = false)
    {
        $this->config = $this->sanitizeConfig($config);
        $this->debug = $debug;
        $this->config['ts'] = time();
        $this->config['access_token'] = $accessToken; // Store token in config
    }
    
    /**
     * Sanitize and validate configuration
     * 
     * @param array $config Input configuration
     * @return array Sanitized configuration
     */
    private function sanitizeConfig($config)
    {
        
        // Set default values
        $defaults = array(
            'url' => 'https://partner.shopeemobile.com',
            'api_version' => '/api/v2',
            'shop_id' => null,
            'partner_id' => null,
            'secret_key' => null,
            'is_sandbox' => false
        );
        
        return array_merge($defaults, $config);
    }
    
    /**
     * Mask sensitive key for security
     * 
     * @param string $key Original key
     * @return string Masked key
     */
    private function maskKey($key)
    {
        if (strlen($key) > 8) {
            return substr($key, 0, 4) . str_repeat('*', strlen($key) - 8) . substr($key, -4);
        }
        return str_repeat('*', strlen($key));
    }
    
    /**
     * Set the access token
     * 
     * @param string $token Access token
     */
    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }
    
    /**
     * Generate authentication signature
     * 
     * @param string $path API endpoint path
     * @param array $params Request parameters
     * @param int $level Signature level (1 or 2)
     * @return string Generated signature
     */
    private function generateSignature($path, $params = array(), $level = 1)
    {
        $baseString = $this->config['partner_id'] . $path . $this->config['ts'];
        
        if ($level == 2) {
            $baseString .= $this->config['access_token'] . $this->config['shop_id'];
        }
        
        if (!empty($params)) {
            ksort($params); // Parameters must be sorted by key
            $baseString .= http_build_query($params);
        }
        
        if($this->debug){
            // DEBUG OUTPUT - REMOVE AFTER VERIFICATION
            echo "<br/>String being signed: " . $baseString . "\n";
            echo "Secret key: " . $this->config['secret_key'] . "\n";
            echo "Generated signature: " . hash_hmac('sha256', $baseString, $this->config['secret_key']) . "\n";
        }
        return hash_hmac('sha256', $baseString, $this->config['secret_key']);
        
    }
    
    /**
     * Make API request
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     * @param int $signLevel Signature level
     * @return array API response
     * @throws Exception On API error
     */
    private function request($method, $endpoint, $params = [], $signLevel = 1)
    {
        $fullUrl = $this->config['url'] . $this->config['api_version'] . $endpoint;
        $signature = $this->generateSignature($this->config['api_version'] . $endpoint, $params, $signLevel);
        
        $queryParams = [
            'partner_id' => $this->config['partner_id'],
            'timestamp' => $this->config['ts'],
            'sign' => $signature,
        ];
        
        if ($signLevel == 2) {
            $queryParams['access_token'] = $this->config['access_token'];
            $queryParams['shop_id'] = $this->config['shop_id'];
        }
        
        $ch = curl_init();
        
        if ($method === 'GET') {
            $fullUrl .= '?' . http_build_query(array_merge($queryParams, $params));
        } else {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            $fullUrl .= '?' . http_build_query($queryParams);
        }
        
        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
        );
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $fullUrl,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        
        if ($this->debug) {
            error_log("Shopee API Request: " . $fullUrl);
            if ($method !== 'GET') {
                error_log("Request Body: " . json_encode($params));
            }
        }
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("CURL Error: {$error}");
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON decode error: " . json_last_error_msg());
        }
        
        if (isset($decoded['error']) && !empty($decoded['error'])) {
            throw new Exception("API Error: {$decoded['error']} - {$decoded['message']}");
        }
        
        return $decoded;
    }
    
    // ========== AUTHENTICATION METHODS ========== //
    
    /**
     * Get authorization URL
     * 
     * @param string $redirectUrl URL to redirect after authorization
     * @return string Authorization URL
     */
    public function getAuthUrl($redirectUrl)
    {
        $endpoint = '/shop/auth_partner';
        $signature = $this->generateSignature($this->config['api_version'] . $endpoint);
        
        return sprintf(
            "%s%s?partner_id=%d&timestamp=%d&sign=%s&redirect=%s",
            $this->config['url'],
            $endpoint,
            $this->config['partner_id'],
            $this->config['ts'],
            $signature,
            urlencode($redirectUrl)
        );
    }
    
    /**
     * Get access token
     * 
     * @param string $authCode Authorization code
     * @return array Token response
     */
    public function getAccessToken($authCode)
    {
        $endpoint = '/auth/token/get';
        $response = $this->request('POST', $endpoint, [
            'code' => $authCode,
            'partner_id' => $this->config['partner_id'],
            'shop_id' => $this->config['shop_id']
        ]);
        
        if (isset($response['access_token'])) {
            $this->config['access_token'] = $response['access_token']; // Store in config
        }
        
        return $response;
    }
    
    /**
     * Refresh access token
     * 
     * @param string $refreshToken Refresh token
     * @return array Token response
     */
    public function refreshToken($refreshToken)
    {
        $endpoint = '/auth/access_token/get';
        $response = $this->request('POST', $endpoint, [
            'refresh_token' => $refreshToken,
            'partner_id' => $this->config['partner_id'],
            'shop_id' => $this->config['shop_id']
        ]);
        
        if (isset($response['access_token'])) {
            $this->config['access_token'] = $response['access_token']; // Store in config
        }
        
        return $response;
    }
    
    // ========== SHOP METHODS ========== //
    
    /**
     * Get shop information
     * 
     * @return array Shop details
     */
    public function getShopInfo()
    {
        return $this->request('GET', '/shop/get_shop_info', [], 2);
    }
    
    /**
     * Get shop profile
     * 
     * @return array Shop profile
     */
    public function getShopProfile()
    {
        return $this->request('GET', '/shop/get_profile', [], 2);
    }
    
    // ========== PRODUCT METHODS ========== //
    
    /**
     * Get item list
     * 
     * @param int $offset Pagination offset
     * @param int $pageSize Items per page (max 100)
     * @param int $itemStatus Item status (0:normal, 1:deleted, 2:unlisted)
     * @return array Item list
     */
    public function getItems($offset = 0, $pageSize = 100, $itemStatus = 0)
    {
        return $this->request('GET', '/item/get_items_list', [
            'offset' => $offset,
            'page_size' => $pageSize,
            'item_status' => $itemStatus
        ], 2);
    }
    
    /**
     * Get item details
     * 
     * @param int $itemId Item ID
     * @return array Item details
     */
    public function getItemDetail($itemId)
    {
        return $this->request('GET', '/product/get_item_base_info', [
            'item_id_list' => $itemId
        ], 2);
    }
    
    /**
     * Update item stock
     * 
     * @param int $itemId Item ID
     * @param int $modelId Model ID
     * @param int $stock New stock quantity
     * @return array API response
     */
    public function updateStock($itemId, $modelId, $stock)
    {
        return $this->request('POST', '/product/update_stock', [
            'item_id' => $itemId,
            'stock_list' => [
                [
                    'model_id' => $modelId,
                    'normal_stock' => $stock
                ]
            ]
        ], 2);
    }
    
    // ========== ORDER METHODS ========== //
    
    /**
     * Get order list
     * 
     * @param array $params Query parameters
     * @return array Order list
     */
    public function getOrders($params = array())
    {
        $defaultParams = [
            'time_range_field' => 'create_time',
            'page_size' => 100
        ];
        
        return $this->request('GET', '/order/get_order_list', array_merge($defaultParams, $params), 2);
    }
    
    /**
     * Get order details
     * 
     * @param array $orderIds Array of order IDs
     * @param array $fields Optional fields to include
     * @return array Order details
     */
    public function getOrderDetails(array $orderIds, array $fields = [])
    {
        $params = [
            'order_sn_list' => implode(',', $orderIds)
        ];
        
        if (!empty($fields)) {
            $params['response_optional_fields'] = implode(',', $fields);
        }
        
        return $this->request('GET', '/order/get_order_detail', $params, 2);
    }
    
    // ========== LOGISTICS METHODS ========== //
    
    /**
     * Get shipping channels
     * 
     * @return array Shipping channels
     */
    public function getShippingChannels()
    {
        return $this->request('GET', '/logistics/get_channel_list', [], 2);
    }
    
    /**
     * Get tracking number
     * 
     * @param string $orderSn Order SN
     * @return array Tracking info
     */
    public function getTrackingNumber($orderSn)
    {
        return $this->request('GET', '/logistics/get_tracking_number', [
            'order_sn' => $orderSn
        ], 2);
    }
    
    // ========== UTILITY METHODS ========== //
    
    /**
     * Upload image to Shopee
     * 
     * @param string $imagePath Path to image file
     * @return array Upload response
     */
    public function uploadImage($imagePath)
    {
        if (!file_exists($imagePath)) {
            throw new Exception("Image file not found: {$imagePath}");
        }
        
        $endpoint = '/media_space/upload_image';
        $signature = $this->generateSignature($this->config['api_version'] . $endpoint);
        
        $queryParams = [
            'partner_id' => $this->config['partner_id'],
            'timestamp' => $this->config['ts'],
            'sign' => $signature,
        ];
        
        $url = $this->config['url'] . $this->config['api_version'] . $endpoint . '?' . http_build_query($queryParams);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['image' => new CURLFile($imagePath)],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: multipart/form-data'],
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("CURL Error: {$error}");
        }
        
        return json_decode($response, true);
    }
}
