<?php
namespace Phasty\Service {

    class Router {

        protected static function notImplemented() {
            http_response_code(501);
            die(json_encode([ "message" => "api class not implemented" ]));
        }

        protected static function getClassAndMethod(array $routeMappings) {
            $requestedUri = $_SERVER[ "PHP_SELF" ];
            if (empty($routeMappings[ $requestedUri ])) {
                static::notImplemented();
            }
            return $routeMappings[ $requestedUri ];
        }

        protected static function callInstance(IService $instance, $method, array $exceptionMappings = []) {
            try {
                $result = json_encode([ "result" => $instance->$method((new Input)->getData()) ]);
                header("Content-Length: " . strlen($result));
                echo $result;
            } catch (\Exception $exception) {
                $exceptionClass = get_class($exception);
                $httpCode = isset($exceptionMappings[ $exceptionClass ]) ? $exceptionMappings[ $exceptionClass ] : 500;
                $instance->fail($httpCode, $exception->getMessage());
            }
        }

        final public static function route(array $settings) {
            header("Content-Type: application/json");
            list($class, $method) = static::getClassAndMethod($settings[ "routes" ]);
            static::callInstance(new $class, $method, $settings[ "exceptions" ]);
        }

    }
}
