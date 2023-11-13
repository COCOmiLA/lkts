<?php

namespace console\controllers;

use backend\components\KladrLoader;
use common\components\ini\iniSet;
use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Progress;
use Yii;

class AddressController extends CLImateController
{
    public $defaultAction = 'update';

    public function actionUpdate()
    {
        $this->climate = new CLImate();

        $selectedUpdateType = $this->choseUpdateType();

        if ($selectedUpdateType == '0') {
            KladrLoader::loadKladrFromDBF($this->climate, $this->climate->progress());

            return;
        }
        if ($selectedUpdateType == '1') {
            foreach (KladrLoader::fetchRegionList() as $number => $name) {
                $this->loadRegion($number, $name);
            }

            return;
        }

        $values = KladrLoader::fetchRegionList();
        $rawSelectedRegions = $this->renderCheckBox(
            Yii::t('console', 'Выберите регион(ы) для обновления'),
            $values,
            Yii::t(
                'console',
                'Укажите номера региона(ов) через пробел:'
            )
        );

        $dictionaryNotExist = Yii::t(
            'console',
            'Значение <bold>«<red>{DICTIONARY_KEY}</red>»</bold> отсутствует в списке регионов'
        );
        if (!$this->canColorizeText) {
            $dictionaryNotExist = Yii::t(
                'console',
                'Значение «{DICTIONARY_KEY}» отсутствует в списке регионов'
            );
        }
        $selectedRegions = $this->normalizeCheckBoxList(
            $rawSelectedRegions,
            $values,
            $dictionaryNotExist
        );

        $this->climate->br();
        $this->climate->border();

        foreach ($selectedRegions  as $number => $name) {
            $this->loadRegion($number, $name);
        }
    }

    


    private function choseUpdateType(): string
    {
        $list = [
            '0' => 'Обновить через <bold>.dbf</bold> файлы',
            '1' => 'Обновить все регионы (из базы адресного классификатора <bold>1С</bold>)',
            '2' => 'Обновить регионы выборочно (из базы адресного классификатора <bold>1С</bold>)',
        ];
        if (!$this->canColorizeText) {
            $list = [
                '0' => 'Обновить через .dbf файлы',
                '1' => 'Обновить все регионы (из базы адресного классификатора 1С)',
                '2' => 'Обновить регионы выборочно (из базы адресного классификатора 1С)',
            ];
        }
        return $this->renderRadio(
            Yii::t('console', 'Тип обновления КЛАДР-а'),
            $list,
            Yii::t('console', 'Выберите один из вариантов:')
        );
    }

    





    private function loadRegion(string $number, string $name): void
    {
        iniSet::disableTimeLimit();
        iniSet::extendMemoryLimit();

        $dictionaryLoadingText = Yii::t(
            'console',
            'Загрузка региона «{NAME} ({NUMBER})»',
        );
        if ($this->canColorizeText) {
            $dictionaryLoadingText = Yii::t(
                'console',
                'Загрузка региона «{NAME} (<cyan>{NUMBER}</cyan>)»',
            );
        }
        $this->out($this->climate->br(), Yii::t(
            'console',
            $dictionaryLoadingText,
            [
                'NUMBER'  => $number,
                'NAME' => $name,
            ]
        ));

        
        $progress = $this->climate->progress();
        $time = microtime(true);
        KladrLoader::loadRegion($number, $progress);
        $deltaTime = microtime(true) - $time;
        $dictionarySuccessText = Yii::t(
            'console',
            "Регион «{NAME} ({NUMBER})» загружен успешно\nЗатрачено времени: {DELTA_TIME} сек.",
        );
        if ($this->canColorizeText) {
            $dictionarySuccessText = Yii::t(
                'console',
                "<background_green><black>Регион «{NAME} ({NUMBER})» загружен успешно</background_green></black>\nЗатрачено времени: <bold>{DELTA_TIME}</bold> сек.",
            );
        }

        $this->out($this->climate->green(), Yii::t(
            'console',
            $dictionarySuccessText,
            [
                'DELTA_TIME' => $deltaTime,
                'NUMBER'  => $number,
                'NAME' => $name,
            ]
        ));

        return;
    }
}
