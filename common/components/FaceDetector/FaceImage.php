<?php


namespace common\components\FaceDetector;

use Exception;
use common\components\FaceDetector\Exception\NoFaceException;
use Yii;

class FaceImage extends FaceDetector
{
    private const WIDTH = 160; 
    private const HEIGHT = 200; 
    public const DEFAULT_PERCENT = 70; 
    public const MAX_PERCENT = 100;
    public const MIN_PERCENT = 50;
    private const SOURCE_FILE_NAME = 'source.jpg';
    private const DESTINATION_FILE_NAME = 'face.jpg';

    
    private $dirPath;

    




    public function __construct(int $userId)
    {
        parent::__construct();
        $this->dirPath = Yii::getAlias('@frontend/web/photo/' . $userId);
        $this->makeDir(dirname($this->dirPath));
    }

    private function makeDir(string $dir)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    




    public function setFile(string $filePath)
    {
        if (!$this->faceDetect($filePath)) {
            throw new NoFaceException();
        }
    }

    public function getDestinationPath()
    {
        return sprintf('%s/%s', $this->dirPath, self::DESTINATION_FILE_NAME);
    }

    public function getSourcePath()
    {
        return sprintf('%s/%s', $this->dirPath, self::SOURCE_FILE_NAME);
    }

    





    public function cropFace(int $percent = self::DEFAULT_PERCENT)
    {
        $percent = $percent > self::MAX_PERCENT ? self::MAX_PERCENT
            : ($percent < self::MIN_PERCENT ? self::MIN_PERCENT : $percent);
        $x = intval($this->face['x']);
        $y = intval($this->face['y']);
        
        $width = intval(sqrt(pow($this->face['w'], 2) * 100 / $percent));
        $height = intval($width * self::HEIGHT / self::WIDTH);

        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagecopyresized(
            $canvas,
            $this->canvas,
            0,
            0,
            $x - ($width - $this->face['w']) / 2,
            $y - ($height - $this->face['w']) / 2,
            self::WIDTH,
            self::HEIGHT,
            $width,
            $height
        );
        $dest = $this->getDestinationPath();
        
        if (!file_exists($dest)) {
            
            $this->makeDir(dirname($dest));
            touch($dest);
        }
        imagejpeg($canvas, $dest);
    }


}