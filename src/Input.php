<?php
namespace Phasty\Service {
    class Input {
        public function getData() {
            static $result = null;
            if (!isset($result)) {
                $result = file_get_contents("php://input");
                if ($result) {
                    $result = json_decode($result, true);
                } else {
                    $result = [];
                }
            }
            return $result;
        }
    }
}
