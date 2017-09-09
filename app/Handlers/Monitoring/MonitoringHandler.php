<?php

namespace TKAccounts\Handlers\Monitoring;

use Tokenly\ConsulHealthDaemon\ServicesChecker;

/**
 * This is invoked when a new block is received.
 */
class MonitoringHandler
{
    public function __construct(ServicesChecker $services_checker)
    {
        $this->services_checker = $services_checker;
    }

    public function handleConsoleHealthCheck()
    {
        // check MySQL
        $this->services_checker->checkMySQLConnection();
    }

    public function subscribe($events)
    {
        $events->listen('consul-health.console.check', 'TKAccounts\Handlers\Monitoring\MonitoringHandler@handleConsoleHealthCheck');
    }

    ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////
    // Checks
}
