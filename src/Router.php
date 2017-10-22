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

        protected $routes;
        protected $format;

        /**
         * Устанавливает конфигурацию роутинга
         *
         * @param  array  $routeMappings  Массив ключ(uri) => [class, method]
         */
        public function init(array $routeMappings = [], $format = "application/json") {
            $this->routes = $routeMappings;
            $this->format = $format;
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
        protected function getClassAndMethod($requestedUri) {
            if (empty($this->routes[ $requestedUri ])) {
                throw new ApiNotImplemented("Unresolved resource '$requestedUri'.");
            }
            return $this->routes[ $requestedUri ];
        }

        /**
         * Пытается обработать запрос в сервис на основании запрошенного ресурса и
         * установленного конфига.
         * Возвращает результат в виде массива.
         * Такая реализация нужна для более простого unit-тестирования.
         *
         * @param  string $appUid         Идентификатор клиента сервиса
         * @param  string $requestedUri   Запрошенный uri
         * @param  mixed  $input          Входяций набор данных
         *
         * @return array  Результат обработки запроса
         */
        protected function getResult($appUid, $requestedUri, $input) {
            list($class, $method) = $this->getClassAndMethod($requestedUri);
            $instance = new $class($appUid);
            return $instance->$method($input);
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
                if ($this->isJson()) {
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
        public function setFormat($contentType) {
            $this->format = empty($contentType) ? "application/json" : $contentType;
        }

        /**
         * Проверяет что текущий формат - json
         *
         * @return boolean  true - если conent-type = application/json
         */
        protected function isJson() {
            return "application/json" == $this->format;
        }

        /**
         * Обрабатывает запрос и возвращает http-ответ в виде:
         *  json-данных - когда $_SERVER["CONTENT_TYPE"] == "application/json"
         *  сырой результат обработки, если входящий запрос не "application/json"
         *
         * @param string $requestedUri  Входящий uri - эквивалентен $_SERVER[ "PHP_SELF" ]
         */
        public function route($requestedUri) {
            // Копим весь прямой вывод (ошибки, случайное echo от разработчика и т.д.)
            ob_start();
            try {
                $this->setFormat($_SERVER["CONTENT_TYPE"]);

                // Берем идентификатор отправителя
                $appUid = isset($_SERVER["HTTP_APP_UID"]) ? $_SERVER["HTTP_APP_UID"] : null;

                $result = $this->getResult($appUid, $requestedUri, $this->getData());
                // Заворачиваем результат в result. Это необходимо, чтобы сервис мог
                // возвращать просто строку или число внутри json, а не только объект
                $result = $this->isJson() ? json_encode(["result" => $result]) : $result;
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

            header("Content-Type: " . $this->format);
            header("Content-Length: " . strlen($result));
            echo $result;
        }

    }
}
