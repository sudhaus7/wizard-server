<?php

namespace Sudhaus7\WizardServer\MiddleWare;

use Psr\Http\Message\ServerRequestInterface;

class LoggingMiddleware
{

    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        echo date('Y-m-d H:i:s') . ' ' . $request->getMethod() . ' ' . $request->getUri() . PHP_EOL;
        return $next($request);
    }
}
