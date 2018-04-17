<?php

namespace Uniplaces\RequestLoggerBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
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

        $this->requestStartTime = microtime(true);
    }

    /**
     * @param PostResponseEvent $event
     */
    public function onTerminate(PostResponseEvent $event): void
    {
        $latency = (int)((microtime(true) - $this->requestStartTime) * 1000);

        $request = $event->getRequest();
        $method = $request->getMethod();
        $path = $request->getBasePath();
        $contentType = $request->getContentType();
        $clientIp = $request->getClientIp();
        $userAgent = $request->headers->get('User-Agent');

        $response = $event->getResponse();
        $statusCode = $response->getStatusCode();

        $queryString = $request->getQueryString();
        $httpVersion = $request->getProtocolVersion();
        $responseSize = (int)$response->headers->get('Content-Length');
        $message = "{$clientIp} \"{$method} {$path}/{$queryString} {$httpVersion} {$statusCode} {$responseSize}\"";

        $this->logRequest(
            $statusCode,
            $message,
            [
                'method' => $method,
                'path' => $path,
                'content-type' => $contentType,
                'latency' => $latency,
                'client-ip' => $clientIp,
                'status_code' => $statusCode,
                'user-agent' => $userAgent
            ]
        );
    }

    /**
     * @param int    $statusCode
     * @param string $message
     * @param array  $fields
     */
    private function logRequest(int $statusCode, string $message, array $fields): void
    {
        if ($statusCode < Response::HTTP_BAD_REQUEST) {
            $this->logger->info($message, $fields);

            return;
        }

        if ($statusCode < Response::HTTP_INTERNAL_SERVER_ERROR) {
            $this->logger->warning($message, $fields);

            return;
        }

        $this->logger->error($message, $fields);
    }
}
