<?php
/**
 * ===================================================================
 * SKRIP EKSPOR LAPORAN HSE KE TEMPLATE EXCEL
 * ===================================================================
 */

// 1. Inisialisasi & Muat Library
// -------------------------------------------------------------------
require '../vendor/autoload.php'; // Sesuaikan path jika perlu

// [DIUBAH] Memuat koneksi PDO dari file konfigurasi terpusat
// Variabel $pdo sekarang tersedia dari file ini.
require_once '../layouts/conf.php'; // Sesuaikan path jika perlu

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// ===================================================================
// KONFIGURASI
// ===================================================================
$templateFile = '../template/template_hse_report.xlsx';

$config = [
    'bsoc_cards'    => ['start_row' => 10, 'max_rows' => 41],
    'contributions' => ['start_row' => 53, 'max_rows' => 17],
    'hse_summary'   => ['start_row' => 71, 'max_rows' => 10]
];
// ===================================================================


// 2. Validasi Input
// -------------------------------------------------------------------
$report_id = isset($_GET['report_id']) ? (int)$_GET['report_id'] : 0;
if ($report_id === 0) {
    die("Error: ID Laporan tidak valid. Contoh penggunaan: ?report_id=1");
}

// Blok koneksi database sudah tidak diperlukan di sini karena sudah di-handle oleh conf.php

// 3. Pengambilan Data dari Database
// -------------------------------------------------------------------
try {
    // Ambil data utama dari tabel daily_reports
    $stmt = $pdo->prepare("SELECT * FROM daily_reports WHERE id = ?");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch();

    if (!$report) {
        die("Error: Laporan dengan ID {$report_id} tidak ditemukan di database.");
    }

    // Ambil data kartu BSOC
    $stmt = $pdo->prepare("SELECT * FROM bsoc_card_reports WHERE report_id = ? AND category LIKE 'U/%' AND description IS NOT NULL AND description != '' ORDER BY entry_number");
    $stmt->execute([$report_id]);
    $bsocCards = $stmt->fetchAll();

    // Ambil data kontribusi bulanan
    $stmt = $pdo->prepare("SELECT entity_name, hazard, unsafe_act, near_miss, safe_work, (hazard + unsafe_act + near_miss + safe_work) AS total, percentage FROM bsoc_monthly_contribution WHERE report_id = ? ORDER BY id");
    $stmt->execute([$report_id]);
    $contributions = $stmt->fetchAll();

    // Ambil data ringkasan HSE
    $stmt = $pdo->prepare("SELECT activity_description FROM hse_summary WHERE report_id = ?");
    $stmt->execute([$report_id]);
    $hseActivities = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Gagal mengambil data dari database: " . $e->getMessage());
} finally {
    // Tutup koneksi setelah semua query selesai
    $pdo = null;
}


// 4. Proses Manipulasi File Excel
// -------------------------------------------------------------------
try {
    $spreadsheet = IOFactory::load($templateFile);
    $sheet = $spreadsheet->getActiveSheet();

    // --- Mengisi Data Tunggal ---
    $sheet->setCellValue('A2', (new DateTime($report['report_date']))->format('d F Y'));
    $sheet->setCellValue('D6', $report['total_bsoc_cards']);
    $sheet->setCellValue('H6', $report['unsafe_bsoc']);
    $sheet->setCellValue('F6', '=D6-H6');
    $sheet->setCellValue('A7', $report['best_bsoc_title']);
    $sheet->setCellValue('A8', $report['best_bsoc_description']);

    // Logika untuk mengisi data berulang dan menghapus baris kosong
    $rowsRemoved = 0;

    // --- Mengisi Kartu BSOC ---
    $startRow = $config['bsoc_cards']['start_row'];
    foreach ($bsocCards as $index => $card) {
        $currentRow = $startRow + $index;
        $sheet->setCellValue('B' . $currentRow, $card['category']);
        $sheet->setCellValue('C' . $currentRow, $card['description']);
    }
    $filledRows = count($bsocCards);
    $rowsToRemove = $config['bsoc_cards']['max_rows'] - $filledRows;
    if ($rowsToRemove > 0) {
        $sheet->removeRow($startRow + $filledRows, $rowsToRemove);
        $rowsRemoved += $rowsToRemove;
    }

    // --- Mengisi Kontribusi Bulanan ---
    $newContributionStartRow = $config['contributions']['start_row'] - $rowsRemoved;
    $contributionHeaderRow = 51 - $rowsRemoved; // Asumsi posisi header asli di baris 51
    $sheet->setCellValue('F' . $contributionHeaderRow, 'BSOC Card contribution for this month');
    
    foreach ($contributions as $index => $item) {
        $currentRow = $newContributionStartRow + $index;
        $sheet->setCellValue('A' . $currentRow, $item['entity_name']);
        $sheet->setCellValue('D' . $currentRow, $item['hazard']);
        $sheet->setCellValue('E' . $currentRow, $item['unsafe_act']);
        $sheet->setCellValue('F' . $currentRow, $item['near_miss']);
        $sheet->setCellValue('G' . $currentRow, $item['safe_work']);
        $sheet->setCellValue('H' . $currentRow, $item['total']);
        $sheet->setCellValue('I' . $currentRow, $item['percentage']);
        $sheet->getStyle('I' . $currentRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
    }
    $filledRows = count($contributions);
    $rowsToRemove = $config['contributions']['max_rows'] - $filledRows;
    if ($rowsToRemove > 0) {
        $sheet->removeRow($newContributionStartRow + $filledRows, $rowsToRemove);
        $rowsRemoved += $rowsToRemove;
    }

    // --- Mengisi Ringkasan HSE ---
    $newHseSummaryStartRow = $config['hse_summary']['start_row'] - $rowsRemoved;
    foreach ($hseActivities as $index => $activity) {
        $currentRow = $newHseSummaryStartRow + $index;
        $sheet->setCellValue('B' . $currentRow, $activity['activity_description']);
    }
    $filledRowsHse = count($hseActivities);
    $rowsToRemove = $config['hse_summary']['max_rows'] - $filledRowsHse;
    if ($rowsToRemove > 0) {
        $sheet->removeRow($newHseSummaryStartRow + $filledRowsHse, $rowsToRemove);
    }
    
    // --- Menambahkan Info Dokter ---
    if (isset($report['doctor']) && !empty(trim($report['doctor']))) {
        $lastHseActivityRow = $newHseSummaryStartRow + $filledRowsHse;
        $doctorRow = $lastHseActivityRow + 1; // 1 baris di bawahnya untuk data
        $sheet->setCellValue('B' . $doctorRow, $report['doctor']);
    }

    // 5. Hasilkan File untuk Diunduh
    // -------------------------------------------------------------------
    $fileName = 'HSE_Daily_Report_' . $report['report_date'] . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fileName . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    die("Terjadi error saat memproses file Excel: " . $e->getMessage());
}

exit;