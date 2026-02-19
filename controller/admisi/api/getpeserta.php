<?php
header('Content-Type: application/json');

$response = [
    "metaData" => [
        "code" => 200,
        "message" => "CREATED"
    ],
    "response" => null
];


echo json_encode($response, JSON_PRETTY_PRINT);
