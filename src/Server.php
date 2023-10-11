<?php

namespace Sudhaus7\WizardServer;

use Exception;
use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\MySQL\Factory;
use React\Socket\SocketServer;
use Sudhaus7\WizardServer\MiddleWare\LoggingMiddleware;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;
use function method_exists;
use const PHP_EOL;

class Server {
    public function __invoke($envfile = '')
    {

        try {
            $dotenv = new Dotenv();
            if ( method_exists( $dotenv, 'usePutenv')) {
                $dotenv->usePutenv( true );
            }
            $dotenv->load( $envfile );
        } catch( PathException $e) {
            // we assume the environment variables are set
        }




        $loop = Loop::get();


        $factory = new Factory($loop);
        $dsn = sprintf("%s:%s@%s:%d/%s",getenv('WIZARD_SERVER_DBUSER'),getenv('WIZARD_SERVER_DBPASS'),getenv('WIZARD_SERVER_DBHOST'),getenv('WIZARD_SERVER_DBPORT'),getenv('WIZARD_SERVER_DBNAME'));
        $db = $factory->createLazyConnection($dsn);



        $tableAction = function ( ServerRequestInterface $request) {

            $tables = new Tables( );
            return $tables->fetch()->then(function($result) {
                return Response::json($result);
            });
        };


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

        $contentActionComplex = function (ServerRequestInterface $request, string $table) use ($db) {
            $content = new Content( $db );

            return  $content->fetchComplex($table,$_POST)->then(function($response) { return $response; });
        };

        $routes = new RouteCollector(new Std(), new GroupCountBased());
        $routes->get('/tables', $tableAction);
        $routes->get('/tree/{id:\d+}', $treeAction);
        $routes->get('/page/{id:\d+}', $pageAction);
        $routes->get('/content/{table:\S+}/{field:\S+}/{id:\d+}', $contentAction);
        $routes->get('/content/{table:\S+}/{id:\d+}', $contentActionSingle);
        $routes->post('/content/{table:\S+}', $contentActionComplex);
        $routes->get('/', function() {
            return Response::json( ['hello'=>'world']);
        });
        $server = new HttpServer(
            new MiddleWare\AccessMiddleware(),
            new MiddleWare\LoggingMiddleware(
                new Logger( 'default',
                    [
                        new StreamHandler( fopen('php://stderr','w'), Logger::INFO)
                    ]
                )
            ),
            new Router( $routes)
        );


        try {
            $socket = new SocketServer( getenv( 'WIZARD_SERVER_HOST' ) . ':' . getenv( 'WIZARD_SERVER_PORT' ), [],
                $loop );
            $server->listen( $socket );
            echo "Server startet ".getenv('WIZARD_SERVER_HOST').':'.getenv('WIZARD_SERVER_PORT') . PHP_EOL;
            $loop->run();
        } catch( Exception $e) {
            echo 'ERROR: '.$e->getMessage() . PHP_EOL;
        }

    }
}
