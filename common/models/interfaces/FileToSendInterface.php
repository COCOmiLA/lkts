<?php

namespace common\models\interfaces;
use common\modules\abiturient\models\File;







interface FileToSendInterface
{
    



    public function getAbsPath();
    public function getFilename();
    public function getExtension();
    public function getLinkedFile();
    public function LinkFile(File $file);
}