<?php

namespace console\controllers;

use backend\models\DictionaryUpdateHistory;
use common\components\AppUpdate;
use common\components\ini\iniSet;
use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Progress;
use Throwable;
use Yii;

class DictionaryController extends CLImateController
{
    public $defaultAction = 'update';

    public function actionUpdate()
    {
        $this->climate = new CLImate();

        $selectedUpdateType = $this->choseUpdateType();

        if ($selectedUpdateType == '0') {
            foreach (AppUpdate::DICTIONARY_UPDATE as $method => $name) {
                $this->updateOneDictionary($method, $name);
            }

            return;
        }

        [
            'keys' => $keys,
            'values' => $values,
        ] = $this->splitDictionaryUpdateList();
        $rawSelectedDictionaries = $this->renderCheckBox(
            Yii::t('console', 'Выберите справочник(и) для обновления'),
            $values,
            Yii::t(
                'console',
                'Укажите номера справочника(ов) через пробел:'
            )
        );

        $dictionaryNotExist = Yii::t(
            'console',
            'Значение <bold>«<red>{DICTIONARY_KEY}</red>»</bold> отсутствует в списке справочников'
        );
        if (!$this->canColorizeText) {
            $dictionaryNotExist = Yii::t(
                'console',
                'Значение «{DICTIONARY_KEY}» отсутствует в списке справочников'
            );
        }
        $selectedDictionaries = $this->normalizeCheckBoxList(
            $rawSelectedDictionaries,
            $keys,
            Yii::t(
                'console',
                $dictionaryNotExist
            )
        );
        $this->climate->br();
        $this->climate->border();

        foreach ($selectedDictionaries as $method) {
            $name = AppUpdate::DICTIONARY_UPDATE[$method];
            $this->updateOneDictionary($method, $name);
        }
    }

    


    private function choseUpdateType(): string
    {
        return $this->renderRadio(
            Yii::t('console', 'Тип обновления справочников'),
            [
                '0' => 'Обновить все справочники',
                '1' => 'Обновить справочники выборочно',
            ],
            Yii::t('console', 'Выберите один из вариантов:')
        );
    }

    


    private function splitDictionaryUpdateList(): array
    {
        return [
            'keys' => array_keys(AppUpdate::DICTIONARY_UPDATE),
            'values' => array_values(AppUpdate::DICTIONARY_UPDATE),
        ];
    }

    





    private function updateOneDictionary(string $method, string $humanReadableName): void
    {
        $dictionaryLoadingText = Yii::t(
            'console',
            '{HUMAN_READABLE_NAME} (<cyan>{METHOD}</cyan>) начал загрузку',
        );
        if (!$this->canColorizeText) {
            $dictionaryLoadingText = Yii::t(
                'console',
                '{HUMAN_READABLE_NAME} ({METHOD}) начал загрузку',
            );
        }
        $this->out($this->climate->br(), Yii::t(
            'console',
            $dictionaryLoadingText,
            [
                'METHOD'  => $method,
                'HUMAN_READABLE_NAME' => $humanReadableName,
            ]
        ));

        $time = microtime(true);
        [
            'status' => $status,
            'error_message' => $errorMessage,
        ] = $this->updateDictionary($method);
        if ($status) {
            $deltaTime = microtime(true) - $time;
            $dictionarySuccessText = Yii::t(
                'console',
                "{HUMAN_READABLE_NAME} установлен успешно\nЗатрачено времени: {DELTA_TIME} сек.",
            );
            if ($this->canColorizeText) {
                $dictionarySuccessText = Yii::t(
                    'console',
                    "<background_green><black>{HUMAN_READABLE_NAME} установлен успешно</black></background_green>\nЗатрачено времени: <bold>{DELTA_TIME}</bold> сек.",
                );
            }
            $this->out($this->climate->green(), Yii::t(
                'console',
                $dictionarySuccessText,
                [
                    'DELTA_TIME' => $deltaTime,
                    'HUMAN_READABLE_NAME' => $humanReadableName
                ]
            ));
        } elseif (!empty($errorMessage)) {
            $dictionaryFailText = Yii::t(
                'console',
                "{HUMAN_READABLE_NAME} ({METHOD}) был загружен с ошибкой:\n{ERROR_MESSAGE}"
            );
            if ($this->canColorizeText) {
                $dictionaryFailText = Yii::t(
                    'console',
                    "<background_red><black>{HUMAN_READABLE_NAME} ({METHOD}) был загружен с ошибкой:</black></background_red>\n{ERROR_MESSAGE}"
                );
            }
            $this->out($this->climate->red(), Yii::t(
                'console',
                $dictionaryFailText,
                [
                    'HUMAN_READABLE_NAME' => $humanReadableName,
                    'METHOD' => $method,
                    'ERROR_MESSAGE' => $errorMessage,
                ]
            ));
        }
    }

    




    private function updateDictionary($method)
    {
        iniSet::disableTimeLimit();
        iniSet::extendMemoryLimit();
        if (empty($method)) {
            $this->out($this->climate->backgroundRed()->black(), Yii::t(
                'console',
                'Переданный метод пустой'
            ));
        }

        $dictionaryManager = Yii::$app->dictionaryManager;
        if (!method_exists($dictionaryManager, $method)) {
            $dictionaryMetedNotFound = Yii::t(
                'console',
                'Невозможно найти метод «{METHOD}»'
            );
            if ($this->canColorizeText) {
                $dictionaryMetedNotFound = Yii::t(
                    'console',
                    'Невозможно найти метод <bold>«{METHOD}»</bold>'
                );
            }
            $this->out($this->climate->backgroundRed()->black(), Yii::t(
                'console',
                $dictionaryMetedNotFound,
                ['METHOD' => $method]
            ));

            die();
        }

        
        $progress = $this->climate->progress();
        try {
            [$status, $error] = $dictionaryManager->{$method}($progress);
        } catch (Throwable $e) {
            $status = -1;
            $error = $e;
        }
        $error_message = '';
        if ($status === 1) { 
            DictionaryUpdateHistory::setUpdateTime($method, time());
            return [
                'status' => true,
                'error_message' => $error_message
            ];
        }

        if ($error) {
            if ($error instanceof Throwable) {
                $error_message = "{$error->getMessage()}\n\n{$error->getTraceAsString()}";
            } else {
                $error_message = print_r($error, true);
            }
            Yii::error($error_message, 'DICTIONARY_UPDATE');

            return [
                'status' => false,
                'error_message' => $error_message
            ];
        }

        
        $dictionaryEmptyData = Yii::t(
            'console',
            'Метод «{METHOD}» пустой'
        );
        if ($this->canColorizeText) {
            $dictionaryEmptyData = Yii::t(
                'console',
                'Метод <bold>«{METHOD}»</bold> пустой'
            );
        }
        $this->out($this->climate->backgroundYellow()->black(), Yii::t(
            'console',
            $dictionaryEmptyData,
            ['METHOD' => $method]
        ));

        return [
            'status' => false,
            'error_message' => ''
        ];
    }
}
