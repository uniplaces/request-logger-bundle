<?php

namespace Uniplaces\RequestLoggerBundle\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Uniplaces\RequestLoggerBundle\EventListener\RequestLoggerEventListener;

/**
 * RequestLoggerEventListenerTest
 */
class RequestLoggerEventListenerTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var HttpKernelInterface
     */
    private $kernel;

    protected function setUp()
    {
        $this->dispatcher = new EventDispatcher();
        $this->kernel = $this->createMock(HttpKernelInterface::class);
    }

    public function testOnRequest(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->expects($this->never())
            ->method('info');

        /** @var LoggerInterface $loggerMock */
        $listener = new RequestLoggerEventListener($loggerMock);
        $this->dispatcher->addListener(KernelEvents::REQUEST, [$listener, 'onRequest']);

        $event = new GetResponseEvent($this->kernel, new Request(), HttpKernelInterface::MASTER_REQUEST);
        $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);
    }

    public function testOnTerminate(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->expects($this->once())
            ->method('info')
            ->with(
                $this->isType('string'),
                $this->isType('array')
            );

        /** @var LoggerInterface $loggerMock */
        $listener = new RequestLoggerEventListener($loggerMock);
        $this->dispatcher->addListener(KernelEvents::TERMINATE, [$listener, 'onTerminate']);

        $event = new PostResponseEvent($this->kernel, new Request(), new Response());
        $this->dispatcher->dispatch(KernelEvents::TERMINATE, $event);
    }
}
