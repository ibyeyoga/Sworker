<?php
/**
 * Created by PhpStorm.
 * User: liyongjia
 * Date: 2018/6/19
 * Time: 19:52
 */

namespace IBye\manager;


interface WorkerManager
{
    public static function createWorker($call);
}