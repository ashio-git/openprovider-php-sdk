<?php

namespace Ashio\Accounting;

use Ashio\Model\AccountBalance;
use Ashio\OpenProvider;
use GuzzleHttp\Exception\GuzzleException;

class Account
{
    private OpenProvider $API;

    public function __construct(OpenProvider $API)
    {
        $this->API = $API;
    }

    /**
     * Get reseller account balance
     * @return AccountBalance
     * @throws GuzzleException
     */
    public function getBalance(): AccountBalance
    {
        $response = $this->API->get('accounting/balance');

        return (new AccountBalance())->fromArray($response['data'] ?? []);
    }
}
