<?php
namespace Phasty\Service {

    /**
     * Class ServiceError
     * Класс, для генерации обычной ошибки, которая является следствием стандартных проверок
     * в процессе исполнения скрипта.
     *
     * @package Phasty\Service
     */
    abstract class ServiceError extends Error {

        public function getHttpStatus() {
            return 400;
        }
    }
}