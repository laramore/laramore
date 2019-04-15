<?php

namespace Laramore\Traits;

trait IsPrepared
{
    protected $preparing = false;
    protected $prepared = false;

    public function prepare()
    {
        if ($this->isPrepared()) {
            throw new \Exception('This has already been prepared');
        }

        $this->preparing = true;

        $this->preparing();

        $this->prepared = true;
        $this->preparing = false;

        return $this;
    }

    public function isPreparing()
    {
        return $this->preparing;
    }

    public function isPrepared()
    {
        return $this->prepared;
    }
}
