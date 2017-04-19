<?php
namespace Phasty\Service {

    use Phasty\Service\Exception\BadRequest;

    /**
     * Class AbstractService
     * Класс, для создания ресурсов (API) в сервисе
     *
     * @package Phasty\Service
     */
    abstract class AbstractService {

        private $appUid;

        /**
         * Конструктор
         */
        public function __construct($appUid) {
            $this->appUid = $appUid;
        }

        /**
         * Получает идентификатор клиента сервиса
         */
        protected function getAppUid() {
            return $this->appUid;
        }

        /**
         * Проверяет, что не переданы лишние параметры в сервис
         *
         * @param array $data Параметры выборки
         *
         * @throws BadRequest
         */
        protected function assertEmpty(array $data) {
            if (!empty($data)) {
                throw new BadRequest("Extra params passed: '" . implode(", ", array_keys($data)) . "'.");
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
