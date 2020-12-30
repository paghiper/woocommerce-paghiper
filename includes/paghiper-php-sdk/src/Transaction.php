<?php 

namespace PagHiper;

class Transaction extends PagHiper {
	
    const CREATE_PIX_ENDPOINT = 'https://pix.paghiper.com/invoice/create/';
    const CREATE_BILLET_ENDPOINT = 'https://api.paghiper.com/transaction/create/';
    const CANCEL_ENDPOINT = '/transaction/cancel/';
    const STATUS_ENDPOINT = '/transaction/status/';
    const MULTIPLE_ENDPOINT = '/transaction/multiple_bank_slip';
    const NOTIFICATION_PIX_ENDPOINT = 'https://pix.paghiper.com/invoice/notification/';
	const NOTIFICATION_BILLET_ENDPOINT = 'https://api.paghiper.com/transaction/notification/';

    /**
     * @var \PagHiper\PagHiper;
     */
    protected $paghiper;

    public function __construct(PagHiper $paghiper) {
        $this->paghiper = $paghiper;
    }

    /**
    * Create a new billet.
    *
    * @param array $data Billet data.
    * @return array
    */
    public function create(array $data = []) {

        $transaction_type       = ((array_key_exists('transaction_type', $data) && $data['transaction_type'] !== '') ? $data['transaction_type'] : 'pix');
        $data['partners_id']    = ($transaction_type == 'pix' ? "EMIIKD1R" : "S0CS1BY0");
        $create_transaction = $this->paghiper->request(
            (($transaction_type == 'pix') ? self::CREATE_PIX_ENDPOINT : self::CREATE_BILLET_ENDPOINT),
            $data
        );

        $transaction = (($transaction_type == 'pix') ? $create_transaction['pix_create_request'] : $create_transaction['create_request']);

        if ($transaction['result'] === 'reject') {
            throw new \Exception($transaction['response_message'], 400);
        }

        return $transaction;
    }

    /**
    * Cancel a billet with the given transaction ID.
    *
    * @param string $transaction_id Transaction ID to cancel.
    * @return array
    */
    public function cancel(string $transaction_id) {

        $cancel_transaction = $this->paghiper->request(
            self::CANCEL_ENDPOINT,
            [
                'transaction_id' => $transaction_id,
                'status' => 'canceled'
            ]
        )['cancellation_request'];

        if ($cancel_transaction['result'] === 'reject') {
            throw new \Exception($cancel_transaction['response_message'], 400);
        }

        return $cancel_transaction;
    }

    /**
     * Retrieves billet status with the given transaction ID.
     *
     * @param   string  $transaction_id
     * @return void
     */
    public function status(string $transaction_id) {

        $transaction_status = $this->paghiper->request(
            self::STATUS_ENDPOINT,
            [
                'transaction_id' => $transaction_id,
            ]
        )['status_request'];

        if ($transaction_status['result'] === 'reject') {
            throw new \Exception($transaction_status['response_message'], 400);
        }

        return $transaction_status;
    }

    /**
     * Generate multiple billets with the given transaction IDs.
     *
     * @param   array   $transactions  Array containing transaction IDs.
     * @param   string  $type          How PDF should be generated: 'boletoCarne' (default) generates a PDF in 'carnê' format
     * (3 billets per page) or 'boletoA4'.
     *
     * @return void
     */
    public function combineBillets(array $transactions = [], string $type = NULL) {

        $type = (is_null($type)) ? 'boletoCarne' : $type;

        $combined_billets = $this->paghiper->request(
            self::MULTIPLE_ENDPOINT,
            [
                'transactions' => $transactions,
                'type_bank_slip' => $type
            ]
        )['status_request'];

        if ($combined_billets['result'] === 'reject') {
            throw new \Exception($combined_billets['response_message'], 400);
        }

        return $combined_billets;
	}
	
    /**
     *  Get notification response.
     *
     * @return array
     */
    public function process_ipn_notification(string $notification_id, string $transaction_id, string $transaction_type) {

        $transaction_type = ( (!is_null($transaction_type)) ? $transaction_type : 'billet');
        $ipn_request = $this->paghiper->request(
            (($transaction_type == 'pix') ? self::NOTIFICATION_PIX_ENDPOINT : self::NOTIFICATION_BILLET_ENDPOINT ),
            [
                'notification_id' => $notification_id,
                'transaction_id' => $transaction_id
            ]
        )['status_request'];

        if ($ipn_request['result'] === 'reject') {
            throw new \Exception($ipn_request['response_message'], 400);
        }

        return $ipn_request;
    }
	
}