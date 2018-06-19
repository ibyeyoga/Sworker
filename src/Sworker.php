<?php

namespace IBye;
use IBye\worker\Worker;
class Sworker extends Worker
{
    public function run(){
        $this->getInstance()->start();
    }

}