<?php

namespace IBye;

use IBye\memory\TableWorkerSpace;
use IBye\memory\WorkerSpaceInterface;
use IBye\worker\BaseWorker;

class SworkerPool
{
    private $pool = null;

    private $defConfig = [
        'max' => 1
    ];

    private $workers = [];
    private $workerSpace = null;

    private $isRunning = false;

    public function __construct($config = [])
    {
        $this->defConfig = array_merge($this->defConfig, $config);
        $_max = (int) $this->defConfig['max'];
//        $_nameLength = 512;
        $this->pool = new Swoole\Process\Pool($_max);

        if ($this->pool) {
            $this->workerSpace = new TableWorkerSpace($_max);
        }

        //释放内存
        unset($_max);
    }

    public function addWorker($workerInfo = [])
    {
        $len = count($this->workers);
        if (!empty($workerInfo) && $len < $this->defConfig['max']) {
            $workerInfo['id'] = $len;
            $this->workers[] = $workerInfo;
            $this->workerSpace->setWorkerInfo($workerInfo['id'], [
                WorkerSpaceInterface::FIELD_WID => $workerInfo['id'],
                WorkerSpaceInterface::FIELD_TYPE => $workerInfo['type'],
                WorkerSpaceInterface::FIELD_STATUS => BaseWorker::STATUS_RUNNING
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
                $workerInfo = $this->workerSpace->get($wId);
                if ($workerInfo !== false) {
                    if ($workerInfo[WorkerSpaceInterface::FIELD_STATUS] == BaseWorker::STATUS_RUNNING && isset($this->workers[$wId]['do'])) {
                        $callable = $this->workers[$wId]['do'];
                        $callable($this->workers[$wId]);
                    }
                }
            });

            //onWorkerStop
            $this->pool->on("WorkerStop", function ($pool, $wId) {
                if ($this->getWorkerStatus($wId) == BaseWorker::STATUS_RUNNING) {
                    if (isset($this->workers[$wId][WorkerSpaceInterface::FIELD_TYPE]) && $this->workers[$wId][WorkerSpaceInterface::FIELD_TYPE] == BaseWorker::STATUS_RUNNING) {
                        //停止进程
                        $this->removeWorker($wId);
                        echo '#' . $this->workers[$wId]['name'] . 'stopped.' . PHP_EOL;
                    }
                }
            });
            $this->isRunning = true;
            $this->pool->start();
        }
    }

    public function getWorkerStatus($wId)
    {
        return $this->workerSpace->getWorkerInfoField($wId, WorkerSpaceInterface::FIELD_STATUS);
    }

    public function removeWorker($wId)
    {
        $this->workerSpace->setWorkerInfoField($wId, WorkerSpaceInterface::FIELD_STATUS, BaseWorker::STATUS_STOP);
    }
}