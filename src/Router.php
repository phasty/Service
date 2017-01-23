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

        /**
         * Находит класс-обработчик и метод на основании запрошенного uri и конфига
         *
         * @param  string $requestedUri   Запрошенный uri
         * @param  array  $routeMappings  Конфиг с маппингом uri - класс-метод
         *
         * @return array  Класс и метод для обработки запроса
         */
        protected static function getClassAndMethod($requestedUri, array $routeMappings) {
            if (empty($routeMappings[ $requestedUri ])) {
                throw new Exception\ApiNotImplemented("Неизвестный ресурс '$requestedUri'.");
            }
            return $routeMappings[ $requestedUri ];
        }

        /**
         * Пытается обработать запрос в сервис на основании запрошенного ресурса и
         * установленного конфига.
         * Возвращает результат в виде массива.
         * Такая реализация нужна для более простого unit-тестирования.
         *
         * @param  string $requestedUri   Запрошенный uri
         * @param  array  $input          Входяций набор данных
         * @param  array  $settings       Конфигурация для сервиса
         *
         * @return array  Результат обработки запроса
         */
        protected static function getResult($requestedUri, array $input, array $settings) {
            list($class, $method) = static::getClassAndMethod($requestedUri, $settings[ "routes" ]);
            $instance = new $class;
            return $instance->$method($input);
        }

        /**
         * Обрабатывает запрос и возвращает http-ответ в виде json-данных
         *
         * @param  array  $settings       Конфигурация для сервиса
         */
        public static function route(array $settings) {
            $requestedUri = $_SERVER[ "PHP_SELF" ];
            try {
                // Копим весь прямой вывод (ошибки, случайное echo от разработчика и т.д.)
                ob_start();
                $result = getResult($requestedUri, (new Input)->getData(), array $settings);
                // Чистим весь левый вывод. Мы должны отдать только результат!
                ob_end_clean();
            } catch (\Exception $e) {
                $e = ($e instanceof Error) ? $e : new Exception\InternalError($e->getMessage());
                http_response_code($e->getHttpStatus());
                // todo: Нужно логировать ошибку. Но про механизм пока не договорились.
                // log::error("[ERROR: " . $e->getCode() . "] " . $e->getMessage());
                $result = ["code" => $e->getCode(), "message" => $e->getMessage()];
            }

            header("Content-Type: application/json");
            header("Content-Length: " . strlen($result));
            echo json_encode($result);
        }

    }
}
