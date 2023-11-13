<?php

namespace common\components;

use common\models\DebuggingSoap;
use common\models\DummySoapResponse;
use common\models\EmptyCheck;
use SoapClient;
use Yii;
use yii\caching\CacheInterface;
use yii\caching\Dependency;
use yii\caching\TagDependency;




class soapClientManager extends \yii\base\Component
{
    public $wsdl;
    public $login;
    public $debug;
    public $password;

    public $client;

    protected ?int $user_id;

    protected CacheInterface $cache;
    protected bool $use_real_cache;
    protected array $local_cache = [];

    public function __construct(CacheInterface $cache, $config = [])
    {
        parent::__construct($config);
        $this->cache = $cache;
        $this->use_real_cache = BooleanCaster::cast(getenv('ENABLE_QUERY_CACHE'));

        if (isset(Yii::$app->user)) {
            if (Yii::$app->user->isGuest) {
                $this->user_id = null;
            } else {
                $this->user_id = (int)Yii::$app->user->id;
            }
        } else {
            
            $this->user_id = 1;
        }
    }

    public function init()
    {
        parent::init();

        if (!str_starts_with($this->wsdl, 'http')) {
            return;
        }

        try {
            ini_set("soap.wsdl_cache_enabled", 0);

            $current_default_socket_timeout = ini_get('default_socket_timeout');
            if ($current_default_socket_timeout < 600) {
                ini_set('default_socket_timeout', 600);
            }
            $this->client = new SoapClient($this->wsdl, [
                'login' => $this->login,
                'password' => $this->password,
                'keep_alive' => BooleanCaster::cast(getenv('SOAP_KEEP_ALIVE')),
                'trace' => false,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'location' => strstr($this->wsdl, '?', true),
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP | SOAP_COMPRESSION_DEFLATE,
            ]);
        } catch (\Throwable $e) {
            Yii::$app->session->setFlash(
                'UnsuccessfulConnectionWithSoap',
                '<strong>Невозможно подключиться к веб-сервисам Информационной системы вуза:</strong> ' . $e->getMessage()
            );
            throw new \yii\base\UserException('Документ WSDL недоступен или некорректен.', '11001', $e);
        }
    }

    public function getIsInitialized(): bool
    {
        return isset($this->client);
    }

    public function isServicesEnabled(): bool
    {
        if (!$this->isInitialized) {
            return false;
        }

        $env = getenv('ENABLE_1C_SERVICES') !== false ? getenv('ENABLE_1C_SERVICES') : 'true'; 
        return $env === 'true';
    }

    






    public function load($action, $params = [], bool $write_logs = true)
    {
        $enable1cServices = $this->isServicesEnabled();
        if (!$enable1cServices) {
            return false;
        }

        $debuggingEnabled = false;
        $dummySoapEnable = false;
        $loggers = [];
        try {
            $model = DebuggingSoap::getInstance();
            $debuggingEnabled = ($model->debugging_enable || $model->xml_debugging_enable);
            $dummySoapEnable = $model->enable_dummy_soap_mode;
            $loggers = $model->getEnabledLoggers();
        } catch (\Throwable $e) {
            $debuggingEnabled = false;
            $loggers = [];
            Yii::error('Не установлена таблица "debuggingsoap"');
        }
        $dummyResponse = null;
        if ($dummySoapEnable) {
            


            $dummyResponse = DummySoapResponse::find()->where(['method_name' => $action])->limit(1)->one();
            if (!empty($dummyResponse)) {
                $data_from_xml = simplexml_load_string($dummyResponse->method_response);
                if (!empty($data_from_xml)) {
                    $array_data = json_decode(json_encode($data_from_xml), true);
                    $array_data = $this->processJsonArray($array_data);
                    if ($array_data == "") {
                        return false;
                    }
                    return json_decode(json_encode($array_data));
                }
            }
        }
        $startTime = date(DATE_ATOM);
        try {
            $buffer = false;
            if ($this->client != null) {
                if (is_string($params)) {
                    
                    $buffer = $this->client->$action(new \SoapVar(trim((string)$params), XSD_ANYXML));
                } else {
                    $buffer = $this->client->$action($params);
                }
            }
            if ($debuggingEnabled && $write_logs) {
                $endTime = date(DATE_ATOM);

                foreach ($loggers as $logger) {
                    $logger->doRequestLog($action, $startTime, $endTime, $params, $buffer);
                }
            }
            return $buffer;
        } catch (\Throwable $e) {
            foreach ($loggers as $logger) {
                $logger->doErrorLog($action, $startTime, $params, $e);
            }

            throw new soapException(
                'Ошибка обращения к методу.',
                '11002',
                $action,
                $e->getMessage(),
                $params
            );
        }
    }

    




