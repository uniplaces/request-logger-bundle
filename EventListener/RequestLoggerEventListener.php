<?php

namespace Uniplaces\RequestLoggerBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

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
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getBasePath();
        $contentType = $request->getContentType();
        $clientIp = $request->getClientIp();
        $userAgent = $request->headers->get('User-Agent');

        $response = $event->getResponse();
        $statusCode = $response->getStatusCode();
        $message = \sprintf(
            'Response %s for "%s %s"',
            [$statusCode, $request->getMethod(), $request->getRequestUri()]
        );

        $this->logResponse(
            $message,
            [
                'method' => $request->getMethod(),
                'path' => $path,
                'uri' => $request->getRequestUri(),
                'content-type' => $contentType,
                'latency' => $this->getTime($request),
                'client-ip' => $clientIp,
                'status_code' => $statusCode,
                'user-agent' => $userAgent
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return float|null
     */
    public function getTime(Request $request): ?float
    {
        if (!$request->server) {
            return null;
        }

        $startTime = $request->server->get(
            'REQUEST_TIME_FLOAT',
            $request->server->get('REQUEST_TIME')
        );
        $time = microtime(true) - $startTime;

        return round($time * 1000);
    }

    /**
     * @param string $message
     * @param array  $fields
     */
    private function logResponse(string $message, array $fields): void
    {
        $this->logger->info($message, $fields);
    }
}
