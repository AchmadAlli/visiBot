<?php
require __DIR__ . '/vendor/autoload.php';
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
 
// set false for production
$pass_signature = true;
 
// set LINE channel_access_token and channel_secret
$channel_access_token = getenv('ch_acc_token');
$channel_secret = getenv('ch_secret');
 
// inisiasi objek bot
//include 'codenya.php';
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);
$bot->getProfile(userId);
$bot->getMessageContent(messageId);
// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  echo "Welcome at Slim Framework";
});
 
// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);
 
    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }
 
        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }
 
    // kode aplikasi nanti disini
    $data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {   
                $userId     = $event['source']['userId'];
                $getprofile = $bot->getProfile($userId);
                $profile    = $getprofile->getJSONDecodedBody();
                $greetings  = new TextMessageBuilder("Halo, ".$profile['displayName']);
                if(
                 $event['source']['type'] == 'group' or
                 $event['source']['type'] == 'room'
                ){
                    if($event['source']['userId']){

                        if (substr($event['message']['text'],0,5)=='<?php') {
                            $data = array(
                                'php' => $event['message']['text']
                            );
                            $babi=file_get_contents('http://farkhan.000webhostapp.com/nutshell/babi.php?'.http_build_query($data));
                            $result = $bot->replyText($event['replyToken'], $babi);
                        }
                        return $res->withJson($result->getJSONDecodedBody(), $event['message']['text'].$result->getHTTPStatus());
                    } 
                } else {
                    if($event['message']['type'] == 'text')
                    {
                        $userId     = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile    = $getprofile->getJSONDecodedBody();
                        
                        if (substr($event['message']['text'],0,5)=='<?php') {
                            $data = array(
                                'php' => $event['message']['text']
                            );
                            $babi=file_get_contents('http://farkhan.000webhostapp.com/nutshell/babi.php?'.http_build_query($data));
                            $result = $bot->replyText($event['replyToken'], $babi);
                        }
                        if ($event['message']['text'] == 'hai') {
                            $result = $bot->replyText($event['replyToken'], "hello");
                        }
                        
                    }
                }
            }
        }
    }
});
$app->run();