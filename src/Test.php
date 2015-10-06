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
    }
}
