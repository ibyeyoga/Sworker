<?php
/**
 * Created by PhpStorm.
 * User: liyongjia
 * Date: 2018/6/20
 * Time: 14:45
 */

namespace IBye\task;


class TableTask extends Task
{
    public static function createTask(){
        return new Task(function(){

        });
    }

}