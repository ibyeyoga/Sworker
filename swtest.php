<?php

try {
    $workerPool = new \IBye\SworkerPool([
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
        'type' => \IBye\worker\BaseWorker::TYPE_UNLIMIT
    ]);

    $workerPool->addWorker([
        'name' => 'worker002',
        'do' => function ($data) {
            echo 'i\'m worker !!!' . PHP_EOL;
        },
        'type' => \IBye\worker\BaseWorker::TYPE_ONCE
    ]);

    $workerPool->run();
} catch (Exception $e) {
    echo $e->getMessage();
}



