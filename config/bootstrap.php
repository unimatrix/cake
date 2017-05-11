<?php

use Cake\Event\EventManager;
use Unimatrix\Cake\Error\Middleware\EmailErrorHandlerMiddleware;

// attach EmailErrorHandlerMiddleware
EventManager::instance()->on('Server.buildMiddleware', function ($event, $queue) {
    $queue->insertAfter('Cake\Error\Middleware\ErrorHandlerMiddleware', EmailErrorHandlerMiddleware::class);
});
