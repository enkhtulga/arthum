<?php

namespace App\Utility;

use App\Models\OtpConfiguration;
use App\Utility\MimoUtility;
use Twilio\Rest\Client;

class SendSMSUtility
{
    public static function sendSMS($to, $from, $text, $template_id)
    {
        if (OtpConfiguration::where('type', 'nexmo')->first()->value == 1) {
            $api_key = env("NEXMO_KEY"); //put ssl provided api_token here
            $api_secret = env("NEXMO_SECRET"); // put ssl provided sid here

            $params = [
                "api_key" => $api_key,
                "api_secret" => $api_secret,
                "from" => $from,
                "text" => $text,
                "to" => $to
            ];

            $url = "https://rest.nexmo.com/sms/json";
            $params = json_encode($params);

            $ch = curl_init(); // Initialize cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params),
                'accept:application/json'
            ));
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;

            $url = "https://www.msegat.com/gw/sendsms.php";
            $data = [
                "apiKey" => env('MSEGAT_API_KEY'),
                "numbers" => $to,
                "userName" => env('MSEGAT_USERNAME'),
                "userSender" => env('MSEGAT_USER_SENDER'),
                "msg" => $text
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } 
        elseif (OtpConfiguration::where('type', 'sparrow')->first()->value == 1) {

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://sms-api.telcocom.mn/sms-api/v1/sms/telco/send?tenantId=637ca1f1e6223943ac10384e&toNumber=99429967&sms=aaa',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'telco-auth-token: da39a3ee5e6b4b0d3255bfef95601890afd80709'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

            return $response;
        }
        return true;
    }
}

