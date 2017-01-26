<?php
namespace Phasty\Service\Exception {

    use Phasty\Service\Exceptions;
    use Phasty\Service\Error;

    /**
     * Class InternalServerError
     * Внутренняя критическая ошибка, возникающая в случае системных сбоев
     *
     * @package Phasty\Service
     */
    final class InternalServerError extends Error {

        protected static function getErrorCode() {
            return Exceptions::INTERNAL_SERVER_ERROR;
        }
    }
}