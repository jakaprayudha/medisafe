<?php

use Valet\Drivers\BasicValetDriver;

class LocalValetDriver extends BasicValetDriver
{
    public function frontControllerPath(string $sitePath, string $siteName, string $uri): ?string
    {
        $uri = rtrim($uri, '/');

        foreach ([
            '/controller/wsfktp/service/v2/antrean/status',
            '/controller/wsfktp/antrean/status',
            '/controller/wsfktp/antrean/sisapeserta',
        ] as $route) {
            if (strpos($uri, $route . '/') === 0) {
                $uri = $route;
                break;
            }
        }

        $root = realpath($sitePath);
        foreach ([
            $sitePath . $uri,
            $sitePath . $uri . '.php',
            $sitePath . $uri . '/index.php',
        ] as $candidate) {
            $path = realpath($candidate);
            if ($root === false || $path === false || !is_file($path) ||
                strpos($path, $root . DIRECTORY_SEPARATOR) !== 0 ||
                pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            $_SERVER['SCRIPT_FILENAME'] = $path;
            $_SERVER['SCRIPT_NAME'] = substr($path, strlen($root));
            $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
            $_SERVER['DOCUMENT_ROOT'] = $root;

            return $path;
        }

        return null;
    }
}
