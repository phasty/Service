<?php
namespace Phasty\Service {
    abstract class AbstractService implements IService {
        /**
         * Проверяет, что не переданы лишние параметры в сервис
         *
         * @param array $data Параметры выборки
         *
         * @param array $data
         */
        protected function assertEmpty(array $data) {
            if (!empty($data)) {
                $this->fail(501, "Not Implemented", "Unknown arguments passed");
            }
        }

        /**
         * Возвращает значение из хеша, удаляя это значение из хеша
         *
         * @param array  $data    Хеш, из которого удалить извлеч значение
         * @param string $key     Ключ, по которому искать значение
         * @param mixed  $default Значение по умолчанию
         *
         * @return mixed
         */
        protected function extract(&$data, $key, $default = null) {
            if (!isset($data[ $key ])) {
                return $default;
            }
            $result = $data[ $key ];
            unset($data[ $key ]);
            return $result;
        }

        protected function error404($message) {
            $this->fail(404, "Not Found", $message);
        }

        private function fail($code, $httpMessage, $message) {
            header("HTTP/1.1 $code $httpMessage");
            die(json_encode(compact("message")));
        }
    }
}
