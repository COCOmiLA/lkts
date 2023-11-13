<?php

namespace common\models\managers;





class BatchMaker
{
    private $batch = [];
    private $batch_size = 1000;
    private $callback;

    public function __construct(int $batch_size, \Closure $callback)
    {
        $this->batch_size = $batch_size;
        $this->callback = $callback;
    }

    public function add($item)
    {
        $this->batch[] = $item;
        $this->checkFlush();
    }

    public function extend(array $items): void
    {
        $this->batch = array_merge($this->batch, $items);
        $this->checkFlush();
    }

    public function flush()
    {
        if (count($this->batch) > 0) {
            $callback = $this->callback;
            foreach (array_chunk($this->batch, $this->batch_size) as $chunk) {
                $callback($chunk);
            }
            $this->batch = [];
        }
    }

    private function checkFlush(): void
    {
        if (count($this->batch) >= $this->batch_size) {
            $this->flush();
        }
    }
}