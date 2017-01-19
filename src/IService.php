<?php
namespace Phasty\Service {
    interface IService {

        const INTERNAL_ERROR      = 0;
        const API_NOT_IMPLEMENTED = 1;
        const BAD_REQUEST         = 2;
        /**
         * Заполнение сделал в таком формате, т.к. так проще читать и добавлять элементы
         * code => [ httpStatus, message ]
         */
        const COMMON_ERRORS = [
            INTERNAL_ERROR      => [ 500, "Internal error." ],
            API_NOT_IMPLEMENTED => [ 501, "API not implemented." ],
            BAD_REQUEST         => [ 400, "Unknown arguments passed." ]
        ];

        function fail($code, $message);

    }
}
