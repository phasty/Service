<?php
namespace Phasty\Service {

    use Phasty\Service\Error;

    /**
     * Class AbstractService
     * Класс, для создания ресурсов (API) в сервисе
     *
     * @package Phasty\Service
     */
    abstract class AbstractService implements IService {

        protected static $errors = [];
        /**
         * Проверяет, что не переданы лишние параметры в сервис
         *
         * @param array $data Параметры выборки
         *
         * @param array $data
         */
        protected function assertEmpty(array $data) {
            if (!empty($data)) {
                $this->fail(BAD_REQUEST);
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

        /**
         * Функция выбрасывает правильное исключение с кодом и текстом ошибки
         *
         * @param  int    $code    Код ошибки
         * @param  string $message Текст ошибки (если для данного кода нужно отправить нестандартный текст)
         *
         * @throws Error
         */
        public function fail($code, $message = "") {
            $error = static::getError($code);

            throw new Error(empty($message) ? $error[1] : $message, $code);
        }

        /**
         * Функция возвращает ошибку по ее коду
         *
         * @param  int    $code    Код ошибки
         *
         * @return array $error
         */
        public static function getError($code) {
            if (!empty(IService::COMMON_ERRORS[$code])) {
                // Одна из базовых ошибок
                return IService::COMMON_ERRORS[$code];
            } elseif (!empty(static::$errors[$code])) {
                // Одна из специфических ошибок сервиса
                return static::$errors[$code]
            } else {
                return IService::COMMON_ERRORS[IService::INTERNAL_ERROR];
            }
        }
    }
}
