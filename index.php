<?php

include 'config.php';

/* 設定config */
$config = new config();
$url = $config->url;
$channelSecret = $config->channelSecret;
$channelAccessToken = $config->channelAccessToken;


/* 讀取資訊 */ 
$httpRequestBody = file_get_contents('php://input'); 
$headerSignature = $_SERVER['HTTP_X_LINE_SIGNATURE']; 


/* 驗證來源是否是LINE官方伺服器 */
$Hash = hash_hmac('sha256', $httpRequestBody, $channelSecret, true); 
$HashSignature = base64_encode($Hash); 
if($HashSignature != $headerSignature) 
{ 
    return 'hash error!'; 
} 

/* 解析 */ 
$DataBody=json_decode($httpRequestBody, true); 

/* 逐一執行事件 */  
foreach($DataBody['events'] as $Event) 
{ 
    /* 訊息 */
    if($Event['type'] == 'message') 
    { 
        $Payload = [ 
            'replyToken' => $Event['replyToken'],
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $Event['message']['text'],
                ]
            ]
        ];
        
        /* 傳送訊息 */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($Payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $channelAccessToken
        ]);
        $Result = curl_exec($ch);
        curl_close($ch);
    }
}



