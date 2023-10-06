<?php

namespace Sudhaus7\WizardServer;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Nyholm\Psr7\Factory\StreamFactory;
use Nyholm\Psr7\Factory\UploadedFileFactory;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Promise\Internal\FulfilledPromise;
use React\Promise\PromiseInterface;
use RingCentral\Psr7\Request;
use RingCentral\Psr7\ServerRequest;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;
use function method_exists;

class Simple {

    public function __invoke($envfile = '') {
        try {
            $dotenv = new Dotenv();
            if ( method_exists( $dotenv, 'usePutenv' ) ) {
                $dotenv->usePutenv( true );
            }
            $dotenv->load( $envfile );
        } catch ( PathException $e ) {
            // we assume the environment variables are set
        }


        $treeAction = function ( ServerRequestInterface $request,int $id) {

            $tree = new Maketree( $id );
            return $tree->fetch()->then(function($result) {
                return Response::json($result);
            });
        };


        $pageAction = function( ServerRequestInterface $request,int $id)
        {
            $page = new Page( $id);
            return $page->fetch()->then(function($result) {
                return Response::json($result);
            });
        };

        $contentActionSingle = function( ServerRequestInterface $request, string $table,  int $id)
        {
            $content = new Content( );
            return $content->fetch($table,$id);
        };


        $contentAction = function( ServerRequestInterface $request, string $table, string $field, int $id)
        {
            $content = new Content(  );
            return $content->fetch($table,$id,$field);
        };

        $routes = new RouteCollector(new Std(), new GroupCountBased());
        $routes->get('/tree/{id:\d+}', $treeAction);
        $routes->get('/page/{id:\d+}', $pageAction);
        $routes->get('/content/{table:\S+}/{field:\S+}/{id:\d+}', $contentAction);
        $routes->get('/content/{table:\S+}/{id:\d+}', $contentActionSingle);
        $routes->get('/', function() {
            return Response::json( ['hello'=>'world']);
        });

        //$serverRequest = \Symfony\Component\HttpFoundation\Request::createFromGlobals();


        $serverRequest = ServerRequestFactory::createFromGlobals();

        $router = new Router( $routes);
        $response = $router($serverRequest);
        if ($response instanceof PromiseInterface) {
             $response->then(function($response) {
                $psr7respone = new \Symfony\Component\HttpFoundation\Response(
                    $response->getBody(),
                    $response->getStatusCode(),
                    $response->getHeaders()
                );
                $psr7respone->send();
            });
        } else {
            $psr7respone = new \Symfony\Component\HttpFoundation\Response(
                $response->getBody(),
                $response->getStatusCode(),
                $response->getHeaders()
            );
            $psr7respone->send();
        }




        //print_r($response);exit;



    }
}
