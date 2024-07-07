<?php

namespace Kosar501\MexcPhpApi;

use Decimal\Decimal;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Kosar501\MexcPhpApi\Enums\OrderSideEnum;
use Kosar501\MexcPhpApi\Enums\OrderStatusEnum;

class Mexc
{
    protected $api_key;
    protected $api_secret;
    protected $proxy;
    protected $recvWindow = 5000;

    function __construct($api_key, $api_secret, $proxy = null)
    {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->proxy = $proxy;
    }

    //****************** Quote ******************//
    /**
     * @param $symbol
     * @return mixed
     * @throws GuzzleException
     * symbol price ticker >> all price tickers
     * symbol ex: ETHUSDT
     */
    public function ticker($symbol = null)
    {
        $data = [
            'symbol' => $symbol,
        ];

        return $this->request('/api/v3/ticker/price?' . http_build_query($data), 'get');
    }
    //****************** Quote******************//


    //****************** Account ******************//
    /**
     * @param $symbol
     * @return mixed
     * @throws GuzzleException
     * symbol price ticker >> all price tickers
     * symbol ex: ETHUSDT
     */
    public function balances(): mixed
    {
        $data = [
            'timestamp' => $this->generateTimestamp(),
            'recvWindow' => $this->recvWindow
        ];
        $data['signature'] = $this->generateSignature($data);

        $response = $this->request('/api/v3/account?' . http_build_query($data), 'get');
        return !is_array($response) && @$response->balances ? $response->balances : (@$response['balances'] ? $response['balances'] : []);
    }
    //****************** Account******************//

    //****************** Orders ******************//
    /**
     * @param string $symbol
     * @param string $origClientOrderId
     * @param string $orderId
     * @throws GuzzleException
     * Get order Status
     */
    public function orderHistory($symbol, $orderId = null, $origClientOrderId = null)
    {
        $data = [
            'symbol' => $symbol,
            'orderId' => $orderId,
            'origClientOrderId' => $origClientOrderId,
            'timestamp' => $this->generateTimestamp(),
            'recvWindow' => $this->recvWindow
        ];
        $data['signature'] = $this->generateSignature($data);
        return $this->request('/api/v3/order?' . http_build_query($data), 'get');
    }

    /**
     * @param string $symbol
     * @param string{OrderSideEnum::keys} $side
     * @param string{OrderTypeEnum::keys} $type
     * @param Decimal $quantity
     * @param Decimal $quoteOrderQty
     * @param Decimal $price
     * @param STRING $newClientOrderId
     * @throws GuzzleException
     * submit market/limit order to buy or sell
     */
    public function addOrder($symbol, $side, $type, $quantity = null, $quoteOrderQty = null, $price = null, $newClientOrderId = null)
    {
        $data = [
            'symbol' => $symbol,
            'side' => $side,
            'type' => $type,
            'quantity' => $quantity,
            'quoteOrderQty' => $quoteOrderQty,
            'price' => $price,
            'newClientOrderId' => $newClientOrderId,
            'timestamp' => $this->generateTimestamp(),
            'recvWindow' => $this->recvWindow
        ];
        $data['signature'] = $this->generateSignature($data);
        return $this->request('/api/v3/order?' . http_build_query($data));
    }
    //****************** Orders ******************//

    //****************** Wallet Endpoint ******************//
    /**
     * Weight = 10
     * @throws GuzzleException
     * returns All coins name && networks list
     */
    public function exchangeInfo()
    {
        $data = [
            'timestamp' => $this->generateTimestamp(),
            'recvWindow' => $this->recvWindow
        ];
        $data['signature'] = $this->generateSignature($data);
        return $this->request('/api/v3/capital/config/getall?' . http_build_query($data), 'get');
    }

    /**
     * @param $coin
     * @param $network
     * @return mixed
     * @throws GuzzleException
     * get Already generated deposit address for selected coin && network
     */
    public function getDepositAddress($coin, $network = null)
    {
        $data = [
            'coin' => $coin,
            'network' => $network,
            'timestamp' => $this->generateTimestamp(),
            'recvWindow' => $this->recvWindow
        ];
        $data['signature'] = $this->generateSignature($data);
        return $this->request('/api/v3/capital/deposit/address?' . http_build_query($data), 'get');
    }

    /**
     * @param $coin
     * @param $network
     * @return mixed
     * @throws GuzzleException
     * generate deposit address for selected coin && network
     */
    public function generateDepositAddress($coin, $network = null)
    {
        $data = [
            'coin' => $coin,
            'network' => $network,
            'timestamp' => $this->generateTimestamp(),
            'recvWindow' => $this->recvWindow
        ];
        $data['signature'] = $this->generateSignature($data);
        return $this->request('/api/v3/capital/deposit/address?' . http_build_query($data));
    }

