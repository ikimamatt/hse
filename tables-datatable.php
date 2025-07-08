<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'hse_reports_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// [DIUBAH] Ambil semua data laporan sekali saja dan simpan di array
$reports_query = "SELECT id, report_date FROM daily_reports ORDER BY report_date DESC";
$reports_result = $conn->query($reports_query);
$all_reports = $reports_result->fetch_all(MYSQLI_ASSOC);

?>

<head>
    <title>Daily Reports | Minia - Admin & Dashboard Template</title>
    <?php include 'layouts/head.php'; ?>

    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <?php include 'layouts/head-style.php'; ?>

    <style>
        .report-container-modal { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
        .report-container-modal .header { text-align: center; margin-bottom: 20px; }
        .report-container-modal .header h1 { font-size: 18px; font-weight: bold; margin: 0; color: #000; }
        .report-container-modal .header p { font-size: 15px; margin: 4px 0; }
        .report-container-modal .report-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 1px solid #000; }
        .report-container-modal .report-table th, .report-container-modal .report-table td { border: 1px solid #000; padding: 8px; vertical-align: top; text-align: left; }
        .report-container-modal .report-table th { background-color: #e9ecef; font-weight: bold; text-align: center; }
        .report-container-modal .report-table td.text-center { text-align: center; }
        .report-container-modal .report-table td.font-weight-bold { font-weight: bold; }
        .report-container-modal .unsafe { color: red; font-weight: bold; }
        .report-container-modal .section-title { background-color: #e9ecef; font-weight: bold; padding: 8px; border: 1px solid #000; text-align: center; margin-top: 15px; }
        .report-container-modal .section-content { border: 1px solid #000; border-top: none; padding: 8px; margin-bottom: 15px; }
        .report-container-modal .summary-section { border: 1px solid #000; padding: 10px; margin-top: 15px; }
        .report-container-modal .summary-section ol { padding-left: 20px; margin: 5px 0 0 0; }
        .report-container-modal .doctor-info { margin-top: 20px; padding-top: 10px; border-top: 1px solid #dee2e6; font-weight: bold; text-align: right; }
    </style>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Daily Report</h4>
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript: void(0);">Report</a></li>
                                <li class="breadcrumb-item active">Daily Report</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table id="datatable" class="table table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>NO</th>
                                            <th>Date</th>
                                            <th>Data</th>
                                            <th>Action</th>
                                            <th>Export</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // [LOOP PERTAMA] Hanya untuk membuat baris tabel
                                        $no = 1;
                                        foreach ($all_reports as $report) {
                                        ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($report['report_date']))); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#reportModal<?php echo $report['id']; ?>">View <i class="mdi mdi-eye"></i></button>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Actions <i class="mdi mdi-chevron-down"></i></button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="#">Update</a>
                                                            <a class="dropdown-item" href="#">Delete</a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Export <i class="mdi mdi-chevron-down"></i></button>
                                                        <div class="dropdown-menu">
                                                            <a class="dropdown-item" href="#">Excel</a>
                                                            <a class="dropdown-item" href="#">PDF</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        } // Akhir dari loop pertama
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<?php
// [LOOP KEDUA] Hanya untuk membuat HTML modal yang tersembunyi
foreach ($all_reports as $report) {
    $modal_report_id = $report['id'];
    // Ambil data detail untuk setiap modal
    $header_data = $conn->query("SELECT * FROM daily_reports WHERE id = $modal_report_id")->fetch_assoc();
    if ($header_data) {
        $bsoc_cards = $conn->query("SELECT * FROM bsoc_card_reports WHERE report_id = $modal_report_id ORDER BY entry_number ASC")->fetch_all(MYSQLI_ASSOC);
        $contributions = $conn->query("SELECT * FROM bsoc_monthly_contribution WHERE report_id = $modal_report_id")->fetch_all(MYSQLI_ASSOC);
        $hse_summary = $conn->query("SELECT * FROM hse_summary WHERE report_id = $modal_report_id")->fetch_all(MYSQLI_ASSOC);
?>
    <div class="modal fade" id="reportModal<?php echo $report['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel<?php echo $report['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel<?php echo $report['id']; ?>">Daily HSE Report - <?php echo htmlspecialchars(date('l, d F Y', strtotime($report['report_date']))); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="report-container-modal">
                        <div class="header">
                            <h1>Great Wall Drilling Company</h1>
                            <p>Daily HSE Report</p>
                            <p><?php echo htmlspecialchars(date('l, d F Y', strtotime($header_data['report_date']))); ?></p>
                        </div>
                        <table class="report-table">
                            <tr>
                                <td class="font-weight-bold">ZYM Start-up date :</td>
                                <td><?php echo htmlspecialchars($header_data['zyh_startup_date'] ?? ''); ?></td>
                                <td class="font-weight-bold">No LTI Days :</td>
                                <td><?php echo htmlspecialchars($header_data['no_lti_days'] ?? 'N/A'); ?> Days</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">ZYM-HSE Stats up-to-date:</td>
                                <td><?php echo htmlspecialchars($header_data['zym_hse_stats'] ?? ''); ?></td>
                                <td class="font-weight-bold">Man Hours without LTI :</td>
                                <td><?php echo htmlspecialchars($header_data['man_hours_without_lti'] ?? 'N/A'); ?> Hours</td>
                            </tr>
                        </table>
                        <table class="report-table">
                            <thead>
                                <tr><th colspan="6">BSOC cards reported in the last 24 hrs.</th></tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center font-weight-bold">Total BSOC Cards</td>
                                    <td class="text-center"><?php echo htmlspecialchars($header_data['total_bsoc_cards'] ?? '0'); ?></td>
                                    <td class="text-center font-weight-bold">Safe Cards</td>
                                    <td class="text-center"><?php echo htmlspecialchars($header_data['safe_cards'] ?? '0'); ?></td>
                                    <td class="text-center font-weight-bold">Unsafe BSOC</td>
                                    <td class="text-center unsafe"><?php echo htmlspecialchars($header_data['unsafe_bsoc'] ?? '0'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="section-title"><?php echo htmlspecialchars($header_data['best_bsoc_title'] ?? 'Best BSOC Card of the Day'); ?></div>
                        <div class="section-content"><?php echo nl2br(htmlspecialchars($header_data['best_bsoc_description'] ?? 'N/A')); ?></div>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 15%;">Category</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($bsoc_cards)): foreach ($bsoc_cards as $card): ?>
                                <tr>
                                    <td class="text-center"><?php echo htmlspecialchars($card['entry_number'] ?? ''); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($card['category'] ?? ''); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($card['description'] ?? '')); ?></td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr><td colspan="3" class="text-center">No BSOC cards reported.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <table class="report-table">
                            <thead>
                                <tr><th colspan="6">BSOC Card contribution for this Month : <?php echo array_sum(array_column($contributions, 'total')); ?></th></tr>
                                <tr>
                                    <th>Entity</th><th>Hazard</th><th>Unsafe Act</th><th>Near Miss</th><th>Safe Work</th><th>Total / Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contributions as $row): 
                                     $total_contributions = array_sum(array_column($contributions, 'total'));
                                     $percentage = ($total_contributions > 0) ? (($row['total'] ?? 0) / $total_contributions) * 100 : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['entity_name'] ?? ''); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($row['hazard'] ?? '0'); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($row['unsafe_act'] ?? '0'); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($row['near_miss'] ?? '0'); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($row['safe_work'] ?? '0'); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($row['total'] ?? '0'); ?> / <?php echo number_format($percentage, 2); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="summary-section">
                            <strong>Concerns:</strong>
                            <div style="margin-top: 5px;"><?php echo nl2br(htmlspecialchars($header_data['concerns'] ?? 'No concerns reported.')); ?></div>
                        </div>
                        <div class="summary-section">
                            <strong>HSE Summary for past 24 hours:</strong>
                            <ol>
                                <?php if (!empty($hse_summary)): foreach ($hse_summary as $summary): ?>
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php
    } // Akhir dari if($header_data)
} // Akhir dari loop kedua
$conn->close();
?>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/jszip/jszip.min.js"></script>
<script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script src="assets/js/pages/datatables.init.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>