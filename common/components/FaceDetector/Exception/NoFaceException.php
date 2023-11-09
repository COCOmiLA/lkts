<?php





namespace common\components\FaceDetector\Exception;

use Exception;

class NoFaceException extends Exception
{
    protected $message = 'На этом изображении нет лица';

}
