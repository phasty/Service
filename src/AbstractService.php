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
                $this->fail(400, "Unknown arguments passed");
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

        public function error404($message) {
            $this->fail(404, $message);
        }

        public function fail($code, $message) {
            http_response_code($code);
            die(json_encode(compact("message")));
        }
    }
}
