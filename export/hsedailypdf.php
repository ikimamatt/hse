<?php
// 1. Sertakan autoload Composer dan class Dompdf
require '../vendor/autoload.php';
use Dompdf\Dompdf;

// 2. Sertakan file koneksi PDO Anda
require_once '../layouts/conf.php'; // Pastikan file ini berisi variabel $pdo

// Inisialisasi variabel
$header_data = null;
$bsoc_cards = [];
$contributions = [];
$hse_summary = [];

try {
    // 3. Ambil report_id dari parameter GET
    $report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;

    // 4. Siapkan dan jalankan query untuk mengambil data header dengan PDO
    if ($report_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM daily_reports WHERE id = ?");
        $stmt->execute([$report_id]);
        $header_data = $stmt->fetch();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM daily_reports ORDER BY report_date DESC LIMIT 1");
        $stmt->execute();
        $header_data = $stmt->fetch();
    }

    // 5. Jika data header ditemukan, ambil data detailnya
    if ($header_data) {
        $current_report_id = $header_data['id'];

        $stmt_bsoc = $pdo->prepare("SELECT * FROM bsoc_card_reports WHERE report_id = ? ORDER BY entry_number ASC");
        $stmt_bsoc->execute([$current_report_id]);
        $bsoc_cards = $stmt_bsoc->fetchAll();

        $stmt_contrib = $pdo->prepare("SELECT * FROM bsoc_monthly_contribution WHERE report_id = ?");
        $stmt_contrib->execute([$current_report_id]);
        $contributions = $stmt_contrib->fetchAll();

        $stmt_summary = $pdo->prepare("SELECT * FROM hse_summary WHERE report_id = ?");
        $stmt_summary->execute([$current_report_id]);
        $hse_summary = $stmt_summary->fetchAll();
    } else {
        die("Tidak ada data laporan ditemukan.");
    }
} catch (PDOException $e) {
    die("Gagal mengambil data dari database: " . $e->getMessage());
}

$pdo = null;

