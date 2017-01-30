<?php
namespace Phasty\Service\Exception {

    use Phasty\Service\Errors;
    use Phasty\Service\RequestError;

    /**
     * Class BadRequest
     * Ошибка валидации входных параметров
     *
     * @package Phasty\Service
     */
    final class BadRequest extends RequestError {

        protected static function getErrorCode() {
            return Errors::BAD_REQUEST;
        }

    }
}