<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Silex\Application();

function getBills()
{
    $json = file_get_contents(__DIR__ . '/bills.json');
    $data = json_decode($json, true);
    return $data['bills'];
}

function findIndexById($id)
{
    $bills = getBills();
    foreach ($bills as $key => $bill) {
        if ($bill['id'] == $id) {
            return $key;
        }
    }
    return false;
}

function writeBills($bills)
{
    $data = ['bills' => $bills];
    $json = json_encode($data);
    file_put_contents(__DIR__ . '/bills.json', $json);
}

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->get('/bills', function () use ($app) {
    $bills = getBills();
    return $app->json($bills);
});

$app->get('/bills/{id}', function ($id) use ($app) {
    $bills = getBills();
    $bill = $bills[findIndexById($id)];
    return $app->json($bill);
});

$app->post('/bills', function (Request $request) use ($app) {
    $bills = getBills();
    $data = $request->request->all();
    $data['id'] = count($bills) + 1;
    $bills[] = $data;
    writeBills($bills);
    return $app->json($data);
});

$app->put('/bills/{id}', function (Request $request, $id) use ($app) {
    $bills = getBills();
    $data = $request->request->all();
    $index = findIndexById($id);
    $bills[$index] = $data;
    $bills[$index]['id'] = (int)$id;
    writeBills($bills);
    return $app->json($bills[$index]);
});

$app->delete('/bills/{id}', function ($id) {
    $bills = getBills();
    $index = findIndexById($id);
    unset($bills[$index]);
    writeBills($bills);
    return new Response("", 204);
});


$app->run();