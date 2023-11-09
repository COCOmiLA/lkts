<?php


namespace common\modules\abiturient\models\bachelor\changeHistory\interfaces;







interface ChangeLoggedModelInterface
    extends ModelWithChangeHistoryHandlerInterface, ChangeHistoryClassTypeInterface
{
    





    public function getChangeLoggedAttributes();

    




    public function getOldAttributes();

    





    public function getOldAttribute($name);

    



    public function getOldClass(): ChangeLoggedModelInterface;

    


    public function attributeLabels();

    




    public function getEntityIdentifier(): ?string;

    public function getEntityChangeType(): int;

}