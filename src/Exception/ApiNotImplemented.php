<?php
namespace Phasty\Service\Exception {

    use Phasty\Service\Errors;
    use Phasty\Service\FatalError;

    /**
     * Class ApiNotImplemented
     * Ошибка, возникающая при обращении на неизвестный ресурс
     *
     * @package Phasty\Service
     */
    final class ApiNotImplemented extends FatalError {

        protected static function getErrorCode() {
            return Errors::API_NOT_IMPLEMENTED;
        }

    }
}