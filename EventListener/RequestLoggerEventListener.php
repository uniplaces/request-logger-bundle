<?php

namespace Uniplaces\RequestLoggerBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * RequestLoggerEventListener
 */
final class RequestLoggerEventListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $requestStartTime;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $this->requestStartTime = time();
    }

    /**
     * @param PostResponseEvent $event
     */
    public function onTerminate(PostResponseEvent $event): void
    {
        $latency = time() - $this->requestStartTime;
        $this->logger->info(
            'What should I put here chines?',
            [
                'uri' => $event->getRequest()->getRequestUri(),
                'method' => $event->getRequest()->getMethod(),
                'latency' => $latency,
                'status_code' => $event->getResponse()->getStatusCode()
            ]);
    }
}
