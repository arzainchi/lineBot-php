<?php
require __DIR__ . '/vendor/autoload.php';
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
use \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
// set false for production
$pass_signature = true;
// set LINE channel_access_token and channel_secret
$channel_access_token = getenv("channel_access_token");
$channel_secret = getenv("channel_secret");
// inisiasi objek bot
//include 'codenya.php';
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
$configs = [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);
$bot->getProfile(userId);
$bot->getMessageContent(messageId);
// buat route untuk url homepage
$app->get('/', function ($req, $res) {
    echo "Welcome at Slim Framework";
});
// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature) {
  // get request body and line signature header
    $body = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';
  // log body and signature
    file_put_contents('php://stderr', 'Body: ' . $body);
    if ($pass_signature === false) {
    // is LINE_SIGNATURE exists in request header?
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }
    // is this request comes from LINE?
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }
    $data = json_decode($body, true);
    if (is_array($data['events'])) {
        foreach ($data['events'] as $event) {
            if ($event['type'] == 'message') {
                $userId = $event['source']['userId'];
                $groupId = $event['source']['groupId'];
                $getprofile = $bot->getProfile($userId);
                $profile = $getprofile->getJSONDecodedBody();
                $messageFromUser = strtolower($event['message']['text']);
                require 'http://arizalmhmd5.000webhostapp.com/logic.php';
                $AI = get_data($userId, $messageFromUser);
                $a = (explode(' ', $messageFromUser));
                if ($a[0] == 'apakah') {
                    $result = $bot->replyText($event['replyToken'], (rand(0, 1)) ? "iya" : "tidak");
                }
                if ($event['source']['type'] == 'group' or $event['source']['type'] == 'room') {
                    if ($event['message']['type'] == 'text') {

                    }
                } else {
                    if ($event['message']['type'] == 'text') {
                        if ($AI['kategori'] == 'thanks') {
                            $replyMessage = new StickerMessageBuilder($AI['jawab'][0], $AI['jawab'][1]);
                            $result = $bot->replyMessage($event['replyToken'], $replyMessage);
                        } else {
                            $result = $bot->replyText($event['replyToken'], $AI['jawab']);
                        }
                    }
                }
            }
        }
    }

});
$app->run();