    public function resetCurrentUserCache(string $action, array $additional_user_ids_to_clear_cache = [])
    {
        if ($this->use_real_cache) {
            foreach ($additional_user_ids_to_clear_cache as $additional_user_id) {
                TagDependency::invalidate($this->cache, $action . '.' . $additional_user_id);
            }
            if (!$this->user_id) {
                return;
            }
            if (!in_array($this->user_id, $additional_user_ids_to_clear_cache)) {
                TagDependency::invalidate($this->cache, $action . '.' . $this->user_id);
            }
        } else {
            foreach (array_keys($this->local_cache) as $key) {
                if (str_starts_with($key, $action)) {
                    unset($this->local_cache[$key]);
                }
            }
        }
    }

    public function load_with_caching(string $action, $params = [], bool $write_logs = true)
    {
        $sorted_params = null;
        if (!is_string($params)) {
            
            $sorted_params = json_decode(json_encode($params), true);
            $this->recursive_ksort($sorted_params);
        } else {
            $sorted_params = $params;
        }
        $params_key = md5(json_encode($sorted_params));
        $cache_key = $action . '_' . $params_key;
        if ($this->cacheExists($cache_key)) {
            return $this->cacheGet($cache_key);
        }
        $result = $this->load($action, $params, $write_logs);
        if ($result) {
            $this->cacheSet($cache_key, $result, 120, new TagDependency(['tags' => [$action, $action . '.' . $this->user_id]]));
        }
        return $result;
    }

    protected function cacheExists(string $cache_key): bool
    {
        if ($this->use_real_cache) {
            return $this->cache->exists($cache_key);
        } else {
            return isset($this->local_cache[$cache_key]);
        }
    }

    protected function cacheGet(string $cache_key)
    {
        if ($this->use_real_cache) {
            return $this->cache->get($cache_key);
        } else {
            return $this->local_cache[$cache_key] ?? null;
        }
    }

    protected function cacheSet(string $cache_key, $value, int $duration, Dependency $dependency = null)
    {
        if ($this->use_real_cache) {
            $this->cache->set($cache_key, $value, $duration, $dependency);
        } else {
            $this->local_cache[$cache_key] = $value;
        }
    }

    protected function recursive_ksort(&$array)
    {
        foreach ($array as $k => &$v) {
            if (is_array($v)) {
                $this->recursive_ksort($v);
            }
        }
        return ksort($array);
    }

    protected function processJsonArray($array_to_convert)
    {
        if (empty($array_to_convert)) {
            return "";
        }
        $is_empty_array = true;
        foreach ($array_to_convert as &$item) {
            if (is_array($item)) {
                $item = $this->processJsonArray($item);
                if ($item !== "") {
                    $is_empty_array = false;
                }
            } else {
                if ($item === 'true') {
                    $item = true;
                    $is_empty_array = false;
                } elseif ($item === 'false') {
                    $item = false;
                    $is_empty_array = false;
                } else {
                    $item = trim((string)$item);
                    if ($item || !EmptyCheck::isEmpty($item)) {
                        $is_empty_array = false;
                    }
                }
            }
        }
        if ($is_empty_array) {
            return "";
        }
        return $array_to_convert;
    }
}
