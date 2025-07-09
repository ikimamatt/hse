<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Pastikan path ini benar menuju file koneksi Anda
require_once '../layouts/conf.php'; 
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function getCellValue($sheet, $cell) {
    return $sheet->getCell($cell)->getFormattedValue();
}

if (isset($_FILES['files']) && !empty($_FILES['files']['name'])) {

    $normalized_files = []; 

    if (is_array($_FILES['files']['name'])) {
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                $normalized_files[] = [
                    'name' => $_FILES['files']['name'][$i],
                    'tmp_name' => $_FILES['files']['tmp_name'][$i],
                ];
            }
        }
    } else {
        if ($_FILES['files']['error'] === UPLOAD_ERR_OK) {
            $normalized_files[] = [
                'name' => $_FILES['files']['name'],
                'tmp_name' => $_FILES['files']['tmp_name'],
            ];
        }
    }

    if (empty($normalized_files)) {
        die("<p style='color:red;'>Tidak ada file valid yang diterima atau terjadi error upload.</p>");
    }

    foreach ($normalized_files as $file) {
        $fileName = $file['name'];
        $inputFileName = $file['tmp_name'];

        echo "<h4>Memproses file: " . htmlspecialchars($fileName) . "</h4>";

        $pdo->beginTransaction();
        try {
            $spreadsheet = IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            $company_name = getCellValue($sheet, 'A1');
            $report_date_raw = $sheet->getCell('A2')->getValue();

            if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($sheet->getCell('A2'))) {
                $report_date_obj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($report_date_raw);
            } else {
                $report_date_obj = DateTime::createFromFormat('l, F j, Y', (string)$report_date_raw);
                if ($report_date_obj === false) {
                    throw new Exception("Format tanggal tidak valid di sel A2: " . htmlspecialchars($report_date_raw));
                }
            }
            $report_date = $report_date_obj->format('Y-m-d');

            $total_bsoc_cards = (int)getCellValue($sheet, 'D6');
            $unsafe_bsoc = (int)getCellValue($sheet, 'H6');
            $safe_cards = $total_bsoc_cards - $unsafe_bsoc;
            $best_bsoc_title = getCellValue($sheet, 'A7');
            $best_bsoc_description = getCellValue($sheet, 'A8');

            $doctorInfo = null;
            for ($row = 1; $row <= $highestRow; $row++) {
                $cellValue = (string)getCellValue($sheet, 'A' . $row);
                if (strpos($cellValue, 'HSE & Doctor') !== false) {
                    $doctorInfo = $cellValue;
                    break; 
                }
            }

            $sql = "INSERT INTO daily_reports (company_name, report_date, total_bsoc_cards, safe_cards, unsafe_bsoc, best_bsoc_title, best_bsoc_description, doctor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$company_name, $report_date, $total_bsoc_cards, $safe_cards, $unsafe_bsoc, $best_bsoc_title, $best_bsoc_description, $doctorInfo]);

            $reportId = $pdo->lastInsertId();

            // Tambahkan sisa logika Anda di sini jika ada

            $pdo->commit();
            echo "<p style='color:green;'>✅ File " . htmlspecialchars($fileName) . " berhasil diimpor!</p><hr>";

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<p style='color:red;'>❌ Gagal mengimpor file " . htmlspecialchars($fileName) . ".</p>";
            echo "<p>Detail Kesalahan: " . $e->getMessage() . " di baris " . $e->getLine() . "</p><hr>";
        }
    }

    $pdo = null;

} else {
    header("HTTP/1.1 400 Bad Request");
    echo "Tidak ada file yang diunggah.";
}