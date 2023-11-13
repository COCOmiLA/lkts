<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\console;

use common\modules\student\components\forumIn\forum\bizley\podium\src\log\Log;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Email;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Exception;
use Yii;
use yii\base\Action;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\Console;
use yii\mail\BaseMailer;







class QueueController extends Controller
{

    const DEFAULT_BATCH_LIMIT = 100;

    





    public $db;

    


    public $defaultAction = 'run';

    


    public $limit = self::DEFAULT_BATCH_LIMIT;

    




    public $mailer = 'mailer';

    


    public $queueTable = '{{%podium_email}}';

    


    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['queueTable', 'db', 'mailer']);
    }

    




    public function beforeAction($action)
    {
        try {
            if (parent::beforeAction($action)) {
                $this->db = !$this->db ? Podium::getInstance()->getDb() : Instance::ensure($this->db, Connection::class);
                $this->mailer = Instance::ensure($this->mailer, BaseMailer::class);
                return true;
            }
        } catch (Exception $e) {
            $this->stderr("ERROR: " . $e->getMessage() . "\n");
        }
        return false;
    }

    




    public function getNewBatch($limit = 0)
    {
        try {
            if (!is_numeric($limit) || $limit <= 0) {
                $limit = $this->limit;
            }
            return (new Query)
                    ->from($this->queueTable)
                    ->where(['status' => Email::STATUS_PENDING])
                    ->orderBy(['id' => SORT_ASC])
                    ->limit((int)$limit)
                    ->all($this->db);
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
    }

    






    public function send($email, $fromName, $fromEmail)
    {
        try {
            $mailer = Yii::$app->mailer->compose();
            $mailer->setFrom([$fromEmail => $fromName]);
            $mailer->setTo($email['email']);
            $mailer->setSubject($email['subject']);
            $mailer->setHtmlBody($email['content']);
            $mailer->setTextBody(strip_tags(str_replace(
                ['<br>', '<br/>', '<br />', '</p>'],
                "\n",
                $email['content']
            )));
            return $mailer->send();
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
    }

    







    public function process($email, $fromName, $fromEmail, $maxAttempts)
    {
        try {
            if ($this->send($email, $fromName, $fromEmail)) {
                $this
                    ->db
                    ->createCommand()
                    ->update(
                        $this->queueTable,
                        ['status' => Email::STATUS_SENT],
                        ['id' => $email['id']]
                    )
                    ->execute();
                return true;
            }

            $attempt = $email['attempt'] + 1;
            if ($attempt <= $maxAttempts) {
                $this
                    ->db
                    ->createCommand()
                    ->update(
                        $this->queueTable,
                        ['attempt' => $attempt],
                        ['id' => $email['id']]
                    )
                    ->execute();
            } else {
                $this
                    ->db
                    ->createCommand()
                    ->update(
                        $this->queueTable,
                        ['status' => Email::STATUS_GAVEUP],
                        ['id' => $email['id']]
                    )
                    ->execute();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    




    public function actionRun($limit = 0)
    {
        $version = $this->module->version;
        $this->stdout("\nPodium mail queue v{$version}\n");
        $this->stdout("------------------------------\n");

        $emails = $this->getNewBatch($limit);
        if (empty($emails)) {
            $this->stdout("No pending emails in the queue found.\n\n", Console::FG_GREEN);
            return ExitCode::OK;
        }

        $total = count($emails);
        $this->stdout(
            "\n$total pending "
                . ($total === 1 ? 'email' : 'emails')
                . " to be sent now:\n",
            Console::FG_YELLOW
        );

        $errors = false;
        foreach ($emails as $email) {
            if (!$this->process(
                    $email,
                    $this->module->podiumConfig->get('from_name'),
                    $this->module->podiumConfig->get('from_email'),
                    $this->module->podiumConfig->get('max_attempts')
                )) {
                $errors = true;
            }
        }

        if ($errors) {
            $this->stdout("\nBatch sent with errors.\n\n", Console::FG_RED);
        } else {
            $this->stdout("\nBatch sent successfully.\n\n", Console::FG_GREEN);
        }
        return ExitCode::OK;
    }

    


    public function actionCheck()
    {
        $version = $this->module->version;
        $this->stdout("\nPodium mail queue check v{$version}\n");
        $this->stdout("------------------------------\n");
        $this->stdout(" EMAILS  | COUNT\n");
        $this->stdout("------------------------------\n");

        $pending = (new Query)
                    ->from($this->queueTable)
                    ->where(['status' => Email::STATUS_PENDING])
                    ->count('id', $this->db);
        $sent = (new Query)
                    ->from($this->queueTable)
                    ->where(['status' => Email::STATUS_SENT])
                    ->count('id', $this->db);
        $gaveup = (new Query)
                    ->from($this->queueTable)
                    ->where(['status' => Email::STATUS_GAVEUP])
                    ->count('id', $this->db);

        $showPending = $this->ansiFormat($pending, Console::FG_YELLOW);
        $showSent = $this->ansiFormat($sent, Console::FG_GREEN);
        $showGaveup = $this->ansiFormat($gaveup, Console::FG_RED);

        $this->stdout(" pending | $showPending\n");
        $this->stdout(" sent    | $showSent\n");
        $this->stdout(" stucked | $showGaveup\n");
        $this->stdout("------------------------------\n\n");
        return ExitCode::OK;
    }
}
