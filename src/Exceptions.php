<?php
namespace Phasty\Service {

    /**
     * Class Exceptions
     * Класс, списка кодов ошибок
     *
     * @package Phasty\Service
     */
    abstract class Exceptions {

        const INTERNAL_ERROR      = 1;
        const API_NOT_IMPLEMENTED = 2;
        const BAD_REQUEST         = 3;

    }
}