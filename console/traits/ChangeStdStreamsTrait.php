<?php

namespace console\traits;

trait ChangeStdStreamsTrait
{
    protected $_std_err;
    protected $_std_out;

    public function setStdOut($stream)
    {
        $this->_std_out = $stream;
    }

    public function setStdErr($stream)
    {
        $this->_std_err = $stream;
    }

    public function stderr($string)
    {
        if ($this->_std_err) {
            return fwrite($this->_std_err, $string);
        }
        return parent::stderr($string);
    }

    public function stdout($string)
    {
        if ($this->_std_out) {
            return fwrite($this->_std_out, $string);
        }
        return parent::stdout($string);
    }
}