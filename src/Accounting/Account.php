<?php

namespace Ashio\Accounting;

use GuzzleHttp\Exception\GuzzleException;
use Ashio\Model\AccountBalance;
use Ashio\OpenProvider;

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
