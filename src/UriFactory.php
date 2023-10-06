<?php

namespace Sudhaus7\WizardServer;

use Nyholm\Psr7\Uri;

class UriFactory {
    public function createFromGlobals(array $globals): Uri
    {
        // Scheme
        $https = $globals['HTTPS'] ?? false;
        $scheme = !$https || $https === 'off' ? 'http' : 'https';

        // Authority: Username and password
        $username = $globals['PHP_AUTH_USER'] ?? '';
        $password = $globals['PHP_AUTH_PW'] ?? '';

        // Authority: Host
        $host = '';
        if (isset($globals['HTTP_HOST'])) {
            $host = $globals['HTTP_HOST'];
        } elseif (isset($globals['SERVER_NAME'])) {
            $host = $globals['SERVER_NAME'];
        }

        // Authority: Port
        $port = !empty($globals['SERVER_PORT']) ? (int)$globals['SERVER_PORT'] : ($scheme === 'https' ? 443 : 80);
        if (preg_match('/^(\[[a-fA-F0-9:.]+])(:\d+)?\z/', $host, $matches)) {
            $host = $matches[1];

            if (isset($matches[2])) {
                $port = (int) substr($matches[2], 1);
            }
        } else {
            $pos = strpos($host, ':');
            if ($pos !== false) {
                $port = (int) substr($host, $pos + 1);
                $host = strstr($host, ':', true);
            }
        }

        // Query string
        $queryString = $globals['QUERY_STRING'] ?? '';

        // Request URI
        $requestUri = '';
        if (isset($globals['REQUEST_URI'])) {
            $uriFragments = explode('?', $globals['REQUEST_URI']);
            $requestUri = $uriFragments[0];

            if ($queryString === '' && count($uriFragments) > 1) {
                $queryString = parse_url('https://www.example.com' . $globals['REQUEST_URI'], PHP_URL_QUERY) ?? '';
            }
        }

        // Build Uri and return
        return new Uri($scheme, $host, $port, $requestUri, $queryString, '', $username, $password);
    }
}
