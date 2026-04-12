<?php
header('Content-Type: application/json');

$response = [
    "response" => [
        "field"=> "noKunjungan",
        "message"=> "0114U1630316Y000001"
    ],
    "metaData" => [
        "message" => "CREATED",
        "code" => 200
    ]
];


echo json_encode($response, JSON_PRETTY_PRINT);
