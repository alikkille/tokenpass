<?php 

namespace TKAccounts\Http\Controllers\XChain;

use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TKAccounts\Http\Controllers\Controller;
use Tokenly\LaravelEventLog\Facade\EventLog;
use Tokenly\XChainClient\WebHookReceiver;

class XChainWebhookController extends Controller {

    public function receive(WebHookReceiver $webhook_receiver, Request $request) {
        try {
            $data = $webhook_receiver->validateAndParseWebhookNotificationFromRequest($request);
            $payload = $data['payload'];

            // check block, receive or send
            $this->handleXChainPayload($payload);

        } catch (Exception $e) {
            EventLog::logError('webhook.error', $e);
            if ($e instanceof HttpResponseException) { throw $e; }
            throw new HttpResponseException(new Response("An error occurred"), 500);
        }

        return 'ok';
    }

    // ------------------------------------------------------------------------
    
    protected function handleXChainPayload($payload) {
        switch ($payload['event']) {
            case 'block':
                // new block event
                app('TKAccounts\Handlers\XChain\XChainBlockHandler')->handleBlock($payload);
                break;

            case 'send':
            case 'receive':
                // new send or receive event
                app('TKAccounts\Handlers\XChain\XChainTransactionHandler')->handleTransaction($payload);
                break;

            case 'invalidation':
                // new invalidation event
                EventLog::log('event.invalidation', $payload);
                break;

            default:
                EventLog::log('event.unknown', "Unknown event type: {$payload['event']}");
        }
    }

}
