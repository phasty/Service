<?php
namespace Phasty\Service {

    /**
     * Class Error
     * Класс, списка кодов ошибок
     *
     * @package Phasty\Service
     */
    abstract class Error {

        /** @var int  INTERNAL_SERVER_ERROR  Ошибка, возникающая в случае серверных сбоев (сервер не может обработать запрос из-за системного сбоя) */
        const INTERNAL_SERVER_ERROR = 1;

        /** @var int  API_NOT_IMPLEMENTED    Ошибка, возникающая в случае запроса на неизвестный ресурс API */
        const API_NOT_IMPLEMENTED   = 2;

        /** @var int  BAD_REQUEST            Ошибка, возникающая в случае некорректного запроса (не хватает ключевых параметров и т.п.) */
        const BAD_REQUEST           = 3;

    }
}