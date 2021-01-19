<?php 

namespace PagHiper;

use PagHiper\BankAccount;
use PagHiper\Invoice;
use PagHiper\Transaction;
use GuzzleHttp\Client;

class PagHiper {

	/**
	 * @var PagHiper\API\BankAccount;
	 */
	private $bank_account;

	/**
	 * @var PagHiper\API\Invoice;
	 */
	private $invoice;

	/**
	 * @var PagHiper\API\Transaction;
	 */
	private $transaction;

	/**
	 * @var array PagHiper auth credentials
	 */
	private $auth;

	/**
	 * @var GuzzleHttp\Client;
	 */
	private $client;

	public function __construct($api_key, $token) {

		$this->auth = [
            'apiKey' => $api_key,
            'token' => $token
		];

        $this->client = new Client([
            'base_uri' => 'https://api.paghiper.com',
			'headers' => [
				'Accept' => 'application/json',
				'Accept-Charset' => 'UTF-8',
				'Accept-Encoding' => 'application/json',
				'Content-Type' => 'application/json'
			]
		]);
		
		$this->bank_account	= new BankAccount($this);
		$this->invoice		= new Invoice($this);
		$this->transaction	= new Transaction($this);

	}

	/**
	 * Send out a request to the API
	 * 
	 * @param string $url -
	 * Desired endpoint for our request
	 * 
	 * @param array $params -
	 * Data for our endpoint 
	 */
    public function request($url, $data = []){

		$data = array_merge($this->auth, $data);

        try {

            $response = $this->client->request( 'POST', $url, ['json' => $data] );
			return json_decode($response->getBody(), true);
			
        } catch (\Exception $e) {

			throw $e;
			
        }
    }

	/**
	 * @var PagHiper\BankAccount
	 */
	public function bank_account() {
		return $this->bank_account;
	}

	/**
	 * @var PagHiper\Invoice
	 */
	public function invoice() {
		return $this->invoice;
	}

	/**
	 * @var PagHiper\Transaction
	 */
	public function transaction() {
		return $this->transaction;
	}

}