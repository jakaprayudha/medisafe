<?php
header('Content-Type: application/json');

$response = [
    "metaData" => [
        "message" => "Ok",
        "code" => 200
    ]
];


echo json_encode($response, JSON_PRETTY_PRINT);
