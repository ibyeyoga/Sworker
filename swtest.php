<?php

class WorkerPool
{
    private $pool;

    private $defConfig = [
        'max' => 1
    ];

    private $workers = [];
    private $isRunning = false;

    public function __construct($config = [])
    {
        $this->defConfig = array_merge($this->defConfig, $config);
        $this->pool = new Swoole\Process\Pool($this->defConfig['max']);
    }

    public function addWorker($workerInfo = [])
    {
        $len = count($this->workers);
        if (!empty($workerInfo) && $len < $this->defConfig['max']) {
            $workerInfo['id'] = $len;
            $this->workers[] = $workerInfo;
        } else {
            echo 'You can only add ' . $this->defConfig['max'] . ' workers in pool.';
        }
    }

    public function run()
    {
        //申请内存
//        $table = new swoole_table(1024);
//        $table->column('id', swoole_table::TYPE_INT, 2);       //1,2,4,8
//        $table->column('name', swoole_table::TYPE_STRING, 2048);
        $a = 1;
        $workers = &$this->workers;
        if (!$this->isRunning) {
            $this->pool->on("WorkerStart", function ($pool, $workerId) {
                if (isset($this->workers[$workerId]['do'])) {
                    $callable = $this->workers[$workerId]['do'];
                    $callable($this->workers[$workerId]);
                }
            });
            $this->pool->on("WorkerStop", function ($pool, $workerId) use (&$workers, &$a) {
                if (isset($workers[$workerId]) && $workers[$workerId]['once']) {
                    var_dump($workers);
                    echo time() . ':' . $workers[$workerId]['name'] . ' is stopped.' . PHP_EOL;
//                    unset($workers[$workerId]);
                    $workers[$workerId] = [];
                    var_dump($workers[$workerId], $a++);
                }
            });
            $this->pool->start();
        }
    }
}

try {
    $workerPool = new WorkerPool([
        'max' => 2
    ]);

    $workerPool->addWorker([
        'name' => 'work001',
        'do' => function ($data) {
            echo "#{$data['name']} is started\n";
            $redis = new Redis();
            $redis->pconnect('127.0.0.1', 6379);
            $key = "key1";
            while (true) {
                $msgs = $redis->brpop($key, 2);
                if ($msgs == null) continue;
                //处理任务列表
                var_dump($msgs);
            }
        },
        'once' => true
    ]);

    $workerPool->addWorker([
        'name' => 'worker002',
        'do' => function ($data) {
            echo 'i\'m worker !!!' . PHP_EOL;
        },
        'once' => true
    ]);

    $workerPool->run();
} catch (Exception $e) {
    echo $e->getMessage();
}



