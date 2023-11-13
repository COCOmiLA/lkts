<?php

namespace backend\components;

use common\components\helpers\TableCreateHelper;
use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\ini\iniSet;
use common\models\DebuggingSoap;
use common\models\dictionary\Fias;
use common\models\dictionary\FiasDoma;
use common\models\dictionary\KladrCode;
use common\models\managers\BatchMaker;
use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Progress;
use XBase\TableReader;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class KladrLoader
{

    public static function loadKladr(string $mode): array
    {
        if ($mode == 'file') {
            return KladrLoader::loadKladrFromDBF();
        }
        if ($mode == 'university') {
            if (KladrLoader::isOneSFiasAvailable()) {
                return KladrLoader::loadKladrFromOneSFias();
            }
            throw new UserException("Сервисы для обновления адресного классификатора из Информационной системы вуза не доступны");
        } else {
            throw new UserException("Не удалось распознать способ обновления адресного классификатора");
        }
    }

    



    public static function loadKladrFromDBF(?CLImate $climate = null, ?Progress $progress = null): array
    {
        iniSet::disableTimeLimit();
        iniSet::extendMemoryLimit();

        $iterate = 10000;

        $files = [
            'DOMA' => Yii::getAlias('@backend') . FileHelper::normalizePath('\web\conf\DOMA.dbf'),
            'KLADR' => Yii::getAlias('@backend') . FileHelper::normalizePath('\web\conf\KLADR.dbf'),
            'STREET' => Yii::getAlias('@backend') . FileHelper::normalizePath('\web\conf\STREET.dbf'),
        ];

        $errors = [];

        Yii::$app->db->createCommand()->truncateTable('dictionary_fias')->execute();
        Yii::$app->db->createCommand()->truncateTable('dictionary_fias_doma')->execute();
        Yii::$app->db->createCommand()->truncateTable('kladr_codes')->execute();

        foreach ($files as $key => $file) {
            if ($climate) {
                $climate->darkGray()->out(Yii::t(
                    'console',
                    'Загрузка <bold>«<white>{FILE}</white>»</bold>',
                    ['FILE' => $file]
                ));
            }

            if ($key == 'DOMA') {
                $tables_creator = Yii::createObject(TableCreateHelper::class);
                $tables_creator->createTempTable('fias_kladr_doma_union_temp', [
                    'kladr_code' => 'VARCHAR(255) NOT NULL',
                    'name' => 'VARCHAR(255) NOT NULL',
                    'kladr_index' => 'VARCHAR(255) NOT NULL',
                ]);
                Yii::$app->db
                    ->createCommand("CREATE INDEX temp_kladr_code ON fias_kladr_doma_union_temp (kladr_code);")
                    ->execute();

                $kladr_codes = [];
                $table = new TableReader($file, ['encoding' => 'CP866', 'columns' => ['name', 'korp', 'code', 'index', 'gninmb', 'uno', 'ocatd']]);
                $progressCount = 0;
                if ($progress) {
                    $progressCount = $table->getRecordCount();
                    $progress->total($progressCount);
                }
                for ($I = 0; $row = $table->nextRecord(); $I += $iterate) {
                    if ($progress && $I % 10 == 0) {
                        $progress->current($I);
                    }
                    $kladr_codes[] = ['code' => $row->code];
                    $buffer = [];
                    $buffer[0] = [];
                    $buffer[0]['kladr_code'] = $row->code;
                    $buffer[0]['name'] = $row->name;
                    $buffer[0]['kladr_index'] = $row->index;
                    for ($j = 1; $j < $iterate && $row = $table->nextRecord(); $j++) {
                        $buffer[$j] = [];
                        try {
                            $kladr_codes[] = ['code' => $row->code];
                            $buffer[$j]['kladr_code'] = $row->code;
                            $buffer[$j]['name'] = $row->name;
                            $buffer[$j]['kladr_index'] = $row->index;
                        } catch (\Throwable $e) {
                            Yii::error("Ошибка смены кодировки $key.dbf: {$e->getMessage()} {$j}");
                            Yii::error(print_r($buffer, true));
                            throw $e;
                        }
                        
                        unset($row);
                    }

                    try {
                        Yii::$app->db->createCommand()->batchInsert(KladrCode::tableName(), ['code'], $kladr_codes)->execute();
                        $kladr_codes = [];
                        Yii::$app->db->createCommand()->batchInsert('fias_kladr_doma_union_temp', ['kladr_code', 'name', 'kladr_index'], $buffer)->execute();
                        
                        unset($buffer);
                    } catch (\Throwable $e) {
                        Yii::error("Ошибка установки $key.dbf: {$e->getMessage()}");
                        Yii::error(print_r($buffer, true));
                        throw $e;
                    }
                }

                if ($progress && $progressCount) {
                    $progress->current($progressCount);
                }
                $quoted_index_name = IndependentQueryManager::quoteEntity('index');
                $quoted_name = IndependentQueryManager::quoteEntity('name');
                $quoted_code_id = IndependentQueryManager::quoteEntity('code_id');
                Yii::$app->db->createCommand("
                INSERT INTO dictionary_fias_doma ({$quoted_code_id}, {$quoted_name}, {$quoted_index_name})
                SELECT kladr_codes.id, fias_kladr_doma_union_temp.name, fias_kladr_doma_union_temp.kladr_index
                FROM fias_kladr_doma_union_temp 
                LEFT JOIN kladr_codes ON kladr_codes.code = fias_kladr_doma_union_temp.kladr_code
                ")
                    ->execute();

                
                Yii::$app->db->createCommand()->dropTable('fias_kladr_doma_union_temp')->execute();
            } else {
                $table = new TableReader($file, ['encoding' => 'CP866', 'columns' => ['name', 'socr', 'code', 'index', 'gninmb', 'uno', 'ocatd']]);
                $progressCount = 0;
                if ($progress) {
                    $progressCount = $table->getRecordCount();
                    $progress->total($progressCount);
                }
                for ($I = 0; $row = $table->nextRecord(); $I += $iterate) {
                    if ($progress && $I % 10 == 0) {
                        $progress->current($I);
                    }
                    $buffer = [];
                    $code = $row->code;
                    $actualCode = (int)substr($code, -2);
                    $isActual = false;
                    if ($actualCode == 0) {
                        $buffer[0] = [];
                        $isActual = true;
                    }
                    if ($isActual) {
                        $buffer[0]['name'] = $row->name;
                        $buffer[0]['short'] = $row->socr;
                        $buffer[0]['code'] = $code;
                        $buffer[0]['zip_code'] = $row->index;
                        $kladr_array = static::parseKladrCode($code);
                        $buffer[0]['address_element_type'] = (string)static::getElementType($kladr_array);
                        $buffer[0]['area_code'] = (string)$kladr_array['area_code'];
                        $buffer[0]['city_code'] = (string)$kladr_array['city_code'];
                        $buffer[0]['region_code'] = (string)$kladr_array['region_code'];
                        $buffer[0]['street_code'] = (string)$kladr_array['street_code'];
                        $buffer[0]['village_code'] = (string)$kladr_array['village_code'];
                    }
                    for ($j = 1; $j < $iterate && $row = $table->nextRecord(); $j++) {
                        $code = $row->code;
                        $actualCode = (int)substr($code, -2);
                        $isActual = false;
                        if ($actualCode == 0) {
                            $buffer[$j] = [];
                            $isActual = true;
                        }
                        if ($isActual) {
                            $buffer[$j]['name'] = $row->name;
                            $buffer[$j]['short'] = $row->socr;
                            $buffer[$j]['code'] = $code;
                            $buffer[$j]['zip_code'] = $row->index;
                            $kladr_array = static::parseKladrCode($code);
                            $buffer[$j]['address_element_type'] = (string)static::getElementType($kladr_array);
                            $buffer[$j]['area_code'] = (string)$kladr_array['area_code'];
                            $buffer[$j]['city_code'] = (string)$kladr_array['city_code'];
                            $buffer[$j]['region_code'] = (string)$kladr_array['region_code'];
                            $buffer[$j]['street_code'] = (string)$kladr_array['street_code'];
                            $buffer[$j]['village_code'] = (string)$kladr_array['village_code'];
                        }
                        
                        unset($row);
                    }

                    try {
                        Yii::$app->db->createCommand()->batchInsert(
                            'dictionary_fias',
                            [
                                'name',
                                'short',
                                'code',
                                'zip_code',
                                'address_element_type',
                                'area_code',
                                'city_code',
                                'region_code',
                                'street_code',
                                'village_code'
                            ],
                            $buffer
                        )->execute();
                        
                        unset($buffer);
                    } catch (\Throwable $e) {
                        Yii::error("Ошибка установки (номер группы $I) $key.dbf: {$e->getMessage()}");
                        Yii::error(print_r($buffer, true));
                        throw $e;
                    }
                }

                if ($progress) {
                    $progress->current($progressCount);
                }
            }

            if ($climate) {
                $climate->green()->out(Yii::t(
                    'console',
                    'Загрузка <bold>«<white>{FILE}</white>»</bold> завершена успешно',
                    ['FILE' => $file]
                ));
            }
        }

        return $errors;
    }

    public static function loadKladrFromOneSFias(): array
    {
        iniSet::disableTimeLimit();
        iniSet::extendMemoryLimit();
        try {
            foreach (KladrLoader::fetchRegionList() as $number => $name) {
                KladrLoader::loadRegion($number);
            }
            return [];
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), 'loadKladr');
            return [$e->getMessage()];
        }
    }

    



    public static function parseKLADRCode(string $code): array
    {
        $region_code = substr($code, 0, 2);
        $area_code = substr($code, 2, 3);
        $city_code = substr($code, 5, 3);
        $village_code = substr($code, 8, 3);
        $street_code = substr($code, 11, 4);


        $region_code = ltrim((string)$region_code, '0') ?: '0';
        $area_code = ltrim((string)$area_code, '0') ?: '0';
        $city_code = ltrim((string)$city_code, '0') ?: '0';
        $village_code = ltrim((string)$village_code, '0') ?: '0';
        $street_code = ltrim((string)$street_code, '0') ?: '0';

        return [
            'region_code' => (int)$region_code,
            'area_code' => (int)$area_code,
            'city_code' => (int)$city_code,
            'village_code' => (int)$village_code,
            'street_code' => (int)$street_code,
        ];
    }

    protected static function getElementType(array $kladr_array): int
    {
        if ($kladr_array['street_code'] != 0) {
            return 5;
        } elseif ($kladr_array['village_code'] != 0) {
            return 4;
        } elseif ($kladr_array['city_code'] != 0) {
            return 3;
        } elseif ($kladr_array['area_code'] != 0) {
            return 2;
        } elseif ($kladr_array['region_code'] != 0) {
            return 1;
        }
        throw new \UnexpectedValueException('Неизвестный тип элемента адреса');
    }

    public static function fetchRegionList(): array
    {
        $result = Yii::$app->soapClientAbit->load('GetFiasRegionsList', [], DebuggingSoap::getInstance()->isLoggingForKladrSoapEnabled);
        if ($result && $result->return) {
            $regions = json_decode((string)$result->return, false);
            if ($regions) {
                if (!is_array($regions) || ArrayHelper::isAssociative($regions)) {
                    $regions = [$regions];
                }
                return ArrayHelper::map($regions, 'Number', 'Name');
            }
        }
        return [];
    }

    public static function isOneSFiasAvailable(): bool
    {
        try {
            $result = \Yii::$app->dictionaryManager->GetInterfaceVersion('GetFiasRegionsList');
            return version_compare($result, '0.0.18.12') >= 0;
        } catch (\Throwable $e) {
            \Yii::error("Не удалось получить версию метода GetFiasRegionsList: {$e->getMessage()}");
            return false;
        }
    }

    private static function fetchRegionElements(string $region): \Generator
    {
        $start_uid = null;
        do {
            $result = Yii::$app->soapClientAbit->load('GetFiasRegionElements', [
                'RegionNumber' => $region,
                'FetchingItemsCount' => getenv("FIAS_FETCHING_ITEMS_COUNT") ? (int)getenv("FIAS_FETCHING_ITEMS_COUNT") : 5000,
                'StartFiasIdentity' => $start_uid,
            ], DebuggingSoap::getInstance()->isLoggingForKladrSoapEnabled);
            $start_uid = null;
            if ($result && $result->return) {
                $elements = json_decode((string)$result->return, false);
                if ($elements) {
                    if (!is_array($elements) || ArrayHelper::isAssociative($elements)) {
                        $elements = [$elements];
                    }
                    foreach ($elements as $element) {
                        $start_uid = $element->FiasIdentity;
                        $clear_kladr_code = $element->KLADRCode;
                        if (!$clear_kladr_code) {
                            continue;
                        }
                        if (strlen((string)$clear_kladr_code) > 13) {
                            $clear_kladr_code = str_pad($clear_kladr_code, 17, '0', STR_PAD_LEFT);
                        } else {
                            $clear_kladr_code = str_pad($clear_kladr_code, 13, '0', STR_PAD_LEFT);
                        }
                        $full_region = str_pad($region, 2, '0', STR_PAD_LEFT);
                        $region_code = mb_substr($clear_kladr_code, 0, 2);
                        
                        
                        if ($region_code !== $full_region) {
                            if ($clear_kladr_code[-1] === '0') {
                                
                                $clear_kladr_code = '0' . mb_substr($clear_kladr_code, 0, -1);
                            } else {
                                continue;
                            }
                        }
                        yield [
                            'fias_id' => $element->FiasIdentity,
                            'parent_fias_id' => $element->ParentFiasIdentity,
                            'name' => $element->Name,
                            'short' => $element->Short,
                            'code' => $clear_kladr_code,
                            'buildings' => $element->Buildings,
                        ];
                    }
                }
            }
        } while ($start_uid);
    }

    private static function purgeRegionItems(string $region): void
    {
        $trimmed_region = ltrim($region, '0') ?: '0';
        if (Yii::$app->db->driverName === 'pgsql') {
            Yii::$app->db
                ->createCommand("
                    DELETE FROM dictionary_fias_doma
                    USING dictionary_fias
                    WHERE dictionary_fias_doma.fias_id = dictionary_fias.fias_id
                      AND dictionary_fias.region_code = :trimmed_region
                      AND dictionary_fias_doma.fias_id IS NOT NULL
                ", compact('trimmed_region'))
                ->execute();
        } else {
            Yii::$app->db
                ->createCommand("
                    DELETE dictionary_fias_doma FROM dictionary_fias_doma 
                    INNER JOIN dictionary_fias
                            ON dictionary_fias_doma.fias_id = dictionary_fias.fias_id AND dictionary_fias.region_code = :trimmed_region
                    WHERE dictionary_fias_doma.fias_id IS NOT NULL
                ", compact('trimmed_region'))
                ->execute();
        }

        $deleteQuery = "
            DELETE FROM [[dictionary_fias_doma]]
            WHERE fias_id IS NULL
            LIMIT 100000
        ";
        if (Yii::$app->db->driverName === 'pgsql') {
            $deleteQuery = "
                DELETE FROM [[dictionary_fias_doma]]
                WHERE ctid IN (
                    SELECT ctid
                    FROM [[dictionary_fias_doma]]
                    WHERE fias_id IS NULL
                    LIMIT 100000
                )
            ";
        }
        
        do {
            $affectedRows = Yii::$app->db
                ->createCommand($deleteQuery)
                ->execute();
        } while ($affectedRows > 0);

        $deleteQuery = "
            DELETE FROM [[dictionary_fias]]
            WHERE region_code = :trimmed_region OR fias_id IS NULL
            LIMIT 100000
        ";
        if (Yii::$app->db->driverName === 'pgsql') {
            $deleteQuery = "
                DELETE FROM [[dictionary_fias]]
                WHERE ctid IN (
                    SELECT ctid
                    FROM [[dictionary_fias]]
                    WHERE region_code = :trimmed_region OR fias_id IS NULL
                    LIMIT 100000
                )
            ";
        }
        do {
            $affectedRows = Yii::$app->db
                ->createCommand($deleteQuery, ['trimmed_region' => $trimmed_region])
                ->execute();
        } while ($affectedRows > 0);
    }

    private static function getFiasTypesMap(): array
    {
        static $map;
        if (!$map) {
            $map = Yii::$app->cache->getOrSet('fias_types_map', function () {
                try {
                    $result = Yii::$app->soapClientAbit->load_with_caching("GetFiasOwnershipsBuildingsTypes");
                    if (!empty($result->return)) {
                        $raw_map = json_decode((string)$result->return, false);
                        $result = [];
                        if (isset($raw_map->Ownerships)) {
                            $ownerships = [];
                            if (!is_array($raw_map->Ownerships)) {
                                $raw_map->Ownerships = [$raw_map->Ownerships];
                            }
                            foreach ($raw_map->Ownerships as $ownership) {
                                $ownerships[$ownership->Key] = $ownership->Value;
                            }
                            $result['ownerships'] = $ownerships;
                        }
                        if (isset($raw_map->Buildings)) {
                            $buildings = [];
                            if (!is_array($raw_map->Buildings)) {
                                $raw_map->Buildings = [$raw_map->Buildings];
                            }
                            foreach ($raw_map->Buildings as $building) {
                                $buildings[$building->Key] = $building->Value;
                            }
                            $result['buildings'] = $buildings;
                        }
                        return $result;
                    }
                    return [];
                } catch (\Throwable $e) {
                    Yii::error("Не удалось получить карту типов ФИАС: {$e->getMessage()}");
                    return [];
                }
            }, 3600);
        }
        return $map;
    }

    private static function getOwnershipType(string $encoded): string
    {
        $map = KladrLoader::getFiasTypesMap();
        if (isset($map['ownerships'][$encoded])) {
            
            return mb_strtolower(mb_substr($map['ownerships'][$encoded], 0, 1));
        }
        return $encoded;
    }

    private static function getBuildingType(string $encoded): string
    {
        $map = KladrLoader::getFiasTypesMap();
        if (isset($map['buildings'][$encoded])) {
            return mb_strtolower(mb_substr($map['buildings'][$encoded], 0, 1));
        }
        return $encoded;
    }

    private static function decodeFiasId(string $fias_id): string
    {
        if (empty($fias_id)) {
            return '';
        }
        $decoded = bin2hex(base64_decode($fias_id));
        return mb_strcut($decoded, 0, 8) . '-' . mb_strcut($decoded, 8, 4) . '-' . mb_strcut($decoded, 12, 4) . '-' . mb_strcut($decoded, 16, 4) . '-' . mb_strcut($decoded, 20);
    }

    private static function parseBuilding(string $building): array
    {
        $result = [];
        [$encoded_fias_id, $type, $name, $housing, $structure_type, $structure] = KladrLoader::parseBuildingString($building);
        $type = KladrLoader::getOwnershipType((string)$type);
        $structure_type = KladrLoader::getBuildingType((string)$structure_type);
        $fias_id = KladrLoader::decodeFiasId($encoded_fias_id);
        $result['fias_id'] = $fias_id;
        $result['name'] = "";
        if ($name) {
            $result['name'] = "{$type} {$name}";
        }
        if ($housing) {
            $result['name'] .= "/{$housing}";
        }
        if ($structure) {
            $result['name'] .= "/{$structure_type} {$structure}";
        }
        $result['name'] = trim($result['name'], '/');
        return $result;
    }

    public static function loadRegion(string $region, ?Progress $progress = null): void
    {
        iniSet::disableTimeLimit();
        iniSet::extendMemoryLimit();
        gc_disable();
        $buildingsBatcher = new BatchMaker(5000, function (array $batch) {
            if ($batch) {
                FiasDoma::getDb()->createCommand()->batchInsert(FiasDoma::tableName(), array_keys($batch[0]), $batch)->execute();
            }
        });
        $itemsBatcher = new BatchMaker(20000, function (array $batch) {
            if ($batch) {
                Fias::getDb()->createCommand()->batchInsert(Fias::tableName(), array_keys($batch[0]), $batch)->execute();
            }
        });
        $transaction = Fias::getDb()->beginTransaction();
        try {
            KladrLoader::purgeRegionItems($region);
            $regionElements = KladrLoader::fetchRegionElements($region);
            $progressCount = 0;
            if ($progress) {
                $regionElements = iterator_to_array($regionElements);
                $progressCount = count($regionElements);
                $progress->total($progressCount);
            }
            $time = time();
            foreach ($regionElements as $I => $item) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $item_info = array_merge($item, KladrLoader::parseKLADRCode($item['code']));
                $item_info['address_element_type'] = KladrLoader::getElementType($item_info);
                unset($item_info['buildings']);
                $item_info['created_at'] = $time;
                $item_info['updated_at'] = $time;
                $itemsBatcher->add($item_info);
                if ($item['buildings']) {
                    if (!is_array($item['buildings'])) {
                        $item['buildings'] = [$item['buildings']];
                    }
                    foreach ($item['buildings'] as $building_info) {
                        $clear_zip_code = $building_info->PostalIndex;
                        $buildings = explode("\t", (string)$building_info->BuildingsString);
                        foreach (array_chunk($buildings, 50) as $buildings_chunk) {
                            $names = array_reduce($buildings_chunk, function ($carry, $raw_building) {
                                $building = KladrLoader::parseBuilding($raw_building);
                                if (!empty($building['fias_id'])) {
                                    $carry .= "{$building['name']}, ";
                                }
                                return $carry;
                            }, '');
                            $names = trim($names, ', ');
                            $building_info = [
                                'fias_id' => $item['fias_id'],
                                'name' => $names,
                                'index' => $clear_zip_code,
                                'created_at' => $time,
                                'updated_at' => $time,
                            ];
                            $buildingsBatcher->add($building_info);
                        }
                    }
                }
            }
            $itemsBatcher->flush();
            $buildingsBatcher->flush();

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($progressCount);
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            \Yii::error("Ошибка при загрузке ФИАС: " . $e->getMessage(), 'loadRegion');
            throw $e;
        }
    }

    private static function parseBuildingString(string $building): array
    {
        
        $equals_index = mb_strpos($building, '==');
        if ($equals_index === false) {
            return ['', '', '', '', '', ''];
        }
        
        $encoded_fias_id = mb_substr($building, 0, $equals_index + 2);
        $tilde_index = mb_strpos($building, '~', $equals_index);
        $type = mb_substr($building, $equals_index + 2, 1);
        $name = mb_substr($building, $equals_index + 3, $tilde_index ? $tilde_index - $equals_index - 3 : null);
        $housing = null;
        $structure_type = null;
        $structure = null;

        if ($tilde_index) {
            $second_tilde_index = mb_strpos($building, '~', $tilde_index + 1);
            $housing = mb_substr($building, $tilde_index + 1, $second_tilde_index ? $second_tilde_index - $tilde_index - 1 : null);
            if ($second_tilde_index) {
                $third_tilde_index = mb_strpos($building, '~', $second_tilde_index + 1);

                if ($third_tilde_index) {
                    $structure_type = mb_substr($building, $second_tilde_index + 1, $third_tilde_index - $second_tilde_index - 1);
                    $structure = mb_substr($building, $third_tilde_index + 1);
                }
            }
        }
        return [$encoded_fias_id, $type, $name, $housing, $structure_type, $structure];
    }
}
