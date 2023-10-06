<?php

namespace Sudhaus7\WizardServer;

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\MySQL\Factory;
use React\Socket\SocketServer;
use Sudhaus7\WizardServer\MiddleWare\LoggingMiddleware;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;

class Server {
    public function __invoke($envfile = '')
    {

        try {
            $dotenv = new Dotenv();
            $dotenv->usePutenv( true );
            $dotenv->load( $envfile );
        } catch( PathException $e) {
            // we assume the environment variables are set
        }




        $loop = Loop::get();


        $factory = new Factory($loop);
        $dsn = sprintf("%s:%s@%s:%d/%s",getenv('WIZARD_SERVER_DBUSER'),getenv('WIZARD_SERVER_DBPASS'),getenv('WIZARD_SERVER_DBHOST'),getenv('WIZARD_SERVER_DBPORT'),getenv('WIZARD_SERVER_DBNAME'));
        $db = $factory->createLazyConnection($dsn);


        $treeAction = function ( ServerRequestInterface $request,int $id) use ($db) {
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

        $contentActionSingle = function( ServerRequestInterface $request, string $table,  int $id) use ($db)
        {
            $content = new Content( $db );
            return $content->fetch($table,$id)->then(function($response) { return $response; });
        };


        $contentAction = function( ServerRequestInterface $request, string $table, string $field, int $id) use ($db)
        {
            $content = new Content( $db );
            return $content->fetch($table,$id,$field)->then(function($response) { return $response; });
        };

        $routes = new RouteCollector(new Std(), new GroupCountBased());
        $routes->get('/tree/{id:\d+}', $treeAction);
        $routes->get('/page/{id:\d+}', $pageAction);
        $routes->get('/content/{table:\S+}/{field:\S+}/{id:\d+}', $contentAction);
        $routes->get('/content/{table:\S+}/{id:\d+}', $contentActionSingle);

        $server = new HttpServer(
            new MiddleWare\AccessMiddleware(),
            new MiddleWare\LoggingMiddleware(),
            new \Sudhaus7\WizardServer\Router( $routes)
        );

        $socket = new SocketServer(getenv('WIZARD_SERVER_HOST').':'.getenv('WIZARD_SERVER_PORT'), [],$loop);
        $server->listen($socket);

        $loop->run();
    }
}
