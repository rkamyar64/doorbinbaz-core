<?php

namespace App\Http\Libs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Kavenegar\KavenegarApi;

class GetApiData
{
    const API_KAVE_NEGAR = "4D5A6E6C6F4C66534D6E3841305370795451654436696F4E766C6F3057477769657453634E342B397936413D";
    public function ApiCall($type, $method, $BaseUrl, $url, $token_type, $token, $data, $payment_token = null)
    {
        try {
            $josn_data = null;
            $client = new Client([
                'base_uri' => $BaseUrl,
                'timeout' => 800,
            ]);
            $sendData = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ];
            if ($token)
                $sendData["headers"]["Authorization"] = $token_type . " " . $token;
            if ($data)
                $sendData["body"] = $type === "json" ? json_encode($data) : $data;
            if ($payment_token)
                $sendData["headers"]["token"] = $payment_token;
            $res = $client->request($method, $url, $sendData);
            if ($res->getStatusCode() == 200)
                $josn_data = json_decode($res->getBody()->getContents());

//            dd($josn_data);
            return $josn_data;
        } catch (\Exception $err) {
            SystemErrorLog::create(["status_code" => $err->getCode(), "manufacture" => "", "error_log" => utf8_encode($err->getMessage())]);
            return false;
        }

    }


    public function send($url, $method = 'GET', $params = [], $headers = [])
    {
        $ch = curl_init();

        if ($method === 'GET' && !empty($params)) {
            $queryString = http_build_query($params);
            $url .= (strpos($url, '?') === false ? '?' : '&') . $queryString;
        }


        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (in_array($method, ['POST', 'PUT', 'DELETE']) && !empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            // Automatically set JSON header if not set
            if (!in_array('Content-Type: application/json', $headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/json']));
            }
        }
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        return [
            'status' => $status,
            'response' => $response,
            'error' => $error
        ];
    }

    public function KaveNegarPattern($receptor,$pattern,$token1=null,$token2=null,$token3=null,$token4=null)
    {
        $sendUrl = "https://api.kavenegar.com/v1/".self::API_KAVE_NEGAR."/verify/lookup.json";
        $params = [
            'receptor' => $receptor,
            'token' =>$token1,
            'template' => $pattern
        ];
        if(isset($token2))
            $params['token2'] = $token2;
        if(isset($token3))
            $params['token3'] = $token3;
        if(isset($token4))
            $params['token4'] = $token4;
        self::send($sendUrl,"GET",$params);
    }
}
