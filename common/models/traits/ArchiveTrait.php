<?php


namespace common\models\traits;


use common\components\queries\ArchiveQuery;
use common\components\queries\DictionaryQuery;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use common\models\errors\ArchiveError;
use common\models\ModelFrom1CByOData;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use Throwable;
use Yii;
use yii\db\Exception;

trait ArchiveTrait
{
    






    public function archive(bool $update_history = true): bool
    {
        if (!$this->isArchive()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                if (method_exists($this, 'beforeArchive')) {
                    $this->beforeArchive();
                }
                $this->{static::getArchiveColumn()} = static::getArchiveValue();
                $this->{static::getArchivedAtColumn()} = time();
                $this->save(false);
                if ($update_history) {
                    if ($this instanceof ChangeLoggedModelInterface) {
                        $handler = $this->getChangeHistoryHandler();
                        if ($handler) {
                            $handler
                                ->getDeleteHistoryAction()
                                ->proceed();
                        }
                    }
                }

                $transaction->commit();
            } catch (Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        return true;
    }

    


    public static function getArchiveColumn(): string
    {
        return 'archive';
    }

    public static function getArchivedAtColumn(): string
    {
        return 'archived_at';
    }

    public static function getArchiveValue()
    {
        return true;
    }

    public static function find()
    {
        $instance = new static();
        if (($instance instanceof ModelFrom1CByOData) || ($instance instanceof StoredReferenceType)) {
            return new DictionaryQuery(static::class);
        }
        return new ArchiveQuery(static::class);
    }

    


    public function isArchive(): bool
    {
        return $this->{static::getArchiveColumn()} == static::getArchiveValue();
    }
}
