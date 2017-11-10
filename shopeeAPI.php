<?php
	class shopeeAPI{

		var $shopeeShopId;

		var $shopeePartnerId;

		var $shopeeSecretKey;

		var $shopeeUrl;

		var $contentLength 		= 0;

		var $dataPostJson		= '';

		var $authorizationKey 	= '';

		function __construct($shopeeShopId, $shopeePartnerId, $shopeeSecretKey, $shopeeUrl){

			if(!$shopeeShopId || !$shopeePartnerId || !$shopeeSecretKey || !$shopeeUrl)
				throw new Exception('need to get shopId, partner Id, secret Key, shopee Url from shopee');

			$this->shopeeShopId 		= $shopeeShopId;
			$this->shopeePartnerId 		= $shopeePartnerId;
			$this->shopeeSecretKey 		= $shopeeSecretKey;
			$this->shopeeUrl			= $shopeeUrl;
		}

		/*
		* 	returns time stamp based on the current time required by shopee
		*
		*/
		function shopeeGetTimeStamp(){
			$date = new DateTime();
			$timeStamp = $date->getTimestamp();		
			return $timeStamp;
		}

		/*
		* 	returns time stamp based on setDate time in parameter
		*	@datetime parameter to set date time e.g. 2017-10-11
		*/
		function shopeeGetTimeStampFormatted($datetime){
			if(strlen($datetime)==0)
				throw new Exception('datetime is required');
			$date = new DateTime($datetime);
			$timeStamp = $date->getTimestamp();		
			return $timeStamp;
		}


		/*
		*	generate authorization header values based on your URL call n attributes of API..
		*	@url used by API call to achieve retrieving products / orders
		*	@attributes required by the API call based on the URL
		*/
		function generateAuthotentication($url, $attributes){
			
			$attributesExtras = array(
				'shopid'		=>$this->shopeeShopId, 
				'partner_id'	=>$this->shopeePartnerId, 
				'timestamp'		=>$this->shopeeGetTimeStamp());
			
			if(is_array($attributes))
				foreach($attributes as $k=>$v){
					$attributesExtras[$k] = $v;
				}
			
			$attributesExtrasJSON = json_encode($attributesExtras);
			
			$this->dataPostJson = $attributesExtrasJSON;

			$this->contentLength = strlen($this->dataPostJson);

			$strConcat = $url.'|'.$attributesExtrasJSON;
			
			$hexHash = hash_hmac('sha256', $strConcat, $this->shopeeSecretKey);
			return $hexHash;
		}

		/*
		*	retrieve item ID 
		*	@pagination_offset => the page number
		* 	@pagination_entries_per_page => maximimum ID to show per page
		*
		*/
		function retrieveItemList($pagination_offset =0, $pagination_entries_per_page = 100){

			$retrieveItemListUrl = $this->shopeeUrl.'/items/get';

			$attributes = array(
				'pagination_offset'				=>	$pagination_offset, 
				'pagination_entries_per_page'	=>	$pagination_entries_per_page
			);
			
			$this->authorizationKey = $this->generateAuthotentication($retrieveItemListUrl, $attributes);

			$response = $this->curlPost($retrieveItemListUrl);

			return $response;
		}

		/*
		*	retrieve item detail 
		*	@item_id => product ID of shopee to be retrieve 
		*
		*/
		function retrieveItemDetail($item_id){
			$retrieveItemDetailUrl = $this->shopeeUrl.'/item/get';
			if(strlen($item_id)==0)
				throw new Exception('item_id is required');

			$attributes = array('item_id' => intval($item_id));

			$this->authorizationKey = $this->generateAuthotentication($retrieveItemDetailUrl, $attributes);

			$response = $this->curlPost($retrieveItemDetailUrl);

			return $response;
		}

		/*
		*	update stock quantity 
		*	@item_id product id of shopee system record
		*	@qty product stock units to be updated
		*
		*/
		function updateItemStock($item_id, $qty){
		
			$updateStockUrl = $this->shopeeUrl.'/items/update_stock';
			
			if(strlen($item_id)==0)
				throw new Exception('item_id is required');

			if(strlen($qty)==0)
				throw new Exception('quantity is required');		

			$attributes = array('item_id' => intval($item_id), 'stock'=>intval($qty));

			$this->authorizationKey = $this->generateAuthotentication($updateStockUrl, $attributes);

			$response = $this->curlPost($updateStockUrl);

			return $response;
			
		}

		/*
		*	update stock price 
		*	@item_id product id of shopee system record
		*	@price pricing of the item_id to be updated
		*
		*/
		function updateItemPrice($item_id, $price){

			$updatePriceUrl = $this->shopeeUrl.'/items/update_price';
			
			if(strlen($item_id)==0)
				throw new Exception('item_id is required');

			if(strlen($price)==0)
				throw new Exception('price is required');	

			$attributes = array('item_id' => intval($item_id), 'price'=>floatval($price));

			$this->authorizationKey = $this->generateAuthotentication($updatePriceUrl, $attributes);

			$response = $this->curlPost($updatePriceUrl);

			return $response;					
		}

		/*
		*	curl to API target to retrieve DATA
		*	@url refers to the target URL
		*/
		function curlPost($url){

			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER 	=> true,
			  CURLOPT_ENCODING 			=> "",
			  CURLOPT_MAXREDIRS 		=> 10,
			  CURLOPT_TIMEOUT 			=> 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => $this->dataPostJson,
			  CURLOPT_HTTPHEADER => array(
			    "authorization: ".$this->authorizationKey,
			    "cache-control: no-cache",
			    "content-length: ".$this->contentLength,
			    "content-type: application/json"
			  ),
			));		

			$response = curl_exec($curl);
			
			$err = curl_error($curl);
			curl_close($curl);
			if($err)
				throw new Exception($err);

			return $response;
		}
	}

	class shopeeAPIOrder extends shopeeAPI{
		
		/*
		*	get Order List
		*	@datetime date time string (may be customized according to php program)
		*
		*/		
		function getOrderList($datetime = null){

			$getOrdersListUrl = $this->shopeeUrl.'/orders/basics';

			if(!isset($datetime))
				$fromTimeStamp = $this->shopeeGetTimeStampFormatted(date("Y-m-d ", strtotime("-7 days") ) );
			else 
				$fromTimeStamp = $this->shopeeGetTimeStampFormatted( $datetime  );

			$toTimeStamp = $this->shopeeGetTimeStamp();

			$pagination_entries_per_page = 100;

			$pagination_offset = 0;


			$attributes = array(
				'update_time_from'				=>	$fromTimeStamp,
				'update_time_to'				=>	$toTimeStamp,
				'pagination_offset'				=>	$pagination_offset, 
				'pagination_entries_per_page'	=>	$pagination_entries_per_page
			);

			$this->authorizationKey = $this->generateAuthotentication($getOrdersListUrl, $attributes);

			$response = $this->curlPost($getOrdersListUrl);

			return $response;
		}

		/*
		*	get Orders Detail
		*	@ordersn array of orderid to be retrieved string [] ordersn
		*
		*/
		function getOrderDetail($ordersn){

			$getOrdersDetailUrl = $this->shopeeUrl.'/orders/detail';

			if(is_array($ordersn)==0)
				throw new Exception('ordersn is required');

			$attributes = array(
				'ordersn_list'	=>	$ordersn
			);

			$this->authorizationKey = $this->generateAuthotentication($getOrdersDetailUrl, $attributes);

			$response = $this->curlPost($getOrdersDetailUrl);

			return $response;
		}
	}
?>
