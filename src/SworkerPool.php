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

    public function __construct($config = [])
    {
        $this->defConfig = array_merge($this->defConfig, $config);
        $_max = (int)$this->defConfig['max'];
        $this->pool = new Pool($_max);

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
                WorkerSpace::FIELD_WID => $workerInfo['id'],
                WorkerSpace::FIELD_TYPE => $workerInfo['type'],
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
                    if ($workerInfo[WorkerSpace::FIELD_STATUS] == Worker::STATUS_RUNNING && isset($this->workers[$wId][Worker::FIELD_CALL])) {
                        $callable = $this->workers[$wId][Worker::FIELD_CALL];
                        $callable($this->workers[$wId]);
                    }
                }
            });

            //onWorkerStop
            $this->pool->on("WorkerStop", function ($pool, $wId) {
                if ($this->getWorkerStatus($wId) == Worker::STATUS_RUNNING) {
                    //check if it can only run 1 time
                    if (isset($this->workers[$wId][WorkerSpace::FIELD_TYPE]) && $this->workers[$wId][WorkerSpace::FIELD_TYPE] == Worker::TYPE_ONCE) {
                        //kill it
                        $this->removeWorker($wId);
                    }
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

/**
 * Example
 * Start
 *
 * use IBye\SworkerPool;
 * use IBye\worker\BaseWorker;
 *
 * try {
 * $workerPool = new SworkerPool([
 * 'max' => 2
 * ]);
 *
 * $workerPool->addWorker([
 * 'name' => 'work001',
 * 'do' => function ($data) {
 * echo "#{$data['name']} is started\n";
 * $redis = new Redis();
 * $redis->pconnect('127.0.0.1', 6379);
 * $key = "key1";
 * while (true) {
 * $msgs = $redis->brpop($key, 2);
 * if ($msgs == null) continue;
 * //处理任务列表
 * var_dump($msgs);
 * }
 * },
 * 'type' => BaseWorker::TYPE_UNLIMIT
 * ]);
 *
 * $workerPool->addWorker([
 * 'name' => 'worker002',
 * 'do' => function ($data) {
 * echo 'i\'m worker !!!' . PHP_EOL;
 * },
 * 'type' => BaseWorker::TYPE_ONCE
 * ]);
 *
 * $workerPool->run();
 * } catch (Exception $e) {
 * echo $e->getMessage();
 * }
 *
 * End
 */