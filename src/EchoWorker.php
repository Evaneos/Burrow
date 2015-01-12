<?php
namespace Burrow;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class EchoWorker extends AbstractWorker implements Worker
{

    /**
     * Init the consummation
     */
    protected function init()
    {
        $this->queueService->registerConsumer(function($messageBody) {
            echo $messageBody . "\n";
        });
    }
}
