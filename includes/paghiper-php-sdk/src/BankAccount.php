<?php 

namespace PagHiper;

class BankAccount extends PagHiper {

    const ACCOUNTS_ENDPOINT = '/bank_accounts/list/';
    const CASHOUT_ENDPOINT = '/bank_accounts/cash_out/';

    /**
     * @var \WebMaster\PagHiper\PagHiper;
     */
    protected $paghiper;

    public function __construct(PagHiper $paghiper) {
        $this->paghiper = $paghiper;
    }

    /**
    * Withdraw cash to the given bank account.
    *
    * @param string $transactionId Transaction ID to cancel.
    * @return array
    */
    public function accounts()
    {
        $accountsList = $this->paghiper->request(
            self::ACCOUNTS_ENDPOINT
        )['bank_account_list_request'];

        if ($accountsList['result'] === 'reject') {
            throw new \Exception($accountsList['response_message'], 400);
        }

        return $accountsList;
    }

    /**
    * Withdraw cash to the given bank account.
    *
    * @param string $transactionId Transaction ID to cancel.
    * @return array
    */
    public function withdraw(int $bank_account_id)
    {
        $withdraw = $this->paghiper->request(
            self::CASHOUT_ENDPOINT,
            [
                'bank_account_id' => $bank_account_id
            ]
        )['cash_out_request'];

        if ($withdraw['result'] === 'reject') {
            throw new \Exception($withdraw['response_message'], 400);
        }

        return $withdraw;
    }

}