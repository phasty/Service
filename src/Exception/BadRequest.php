<?php
namespace Phasty\Service\Exception {

    use Phasty\Service\Error;
    use Phasty\Service\RequestError;

    /**
     * Class BadRequest
     * Ошибка валидации входных параметров
     *
     * @package Phasty\Service
     */
    final class BadRequest extends RequestError {

        protected static function getErrorCode() {
            return Error::BAD_REQUEST;
        }

    }
}