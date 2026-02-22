<?php
header('Content-Type: application/json');

$response = [
    "response" => [
        [
            "field" => "kdObatSK",
            "message" => "37"
        ],
        [
            "field" => "kdRacikan",
            "message" => "R.12"
        ]
    ],
    "metaData" => [
        "message" => "CREATED",
        "code" => 200
    ]
];


echo json_encode($response, JSON_PRETTY_PRINT);
