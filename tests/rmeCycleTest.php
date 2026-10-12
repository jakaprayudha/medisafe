<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(405);
    exit('CLI only');
}
// Temporary tables shadow production tables on this connection only.
session_start();
$_SESSION['uid_user'] = 'rme-cycle-test';
require_once __DIR__ . '/../database/connect.php';
require_once __DIR__ . '/../controller/doctor/rmeCycleService.php';

function expectCycle(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$tables = [
    'ms_patient' => 'id_patient INT PRIMARY KEY, nomor_rm VARCHAR(20)',
    'pasien_visit' => 'visit_ID VARCHAR(50) PRIMARY KEY, id_customer INT, id_patient INT, visit_status INT, id_provider INT, noKunjung VARCHAR(50)',
    'permintaan_pharmacy' => 'id_permintaan_farmasi INT PRIMARY KEY, id_visit VARCHAR(50), id_customer INT, status_permintaan INT, rck_signa VARCHAR(100)',
    'permintaan_pharmacy_details' => 'id_pharmacy_details INT PRIMARY KEY, id_permintaan_farmasi INT, id_pharmacy INT, signa VARCHAR(100), qty DECIMAL(10,2)',
    'ms_pharmacy' => 'id_pharmacy INT PRIMARY KEY, pharmacy_name_generic VARCHAR(100)',
    'pcare_pendaftaran' => 'nomor_visit VARCHAR(50)',
];
$created = [];
$cases = 0;
try {
    foreach ($tables as $name => $columns) {
        $koneksi->query("CREATE TEMPORARY TABLE $name ($columns) ENGINE=InnoDB");
        $created[] = $name;
    }
    $koneksi->query("INSERT INTO ms_patient VALUES (101, 'TEST-RM')");
    $koneksi->query("INSERT INTO pasien_visit VALUES ('TEST-VISIT', 1, 101, 0, 1, NULL)");
    $koneksi->query("INSERT INTO pcare_pendaftaran VALUES ('TEST-VISIT')");
    $koneksi->query("INSERT INTO ms_pharmacy VALUES (201, 'Test medicine')");
    $post = ['nomor_visit' => 'TEST-VISIT', 'nomor_rm' => 'TEST-RM', 'id_patient' => 'WRONG', 'noKunjungan' => 'WRONG'];
    $status = static function () use ($koneksi): int {
        return (int) $koneksi->query("SELECT status_permintaan FROM permintaan_pharmacy WHERE id_permintaan_farmasi = 301")->fetch_row()[0];
    };
    $success = static function (array $data) use ($koneksi): array {
        expectCycle($data['id_patient'] === 101, 'Patient must come from scoped visit');
        expectCycle($data['noKunjungan'] !== 'WRONG', 'Visit number must not trust browser');
        $koneksi->query("UPDATE pasien_visit SET noKunjung = 'TEST-BPJS' WHERE visit_ID = 'TEST-VISIT'");
        return ['success' => true, 'noKunjung' => 'TEST-BPJS', 'message' => 'Visit saved'];
    };
    $result = saveRmeCycle($koneksi, $post, 1, $success);
    expectCycle($result['prescriptions_sent'] === 0, 'No prescription must still save visit');
    $cases++;

    $koneksi->query("UPDATE pasien_visit SET noKunjung = NULL");
    $koneksi->query("INSERT INTO permintaan_pharmacy VALUES (301, 'TEST-VISIT', 1, 0, NULL)");
    $koneksi->query("INSERT INTO permintaan_pharmacy_details VALUES (401, 301, 201, '3 x 1', 10)");
    $result = saveRmeCycle($koneksi, $post, 1, static function (array $data) use ($success, $status): array {
        expectCycle($status() === 0, 'Prescription must remain draft before visit save');
        expectCycle($data['noKunjungan'] === '', 'New BPJS visit must be created without a visit number');
        expectCycle($data['terapiObat'] === 'Test medicine/3 x 1/10.00', 'Prescription must be in visit payload');
        return $success($data);
    });
    expectCycle($status() === 1 && $result['prescriptions_sent'] === 1, 'Send only after visit success');
    $cases++;

    $result = saveRmeCycle($koneksi, $post, 1, static function (array $data) use ($success): array {
        expectCycle($data['noKunjungan'] === 'TEST-BPJS', 'Retry must use persisted visit number');
        expectCycle(str_contains($data['terapiObat'], 'Test medicine'), 'Update must retain earlier medicines');
        return $success($data);
    });
    expectCycle($result['prescriptions_sent'] === 0, 'Already sent prescription must not be resent');
    $cases++;

    $koneksi->query("UPDATE permintaan_pharmacy SET status_permintaan = 0");
    $koneksi->query("UPDATE pasien_visit SET noKunjung = NULL");
    $result = saveRmeCycle($koneksi, $post, 1, static function () use ($koneksi): array {
        $koneksi->query("UPDATE pasien_visit SET noKunjung = 'ROLLBACK'");
        return ['success' => false, 'message' => 'BPJS unavailable'];
    });
    expectCycle(!$result['success'] && $status() === 0, 'Failed visit must leave draft');
    expectCycle($koneksi->query("SELECT noKunjung FROM pasien_visit")->fetch_row()[0] === null, 'Failed visit DB changes must roll back');
    $cases++;

    foreach (['empty', 'quantity', 'signa', 'name'] as $invalid) {
        if ($invalid === 'empty') $koneksi->query("DELETE FROM permintaan_pharmacy_details");
        if ($invalid === 'quantity') $koneksi->query("UPDATE permintaan_pharmacy_details SET qty = 0");
        if ($invalid === 'signa') $koneksi->query("UPDATE permintaan_pharmacy_details SET signa = ''");
        if ($invalid === 'name') $koneksi->query("UPDATE ms_pharmacy SET pharmacy_name_generic = ''");
        try {
            saveRmeCycle($koneksi, $post, 1, static function (): array {
                throw new RuntimeException('Invalid prescription reached remote save');
            });
            throw new RuntimeException("Missing validation: $invalid");
        } catch (InvalidArgumentException $error) {
            expectCycle($status() === 0, "Invalid $invalid must leave draft");
        }
        $koneksi->query("DELETE FROM permintaan_pharmacy_details");
        $koneksi->query("INSERT INTO permintaan_pharmacy_details VALUES (401, 301, 201, '3 x 1', 10)");
        $koneksi->query("UPDATE ms_pharmacy SET pharmacy_name_generic = 'Test medicine'");
        $cases++;
    }
    foreach ([
        [$post, 2],
        [array_replace($post, ['nomor_rm' => 'OTHER']), 1],
        [array_replace($post, ['nomor_visit' => 'OTHER']), 1],
    ] as [$invalidPost, $clinic]) {
        try {
            saveRmeCycle($koneksi, $invalidPost, $clinic, static function (): array {
                throw new RuntimeException('Unowned visit reached remote save');
            });
            throw new RuntimeException('Ownership was not checked');
        } catch (InvalidArgumentException $error) {
            expectCycle($status() === 0, 'Ownership failure must leave draft');
        }
        $cases++;
    }
    $koneksi->query("DELETE FROM pcare_pendaftaran");
    try {
        saveRmeCycle($koneksi, $post, 1, $success);
        throw new RuntimeException('Missing BPJS registration was accepted');
    } catch (InvalidArgumentException $error) {
        expectCycle($status() === 0, 'Missing registration must leave draft');
    }
    $cases++;
    $koneksi->query("INSERT INTO pcare_pendaftaran VALUES ('TEST-VISIT')");
    try {
        saveRmeCycle($koneksi, $post, 1, static fn() => ['success' => true, 'message' => 'No BPJS number']);
        throw new InvalidArgumentException('Missing BPJS number was accepted');
    } catch (RuntimeException $error) {
        expectCycle($status() === 0, 'Missing response visit number must leave draft');
    }
    $cases++;
    $koneksi->query("UPDATE permintaan_pharmacy SET rck_signa = '2 x 1 sachet'");
    $koneksi->query("UPDATE permintaan_pharmacy_details SET signa = '-'");
    saveRmeCycle($koneksi, $post, 1, static function (array $data) use ($success): array {
        expectCycle(str_contains($data['terapiObat'], '2 x 1 sachet'), 'Racikan must use ticket signa');
        return $success($data);
    });
    expectCycle($status() === 1, 'Racikan must be sent');
    $cases++;
    $koneksi->query("UPDATE permintaan_pharmacy SET status_permintaan = 2");
    $koneksi->query("INSERT INTO permintaan_pharmacy VALUES (302, 'TEST-VISIT', 1, 0, NULL)");
    $koneksi->query("INSERT INTO permintaan_pharmacy_details VALUES (402, 302, 201, '1 x 1', 5)");
    $result = saveRmeCycle($koneksi, $post, 1, $success);
    expectCycle($status() === 2 && $result['prescriptions_sent'] === 1, 'Preparing tickets must not be downgraded');
    $cases++;
    $koneksi->query("UPDATE pasien_visit SET id_provider = 2, noKunjung = NULL");
    $koneksi->query("UPDATE permintaan_pharmacy SET status_permintaan = 0");
    $result = saveRmeCycle($koneksi, $post, 1, static function (array $data): array {
        expectCycle($data['status_pasien'] === 'UMUM', 'General patient must not use BPJS');
        return ['success' => true, 'message' => 'Local visit saved'];
    });
    expectCycle($result['prescriptions_sent'] === 2, 'General patient prescriptions must be sent locally');
    $cases++;
    echo "PASS: $cases RME cycle regression cases; temporary fixtures only; no BPJS requests.\n";
} finally {
    foreach (array_reverse($created) as $table) {
        $koneksi->query("DROP TEMPORARY TABLE $table");
    }
    $koneksi->close();
}
