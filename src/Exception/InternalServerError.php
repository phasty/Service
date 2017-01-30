<?php
namespace Phasty\Service\Exception {

    use Phasty\Service\Error;
    use Phasty\Service\FatalError;

    /**
     * Class InternalServerError
     * Внутренняя критическая ошибка, возникающая в случае системных сбоев
     *
     * @package Phasty\Service
     */
    final class InternalServerError extends FatalError {

        protected static function getErrorCode() {
            return Error::INTERNAL_SERVER_ERROR;
        }
    }
}