<?php
/**
 * This file is part of Phasty\Service
 *
 * (c) Dmitry Cheremnykh <lockalister@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
function notImplemented() {
    header("HTTP/1.1 501 Not Implemented");
    echo( '{ "message": "api class not implemented" }' );
}

function findAutoloader() {
    $done = false;
    foreach ([ "/../../../autoload.php", "/../vendor/autoload.php" ] as  $file ) {
        if (!file_exists(__DIR__ . $file)) continue;
        require __DIR__ . $file;
        $done = true;
    }
    if (!$done) {
        header("HTTP/1.1 500 Internal Server Error");
        die('{ "message": "Autoloader not found" }');
    }
}

function getClassAndMethod() {
    $arguments = explode("/", $_SERVER[ "PHP_SELF" ]);
    $method = array_pop($arguments);
    return [ implode("\\", $arguments), $method ];
}

function findAndCheckInstance($class, $method) {
    if (!class_exists($class, true) || !is_callable([ $class, $method ])) {
        notImplemented(); exit;
    }

    $instance = new $class;

    if (!$instance instanceof \Phasty\Service\IService) {
        notImplemented(); exit;
    }
    return $instance;
}

function callInstance($instance, $method) {
    try {
        // sleep(1);
        echo json_encode([ "result" => $instance->$method((new \Phasty\Service\Input)->getData()) ]);
    } catch (\Exception $e) {
        header("HTTP/1.1 500 Internal Server Error");
        die(json_encode([ "message" => $e->getMessage() ]));
    }
}

header("Content-Type: application/json");

findAutoloader();
list($class, $method) = getClassAndMethod();
$instance = findAndCheckInstance($class, $method);
callInstance($instance, $method);
