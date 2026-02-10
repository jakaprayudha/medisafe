<?php
header('Content-Type: application/json');

// Get Peseta
$response = [
    "response" => [
        "noKartu" => "0001261832477",
        "nama" => "TRI ARNI",
        "hubunganKeluarga" => "Istri",
        "sex" => "P",
        "tglLahir" => "29-09-1965",
        "tglMulaiAktif" => "09-01-2014",
        "tglAkhirBerlaku" => "31-12-2050",

        "kdProviderPst" => [
            "kdProvider" => "09020107",
            "nmProvider" => "KEL. MANGGARAI SELATAN"
        ],

        "kdProviderGigi" => [
            "kdProvider" => null,
            "nmProvider" => null
        ],

        "jnsKelas" => [
            "kode" => "3",
            "nama" => "KELAS III"
        ],

        "jnsPeserta" => [
            "kode" => "22",
            "nama" => "PBI (APBD)"
        ],

        "golDarah" => "0",
        "noHP" => "083876592594",
        "noKTP" => "3174016909650001",
        "aktif" => true,
        "ketAktif" => "AKTIF",

        "asuransi" => [
            "kdAsuransi" => null,
            "nmAsuransi" => null,
            "noAsuransi" => null
        ],

        "tunggakan" => 0
    ],

    "metaData" => [
        "message" => "OK",
        "code" => 200
    ]
];

// GetPoli
// $response = [
//     "metaData" => [
//         "code" => 200,
//         "message" => "OK"
//     ],
//     "response" => [
//         "count" => 2,
//         "list" => [
//             [
//                 "kdPoli" => "001",
//                 "nmPoli" => "Umum",
//                 "poliSakit" => true
//             ],
//             [
//                 "kdPoli" => "003",
//                 "nmPoli" => "K I A",
//                 "poliSakit" => true
//             ]
//         ]
//     ]
// ];


echo json_encode($response, JSON_PRETTY_PRINT);
