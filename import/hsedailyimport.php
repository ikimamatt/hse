<?php
// Mengaktifkan pelaporan error untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Memuat file konfigurasi database dan autoloader Composer
require_once '../layouts/conf.php'; // Pastikan path ini benar menuju koneksi PDO Anda
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Fungsi helper untuk mendapatkan nilai sel yang sudah diformat dari sheet.
 * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet Objek worksheet
 * @param string $cell Alamat sel (misal: 'A1')
 * @return string Nilai dari sel
 */
function getCellValue($sheet, $cell) {
    return $sheet->getCell($cell)->getFormattedValue();
}

// Memeriksa apakah ada file yang diunggah melalui parameter 'files' dari Dropzone
if (isset($_FILES['files']) && !empty($_FILES['files']['name'])) {

    // Normalisasi array $_FILES untuk mempermudah iterasi
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
        die("<div class='alert alert-danger'>Tidak ada file valid yang diterima atau terjadi error upload.</div>");
    }

    // Iterasi melalui setiap file yang valid untuk diproses
    foreach ($normalized_files as $file) {
        $fileName = $file['name'];
        $inputFileName = $file['tmp_name'];

        echo "<h4>Memproses file: " . htmlspecialchars($fileName) . "</h4>";

        // Memulai transaksi database untuk setiap file
        $pdo->beginTransaction();
        try {
            $spreadsheet = IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            // 1. Ekstrak dan Masukkan Data Laporan Harian Utama (daily_reports)
            // ... (kode sebelumnya)
            $company_name = getCellValue($sheet, 'A1');
            $report_date_raw = $sheet->getCell('A2')->getValue();
            $report_date_obj = null; // Inisialisasi variabel objek tanggal

            // Cek apakah formatnya adalah tanggal asli Excel
            if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($sheet->getCell('A2'))) {
                $report_date_obj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($report_date_raw);
            } else {
                // Jika bukan, proses sebagai string
                $report_date_string = (string)$report_date_raw;

                // 1. Coba format 'l, F j, Y' (contoh: 'Monday, July 7, 2025')
                $report_date_obj = DateTime::createFromFormat('l, F j, Y', $report_date_string);

                // 2. Jika format pertama gagal, coba format 'j F Y' (contoh: '31 March 2025')
                if ($report_date_obj === false) {
                    $report_date_obj = DateTime::createFromFormat('j F Y', $report_date_string);
                }
            }

            // Setelah mencoba semua format, periksa apakah hasilnya valid
            if ($report_date_obj === false || $report_date_obj === null) {
                // Jika semua upaya gagal, lempar error
                throw new Exception("Format tanggal di sel A2 tidak valid. Pastikan formatnya benar (contoh: '31 March 2025'). Nilai ditemukan: " . htmlspecialchars($report_date_raw));
            }

            // Jika berhasil, format tanggal untuk disimpan ke database
            $report_date = $report_date_obj->format('Y-m-d');
            // ... (kode selanjutnya)

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

            $sqlDaily = "INSERT INTO daily_reports (company_name, report_date, total_bsoc_cards, safe_cards, unsafe_bsoc, best_bsoc_title, best_bsoc_description, doctor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtDaily = $pdo->prepare($sqlDaily);
            $stmtDaily->execute([$company_name, $report_date, $total_bsoc_cards, $safe_cards, $unsafe_bsoc, $best_bsoc_title, $best_bsoc_description, $doctorInfo]);
            $reportId = $pdo->lastInsertId();

            // 2. Ekstrak Data Kartu BSOC (bsoc_card_reports)
            $sqlBsoc = "INSERT INTO bsoc_card_reports (report_id, entry_number, category, description) VALUES (?, ?, ?, ?)";
            $stmtBsoc = $pdo->prepare($sqlBsoc);
            for ($row = 10; $row <= 24; $row++) {
                $entryNum = getCellValue($sheet, 'A' . $row);
                $category = getCellValue($sheet, 'B' . $row);
                $description = getCellValue($sheet, 'C' . $row);

                if (is_numeric($entryNum) && !empty($category) && strpos($category, 'U/') === 0 && !empty(trim($description))) {
                    $stmtBsoc->execute([$reportId, $entryNum, $category, $description]);
                }
            }

            // 3. Ekstrak Kontribusi Bulanan (bsoc_monthly_contribution)
            $contribStartRow = null;
            for ($row = 1; $row <= $highestRow; $row++) {
                if (strpos((string)getCellValue($sheet, 'A' . $row), 'BSOC Card contribution for this Month') !== false) {
                    $contribStartRow = $row;
                    break;
                }
            }

            if ($contribStartRow !== null) {
                $sqlContrib = "INSERT INTO bsoc_monthly_contribution (report_id, entity_name, hazard, unsafe_act, near_miss, safe_work, total, percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtContrib = $pdo->prepare($sqlContrib);
                $entityRow = $contribStartRow + 2;
                while ($entityRow <= $highestRow) {
                    $entityName = getCellValue($sheet, 'A' . $entityRow);
                    if (empty($entityName) || strpos($entityName, 'HSE Summary for past 24 hours:') !== false) {
                        break;
                    }
                    
                    $hazard = (int)getCellValue($sheet, 'D' . $entityRow);
                    $unsafe_act = (int)getCellValue($sheet, 'E' . $entityRow);
                    $near_miss = (int)getCellValue($sheet, 'F' . $entityRow);
                    $safe_work = (int)getCellValue($sheet, 'G' . $entityRow);
                    $total = $hazard + $unsafe_act + $near_miss + $safe_work;
                    
                    $percentageStr = trim(getCellValue($sheet, 'I' . $entityRow), " %");
                    $percentageFloat = floatval($percentageStr) / 100.0;

                    $stmtContrib->execute([$reportId, $entityName, $hazard, $unsafe_act, $near_miss, $safe_work, $total, $percentageFloat]);
                    $entityRow++;
                }
            } else {
                 // Anda bisa melempar error jika tabel ini wajib ada
                 // throw new Exception("Tidak ditemukan tabel 'BSOC Card contribution for this Month'.");
            }

            // 4. Ekstrak Ringkasan HSE (hse_summary)
            $summaryStartRow = null;
            for ($row = 1; $row <= $highestRow; $row++) {
                if (strpos((string)getCellValue($sheet, 'A' . $row), 'HSE Summary for past 24 hours:') !== false) {
                    $summaryStartRow = $row;
                    break;
                }
            }
            
            if ($summaryStartRow !== null) {
                $sqlSummary = "INSERT INTO hse_summary (report_id, activity_description) VALUES (?, ?)";
                $stmtSummary = $pdo->prepare($sqlSummary);
                $summaryRow = $summaryStartRow + 1;
                while ($summaryRow <= $highestRow) {
                    $activity = getCellValue($sheet, 'B' . $summaryRow);
                    if (empty(trim($activity))) {
                        break;
                    }
                    $stmtSummary->execute([$reportId, $activity]);
                    $summaryRow++;
                }
            } else {
                // Anda bisa melempar error jika tabel ini wajib ada
                // throw new Exception("Tidak ditemukan tabel 'HSE Summary for past 24 hours:'.");
            }
            
            // Jika semua query berhasil, commit transaksi
            $pdo->commit();
            echo "<div class='alert alert-success'>✅ File <strong>" . htmlspecialchars($fileName) . "</strong> berhasil diimpor!</div><hr>";

        } catch (Exception $e) {
            // Jika terjadi error, batalkan semua perubahan
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>❌ Gagal mengimpor file <strong>" . htmlspecialchars($fileName) . "</strong>.</div>";
            echo "<p class='text-danger'><strong>Detail Kesalahan:</strong> " . $e->getMessage() . " (baris: " . $e->getLine() . ")</p><hr>";
        }
    }

    // Menutup koneksi database
    $pdo = null;

} else {
    // Memberikan respons error jika tidak ada file yang diunggah
    header("HTTP/1.1 400 Bad Request");
    echo "Tidak ada file yang diunggah atau terjadi kesalahan pada server.";
}
?>