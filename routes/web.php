<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('tasks', 'TaskController@index');
    $router->get('tasks/{id}', 'TaskController@show');
    $router->post('tasks', 'TaskController@store');
    $router->put('tasks/{id}', 'TaskController@update');
    $router->delete('tasks/{id}', 'TaskController@destroy');
});