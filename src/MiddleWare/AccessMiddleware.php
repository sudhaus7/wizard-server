<?php

namespace Sudhaus7\WizardServer\MiddleWare;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class AccessMiddleware {
    public function __invoke(ServerRequestInterface $request, callable $next)
    {

        if($request->hasHeader( 'X-Authorization')) {
            $authorization = \base64_decode( $request->getHeader( 'X-Authorization')[0]);
            if (password_verify(\getenv('WIZARD_SERVER_SHARED_SECRET'), $authorization)) {
                return $next($request);
            }
            return new \Nyholm\Psr7\Response(403,[],'Access denied');

        }
        //echo date('Y-m-d H:i:s') . ' ' . $request->getMethod() . ' ' . $request->getUri() . PHP_EOL;
        return $next($request);
    }
}
