<?php
namespace Phasty\Service {

    use Phasty\Service\Error;
    use Phasty\Service\Exception;

    class Router {

        protected static function getClassAndMethod(array $routeMappings) {
            $requestedUri = $_SERVER[ "PHP_SELF" ];
            if (empty($routeMappings[ $requestedUri ])) {
                throw new Exception\ApiNotImplemented("Неизвестный ресурс '$requestedUri'.");
            }
            return $routeMappings[ $requestedUri ];
        }

        protected static function getResult(array $settings) {
            try {
                list($class, $method) = static::getClassAndMethod($settings[ "routes" ]);
                $instance = new $class;
                return json_encode([ "result" => $instance->$method((new Input)->getData()) ]);
            } catch (\Exception $e) {
                $e = ($e instanceof Error) ? $e : new Exception\InternalError($e->getMessage());
                http_response_code($e->getHttpStatus());
                // log::error("[ERROR: " . $e->getCode() . "] " . $e->getMessage());
                return json_encode(["code" => $e->getCode(), "message" => $e->getMessage()]);
            }
        }

        public static function route(array $settings) {
            $result = getResult(array $settings);

            header("Content-Type: application/json");
            header("Content-Length: " . strlen($result));
            echo $result;
        }

    }
}
