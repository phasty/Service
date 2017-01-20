<?php
namespace Phasty\Service {

    use Phasty\Service\Exceptions;

    /**
     * Class InternalError
     * Внутренняя критическая ошибка, возникающая в случае системных сбоев
     *
     * @package Phasty\Service
     */
    final class InternalError extends Error {

        protected static function getErrorCode() {
            return Exceptions::INTERNAL_ERROR;
        }
    }
}