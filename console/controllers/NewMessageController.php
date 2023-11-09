<?php





namespace console\controllers;

use yii\console\controllers\MessageController;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\VarDumper;





class NewMessageController extends MessageController
{
    











    protected function saveMessagesCategoryToPHP($messages, $fileName, $overwrite, $removeUnused, $sort, $category, $markUnused)
    {
        if (is_file($fileName)) {
            $rawExistingMessages = require $fileName;
            $existingMessages = $rawExistingMessages;
            sort($messages);
            ksort($existingMessages);
            if (array_keys($existingMessages) === $messages && (!$sort || array_keys($rawExistingMessages) === $messages)) {
                $this->stdout("Nothing new in \"$category\" category... Nothing to save.\n\n", Console::FG_GREEN);
                return ExitCode::OK;
            }
            unset($rawExistingMessages);
            $merged = [];
            $untranslated = [];
            foreach ($messages as $message) {
                if (array_key_exists($message, $existingMessages) && $existingMessages[$message] !== '') {
                    $merged[$message] = $existingMessages[$message];
                } else {
                    $untranslated[] = $message;
                }
            }
            ksort($merged);
            sort($untranslated);
            $todo = [];
            foreach ($untranslated as $messageKey) {
                if (preg_match('/\`(.*?)\`/', $messageKey,  $message)) {
                    $todo[$messageKey] = $message[1];
                } else {
                    $todo[$messageKey] = '';
                }
            }
            ksort($existingMessages);
            foreach ($existingMessages as $message => $translation) {
                if (!$removeUnused && !isset($merged[$message]) && !isset($todo[$message])) {
                    if (!$markUnused || (!empty($translation) && (strncmp($translation, '@@', 2) === 0 && substr_compare($translation, '@@', -2, 2) === 0))) {
                        $todo[$message] = $translation;
                    } else {
                        $todo[$message] = '@@' . $translation . '@@';
                    }
                }
            }
            $merged = array_merge($merged, $todo);
            if ($sort) {
                ksort($merged);
            }
            if (false === $overwrite) {
                $fileName .= '.merged';
            }
            $this->stdout("Translation merged.\n");
        } else {
            $merged = [];
            foreach ($messages as $messageKey) {
                if (preg_match('/\`(.*?)\`/', $messageKey,  $message)) {
                    $merged[$messageKey] = $message[1];
                } else {
                    $merged[$messageKey] = '';
                }
            }
            ksort($merged);
        }

        $array = VarDumper::export($merged);
        $content = <<<EOD
<?php
{$this->config['phpFileHeader']}{$this->config['phpDocBlock']}
return $array;

EOD;

        if (file_put_contents($fileName, $content, LOCK_EX) === false) {
            $this->stdout("Translation was NOT saved.\n\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Translation saved.\n\n", Console::FG_GREEN);
        return ExitCode::OK;
    }
}
