<?php

if (PHP_SAPI !== 'cli') {
    echo "This application only for command line usage!";
    exit(1);
}

define('ROOT_DIR', __DIR__);

spl_autoload_register(function($className) {
        $path = ROOT_DIR . '/App';
        $fileName = $path . '/' . str_replace("\\", '/', $className) . '.php';
        
        if (!file_exists($fileName)) {
            throw new \Exception("File {$fileName} not exists");
        }
        require_once $fileName;
    });

require_once './ConsoleApplication.php';

$app = new ConsoleApplication();
$app->addCommand(new \Command\Demo());
$app->addCommand(new \Command\FilesList());
$app->run();