<?php 

namespace Rapnet\RapnetPriceList;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

require('env.php');

class Index {

    private $config;

    public function __construct($clientId = null, $clientSecret = null) {
        $this->config = 
            [
              'base_path' => $_ENV['RAPNET_GATEWAY_BASE_URL'],
              'authorization_url' => $_ENV['RAPNET_AUTH_URL'],
              'machine_auth_url' => $_ENV['RAPNET_MACHINE_TO_MACHINE_AUTH_URL'],
              'client_id' => $clientId,
              'client_secret' => $clientSecret,
              'redirect_uri' => null,              
              'token_callback' => null,
              'pricelist_url' => $_ENV['RAPNET_GATEWAY_BASE_URL'].'/pricelist/api',
              'jwt' => null,
              'scope' => 'manageListings priceListWeekly instantInventory',
              'audience' => 'https://pricelist.rapnetapis.com'
            ];
    }

     public function authorize($redirectUrl)
    {

        $client = new GuzzleClient(['verify' => false]);
        $url = "{$this->config['authorization_url']}/authorize?response_type=code&client_id={$this->config['client_id']}&redirect_uri={$redirectUrl}&audience={$this->config['audience']}&scope={$this->config['scope']}";

        header("Location: {$url}");
        exit();
    }

    public function getAuthToken($code, $redirectUrl)
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['machine_auth_url']}/api/get";

            $response = $client->request('POST',  $url, [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ],
                    'json' => [
                        'client_id' => $this->config['client_id'],
                        'client_secret' => $this->config['client_secret'],
                        'code' => $code,
                        'redirect_uri' => $redirectUrl
                    ],
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }        
    }
    

    public function getAuthTokenMachineToMachinMethod()
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['machine_auth_url']}/api/get";

            $response = $client->request('GET',  $url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'client_id' => $this->config['client_id'],
                        'client_secret' => $this->config['client_secret']
                    ],
                ]
            );
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }        
    }   

    
    /*
        'application/json'
        'application/xml'
        'application/dbf'
    */
    public function getPricesList($token, $shape = 'Round', $acceptType = 'application/json')
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['pricelist_url']}/Prices/list?shape={$shape}";            

            $response = $client->request('GET', $url, [
                'headers' => [
                    'accept' => "${acceptType}",
                    'Content-Type' => 'application/json',
                    'authorization' => "Bearer {$token}"
                ]
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }        
    }   

    public function getNormalizedPricesList($token, $shape = 'Round', $csvnormalized = true)
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['pricelist_url']}/Prices/list?shape={$shape}&csvnormalized={$csvnormalized}";            

            $response = $client->request('GET', $url, [
                'headers' => [
                    'accept' => 'text/csv',
                    'Content-Type' => 'application/json',
                    'authorization' => "Bearer {$token}"
                ]
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }        
    }

    /*
        'application/xml'
    */

    public function getPriceItems($token, $shape = 'Round', $size, $color, $clarity, $acceptType = 'application/json')
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));

            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['pricelist_url']}/Prices?shape={$shape}&size={$size}&color={$color}&clarity={$clarity}";

            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => "${acceptType}",
                    'Content-Type' => 'application/json',
                    'authorization' => "Bearer {$token}"
                ]
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }        
    }
   
    public function getPricesChanges($token, $shape = 'Round')
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory([
                'max_retry_attempts' => 2,
                'retry_on_status' => [429, 503, 500]
            ]));
            
            $client = new GuzzleClient(['verify' => false, 'handler' => $stack]);
            $url = "{$this->config['pricelist_url']}/Prices/changes?shape={$shape}";

            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'authorization' => "Bearer {$token}"
                ]
            ]);

            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return $e->getMessage();
        }        
    }
}