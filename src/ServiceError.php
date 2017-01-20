<?php
namespace Phasty\Service {

    /**
     * Class ServiceError
     * Класс, для генерации обычной ошибки
     *
     * @package Phasty\Service
     */
    abstract class ServiceError extends Error {

        public function getHttpStatus() {
            return 400;
        }
    }
}