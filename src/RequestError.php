<?php
namespace Phasty\Service {

    /**
     * Class RequestError
     * Класс, для генерации обычной ошибки, которая является следствием стандартных проверок
     * в процессе исполнения скрипта.
     *
     * @package Phasty\Service
     */
    abstract class RequestError extends FatalError {

        public function getHttpStatus() {
            return 400;
        }
    }
}