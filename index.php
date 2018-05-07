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
$channel_access_token = getenv("catheroku");
$channel_secret = getenv("csheroku");
 
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
                        $a = (explode('-',$event['message']['text']));
                        if ($a[0]=="/tambah") {
                            $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/storeData.php?groupid='.$event['source']['groupId'].'&nama_jadwal='.urlencode($a[1]).'&isi_jadwal='.urlencode($a[2]));
                            $obj = json_decode($stored, TRUE);
                            $result = $bot->replyText($event['replyToken'], $obj['message']);
                        }
                        else if ($a[0]=="/semua") {
                            $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/GetData.php?groupid='.$event['source']['groupId']);
                            $datanya = json_decode($stored, TRUE);
                            $hasilnya="Note Yang Disimpan";
                            if (is_array($datanya) || is_object($datanyas)) {
                                foreach ($datanya as $datanyas) {
                                    echo $datanyas['jadwal'];
                                    foreach($datanyas as $datanyass)
                                    {
                                        $hasilnya=$hasilnya."\n".$datanyass['nama_jadwal'];
                                    }
                                }   
                            }
                            
                            $result = $bot->replyText($event['replyToken'],$hasilnya);
                        }else if ($a[0]=="/detail") {
                            $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/GetData.php?groupid='.$event['source']['groupId'].'&nama_jadwal='.urlencode($a[1]));
                            $datanya = json_decode($stored, TRUE);
                            $hasilnya="Detail Note ".$a[1];
                            if (is_array($datanya) || is_object($datanyas)) {
                                foreach ($datanya as $datanyas) {
                                    echo $datanyas['jadwal'];
                                    foreach($datanyas as $datanyass)
                                    {
                                        $hasilnya=$hasilnya."\n".$datanyass['detail'];
                                    }
                                }   
                            }
                            
                            $result = $bot->replyText($event['replyToken'],$hasilnya);
                        }else if ($a[0]=="/hapus") {
                            $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/deleteNote.php?groupid='.$event['source']['groupId'].'&nama_jadwal='.urlencode($a[1]));
                            $obj = json_decode($stored, TRUE);
                            $result = $bot->replyText($event['replyToken'], $obj['message']);
                        }
                        // else if ($a[0]=="/help") {
                        //     $help="menambah note\n/tambah-nama note-detail note\nmelihat semua note\n/semua\nmelihat detail note\n/detail-nama note\nmenghapus note\n/hapus-nama note";
                        //     $result = $bot->replyText($event['replyToken'], $help);
                        // }
                        // else if ($a[0]=="/ig") {
                        //     $stored = file_get_contents("https://www.instagram.com/$a[1]/?__a=1");
                        //     $obj = json_decode($stored, TRUE);
                        //     $multiMessageBuilder = new MultiMessageBuilder();
                        //     if ($obj['graphql']['user']['is_private']!="false") {
                        //         $nomer=0;
                        //         if (!empty($a[2])) {
                        //             $nomer=$a[2]-1;
                        //             if ($a[2]>12) {
                        //                 $nomer=($a[2]%12)-1;
                        //             }
                        //         }
                        //         $linkfoto=$obj['graphql']['user']['edge_owner_to_timeline_media']['edges']["$nomer"]['node']['display_url'];
                        //         $linkfotoprev=$obj['graphql']['user']['edge_owner_to_timeline_media']['edges']["$nomer"]['node']['thumbnail_src'];
                        //         $image = new ImageMessageBuilder($linkfoto, $linkfotoprev);
                        //         $multiMessageBuilder->add($image);
                        //     }else{
                        //          $text = new TextMessageBuilder("akun ini di lock");
                        //          $multiMessageBuilder->add($text);
                        //     }    
                        //     $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);
                        // }




                        if ($userId=="U4f3b524bfcd08556173108d04ae067ad") {
                            if ($a[0]=="/ktpkk") {
                                $stored = file_get_contents('http://farkhan.000webhostapp.com/nutshell/read.php?AksesToken='.getenv("csheroku"));
                                $obj = json_decode($stored, TRUE);
                                $result = $bot->replyText($event['replyToken'], $obj['Data'][0]['nik_kk']);
                            }
                        }



                        //explain sheell
                        $a = (explode('#',$event['message']['text']));
                        if ($a[0]=="/eshell") {
                            $qq=file_get_contents('https://explainshell.com/explain?cmd='.urldecode($a[1]));
                            error_reporting(0);
                            $dochtml = new DOMDocument;
                            $dochtml->loadHTML($qq);
                            $prgs = $dochtml->getElementById('help');
                            $result = $bot->replyText($event['replyToken'], $prgs->nodeValue);
                        }
                        if (substr($event['message']['text'],0,5)=='<?php') {
                            $data = array(
                                'php' => $event['message']['text']
                            );
                            $babi=file_get_contents('http://farkhan.000webhostapp.com/nutshell/babi.php?'.http_build_query($data));
                            $result = $bot->replyText($event['replyToken'], $babi);
                        }
                        return $res->withJson($result->getJSONDecodedBody(), $event['message']['text'].$result->getHTTPStatus());
                    } else {
                        if (substr($event['message']['text'],0,2)=='IP' & strlen($event['message']['text'])==18){
                            $result = $bot->replyText($event['replyToken'], 'Add terlebih dahulu');   
                        }
                        return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                    }  
                } else {
                    if($event['message']['type'] == 'text')
                    {
                        $userId     = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile    = $getprofile->getJSONDecodedBody();
                        $a = (explode('-',$event['message']['text']));
                        if ($a[0]=="/tambah") {
                            $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/storeData.php?groupid='.$event['source']['userId'].'&nama_jadwal='.urlencode($a[1]).'&isi_jadwal='.urlencode($a[2]));
                            $obj = json_decode($stored, TRUE);
                            $result = $bot->replyText($event['replyToken'], $obj['message']);
                        }
                        else if ($a[0]=="/semua") {
                            $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/GetData.php?groupid='.$event['source']['userId']);
                            $datanya = json_decode($stored, TRUE);
                            $hasilnya="Note Yang Disimpan";
                            if (is_array($datanya) || is_object($datanyas)) {
                                foreach ($datanya as $datanyas) {
                                    echo $datanyas['jadwal'];
                                    foreach($datanyas as $datanyass)
                                    {
                                        $hasilnya=$hasilnya."\n".$datanyass['nama_jadwal'];
                                    }
                                }   
                            }
                            $result = $bot->replyText($event['replyToken'],$hasilnya);
                        }else if ($a[0]=="/detail") {
                            $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/GetData.php?groupid='.$event['source']['userId'].'&nama_jadwal='.urlencode($a[1]));
                            $datanya = json_decode($stored, TRUE);
                            $hasilnya="Detail Note ".$a[1];
                            if (is_array($datanya) || is_object($datanyas)) {
                                foreach ($datanya as $datanyas) {
                                    echo $datanyas['jadwal'];
                                    foreach($datanyas as $datanyass)
                                    {
                                        $hasilnya=$hasilnya."\n".$datanyass['detail'];
                                    }
                                }   
                            }
                            $result = $bot->replyText($event['replyToken'],$hasilnya);
                        }else if ($a[0]=="/hapus") {
                            $stored = file_get_contents('http://farkhan.000webhostapp.com/tae/deleteNote.php?groupid='.$event['source']['userId'].'&nama_jadwal='.urlencode($a[1]));
                            $obj = json_decode($stored, TRUE);
                            $result = $bot->replyText($event['replyToken'], $obj['message']);
                        }
                        // else if ($a[0]=="/help") {
                        //     $help="menambah note\n/tambah-nama note-detail note\nmelihat semua note\n/semua\nmelihat detail note\n/detail-nama note\nmenghapus note\n/hapus-nama note";
                        //     $result = $bot->replyText($event['replyToken'], $help);
                        // }
                        else if ($a[0]=="/userid") {
                            $result = $bot->replyText($event['replyToken'], $userId);
                        }
                        // else if ($a[0]=="/simi") {
                        //     $stored = file_get_contents('http://farkhan.000webhostapp.com/nutshell/simsimi_api.php?text='.urlencode($a[1]));
                        //     $coba=json_decode($stored);
                        //     $result = $bot->replyText($event['replyToken'], $coba->response);
                        // }
                        // else if ($a[0]=="/ig") {
                        //     $stored = file_get_contents("https://www.instagram.com/$a[1]/?__a=1");
                        //     $obj = json_decode($stored, TRUE);
                        //     $multiMessageBuilder = new MultiMessageBuilder();
                        //     if ($obj['graphql']['user']['is_private']!="false") {
                        //         $nomer=0;
                        //         if (!empty($a[2])) {
                        //             $nomer=$a[2]-1;
                        //             if ($a[2]>12) {
                        //                 $nomer=($a[2]%12)-1;
                        //             }
                        //         }
                        //         $linkfoto=$obj['graphql']['user']['edge_owner_to_timeline_media']['edges']["$nomer"]['node']['display_url'];
                        //         $linkfotoprev=$obj['graphql']['user']['edge_owner_to_timeline_media']['edges']["$nomer"]['node']['thumbnail_src'];
                        //         $image = new ImageMessageBuilder($linkfoto, $linkfotoprev);
                        //         $multiMessageBuilder->add($image);
                        //     }else{
                        //          $text = new TextMessageBuilder("akun ini di lock");
                        //          $multiMessageBuilder->add($text);
                        //     }    
                        //     $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);
                        // }




                        if ($userId=="U4f3b524bfcd08556173108d04ae067ad") {
                            if ($a[0]=="/ktpkk") {
                                $stored = file_get_contents('http://farkhan.000webhostapp.com/nutshell/read.php?AksesToken='.getenv("csheroku"));
                                $obj = json_decode($stored, TRUE);
                                $result = $bot->replyText($event['replyToken'], $obj['Data'][0]['nik_kk']);
                            }
                        }


                        //explain sheell
                        $a = (explode('#',$event['message']['text']));
                        if ($a[0]=="/eshell") {
                            $qq=file_get_contents('https://explainshell.com/explain?cmd='.urldecode($a[1]));
                            error_reporting(0);
                            $dochtml = new DOMDocument;
                            $dochtml->loadHTML($qq);
                            $prgs = $dochtml->getElementById('help');
                            $result = $bot->replyText($event['replyToken'], $prgs->nodeValue);
                        }
                        if (substr($event['message']['text'],0,5)=='<?php') {
                            $data = array(
                                'php' => $event['message']['text']
                            );
                            $babi=file_get_contents('http://farkhan.000webhostapp.com/nutshell/babi.php?'.http_build_query($data));
                            $result = $bot->replyText($event['replyToken'], $babi);
                        }
                        
                    }
                }
            }
        }
    }
});
$app->run();