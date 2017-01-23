<?php
namespace Phasty\Service {

    use Phasty\Service\Error;
    use Phasty\Service\Exception;

    /**
     * Class Router
     * Класс для маршрутизации и обработки входящего запроса.
     *
     * @package Phasty\Service
     */
    class Router {

        protected static $routes = [];
        protected static $format = "application/json";

        /**
         * Устанавливает конфигурацию роутинга
         *
         * @param  array  $routeMappings  Массив ключ(uri) => [class, method]
         */
        public static function init(array $routeMappings) {
            static::$routes = $config;
        }

        /**
         * Находит класс-обработчик и метод на основании запрошенного uri и конфига
         *
         * @param  string $requestedUri   Запрошенный uri
         * @param  array  $routeMappings  Конфиг с маппингом uri - класс-метод
         *
         * @return array  Класс и метод для обработки запроса
         */
        protected static function getClassAndMethod($requestedUri) {
            if (empty(static::$routes[ $requestedUri ])) {
                throw new Exception\ApiNotImplemented("Неизвестный ресурс '$requestedUri'.");
            }
            return static::$routes[ $requestedUri ];
        }

        /**
         * Пытается обработать запрос в сервис на основании запрошенного ресурса и
         * установленного конфига.
         * Возвращает результат в виде массива.
         * Такая реализация нужна для более простого unit-тестирования.
         *
         * @param  string $requestedUri   Запрошенный uri
         * @param  mixed  $input          Входяций набор данных
         * @param  array  $settings       Конфигурация для сервиса
         *
         * @return array  Результат обработки запроса
         */
        protected static function getResult($requestedUri, $input) {
            list($class, $method) = static::getClassAndMethod($requestedUri);
            $instance = new $class;
            return $instance->$method($input);
        }

        /**
         * Метод ожидает json-объект на входном потоке данных (в теле http-запроса)
         * и пытается рапарсить его
         *
         * @return array  Ассоциативный массив данных из входящего запроса
         */
        public static function getData() {
            static $result = null;
            if (!isset($result)) {
                $result = file_get_contents("php://input");
                if (static::isJson()) {
                    $result = ($result) ? json_decode($result, true) : [];
                }
            }
            return $result;
        }

        /**
         * Устанавливает формат ответа.
         * По умолчанию формат ответа совпадает с форматом запроса.
         *
         * @param string $contentType Формат в виде MIME-type
         */
        public static function setFormat($contentType) {
            static::$format = empty($contentType) ? "application/json" : $contentType;
        }

        /**
         * Проверяет что текущий формат - json
         *
         * @return boolean
         */
        protected static function isJson() {
            return ("application/json" == static::$format) ? true : false;
        }

        /**
         * Обрабатывает запрос и возвращает http-ответ в виде:
         *  json-данных - когда $_SERVER["CONTENT_TYPE"] == "application/json"
         *  сырой результат обработки, если входящий запрос не "application/json"
         *
         * @param string $requestedUri  Входящий uri - эквивалентен $_SERVER[ "PHP_SELF" ]
         */
        public static function route($requestedUri) {
            try {
                // Копим весь прямой вывод (ошибки, случайное echo от разработчика и т.д.)
                ob_start();
                static::setFormat($_SERVER["CONTENT_TYPE"]);
                $result = static::getResult($requestedUri, static::getData());
                // Чистим весь левый вывод. Мы должны отдать только результат!
                ob_end_clean();
            } catch (\Exception $e) {
                $e = ($e instanceof Error) ? $e : new Exception\InternalError($e->getMessage());
                http_response_code($e->getHttpStatus());
                // todo: Нужно логировать ошибку. Но про механизм пока не договорились.
                // log::error("[ERROR: " . $e->getCode() . "] " . $e->getMessage());
                $result = ["code" => $e->getCode(), "message" => $e->getMessage()];
            }

            header("Content-Type: " . static::$format);
            header("Content-Length: " . strlen($result));
            echo static::isJson() ? json_encode(["result" => $result]) : $result;
        }

    }
}
