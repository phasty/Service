<?php
namespace Phasty\Service {
    class Router {

        protected static function internalServerError($message) {
            http_response_code(500);
            die(json_encode([ "message" => $message ]));
        }

        protected static function notImplemented() {
            http_response_code(501);
            die(json_encode([ "message" => "api class not implemented" ]));
        }

        protected static function getClassAndMethod() {
            $arguments = explode("/", $_SERVER[ "PHP_SELF" ]);
            $method = array_pop($arguments);
            return [ implode("\\", $arguments), $method ];
        }

        protected static function findAndCheckInstance($class, $method) {
            if (!class_exists($class, true) || !is_callable([ $class, $method ])) {
                static::notImplemented();
            }

            $instance = new $class;

            if (!$instance instanceof IService) {
                static::notImplemented();
            }
            return $instance;
        }

        protected static function callInstance(IService $instance, $method, array $exceptionMappings = []) {
            try {
                $result = json_encode([ "result" => $instance->$method((new Input)->getData()) ]);
                header("Content-Length: " . strlen($result));
                echo $result;
            } catch (\Exception $exception) {
                $exceptionClass = get_class($exception);
                if (isset($exceptionMappings[ $exceptionClass ])) {
                    $instance->fail($exceptionMappings[ $exceptionClass ], $exception->getMessage());
                } else {
                    static::internalServerError($exception->getMessage());
                }
            }
        }

        final public static function route(array $exceptionMappings = []) {
            header("Content-Type: application/json");
            list($class, $method) = static::getClassAndMethod();
            $instance = static::findAndCheckInstance($class, $method);
            static::callInstance($instance, $method, $exceptionMappings);
        }

    }
}
