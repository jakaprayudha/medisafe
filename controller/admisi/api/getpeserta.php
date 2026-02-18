<?php
header('Content-Type: application/json');

$response = [
    "metaData" => [
        "code" => 200,
        "message" => "CREATED"
    ],
    "response" => [
        "field" => "eduId",
        "message" => "16030000009"
    ]
];


echo json_encode($response, JSON_PRETTY_PRINT);
