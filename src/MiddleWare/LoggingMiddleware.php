<?php

namespace Sudhaus7\WizardServer\MiddleWare;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class LoggingMiddleware
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        $this->logger->info( date('Y-m-d H:i:s') . ' ' . $request->getMethod() . ' ' . $request->getUri());
        return $next($request);
    }
}
