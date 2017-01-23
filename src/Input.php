<?php
namespace Phasty\Service {

    /**
     * Calss Input
     * Класс для чтения тела запроса и его парсинга
     *
     * @package Phasty\Service
     */
    class Input {

        /**
         * Метод ожидает json-объект на входном потоке данных (в теле http-запроса)
         * и пытается рапарсить его
         *
         * @return array  Ассоциативный массив данных из входящего запроса
         */
        public function getData() {
            static $result = null;
            if (!isset($result)) {
                $result = file_get_contents("php://input");
                if ($result) {
                    $result = json_decode($result, true);
                } else {
                    $result = [];
                }
            }
            return $result;
        }
    }
}
