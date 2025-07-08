<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'hse';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>

<head>
    <title>DataTables | Minia - Admin & Dashboard Template</title>
    <?php include 'layouts/head.php'; ?>

    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<!-- Begin page -->
<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Daily Report</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Report</a></li>
                                    <li class="breadcrumb-item active">Daily Report</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

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
                                        // Fetch all daily reports to populate the table
                                        $reports = $conn->query("SELECT id, report_date FROM daily_reports ORDER BY report_date DESC");
                                        $no = 1;
                                        while ($report = $reports->fetch_assoc()) {
                                        ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($report['report_date']))); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-warning waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#reportModal<?php echo $report['id']; ?>">View <i class="mdi mdi-eye"></i></button>
                                                    <!-- Extra Large modal example -->
                                                    <div class="modal fade" id="reportModal<?php echo $report['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel<?php echo $report['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog modal-xl" style="max-width: 900px; width: 900px;">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="reportModalLabel<?php echo $report['id']; ?>">Daily HSE Report - <?php echo htmlspecialchars(date('l, d F Y', strtotime($report['report_date']))); ?></h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <?php
                                                                    // Fetch report data for the modal
                                                                    $report_id = $report['id'];
                                                                    $header_data = $conn->query("SELECT * FROM daily_reports WHERE id = $report_id")->fetch_assoc();
                                                                    if ($header_data) {
                                                                        $bsoc_cards = $conn->query("SELECT * FROM bsoc_card_reports WHERE report_id = $report_id ORDER BY entry_number ASC")->fetch_all(MYSQLI_ASSOC);
                                                                        $contributions = $conn->query("SELECT * FROM bsoc_monthly_contribution WHERE report_id = $report_id")->fetch_all(MYSQLI_ASSOC);
                                                                        $hse_summary = $conn->query("SELECT * FROM hse_summary WHERE report_id = $report_id")->fetch_all(MYSQLI_ASSOC);
                                                                    ?>
                                                                        <style>
                                                                            .container {
                                                                                max-width: 100%;
                                                                                margin: auto;
                                                                                padding: 10px;
                                                                            }
                                                                            .header {
                                                                                text-align: center;
                                                                                margin-bottom: 10px;
                                                                            }
                                                                            .header h1 {
                                                                                font-size: 14px;
                                                                                font-weight: bold;
                                                                                margin: 0;
                                                                            }
                                                                            .header p {
                                                                                font-size: 12px;
                                                                                margin: 2px 0;
                                                                            }
                                                                            .info-table {
                                                                                width: 100%;
                                                                                border-collapse: collapse;
                                                                                margin-bottom: 10px;
                                                                            }
                                                                            .info-table td {
                                                                                border: 1px solid #000;
                                                                                padding: 4px;
                                                                                text-align: left;
                                                                            }
                                                                            .bsoc-table, .bsoc-contribution-table {
                                                                                width: 100%;
                                                                                border-collapse: collapse;
                                                                                margin-bottom: 10px;
                                                                            }
                                                                            .bsoc-table th, .bsoc-table td, .bsoc-contribution-table th, .bsoc-contribution-table td {
                                                                                border: 1px solid #000;
                                                                                padding: 5px;
                                                                                text-align: center;
                                                                                font-weight: bold;
                                                                            }
                                                                            .bsoc-table .unsafe {
                                                                                color: red;
                                                                            }
                                                                            .section-title {
                                                                                background-color: #d9d9d9;
                                                                                font-weight: bold;
                                                                                padding: 5px;
                                                                                border: 1px solid #000;
                                                                                margin-top: 10px;
                                                                                text-align: center;
                                                                            }
                                                                            .description-table {
                                                                                width: 100%;
                                                                                border-collapse: collapse;
                                                                                margin-top: -1px;
                                                                            }
                                                                            .description-table th, .description-table td {
                                                                                border: 1px solid #000;
                                                                                padding: 5px;
                                                                                text-align: left;
                                                                                vertical-align: top;
                                                                            }
                                                                            .description-table th {
                                                                                background-color: #d9d9d9;
                                                                                text-align: center;
                                                                                font-weight: bold;
                                                                            }
                                                                            .description-table .no {
                                                                                width: 30px;
                                                                                text-align: center;
                                                                            }
                                                                            .description-table .category {
                                                                                width: 60px;
                                                                                text-align: center;
                                                                            }
                                                                            .summary-section {
                                                                                border: 1px solid #000;
                                                                                padding: 5px;
                                                                            }
                                                                            .summary-section ol {
                                                                                margin: 0;
                                                                                padding-left: 20px;
                                                                            }
                                                                            .summary-section li {
                                                                                margin-bottom: 2px;
                                                                            }
                                                                            .bsoc-contribution-table .align-left {
                                                                                text-align: left;
                                                                            }
                                                                            .bsoc-contribution-table .gray-bg {
                                                                                background-color: #f2f2f2;
                                                                            }
                                                                            .bsoc-contribution-table .concerns-cell {
                                                                                vertical-align: top;
                                                                                text-align: left;
                                                                                padding-left: 5px;
                                                                            }
                                                                            .doctor-info {
                                                                                margin-top: 10px;
                                                                                padding: 5px;
                                                                                font-weight: bold;
                                                                            }
                                                                            @media (max-width: 768px) {
                                                                                .description-table td, .description-table th,
                                                                                .bsoc-table td, .bsoc-table th,
                                                                                .bsoc-contribution-table td, .bsoc-contribution-table th,
                                                                                .info-table td {
                                                                                    font-size: 8px;
                                                                                    padding: 2px;
                                                                                }
                                                                                .modal-dialog {
                                                                                    max-width: 100%;
                                                                                    width: 100%;
                                                                                }
                                                                            }
                                                                        </style>
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
                                                                                    <td>Days</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>ZYM-HSE Stats up-to-date:</td>
                                                                                    <td></td>
                                                                                    <td>No LTI Days :</td>
                                                                                    <td>Days</td>
                                                                                </tr>
                                                                            </table>
                                                                            <table class="bsoc-table">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th colspan="6">BSOC cards reported in the last 24 hrs.</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <tr>
                                                                                        <td>Total BSOC Cards</td>
                                                                                        <td><?php echo htmlspecialchars($header_data['total_bsoc_cards'] ?? ''); ?></td>
                                                                                        <td>Safe Cards</td>
                                                                                        <td><?php echo htmlspecialchars($header_data['safe_cards'] ?? ''); ?></td>
                                                                                        <td>Unsafe BSOC</td>
                                                                                        <td class="unsafe"><?php echo htmlspecialchars($header_data['unsafe_bsoc'] ?? ''); ?></td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                            <div class="section-title">
                                                                                <?php echo htmlspecialchars($header_data['best_bsoc_title'] ?? ''); ?>
                                                                            </div>
                                                                            <div style="border: 1px solid #000; border-top: none; padding: 5px;">
                                                                                <?php echo nl2br(htmlspecialchars($header_data['best_bsoc_description'] ?? '')); ?>
                                                                            </div>
                                                                            <table class="description-table" style="margin-top: 10px;">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th class="no">No</th>
                                                                                        <th class="category">Category</th>
                                                                                        <th>Description</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php foreach ($bsoc_cards as $card): ?>
                                                                                        <tr>
                                                                                            <td class="no"><?php echo htmlspecialchars($card['entry_number'] ?? ''); ?></td>
                                                                                            <td class="category"><?php echo htmlspecialchars($card['category'] ?? ''); ?></td>
                                                                                            <td><?php echo nl2br(htmlspecialchars($card['description'] ?? '')); ?></td>
                                                                                        </tr>
                                                                                    <?php endforeach; ?>
                                                                                </tbody>
                                                                            </table>
                                                                            <table class="bsoc-contribution-table" style="margin-top: 10px;">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th colspan="7">BSOC Card contribution for this Month : <?php echo array_sum(array_column($contributions, 'total')); ?></th>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th></th>
                                                                                        <th>Hazard</th>
                                                                                        <th>Unsafe Act</th>
                                                                                        <th>Near Miss</th>
                                                                                        <th>Safe Work</th>
                                                                                        <th>Total / Percentage</th>
                                                                                        <th rowspan="<?php echo count($contributions) + 1; ?>" class="concerns-cell">Concerns :</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php 
                                                                                    $loop_index = 0;
                                                                                    foreach ($contributions as $row): 
                                                                                        $percentage = ($row['percentage'] ?? 0) * 100;
                                                                                    ?>
                                                                                        <tr class="<?php echo $loop_index % 2 == 1 ? 'gray-bg' : ''; ?>">
                                                                                            <td class="align-left"><?php echo htmlspecialchars($row['entity_name'] ?? ''); ?></td>
                                                                                            <td><?php echo htmlspecialchars($row['hazard'] ?? ''); ?></td>
                                                                                            <td><?php echo htmlspecialchars($row['unsafe_act'] ?? ''); ?></td>
                                                                                            <td><?php echo htmlspecialchars($row['near_miss'] ?? ''); ?></td>
                                                                                            <td><?php echo htmlspecialchars($row['safe_work'] ?? ''); ?></td>
                                                                                            <td><?php echo htmlspecialchars($row['total'] ?? ''); ?> / <?php echo number_format($percentage, 2); ?>%</td>
                                                                                        </tr>
                                                                                    <?php 
                                                                                        $loop_index++; 
                                                                                    endforeach; 
                                                                                    ?>
                                                                                </tbody>
                                                                            </table>
                                                                            <div class="summary-section">
                                                                                <strong>HSE Summary for past 24 hours:</strong>
                                                                                <ol>
                                                                                    <?php foreach ($hse_summary as $summary): ?>
                                                                                        <li><?php echo htmlspecialchars($summary['activity_description']); ?></li>
                                                                                    <?php endforeach; ?>
                                                                                </ol>
                                                                            </div>
                                                                            <?php if (!empty($header_data['doctor'])): ?>
                                                                                <div class="doctor-info">
                                                                                    <?php echo htmlspecialchars($header_data['doctor']); ?>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    <?php
                                                                    } else {
                                                                        echo "<p>Tidak ada data laporan ditemukan untuk ID ini.</p>";
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Actions <i class="mdi mdi-chevron-down"></i></button>
                                                        <div class="dropdown-menu dropdownmenu-primary">
                                                            <a class="dropdown-item" href="#">Update</a>
                                                            <a class="dropdown-item" href="#">Delete</a>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">Export <i class="mdi mdi-chevron-down"></i></button>
                                                        <div class="dropdown-menu dropdownmenu-success">
                                                            <a class="dropdown-item" href="#">Excel</a>
                                                            <a class="dropdown-item" href="#">PDF</a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                        $conn->close();
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

<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>

<!-- JAVASCRIPT -->
<?php include 'layouts/vendor-scripts.php'; ?>

<!-- Required datatable js -->
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- Buttons examples -->
<script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/jszip/jszip.min.js"></script>
<script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

<!-- Responsive examples -->
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<!-- Datatable init js -->
<script src="assets/js/pages/datatables.init.js"></script>

<script src="assets/js/app.js"></script>
</body>
</html>