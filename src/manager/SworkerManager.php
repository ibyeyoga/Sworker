<?php
/**
 * Created by PhpStorm.
 * User: liyongjia
 * Date: 2018/6/19
 * Time: 19:52
 */

namespace IBye\manager;
use IBye\Sworker;

class SworkerManager implements WorkerManager
{
    public static function createWorker($call)
    {
        return new Sworker($call);
    }

    public static function runWorkers(){

    }
}