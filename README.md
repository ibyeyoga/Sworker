# Sworker

### How to use?
```php
$workerPool = new SworkerPool([
    'max' => 1
]);
$task = new Task(function (){
    echo 'hello world' . PHP_EOL;
});

$worker = SworkerManager::createWorker($task);
$workerPool->addWorker($worker);
$workerPool->run();

```

### also like this
```php
$task = new Task(function (){
    echo 'hello' . PHP_EOL;
});

$process = SworkerManager::createWorker($task);

$process->run();
```