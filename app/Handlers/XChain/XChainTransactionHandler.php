<?php

namespace TKAccounts\Handlers\XChain;

use Exception;
use Illuminate\Support\Facades\Log;
use TKAccounts\Repositories\AddressRepository;
use Tokenly\LaravelEventLog\Facade\EventLog;
use Tokenly\XChainClient\Client as XChainClient;

/**
 * This is invoked when a new block is received
 */
class XChainTransactionHandler {

    const MINIMUM_CONFIRMATIONS = 2;

    public function __construct(AddressRepository $address_repository, XChainClient $xchain_client) {
        $this->address_repository = $address_repository;
        $this->xchain_client      = $xchain_client;
    }

    public function handleTransaction($payload) {
        Log::debug("\$payload=".json_encode($payload, 192));
        EventLog::log('transaction.'.$payload['event'].'.received', $payload, ['network', 'confirmations', 'quantity', 'asset', 'sources', 'destinations']);

        switch ($payload['event']) {
            case 'send': $is_send = true; break;
            case 'receive': $is_send = false; break;
            default: throw new Exception("Unknown event type: {$payload['event']}", 1);
        }

        // find the address
        if ($is_send) {
            $address = $this->address_repository->findBySendMonitorID($payload['notifiedAddressId']);
        } else {
            $address = $this->address_repository->findByReceiveMonitorID($payload['notifiedAddressId']);
        }
        if (!$address) { throw new Exception("Unable to find address", 1); }

        // sync new balance from xchain if this has 2 or more confirmations
        if ($payload['confirmations'] >= self::MINIMUM_CONFIRMATIONS) {
            try {
                // sync balances
                EventLog::log('address.sync', $address, ['id','address']);
                $address->syncWithXChain();
            } catch (Exception $e) {
                $error_data = [];
                EventLog::logError('address.sync.failed', $e, ['id' => $address['id'], 'address' => $address['address']]);
            }
        }

        return;
    }

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    // Checks
    
}
