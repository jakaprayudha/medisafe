<?php
header('Content-Type: application/json');

$response = [
    "response" => [
        "field" => "noUrut",
        "message" => "A1"
    ],
    "metaData" => [
        "message" => "CREATED",
        "code" => 200
    ]
];


echo json_encode($response, JSON_PRETTY_PRINT);