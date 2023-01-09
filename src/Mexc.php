<?php

namespace Kosar\MexcPhpApi;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Mexc
{
    protected $api;
    protected $api_secret;
    protected $proxy;
    protected $recvWindow = 5000;

    function __construct($api_key, $api_secret, $proxy = null)
    {
        $this->api = $api_key;
        $this->api_secret = $api_secret;
        $this->proxy = $proxy;
    }

    //****************** Wallet Endpoint ******************//

    /**
     * Weight = 10
     * @throws GuzzleException
     */
    public function exchangeInfo()
    {
        $data = [
            'timestamp' => $this->generateTimestamp(),
            'recvWindow' => $this->recvWindow
        ];
        return $this->request('/api/v3/capital/config/getall?' . http_build_query($data), 'get');
    }
    //****************** Wallet Endpoint ******************//

    //****************** Private Functions ******************//
    /**
     * @throws GuzzleException
     */
    private function generateTimestamp()
    {
        $getTime = $this->request('/open/api/v2/common/timestamp', 'get');
        return $getTime->data;
    }

    /**
     * @throws GuzzleException
     */
    private function request($path, $type = 'post', $parameters = [])
    {
        $url = 'https://api.mexc.com' . $path;
        $client = new Client();

        $options = [
            'proxy' => $this->proxy,
            'headers' => [
                'User-Agent' => 'Mozilla/4.0 (compatible; PHP Mexc API)',
                'X-MEXC-APIKEY' => $this->api,
                'Content-Type' => 'application/json'
            ]
        ];

        if ($type == 'post') {
            $options['form_params'] = $parameters;
            $response = $client->post($url, $options);
        } else {
            $response = $client->get($url, $options);
        }


        return json_decode($response->getBody());
    }


}