// Start output buffering untuk menangkap konten HTML
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Great Wall Drilling Company - Daily HSE Report</title>
<style>
    /* CSS tidak berubah */
    body { font-family: Arial, sans-serif; font-size: 10px; color: #000; background-color: #fff; margin: 15px; }
    .container { max-width: 800px; margin: auto; border: 1px solid #ccc; padding: 10px; }
    .header { text-align: center; margin-bottom: 10px; }
    .header h1 { font-size: 14px; font-weight: bold; margin: 0; }
    .header p { font-size: 12px; margin: 2px 0; }
    .info-table, .bsoc-table, .bsoc-contribution-table, .description-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    .info-table td, .bsoc-table th, .bsoc-table td, .bsoc-contribution-table th, .bsoc-contribution-table td, .description-table th, .description-table td { border: 1px solid #000; padding: 5px; vertical-align: top; }
    .bsoc-table th, .bsoc-contribution-table th, .description-table th { background-color: #d9d9d9; text-align: center; font-weight: bold; }
    .bsoc-table td, .bsoc-contribution-table td { text-align: center; font-weight: bold; }
    .info-table td, .description-table td { text-align: left; font-weight: normal; }
    .description-table .no, .description-table .category { text-align: center; }
    .bsoc-table .unsafe { color: red; }
    .section-title { background-color: #d9d9d9; font-weight: bold; padding: 5px; border: 1px solid #000; margin-top: 10px; text-align: center; }
    .description-table { margin-top: -1px; }
    .description-table .no { width: 30px; }
    .description-table .category { width: 60px; }
    .summary-section { border: 1px solid #000; padding: 8px; margin-top: 10px; }
    .summary-section strong { font-weight: bold; }
    .summary-section ol { margin: 5px 0 0 20px; padding-left: 0; }
    .summary-section li { margin-bottom: 2px; }
    .bsoc-contribution-table .align-left { text-align: left; }
    .bsoc-contribution-table .gray-bg { background-color: #f2f2f2; }
    .doctor-info { margin-top: 15px; padding-top:5px; font-weight: bold; text-align:right;}
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Great Wall Drilling Company</h1>
        <p>Daily HSE Report</p>
        <p><?php echo htmlspecialchars(date('l, d F Y', strtotime($header_data['report_date']))); ?></p>
    </div>

    <table class="info-table">
        <tr>
            <td>ZYM Start-up date :</td>
            <td style="width: 50%;"><?php echo htmlspecialchars($header_data['zyh_startup_date'] ?? ''); ?></td>
            <td>No LTI Days :</td>
            <td><?php echo htmlspecialchars($header_data['no_lti_days'] ?? 'N/A'); ?> Days</td>
        </tr>
        <tr>
            <td>ZYM-HSE Stats up-to-date:</td>
            <td><?php echo htmlspecialchars($header_data['zym_hse_stats'] ?? ''); ?></td>
            <td>Man Hours without LTI :</td>
            <td><?php echo htmlspecialchars($header_data['man_hours_without_lti'] ?? 'N/A'); ?> Hours</td>
        </tr>
    </table>
    <table class="bsoc-table">
        <thead>
            <tr><th colspan="6">BSOC cards reported in the last 24 hrs.</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Total BSOC Cards</td>
                <td><?php echo htmlspecialchars($header_data['total_bsoc_cards'] ?? '0'); ?></td>
                <td>Safe Cards</td>
                <td><?php echo htmlspecialchars($header_data['safe_cards'] ?? '0'); ?></td>
                <td>Unsafe BSOC</td>
                <td class="unsafe"><?php echo htmlspecialchars($header_data['unsafe_bsoc'] ?? '0'); ?></td>
            </tr>
        </tbody>
    </table>
    <div class="section-title"><?php echo htmlspecialchars($header_data['best_bsoc_title'] ?? ''); ?></div>
    <div style="border: 1px solid #000; border-top: none; padding: 5px;"><?php echo nl2br(htmlspecialchars($header_data['best_bsoc_description'] ?? '')); ?></div>
    <table class="description-table" style="margin-top: 10px;">
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="category">Category</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($bsoc_cards)): foreach ($bsoc_cards as $card): ?>
            <tr>
                <td class="no"><?php echo htmlspecialchars($card['entry_number'] ?? ''); ?></td>
                <td class="category"><?php echo htmlspecialchars($card['category'] ?? ''); ?></td>
                <td><?php echo nl2br(htmlspecialchars($card['description'] ?? '')); ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="3" style="text-align:center;">No BSOC cards reported.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="bsoc-contribution-table" style="margin-top: 10px;">
        <thead>
            <tr>
                <th colspan="6">BSOC Card contribution for this Month : &nbsp;&nbsp;<?php echo array_sum(array_column($contributions, 'total')); ?></th>
            </tr>
            <tr>
                <th>Entity</th><th>Hazard</th><th>Unsafe Act</th><th>Near Miss</th><th>Safe Work</th><th>Total / Percentage</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $loop_index = 0;
            if(!empty($contributions)): foreach ($contributions as $row):
                $total_contributions = array_sum(array_column($contributions, 'total'));
                $percentage = ($total_contributions > 0) ? (($row['total'] ?? 0) / $total_contributions) * 100 : 0;
            ?>
            <tr class="<?php echo $loop_index % 2 == 1 ? 'gray-bg' : ''; ?>">
                <td class="align-left"><?php echo htmlspecialchars($row['entity_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['hazard'] ?? '0'); ?></td>
                <td><?php echo htmlspecialchars($row['unsafe_act'] ?? '0'); ?></td>
                <td><?php echo htmlspecialchars($row['near_miss'] ?? '0'); ?></td>
                <td><?php echo htmlspecialchars($row['safe_work'] ?? '0'); ?></td>
                <td><?php echo htmlspecialchars($row['total'] ?? '0'); ?> / <?php echo number_format($percentage, 2); ?>%</td>
            </tr>
            <?php
            $loop_index++;
            endforeach; endif;
            ?>
        </tbody>
    </table>

    <div class="summary-section">
        <strong>Concerns:</strong>
        <div style="text-align: left; font-weight:normal; margin-top: 5px;">
            <?php echo nl2br(htmlspecialchars($header_data['concerns'] ?? 'No concerns reported.')); ?>
        </div>
    </div>

    <div class="summary-section">
        <strong>HSE Summary for past 24 hours:</strong>
        <ol>
            <?php if(!empty($hse_summary)): foreach ($hse_summary as $summary): ?>
            <li><?php echo htmlspecialchars($summary['activity_description']); ?></li>
            <?php endforeach; else: ?>
            <li>No summary available.</li>
            <?php endif; ?>
        </ol>
    </div>

    <?php if (!empty($header_data['doctor'])): ?>
    <div class="doctor-info"><?php echo htmlspecialchars($header_data['doctor']); ?></div>
    <?php endif; ?>

</div>

</body>
</html>
<?php
// Bagian Dompdf tidak berubah
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->set_option('isRemoteEnabled', TRUE); // Memungkinkan Dompdf mengambil gambar dari URL eksternal jika ada
$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'HSE_Daily_Report_' . ($header_data['report_date'] ?? date('Y-m-d')) . '.pdf';
$dompdf->stream($filename, ["Attachment" => true]);
exit;
?>