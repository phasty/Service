<?php
namespace Phasty\Service {

    use Phasty\Service\FatalError;
    use Phasty\Service\Exception\InternalServerError;
    use Phasty\Service\Exception\ApiNotImplemented;

    /**
     * Class Router
     * Класс для маршрутизации и обработки входящего запроса.
     *
     * @package Phasty\Service
     */
    class Router {

        protected static $routes = [];
        protected static $format = "application/json";
        protected static $appUid;

        /**
         * Устанавливает конфигурацию роутинга
         *
         * @param  array  $routeMappings  Массив ключ(uri) => [class, method]
         */
        public static function init(array $routeMappings) {
            static::$routes = $routeMappings;
        }

        /**
         * Находит класс-обработчик и метод на основании запрошенного uri и конфига
         *
         * @param  string $requestedUri   Запрошенный uri
         *
         * @return array  Класс и метод для обработки запроса
         *
         * @throws ApiNotImplemented
         */
        protected static function getClassAndMethod($requestedUri) {
            if (empty(static::$routes[ $requestedUri ])) {
                throw new ApiNotImplemented("Unresolved resource '$requestedUri'.");
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
         *
         * @return array  Результат обработки запроса
         */
        protected static function getResult($requestedUri, $input) {
            list($class, $method) = static::getClassAndMethod($requestedUri);
            $instance = new $class(static::$appUid);
            return $instance->$method($input);
        }

        /**
         * Извлекает идентифтикатор клиента сервиса и сохраняет его в переменную
         *
         * @param  mixed  $input    Входяций набор данных
         */
        protected static function extractAppUid(&$input) {
            // Пока работаем только с json
            if (static::isJson() && isset($input["app-uid"])) {
                static::$appUid = $input["app-uid"];
                unset($input["app-uid"]);
            }
        }

        /**
         * Метод читает тело http-запроса
         * и пытается преобразовать его в зависимости от content-type
         *
         * @return mixed  Тело запроса, или ассоциативный массив в случае content-type = application/json
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
         * @return boolean  true - если conent-type = application/json
         */
        protected static function isJson() {
            return "application/json" == static::$format;
        }

        /**
         * Обрабатывает запрос и возвращает http-ответ в виде:
         *  json-данных - когда $_SERVER["CONTENT_TYPE"] == "application/json"
         *  сырой результат обработки, если входящий запрос не "application/json"
         *
         * @param string $requestedUri  Входящий uri - эквивалентен $_SERVER[ "PHP_SELF" ]
         */
        public static function route($requestedUri) {
            // Копим весь прямой вывод (ошибки, случайное echo от разработчика и т.д.)
            ob_start();
            try {
                static::setFormat($_SERVER["CONTENT_TYPE"]);

                $data = static::getData();

                // Извлекаем идентификатор отправителя
                static::extractAppUid($data);

                $result = static::getResult($requestedUri, $data);
                // Заворачиваем результат в result. Это необходимо, чтобы сервис мог
                // возвращать просто строку или число внутри json, а не только объект
                $result = static::isJson() ? json_encode(["result" => $result]) : $result;
            } catch (\Exception $e) {
                $e = ($e instanceof FatalError) ? $e : new InternalServerError($e->getMessage());
                http_response_code($e->getHttpStatus());
                // todo: Нужно логировать ошибку. Но про механизм пока не договорились.
                // log::error("[ERROR: " . $e->getCode() . "] " . $e->getMessage());
                $result = static::isJson() ?
                    json_encode(["code" => $e->getCode(), "message" => $e->getMessage()]) : $e->getMessage();
            } finally {
                // Чистим весь левый вывод. Мы должны отдать только результат!
                ob_end_clean();
            }

            header("Content-Type: " . static::$format);
            header("Content-Length: " . strlen($result));
            echo $result;
        }

    }
}
