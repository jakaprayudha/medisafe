<?php
header('Content-Type: application/json');

$response = [
    "metaData" => [
        "code" => 200,
        "message" => "CREATED"
    ],
    "response" => [
        "field"=> "noKunjungan",
	    "message"=> "0114U1630316Y000001"
    ]
];


echo json_encode($response, JSON_PRETTY_PRINT);
