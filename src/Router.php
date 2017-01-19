<?php
namespace Phasty\Service {

    use Phasty\Service\Error;
    use Phasty\Service\AbstractService;

    class Router {

        protected static function getClassAndMethod(array $routeMappings) {
            $requestedUri = $_SERVER[ "PHP_SELF" ];
            if (empty($routeMappings[ $requestedUri ])) {
                $error = AbstractService::getError(IService::API_NOT_IMPLEMENTED);
                throw new Error($error[1], $error[0]);
            }
            return $routeMappings[ $requestedUri ];
        }

        protected static function getResult(array $settings) {
            try {
                list($class, $method) = static::getClassAndMethod($settings[ "routes" ]);
                $instance = new $class;
                return json_encode([ "result" => $instance->$method((new Input)->getData()) ]);
            } catch (\Exception $e) {
                $errorCode = ($e instanceof Error) ? $e->getCode() : 0;
                if (empty($class) || empty(class_parents($class)[AbstractService::class])) {
                    $class = AbstractService::class;
                }

                http_response_code($class::getError($errorCode)[0]);
                return json_encode(["code" => $errorCode, "message" => $e->getMessage()]);
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
