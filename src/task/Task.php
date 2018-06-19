<?php
/**
 * Created by PhpStorm.
 * User: liyongjia
 * Date: 2018/6/19
 * Time: 20:19
 */

namespace IBye\task;


class Task
{
    const STATUS_EXECUTING = 1;
    const STATUS_FINISHED = 2;
    const STATUS_WAITING = 3;

    private $call;

    public function __construct(callable $call)
    {
        $this->call = $call;
    }

    //执行任务
    public function exec(...$args){
        $call = $this->call;
        $call(...$args);
    }

}