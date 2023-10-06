<?php

namespace Sudhaus7\WizardServer;

use Nyholm\Psr7\Factory\StreamFactory;
use Nyholm\Psr7\Factory\UploadedFileFactory;
use Nyholm\Psr7\Factory\UriFactory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\UploadedFile;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use RingCentral\Psr7\Stream;

class ServerRequestFactory {

    public static function createFromGlobals(): ServerRequestInterface
    {

        $serverArray = $_SERVER;
        if (isset($serverArray['PHP_SELF']) && strpos($serverArray['REQUEST_URI'],$serverArray['PHP_SELF']) === 0) {
            $serverArray['REQUEST_URI'] = substr($serverArray['REQUEST_URI'],strlen($serverArray['PHP_SELF']));
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = (new UriFactory())->createUriFromArray($serverArray);

        //print_r($uri);exit;

        $headers = null;

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }

        if (!is_array($headers)) {
            $headers = [];
        }

        // Cache the php://input stream as it cannot be re-read
        $cacheResource = fopen('php://temp', 'wb+');
        //$cache = $cacheResource ? new Stream($cacheResource) : null;

        $body = (new StreamFactory())->createStreamFromFile('php://input', 'r');

        $request = new ServerRequest($method, $uri, $headers, $body,'1.1',$serverArray);
        $contentTypes = $request->getHeader('Content-Type');

        $parsedContentType = '';
        foreach ($contentTypes as $contentType) {
            $fragments = explode(';', $contentType);
            $parsedContentType = current($fragments);
        }

        $contentTypesWithParsedBodies = ['application/x-www-form-urlencoded', 'multipart/form-data'];
        if ($method === 'POST' && in_array($parsedContentType, $contentTypesWithParsedBodies)) {
            return $request->withParsedBody($_POST);
        }

        return $request;
    }
}
