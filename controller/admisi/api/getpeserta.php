<?php
header('Content-Type: application/json');

$response = [
    "metaData" => [
        "code" => 201,
        "message" => "CREATED"
    ],
    "response" => [
        "field" => "noUrut",
        "message" => "A1"
    ]
];


echo json_encode($response, JSON_PRETTY_PRINT);
