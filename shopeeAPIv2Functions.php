<?php 
	#https://seller.test-stable.shopee.com.my/account/signin?next=%2F
		$shopeeAcc 			= ''; #shopee seller profile username

		$shopPass 			= ''; 

		$shopeeShopId 		= ''; #shopee shop ID

		$shopeePartnerId 	= ''; #shopee open api partner ID

		$shopeeSecretKey 	= '4a504874507371786b6e6b4a716d694d6e4773565252477644524d734b494c4d';

		$shopeeUrl 			= 'https://partner.shopeemobile.com/api/v2';

		$shopee_token_file	= 'shopee_test_tokenv2';

	  $ShopeeDate 		= new DateTime();

	  $shopeeTimestamp 	= $ShopeeDate->getTimestamp();

	$shopee_base 		= Array(
		'shop_id'		=>	$shopeeShopId, 
		'partner_id'	=>	$shopeePartnerId, 
		'secret'		=>	$shopeeSecretKey, 
		'url'			=> 	$shopeeUrl,
		'ts'			=>	$shopeeTimestamp
	);	

	$shopee_order_param = Array(
		'time_range_field'		=>	'create_time',
		'page_size'				=>	100
		#'cursor'				=>	
		#'order_status'			=>	
		#'time_from'			=>
		#'time_to'				=>
	);
	if(isset($shopee_express_shipping_value) && strlen($shopee_express_shipping_value)>0){
		$tmp_express_val = explode("\n", $shopee_express_shipping_value);
		foreach($tmp_express_val as $v){
			$tmp_express_val2[] = strtolower(trim($v));
		}
		$shopee_express_v2array = $tmp_express_val2;
	}else{
		$shopee_express_v2array = Array(
			'shopee express (self collection)',
			'shopee express (sea shipping)',
			'shopee express',
			'shopee xpress',
			'shopee xpress (west malaysia)',
			'shopee xpress (east malaysia)',
			'shopee xpress (sea shipping)',
			'spx express (west malaysia)',
			'spx express (east malaysia)',
			'same day delivery'
		);
	}
	function shopeeV2GetCommentOnID($token, $shopee, $commentid, $comment_url = '/product/get_comment'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$comment_url2 		= '/api/v2'.$comment_url;
		if($local_debug)
			print 'line 363: comment_url2:'.$comment_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $comment_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(
			'access_token'	=>	$token,

			'comment_id'	=>	$commentid,

			'cursor'		=> 	null,

			'page_size'		=>	10,

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,	

			'timestamp'		=> 	$ts
			
		);

		$model_url3			= $shopee['url'].$comment_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>model_url3:'.$model_url3.'<hr/>comment_url:'.$comment_url.'<hr/>comment_url2:'.$comment_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 316: model_url3: <input type="text" value="'.$model_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($model_url3);
		return $output;				
	}
	function shopeeV2GetProductComment($token, $shopee, $page_size=10, $item_id=null, $cursor='', $comment_url = '/product/get_comment'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$comment_url2 		= '/api/v2'.$comment_url;
		if($local_debug)
			print 'line 363: comment_url2:'.$comment_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $comment_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(
			'access_token'	=>	$token,

			'cursor'		=> 	$cursor,

			'page_size'		=>	$page_size,

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,	

			'timestamp'		=> 	$ts
			
		);

		$model_url3			= $shopee['url'].$comment_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>model_url3:'.$model_url3.'<hr/>comment_url:'.$comment_url.'<hr/>comment_url2:'.$comment_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 316: model_url3: <input type="text" value="'.$model_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($model_url3);
		return $output;				
	}
	function shopeeV2GenerateAuthorizationUrl($return_url, $shopee, $auth_url='/shop/auth_partner'){

		global $local_debug;
		$auth_url2 			= '/api/v2'.$auth_url;

		$sign				= shopeev2GetSignAccountLvl1($shopee, $auth_url2);

		$authlink			= sprintf($shopee['url'].$auth_url.'?partner_id=%d&timestamp=%d&sign=%s&redirect=%s', $shopee['partner_id'], $shopee['ts'], $sign, $return_url);
		if($local_debug)
			print '<hr/>common.php line 53: auth_link: <input type="text" value="'.$authlink.'"/>';
		return $authlink;
	}
	function shopeev2GetSignAccountLvl1($shopee, $api_path, $level=1){
		global $local_debug;
		if($level==1)
			$sign 	= $shopee['partner_id'].$api_path.$shopee['ts'];
		else if($level==2)
			$sign 	= $shopee['partner_id'].$api_path.$shopee['ts'].$shopee['token'].$shopee['shop_id'];
		if($local_debug)
			print '<hr/>common.php line 60: sign: (level '.$level.') <input type="text" value="'.$sign.'"/>';
		$sign	= hash_hmac('sha256', $sign, $shopee['secret']);
		return $sign;
	}
	function shopeeV2CurlPost($url, $dataPost = null){
		global $local_debug;
		$curl = curl_init();
		if(is_array($dataPost)){
			if(is_array($dataPost) && $local_debug){
				print '<hr/>curl data posted line 76: <pre>';
				print_r($dataPost);
				print '</pre>';
			}

			$dp = json_encode($dataPost);
			$ct = "application/json";
			if($local_debug){
				print '<pre>line 113 - datapost: ';
				print_r($dp);
			}
			if(stristr($url, '/media_space/upload_image')!=false){
				$dp = $dataPost;
				$ct = "multipart/form-data";
			}

			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER 	=> true,
			  CURLOPT_ENCODING 			=> "",
			  CURLOPT_MAXREDIRS 		=> 10,
			  CURLOPT_TIMEOUT 			=> 0,
			  CURLOPT_HTTP_VERSION 		=> CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST 	=> "POST",
			  CURLOPT_POSTFIELDS 		=> $dp,
			  CURLOPT_HTTPHEADER => array(
			    "content-type: $ct"
			  )			  
			));		
		}else{
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER 	=> true,
			  CURLOPT_ENCODING 			=> "",
			  CURLOPT_MAXREDIRS 		=> 10,
			  CURLOPT_TIMEOUT 			=> 30,
			  CURLOPT_HTTP_VERSION 		=> CURL_HTTP_VERSION_1_1,
			  CURLOPT_HTTPHEADER => array(
			    "content-type: application/json"
			  )			  
			));				
		}

		$response = curl_exec($curl);
		
		$err = curl_error($curl);
		
		curl_close($curl);
		if($err)
			throw new Exception($err);

		return $response;
	}	
	function shopeev2GetAccessToken($code, $shopee, $token_url='/auth/token/get'){

		$ts 				= $shopee['ts'];

		$token_url2 		= '/api/v2'.$token_url;

		$sign				= shopeev2GetSignAccountLvl1($shopee, $token_url2);

		$commonquery 		= Array(
			'sign'			=>	$sign,

			'partner_id'	=>	$shopee['partner_id'],

			'timestamp'		=> 	$shopee['ts']
		);

		$datapost 			= Array(

			'code'			=>	$code, 

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=>	$shopee['shop_id']
		);

		$token_url3			= $shopee['url'].$token_url.'?'.http_build_query($commonquery);

		$output 			= shopeeV2CurlPost($token_url3, $datapost);
		return $output;
		
	}
	function shopeev2GetShopeInfo($token, $shopee, $shopurl = '/shop/get_shop_info'){
		global $local_debug;

		$shopurl2			= '/api/v2'.$shopurl;
		$shopee['token'] 	= $token;
		$sign				= shopeev2GetSignAccountLvl1($shopee, $shopurl2, 2);

		$commonquery 		= Array(

			'partner_id'	=>	$shopee['partner_id'],

			'timestamp'		=> 	$shopee['ts'],

			'access_token'	=>	$token,

			'shop_id'		=>	$shopee['shop_id'],

			'sign'			=>	$sign
		);			

		$getShopInfoUrl = $shopee['url'].$shopurl;

		$shopurl3		= $shopee['url'].$shopurl.'?'.http_build_query($commonquery);
		if($local_debug)
			print 'line 162: shopurl3: <input type="text" value="'.$shopurl3.'"/>';
		$response 		= shopeeV2CurlPost($shopurl3);

		return $response;		
	}
	function shopeeV2GetLogistics($token, $shopee, $shipping_url = '/logistics/get_channel_list'){

			$ts 				= $shopee['ts'];

			$shipping_url2 		= '/api/v2'.$shipping_url;
			$shopee['token'] 	= $token;
			$sign 				= shopeev2GetSignAccountLvl1($shopee, $shipping_url2, 2);

			$commonquery 		= Array(
				'sign'			=>	$sign,

				'partner_id'	=>	$shopee['partner_id'],

				'timestamp'		=> 	$ts,

				'access_token'	=>	$token,

				'shop_id'		=> 	$shopee['shop_id']
			);
			#print_r($commonquery);
 			$shipping_url3		= $shopee['url'].$shipping_url.'?'.http_build_query($commonquery);
 			#print $shipping_url3;
			$output 			= shopeeV2CurlPost($shipping_url3);
			return $output;		
	}
	function shopeeV2UpdateStockSeller($token, $shopee, $dataPost, $stock_url = '/product/update_stock'){
			global $local_debug;
			$ts 				= $shopee['ts'];

			$stock_url2 		= '/api/v2'.$stock_url;
			if($local_debug)
				print 'stock_url2:'.$stock_url2;
			$shopee['token'] 	= $token;
			$sign 				= shopeev2GetSignAccountLvl1($shopee, $stock_url2, 2);
			if($local_debug)
				printf ("sign: %s", $sign);
			$commonquery 		= Array(
				'access_token'	=>	$token,

				'partner_id'	=>	$shopee['partner_id'],

				'shop_id'		=> 	$shopee['shop_id'],

				'sign'			=>	$sign,
		

				'timestamp'		=> 	$ts
				
			);

			$dataBPost 			= Array(
				'item_id'	=>	intval($dataPost['item_id']),
				'stock_list'=>	Array(
					Array(
						'model_id'		=>	intval($dataPost['model_id']),
						'seller_stock'	=>	Array(
							Array(
								'stock'=>intval($dataPost['normal_stock'])
							)
						)
					)
				)
			);
			if($local_debug)
				print '<hr/>'.json_encode($dataBPost);
			#print_r($commonquery);

 			$stock_url3			= $shopee['url'].$stock_url.'?'.http_build_query($commonquery);
 			if($local_debug)
 				print 'stock_url3:'.$shopee['url'];
 			#print $shipping_url3;
			if($local_debug)
				print 'line 230: stock_url3: <input type="text" value="'.$stock_url3.'"/>'; 			
			$output 			= shopeeV2CurlPost($stock_url3, $dataBPost);
			return $output;			
	}	
	function shopeeV2UpdateStock($token, $shopee, $dataPost, $stock_url = '/product/update_stock'){
			global $local_debug;
			$ts 				= $shopee['ts'];

			$stock_url2 		= '/api/v2'.$stock_url;
			if($local_debug)
				print 'stock_url2:'.$stock_url2;
			$shopee['token'] 	= $token;
			$sign 				= shopeev2GetSignAccountLvl1($shopee, $stock_url2, 2);
			if($local_debug)
				printf ("sign: %s", $sign);
			$commonquery 		= Array(
				'access_token'	=>	$token,

				'partner_id'	=>	$shopee['partner_id'],

				'shop_id'		=> 	$shopee['shop_id'],

				'sign'			=>	$sign,
		

				'timestamp'		=> 	$ts
				
			);

			$dataBPost 			= Array(
				'item_id'	=>	intval($dataPost['item_id']),
				'stock_list'=>	Array(
					Array(
						'model_id'		=>	intval($dataPost['model_id']),
						'normal_stock'	=>	intval($dataPost['normal_stock'])
					)
				)
			);
			if($local_debug)
				print '<hr/>'.json_encode($dataBPost);
			#print_r($commonquery);

 			$stock_url3			= $shopee['url'].$stock_url.'?'.http_build_query($commonquery);
 			if($local_debug)
 				print 'stock_url3:'.$shopee['url'];
 			#print $shipping_url3;
			if($local_debug)
				print 'line 230: stock_url3: <input type="text" value="'.$stock_url3.'"/>'; 			
			$output 			= shopeeV2CurlPost($stock_url3, $dataBPost);
			return $output;			
	}

	function shopeeV2GetItemBaseInfo($token, $shopee, $item_id_list, $item_url = '/product/get_item_base_info'){
			global $local_debug;
			$ts 				= $shopee['ts'];

			$stock_url2 		= '/api/v2'.$item_url;
			#print 'stock_url2:'.$stock_url2;
			$shopee['token'] 	= $token;
			$sign 				= shopeev2GetSignAccountLvl1($shopee, $stock_url2, 2);
			if($local_debug)
				printf ("sign: %s", $sign);
			$commonquery 		= Array(
				'access_token'	=>	$token,

				'item_id_list'	=>	implode(',',$item_id_list),

				'partner_id'	=>	$shopee['partner_id'],

				'shop_id'		=> 	$shopee['shop_id'],

				'sign'			=>	$sign,
		

				'timestamp'		=> 	$ts
				
				
			);
			#print_r($commonquery);

 			$stock_url3			= $shopee['url'].$item_url.'?'.http_build_query($commonquery);
 			if($local_debug)
 				print 'stock_url3:'.$stock_url3;
 			#print $shipping_url3;
			if($local_debug)
				print 'line 230: stock_url3: <input type="text" value="'.$stock_url3.'"/>'; 			
			$output 			= shopeeV2CurlPost($stock_url3);
			return $output;			
	}

	function shopeeV2GetModelList($token, $shopee, $item_id, $model_url='/product/get_model_list'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$model_url2 		= '/api/v2'.$model_url;
		#print 'stock_url2:'.$stock_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $model_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(
			'access_token'	=>	$token,

			'item_id'		=>	$item_id,

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,	

			'timestamp'		=> 	$ts
			
		);

		$model_url3			= $shopee['url'].$model_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>model_url3:'.$model_url3.'<hr/>model_url:'.$model_url.'<hr/>model_url2:'.$model_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 316: model_url3: <input type="text" value="'.$model_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($model_url3);
		return $output;					
	}	
	function shopeev2RefreshToken($shopee, $refresh_token, $token_url='/auth/access_token/get'){
		global $local_debug;

		$token_url2			= '/api/v2'.$token_url;
		$shopee['token'] 	= $token;
		$sign				= shopeev2GetSignAccountLvl1($shopee, $token_url2, 1);

		$commonquery 		= Array(

			'partner_id'	=>	$shopee['partner_id'],

			'timestamp'		=> 	$shopee['ts'],

			'sign'			=>	$sign
		);

		$datapost 			= Array(

			'refresh_token'	=> $refresh_token, 

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=>	$shopee['shop_id']
		);		
		if($local_debug){
			print '<pre>line 347 datapost: ';
			print_r($datapost);
		}
		$token_url3		= $shopee['url'].$token_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print 'line 352: token_url3: <input type="text" value="'.$token_url3.'"/>';
		$response 		= shopeeV2CurlPost($token_url3, $datapost);

		return $response;	
	}	
	function shopeeV2GetExtraInfo($token, $shopee, $item_id, $info_url = '/product/get_item_extra_info'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$info_url2 		= '/api/v2'.$info_url;
		if($local_debug)
			print 'line 363: info_url2:'.$stock_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $info_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(
			'access_token'	=>	$token,

			'item_id_list'	=>	implode(',',$item_id),

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,	

			'timestamp'		=> 	$ts
			
		);

		$model_url3			= $shopee['url'].$info_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>model_url3:'.$model_url3.'<hr/>info_url:'.$info_url.'<hr/>model_url2:'.$model_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 316: model_url3: <input type="text" value="'.$model_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($model_url3);
		return $output;				
	}
	function shopeeV2GetOrderDetail($token, $shopee, $orderid, $orderneedinfo = Array('recipient_address', 'item_list', 'checkout_shipping_carrier', 'shipping_carrier', 'payment_method', 'note', 'actual_shipping_fee_confirmed', 'package_list'), $order_url = '/order/get_order_detail'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$order_url2			= '/api/v2'.$order_url;
		if($local_debug)
			print 'order_url2:'.$order_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $order_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(
			'access_token'	=>	$token,


			'order_sn_list'	=>	implode(',',$orderid),

			'partner_id'	=>	$shopee['partner_id'],

			'response_optional_fields'	=>	implode(',', $orderneedinfo),

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,	

			'timestamp'		=> 	$ts
			
		);

		$order_url3			= $shopee['url'].$order_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>order_url3:'.$order_url3.'<hr/>order_url:'.$order_url.'<hr/>order_url2:'.$order_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 389: order_url3: <input type="text" value="'.$order_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($order_url3);
		return $output;		
	}	
	function shopeeV2GetPaymentEscrow($token, $shopee, $ordersn, $escrow_url='/payment/get_escrow_detail'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$escrow_url2		= '/api/v2'.$escrow_url;
		if($local_debug)
			print 'escrow_url2:'.$escrow_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $escrow_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(
			'access_token'	=>	$token,

			'order_sn'		=>	$ordersn,

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,	

			'timestamp'		=> 	$ts
			
		);

		$escrow_url3		= $shopee['url'].$escrow_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>escrow_url3:'.$escrow_url3.'<hr/>escrow_url:'.$escrow_url.'<hr/>escrow_url2:'.$escrow_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 461: escrow_url3: <input type="text" value="'.$escrow_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($escrow_url3);
		return $output;
	}	
	function shopeeV2ItemGetCategoryAttribute($token, $shopee, $category_id, $attr_url='/product/get_attributes'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$attr_url2		= '/api/v2'.$attr_url;
		if($local_debug)
			print 'attr_url2:'.$attr_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $attr_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(
			'access_token'	=>	$token,

			'category_id'	=>	$category_id,

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,	

			'timestamp'		=> 	$ts
			
		);

		$attr_url3			= $shopee['url'].$attr_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>attr_url3:'.$attr_url3.'<hr/>attr_url:'.$attr_url.'<hr/>attr_url2:'.$attr_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 496: attr_url3: <input type="text" value="'.$attr_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($attr_url3);
		return $output;		
	}
	function shopeeV2LogisticGetChannelList($token, $shopee, $ship_url='/logistics/get_channel_list'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$ship_url2		= '/api/v2'.$ship_url;
		if($local_debug)
			print 'attr_url2:'.$attr_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $ship_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(
			'access_token'	=>	$token,

			'category_id'	=>	$category_id,

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,	

			'timestamp'		=> 	$ts
			
		);

		$ship_url3			= $shopee['url'].$ship_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>ship_url3:'.$ship_url3.'<hr/>attr_url:'.$ship_url.'<hr/>ship_url2:'.$ship_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 531: ship_url3: <input type="text" value="'.$ship_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($ship_url3);
		return $output;		
	}		
	function shopeeV2UploadImage($token, $shopee, $image_is, $image_url = '/media_space/upload_image'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$image_url2		= '/api/v2'.$image_url;
		if($local_debug)
			print 'image_url2:'.$image_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $image_url2, 1);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(

			'partner_id'	=>	$shopee['partner_id'],

			'sign'			=>	$sign,	

			'timestamp'		=> 	$ts
			
		);
		$dataPost 			=  array('image'=> new CURLFILE($image_is));
		$image_url3			= $shopee['url'].$image_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>image_url3:'.$image_url3.'<hr/>image_url:'.$image_url.'<hr/>image_url2:'.$image_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 569: image_url3: <input type="text" value="'.$image_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($image_url3, $dataPost);
		return $output;	
	}	
	function shopeeV2AddItem($token, $shopee, $postData, $item_url ='/product/add_item'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$item_url2		= '/api/v2'.$item_url;
		if($local_debug)
			print 'item_url2:'.$item_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $item_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(

			'access_token'	=>	$token,

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,
	
			'timestamp'		=> 	$ts
			
		);
		if($local_debug){
			print '<pre>';
			print_r($commonquery);
			print '</pre>';
		}
		$item_url3			= $shopee['url'].$item_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>item_url3:'.$item_url3.'<hr/>item_url:'.$item_url.'<hr/>item_url2:'.$item_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 609: item_url3: <input type="text" value="'.$item_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($item_url3, $postData);
		if($local_debug){
			print '<pre>output: ';
			print $output;
		}
		return $output;	
	}	
	function shopeeV2UpdatePrice($token, $shopee, $dataPost, $price_url = '/product/update_price'){
			global $local_debug;
			$ts 				= $shopee['ts'];

			$price_url2 		= '/api/v2'.$price_url;
			if($local_debug)
				print 'stock_url2:'.$stock_url2;
			$shopee['token'] 	= $token;
			$sign 				= shopeev2GetSignAccountLvl1($shopee, $price_url2, 2);
			if($local_debug)
				printf ("sign: %s", $sign);
			$commonquery 		= Array(
				'access_token'	=>	$token,

				'partner_id'	=>	$shopee['partner_id'],

				'shop_id'		=> 	$shopee['shop_id'],

				'sign'			=>	$sign,
		

				'timestamp'		=> 	$ts
				
			);

			$dataBPost 			= Array(
				'item_id'	=>	intval($dataPost['item_id']),
				'price_list'=>	Array(
					Array(
						'model_id'		=>	intval($dataPost['model_id']),
						'original_price'=>	intval($dataPost['original_price'])
					)
				)
			);
			if($local_debug)
				print '<hr/>'.json_encode($dataBPost);
			#print_r($commonquery);

 			$price_url3			= $shopee['url'].$price_url.'?'.http_build_query($commonquery);
 			if($local_debug)
 				print 'stock_url3:'.$shopee['url'];
 			#print $shipping_url3;
			if($local_debug)
				print 'line 660: price_url3: <input type="text" value="'.$price_url3.'"/>'; 			
			$output 			= shopeeV2CurlPost($price_url3, $dataBPost);
			return $output;			
	}	
	function shopeeV2UpdateItem($token, $shopee, $dataPost, $update_url = '/product/update_item'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$update_url2		= '/api/v2'.$update_url;
		if($local_debug)
			print 'update_url2:'.$update_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $update_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(

			'access_token'	=>	$token,

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,
	
			'timestamp'		=> 	$ts
			
		);
		if($local_debug){
			print '<pre>';
			print_r($commonquery);
			print '</pre>';
		}
		$update_url3		= $shopee['url'].$update_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>update_url3:'.$update_url3.'<hr/>update_url:'.$update_url.'<hr/>update_url2:'.$update_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 698: update_url3: <input type="text" value="'.$update_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($update_url3, $dataPost);
		if($local_debug){
			print '<pre>output: ';
			print $output;
		}
		return $output;	
	}	
	function shopeeV2GetOrderList($token, $shopee, $param_array,$order_list_url = '/order/get_order_list'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$order_list_url2			= '/api/v2'.$order_list_url;
		if($local_debug)
			print 'order_url2:'.$order_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $order_list_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(
			'access_token'	=>	$token,


			'order_sn_list'	=>	implode(',',$orderid),

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,	

			'timestamp'		=> 	$ts
			
		);

		$commonquery 		= array_merge($param_array, $commonquery);
		if($local_debug){
			print  '<pre>line 742: ';
			print_r($commonquery);
		}
		$order_list_url3	= $shopee['url'].$order_list_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>order_list_url3:'.$order_list_url3.'<hr/>order_list_url:'.$order_list_url.'<hr/>order_list_url2:'.$order_list_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 749: order_list_url3: <input type="text" value="'.$order_list_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($order_list_url3);
		return $output;		
	}	
	function shopeeV2GetShopProfile($token, $shopee, $profile_url='/shop/get_profile'){
		global $local_debug;

		$profile_url2		= '/api/v2'.$profile_url;
		$shopee['token'] 	= $token;
		$sign				= shopeev2GetSignAccountLvl1($shopee, $profile_url2, 2);

		$commonquery 		= Array(

			'partner_id'	=>	$shopee['partner_id'],

			'timestamp'		=> 	$shopee['ts'],

			'access_token'	=>	$token,

			'shop_id'		=>	$shopee['shop_id'],

			'sign'			=>	$sign
		);			

		$getShopInfoUrl = $shopee['url'].$profile_url;

		$profile_url3		= $shopee['url'].$profile_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print 'line 162: profile_url3: <input type="text" value="'.$profile_url3.'"/>';
		$response 		= shopeeV2CurlPost($profile_url3);

		return $response;			
	}	
	function shopeeV2LogisticsGetTrackingNum($token, $shopee, $order_sn, $tracking_url='/logistics/get_tracking_number'){
		global $local_debug;

		$profile_url2		= '/api/v2'.$tracking_url;
		$shopee['token'] 	= $token;
		$sign				= shopeev2GetSignAccountLvl1($shopee, $profile_url2, 2);

		$commonquery 		= Array(

			'order_sn'		=>	$order_sn,

			'partner_id'	=>	$shopee['partner_id'],

			'timestamp'		=> 	$shopee['ts'],

			'access_token'	=>	$token,

			'shop_id'		=>	$shopee['shop_id'],

			'sign'			=>	$sign
		);			

		$getShopInfoUrl = $shopee['url'].$tracking_url;

		$tracking_url3		= $shopee['url'].$tracking_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print 'line 162: tracking_url3: <input type="text" value="'.$tracking_url3.'"/>';
		$response 		= shopeeV2CurlPost($tracking_url3);

		return $response;		
	}
	function shopeev2GetTrackingInfo($token, $shopee, $ordersn, $tracking_url='/logistics/get_tracking_info'){
		global $local_debug;

		$tracking_url2		= '/api/v2'.$tracking_url;
		$shopee['token'] 	= $token;
		$sign				= shopeev2GetSignAccountLvl1($shopee, $tracking_url2, 2);

		$commonquery 		= Array(

			'order_sn'		=>	$ordersn,

			'partner_id'	=>	$shopee['partner_id'],

			'timestamp'		=> 	$shopee['ts'],

			'access_token'	=>	$token,

			'shop_id'		=>	$shopee['shop_id'],

			'sign'			=>	$sign
		);			

		$tracking_url3		= $shopee['url'].$tracking_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print 'line 849: tracking_url3: <input type="text" value="'.$tracking_url3.'"/>';
		$response 		= shopeeV2CurlPost($tracking_url3);

		return $response;			
	}	
	function shopeev2GetBoostedItems($token, $shopee, $boost_url= '/product/get_boosted_list'){

		global $local_debug;

		$boost_url2			= '/api/v2'.$boost_url;
		$shopee['token'] 	= $token;
		$sign				= shopeev2GetSignAccountLvl1($shopee, $boost_url2, 2);

		$commonquery 		= Array(

			'partner_id'	=>	$shopee['partner_id'],

			'timestamp'		=> 	$shopee['ts'],

			'access_token'	=>	$token,

			'shop_id'		=>	$shopee['shop_id'],

			'sign'			=>	$sign
		);			

		$profile_url3		= $shopee['url'].$boost_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print 'line 162: profile_url3: <input type="text" value="'.$profile_url3.'"/>';
		$response 		= shopeeV2CurlPost($profile_url3);

		return $response;			
	}
	function shopeev2PostBoostedItems($token, $shopee, $item_id_array, $boost_url='/product/boost_item'){
		global $local_debug;

		$boost_url2			= '/api/v2'.$boost_url;
		$shopee['token'] 	= $token;
		$sign				= shopeev2GetSignAccountLvl1($shopee, $boost_url2, 2);

		$commonquery 		= Array(

			'partner_id'	=>	$shopee['partner_id'],

			'timestamp'		=> 	$shopee['ts'],

			'access_token'	=>	$token,

			'shop_id'		=>	$shopee['shop_id'],

			'sign'			=>	$sign
		);			
		

		$datapost 			= Array(

			'item_id_list'		=>	$item_id_array, 
		);

		$profile_url3		= $shopee['url'].$boost_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print 'line 162: profile_url3: <input type="text" value="'.$profile_url3.'"/>';
		$response 		= shopeeV2CurlPost($profile_url3, $datapost);

		return $response;			
	}	
	function shopeeV2ItemGetCategory($access_token, $shopee,$cat_url = '/product/get_category'){

		global $local_debug;
		$cat_url2		= '/api/v2'.$cat_url;
		if($local_debug)
			print 'cat_url2:'.$cat_url2;
		$shopee['token'] 	= $access_token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $cat_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		
		$commonquery 		= Array(
			'access_token'	=>	$access_token,

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,	

			'timestamp'		=> 	$shopee['ts']
			
		);

		$cat_url3			= $shopee['url'].$cat_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>cat_url3:'.$cat_url3.'<hr/>cat_url:'.$cat_url.'<hr/>cat_url2:'.$cat_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 531: cat_url3: <input type="text" value="'.$cat_url3.'" size="100"/>'; 			
		$output 			= shopeeV2CurlPost($cat_url3);
		
		return $output;		
	}	
	function shopeev2InitVariation($token, $shopee, $data, $add_variation_url='/product/init_tier_variation'){
		global $local_debug;

		$add_variation_url2	= '/api/v2'.$add_variation_url;
		
		$shopee['token'] 	= $token;
		$sign				= shopeev2GetSignAccountLvl1($shopee, $add_variation_url2, 2);

		$commonquery 		= Array(

			'partner_id'	=>	$shopee['partner_id'],

			'timestamp'		=> 	$shopee['ts'],

			'access_token'	=>	$token,

			'shop_id'		=>	$shopee['shop_id'],

			'sign'			=>	$sign
		);			
		$post_data = json_encode($data, true);
		$add_variation_url3		= $shopee['url'].$add_variation_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print 'line 162: add_variation_url3: <input type="text" value="'.$add_variation_url3.'"/>';
		$response 		= shopeeV2CurlPost($add_variation_url3, $data);

		return $response;			
	}		
	function shopeeV2ItemGetBrand($token, $shopee, $category_id, $offset=1, $brand_url = '/product/get_brand_list'){
		global $local_debug;
		$ts 				= $shopee['ts'];

		$brand_url2		= '/api/v2'.$brand_url;
		if($local_debug)
			print 'attr_url2:'.$attr_url2;
		$shopee['token'] 	= $token;
		$sign 				= shopeev2GetSignAccountLvl1($shopee, $brand_url2, 2);
		if($local_debug)
			printf ("sign: %s", $sign);
		$commonquery 		= Array(
			'access_token'	=>	$token,

			'category_id'	=>	$category_id,

			'offset'		=> $offset,

			'page_size'		=>	100,

			'partner_id'	=>	$shopee['partner_id'],

			'shop_id'		=> 	$shopee['shop_id'],

			'sign'			=>	$sign,	

			'status'		=>	1, //normal brand, 2, pending brand

			'timestamp'		=> 	$ts
			
		);

		$brand_url3			= $shopee['url'].$brand_url.'?'.http_build_query($commonquery);
		if($local_debug)
			print '<hr/>brand_url3:'.$brand_url3.'<hr/>brand_url:'.$brand_url.'<hr/>brand_url2:'.$brand_url2;
		#print $shipping_url3;
		if($local_debug)
			print 'line 531: brand_url3: <input type="text" value="'.$brand_url3.'"/>'; 			
		$output 			= shopeeV2CurlPost($brand_url3);
		#print '<hr/>'.$output;
		return $output;			
	}		
?>
