<?php

namespace console\controllers;

use League\CLImate\CLImate;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class CLImateController extends Controller
{
    private const RADIO_INPUT = 'radio';

    private const CHECK_BOX_INPUT = 'CheckBox';

    
    protected ?CLImate $climate = null;

    
    protected bool $canColorizeText = true;

    


    public function beforeAction($action)
    {
        ob_start();
        phpinfo();
        $pinfo = ob_get_contents();
        ob_end_clean();

        if (preg_match('/(System => Windows NT .* build (\d+) \()/', $pinfo, $matches)) {
            $this->canColorizeText = $matches[2] >= 14393;
        }

        return parent::beforeAction($action);
    }

    






    protected function renderRadio(string $question, array $values, string $prompt): string
    {
        return $this->renderInput($question, $values, $prompt, CLImateController::RADIO_INPUT);
    }

    






    protected function renderCheckBox(string $question, array $values, string $prompt): string
    {
        return $this->renderInput($question, $values, $prompt);
    }

    







    private function renderInput(
        string $question,
        array  $values,
        string $prompt,
        string $inputType = CLImateController::CHECK_BOX_INPUT
    ): string {
        $this->out($this->climate->br(), $question);
        $this->columns($values);
        return $this->input($prompt, $values, $inputType);
    }

    




    private function columnsDataBeautifier(array $values): array
    {
        $data = [];
        $maxKeyLength = strlen((string) max(array_keys($values)));
        foreach ($values as $i => $value) {
            $I = str_repeat(' ', $maxKeyLength - strlen($i)) . $i;
            $data[$i] = "<bold><yellow>{$I}</yellow></bold>: $value";
            if (!$this->canColorizeText) {
                $data[$i] = "{$I}: $value";
            }
        }

        return $data;
    }

    






    protected function normalizeCheckBoxList(
        string $rawList,
        array  $referenceList,
        string $notFoundMessage = ''
    ): array {
        $normalizeList = [];
        $splittedRawList = explode(' ', $rawList);
        foreach ($splittedRawList as $dictionaryKey) {
            $dictionaryKey = trim($dictionaryKey, ', ');
            if ($dictionaryKey != '00') {
                $dictionaryKey = ltrim($dictionaryKey, '0');
            } else {
                $dictionaryKey = '0';
            }

            if (!array_key_exists($dictionaryKey, $referenceList)) {
                $output = $this->climate;
                if ($this->canColorizeText) {
                    $output = $this->climate->yellow()->bold()->blink();
                }
                $output->flank(Yii::t(
                    'console',
                    'Внимание'
                ), '!');
                $this->out($this->climate, Yii::t(
                    'console',
                    $notFoundMessage,
                    ['DICTIONARY_KEY' => $dictionaryKey]
                ));
                continue;
            }

            if (!array_key_exists($dictionaryKey, $normalizeList)) {
                $normalizeList[$dictionaryKey] = $referenceList[$dictionaryKey];
            }
        }

        ksort($normalizeList);
        return $normalizeList;
    }

    protected function out($style, string $string): void
    {
        if ($this->canColorizeText) {
            $style->out($string);
            return;
        }
        echo $string . PHP_EOL;
    }

    protected function input(string $prompt, array $values, string $inputType): string
    {
        $acceptValues = array_keys($values);
        if ($this->canColorizeText) {
            $input = $this->climate->input($prompt);
            if ($inputType == CLImateController::RADIO_INPUT) {
                $input->accept($acceptValues, true);
            }
            $input->strict();

            return $input->prompt();
        }

        $acceptValues = implode(', ', $acceptValues);
        $acceptValues = " [{$acceptValues}]";
        if ($inputType == CLImateController::CHECK_BOX_INPUT) {
            $acceptValues = '';
        }
        echo "{$prompt}{$acceptValues} ";

        return Console::stdin();
    }

    protected function columns(array $values): void
    {
        $values = $this->columnsDataBeautifier($values);
        if ($this->canColorizeText) {
            $this->climate->columns(
                $values
            );
            return;
        }

        foreach ($values as $string) {
            echo $string . PHP_EOL;
        }
    }
}
