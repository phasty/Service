<?php
namespace Phasty\Service\Exception {

    use Phasty\Service\Exceptions;
    use Phasty\Service\ServiceError;

    /**
     * Class BadRequest
     * Ошибка валидации входных параметров
     *
     * @package Phasty\Service
     */
    final class BadRequest extends ServiceError {

        protected static function getErrorCode() {
            return Exceptions::BAD_REQUEST;
        }

    }
}