    /**
     * @param string $coin
     * @param string $status
     * @param string $startTime default: 90 days ago from current time
     * @param string $endTime default:current time
     * @param string $limit default:1000,max:1000
     * @return mixed
     * @throws GuzzleException
     * generate deposit address for selected coin && network
     */
    public function historyOfDeposit($coin = null, $status = null, $startTime = null, $endTime = null, $limit = null)
    {
        $data = [
            'coin' => $coin,
            'status' => $status,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'limit' => $limit,
            'timestamp' => $this->generateTimestamp(),
            'recvWindow' => $this->recvWindow
        ];
        $data['signature'] = $this->generateSignature($data);
        return $this->request('/api/v3/capital/deposit/hisrec?' . http_build_query($data), 'get');
    }

    /**
     * Check special Tx_id exist in depostit history or not
     * @param string $txId
     * @param string $coin default: null
     * @param string $status default: null
     * @param string $startTime default: 90 days ago from current time
     * @param string $endTime default:current time
     * @param string $limit default:1000,max:1000
     * @return array
     * @throws GuzzleException
     */
    public function findTaxIdExistInDepositHistory($txId, $coin = null, $status = null, $startTime = null, $endTime = null, $limit = null)
    {
        $history = $this->historyOfDeposit($coin, $status, $startTime, $endTime, $limit);
        if (is_array($history))
            foreach ($history as $depositItem)
                if ($depositItem->txId == trim($txId))
                    return [$depositItem];
        return [];
    }


    /**
     * @param string $coin
     * @param string $withdrawAddress Withdraw address only support address which added in withdrawal settings on website.
     * @param string $withdrawAmount
     * @param string $withdrawNetwork
     * @param string $memo (If memo is required in the address, it must be passed in)
     * @param string $withdrawOrderId
     * @param string $remark
     * @return mixed
     * @throws GuzzleException
     */
    public function withdraw($coin, $withdrawAddress, $withdrawAmount, $withdrawNetwork = null, $memo = null, $withdrawOrderId = null, $remark = null)
    {
        $data = [
            'coin' => $coin,
            'address' => $withdrawAddress,
            'amount' => $withdrawAmount,
            'network' => $withdrawNetwork,
            'memo' => $memo,
            'withdrawOrderId' => $withdrawOrderId,
            'remark' => $remark,
            'timestamp' => $this->generateTimestamp(),
            'recvWindow' => $this->recvWindow
        ];
        $data['signature'] = $this->generateSignature($data);
        return $this->request('/api/v3/capital/withdraw/apply?' . http_build_query($data));
    }

    /**
     * @param string $coin
     * @param string $status
     * @param string $limit default:1000,max:1000
     * @param string $startTime default: 90 days ago from current time
     * @param string $endTime default:current time
     * @return mixed
     * @throws GuzzleException
     */
    public function historyOfWithdraw($coin = null, $status = null, $limit = null, $startTime = null, $endTime = null)
    {
        $data = [
            'coin' => $coin,
            'status' => $status,
            'limit' => $limit,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'timestamp' => $this->generateTimestamp(),
            'recvWindow' => $this->recvWindow
        ];
        $data['signature'] = $this->generateSignature($data);
        return $this->request('/api/v3/capital/withdraw/history?' . http_build_query($data), 'get');
    }

    /**
     * @param string $WithdrawId
     * @param string $coin default: null
     * @param string $status default: null
     * @param string $startTime default: 90 days ago from current time
     * @param string $endTime default:current time
     * @param string $limit default:1000,max:1000
     * @return array
     * @throws GuzzleException
     */
    public function findWithdrawIDExistInWithdrawHistory($WithdrawId, $coin = null, $status = null, $startTime = null, $endTime = null, $limit = null)
    {
        $history = $this->historyOfWithdraw($coin, $status, $startTime, $endTime, $limit);
        if (is_array($history))
            foreach ($history as $withdrawItem)
                if ($withdrawItem->id == trim($WithdrawId))
                    return [$withdrawItem];
        return [];
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

    private function generateSignature($data)
    {
        $query = http_build_query($data);
        $query = str_replace(['%40'], ['@'], $query); //if send data type "e-mail" then binance return: [Signature for this request is not valid.]
        return hash_hmac('sha256', $query, $this->api_secret);
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
                'X-MEXC-APIKEY' => $this->api_key,
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
    //****************** Private Functions ******************//


}
