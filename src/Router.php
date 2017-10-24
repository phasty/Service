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
        /**
         * @var  string $routes  Список маршрутов
         */
        protected $routes;
        /**
         * @var  string $format  Формат отдаваемых данных
         */
        protected $format;
        /**
         * @var  string $appUid  Идентификатор клиента сервиса
         */
        protected $appUid;
        /**
         * @var FatalError $exception  Исключение, полученное в результате обработки запроса
         */
        protected $exception;

        /**
         * Устанавливает конфигурацию роутинга
         *
         * @param  array  $routeMappings  Массив ключ(uri) => [class, method]
         * @param  string $format
         * @param  string $appUid  Идентификатор приложения
         */
        public function __construct(array $routeMappings = [], $format = null, $appUid = null) {
            $this->routes = $routeMappings;
            $this->setFormat($format);
            // Берем идентификатор отправителя
            $this->appUid = $appUid; //isset($_SERVER["HTTP_APP_UID"]) ? $_SERVER["HTTP_APP_UID"] : null;
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
         * @param  string $requestedUri   Запрошенный uri
         * @param  mixed  $input          Входяций набор данных
         *
         * @return array  Результат обработки запроса
         */
        public function getResult($requestedUri, $input) {
            $this->exception = null;
            try {
                list($class, $method) = $this->getClassAndMethod($requestedUri);
                $instance = new $class($this->appUid);
                $result = $instance->$method($input);
                // Заворачиваем результат в result. Это необходимо, чтобы сервис мог
                // возвращать просто строку или число внутри json, а не только объект
                $result = $this->isJson() ? ["result" => $result] : $result;
            } catch (\Exception $e) {
                // Эксепшн ставим тут, т.к. нам нужно получить чистый результат для тестов.
                $this->exception = ($e instanceof FatalError) ? $e : new InternalServerError($e->getMessage());
                // todo: Нужно логировать ошибку. Но про механизм пока не договорились.
                // log::error("[ERROR: " . $e->getCode() . "] " . $e->getMessage());
                $result = $this->isJson() ?
                    ["code" => $this->exception->getCode(), "message" => $this->exception->getMessage()] :
                    $this->exception->getMessage();
            }
            return $result;
        }

        /**
         * Метод читает тело http-запроса
         * и пытается преобразовать его в зависимости от content-type
         *
         * @return mixed  Тело запроса, или ассоциативный массив в случае content-type = application/json
         */
        protected function getData() {
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
         * @return boolean  true - если content-type = application/json
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

            $result = $this->getResult($requestedUri, $this->getData());
            $result = $this->isJson() ? json_encode($result) : $result;
            if (!is_null($this->exception)) {
                http_response_code($this->exception->getHttpStatus());
            }
            header("Content-Type: " . $this->format);
            header("Content-Length: " . strlen($result));
            // Чистим весь левый вывод. Мы должны отдать только результат!
            ob_end_clean();

            echo $result;
        }

    }
}
