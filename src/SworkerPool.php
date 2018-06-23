<?php

namespace IBye;

use IBye\memory\TableWorkerSpace;
use IBye\memory\WorkerSpace;
use IBye\worker\Worker;
use Swoole\Process\Pool;

class SworkerPool
{
    private $pool = null;

    private $defConfig = [
        'max' => 1
    ];

    private $workers = [];
    private $workerSpace = null;

    private $isRunning = false;

    public function __construct($config = 1)
    {
        if(is_int($config)){
            $_max = $config;
        }
        else if(is_array($config)){
            $this->defConfig = array_merge($this->defConfig, $config);
            $_max = (int)$this->defConfig['max'];
        }

        $this->pool = new Pool($_max);

        if ($this->pool) {
            $this->workerSpace = new TableWorkerSpace($_max);
        }

        unset($_max);
    }

    public function addWorker(Worker $worker)
    {
        $len = count($this->workers);
        if (!empty($worker) && $len < $this->defConfig['max']) {
            $worker->wId = $len;
            $this->workers[] = $worker;
            $this->workerSpace->setWorkerInfo($worker->wId, [
                WorkerSpace::FIELD_WID => $worker->wId,
                WorkerSpace::FIELD_STATUS => Worker::STATUS_RUNNING
            ]);
        } else {
            echo 'You can only add ' . $this->defConfig['max'] . ' workers in pool.';
        }
    }

    public function run()
    {
        if (!$this->isRunning) {
            //onWorkerStart
            $this->pool->on("WorkerStart", function ($pool, $wId) {
                $workerInfo = $this->workerSpace->getWorkerInfo($wId);
                if ($workerInfo !== false) {
                    if ($workerInfo[WorkerSpace::FIELD_STATUS] == Worker::STATUS_RUNNING) {
                        if($this->workers[$wId] instanceof Worker){
                            $this->workers[$wId]->run($this->workers[$wId]);
                        }
                    }
                }
            });

            //onWorkerStop
            $this->pool->on("WorkerStop", function ($pool, $wId) {
                if ($this->getWorkerStatus($wId) == Worker::STATUS_RUNNING) {
//                    if (isset($this->workers[$wId]->type)) {
//                        $this->removeWorker($wId);
//                    }
                }
            });

            $this->isRunning = true;
            $this->pool->start();
        }
    }

    public function getWorkerStatus($wId)
    {
        return $this->workerSpace->getWorkerInfoField($wId, WorkerSpace::FIELD_STATUS);
    }

    public function removeWorker($wId)
    {
        $this->workerSpace->setWorkerInfoField($wId, WorkerSpace::FIELD_STATUS, Worker::STATUS_STOP);
    }
}