<?php
namespace Phasty\Service {

    use Phasty\Service\Exception;

    /**
     * Class AbstractService
     * Класс, для создания ресурсов (API) в сервисе
     *
     * @package Phasty\Service
     */
    abstract class AbstractService {

        /**
         * Проверяет, что не переданы лишние параметры в сервис
         *
         * @param array $data Параметры выборки
         *
         * @param array $data
         */
        protected function assertEmpty(array $data) {
            if (!empty($data)) {
                throw new Exception\BadRequest("Extra params was passed: '" . implode(", ", array_keys($data)) . "'.");
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
    }
}
