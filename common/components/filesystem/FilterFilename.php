<?php

namespace common\components\filesystem;




class FilterFilename
{
    








    public static function sanitize(string $filename, bool $with_extension = true): string
    {
        
        $filename = preg_replace('~[^\w .]~xu', '', $filename);

        $filename = trim((string)$filename, ' .-');
        
        
        $ext = null;
        $fn = $filename;
        if ($with_extension) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $fn = pathinfo($filename, PATHINFO_FILENAME);
            $fn = trim((string)$fn, ' .-');
        }
        return trim(mb_strcut($fn, 0, 248 - ($ext ? mb_strlen((string)$ext) + 1 : 0), mb_detect_encoding($fn))) . ($ext ? '.' . $ext : '');
    }
}
