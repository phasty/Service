<?php
namespace Phasty\Service {
    class Test implements IService {
        /**
         * Test method
         *
         * @api
         */
        public function testMethod() {
            return [
                "message" => "test service execution result"
            ];
        }

        public function fail($code, $message) {
            throw new \Exception($message, $code);
        }

    }
}
