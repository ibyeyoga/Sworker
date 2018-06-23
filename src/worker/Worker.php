<?php

namespace IBye\worker;
use IBye\task\Task;
use swoole_process as process;

class Worker
{
    private $_instance;

    const TYPE_UNLIMIT = 1;

    const STATUS_RUNNING = 1;
    const STATUS_STOP = 2;

    const FIELD_CALL = 'call';

    public $wId;
    public $name;

    public function __construct($call)
    {
        $_call = null;
        if(is_callable($call)){
            $_call = function(process $process) use($call){
                $call($process);
            };

        } else if($call instanceof Task){
            $_call = function(process $process) use($call){
                $call->exec($process);
            };
        } else{
            echo 'Sworker create error !';
            return ;
        }
        $this->setInstance(new process($_call));
    }

    protected function getInstance(){
        return $this->_instance;
    }

    protected function setInstance($instance){
        $this->_instance = $instance;
    }

    public function __call($name, $arguments)
    {
        if(method_exists($this, $name)){
            return parent::__call($name, $arguments);
        }
        return $this->getInstance()->$name(...$arguments);
    }

    /**
     * @return mixed pid
     */
    public function run(){
        return $this->getInstance()->start();
    }
}