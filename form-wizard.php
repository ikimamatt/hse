<?php
include 'layouts/session.php';
include 'layouts/head-main.php';
require_once 'layouts/conf.php'; // Assuming conf.php contains PDO connection

$report_data = [];
$bsoc_cards_data = [];
$monthly_contribution_data = [];
$hse_summary_data = [];
$report_id = $_GET['report_id'] ?? null;

if ($report_id) {
    try {
        // Fetch daily report data
        $stmt = $pdo->prepare("SELECT * FROM daily_reports WHERE id = ?");
        $stmt->execute([$report_id]);
        $report_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch BSOC card reports
        $stmt = $pdo->prepare("SELECT * FROM bsoc_card_reports WHERE report_id = ? ORDER BY entry_number ASC");
        $stmt->execute([$report_id]);
        $bsoc_cards_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch BSOC monthly contribution
        $stmt = $pdo->prepare("SELECT * FROM bsoc_monthly_contribution WHERE report_id = ?");
        $stmt->execute([$report_id]);
        $monthly_contribution_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch HSE Summary
        $stmt = $pdo->prepare("SELECT * FROM hse_summary WHERE report_id = ?");
        $stmt->execute([$report_id]);
        $hse_summary_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Handle error, e.g., log it or display a message
        echo "Error: " . $e->getMessage();
    }
}

// Default values for form fields
$company_name = $report_data['company_name'] ?? 'GWDC Indonesia';
$report_date = $report_data['report_date'] ?? date('Y-m-d');
$zyh_startup_date = $report_data['zyh_startup_date'] ?? '2025-06-01';
$phm_startup_date = $report_data['phm_startup_date'] ?? '2025-06-15';
$no_lti_days_zyh = $report_data['no_lti_days_zyh'] ?? '150';
$no_lti_days_phm = $report_data['no_lti_days_phm'] ?? '145';
$total_bsoc_cards = $report_data['total_bsoc_cards'] ?? '20';
$safe_cards = $report_data['safe_cards'] ?? '15';
$unsafe_bsoc = $report_data['unsafe_bsoc'] ?? '5';
$best_bsoc_title = $report_data['best_bsoc_title'] ?? 'Safety Equipment Usage';
$best_bsoc_description = $report_data['best_bsoc_description'] ?? 'Ensured all workers used proper PPE during operations.';
$total_monthly_bsoc_cards = $report_data['total_monthly_bsoc_cards'] ?? '80';
$doctor = $report_data['doctor'] ?? 'Dr. Smith';
$prepared_by = $report_data['prepared_by'] ?? 'John Doe';
$concern = $report_data['concerns'] ?? 'Need to improve equipment safety checks.'; // Assuming 'concerns' column exists

// Default values for dynamic tables if no data is fetched
if (empty($bsoc_cards_data)) {
    $bsoc_cards_data = [
        ['entry_number' => 1, 'category' => 'U/C', 'description' => 'Uncontrolled equipment movement']
    ];
}
if (empty($monthly_contribution_data)) {
    $monthly_contribution_data = [
        ['entity_name' => 'GWDC', 'hazard' => 2, 'unsafe_act' => 1, 'near_miss' => 0, 'safe_work' => 5, 'total' => 8],
        ['entity_name' => 'Client', 'hazard' => 1, 'unsafe_act' => 0, 'near_miss' => 1, 'safe_work' => 3, 'total' => 5],
        ['entity_name' => '3rd Party', 'hazard' => 0, 'unsafe_act' => 2, 'near_miss' => 0, 'safe_work' => 4, 'total' => 6],
        ['entity_name' => 'Catering', 'hazard' => 0, 'unsafe_act' => 0, 'near_miss' => 1, 'safe_work' => 2, 'total' => 3]
    ];
}
if (empty($hse_summary_data)) {
    $hse_summary_data = [
        ['activity_description' => 'Attended GWDC and PHM supervisor morning meeting.'],
        ['activity_description' => 'Attended and lead pre-tour meeting for day and night crew.'],
        ['activity_description' => 'Daily walk through at the cement silo area.']
    ];
}
?>
<head>
    <title>Wizard | Minia - Admin & Dashboard Template</title>
    <?php include 'layouts/head.php'; ?>

    <!-- twitter-bootstrap-wizard css -->
    <link rel="stylesheet" href="assets/libs/twitter-bootstrap-wizard/prettify.css">

    <?php include 'layouts/head-style.php'; ?>
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
                            <h4 class="mb-sm-0 font-size-18">Wizard</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                    <li class="breadcrumb-item active">Wizard</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Wizard with Progressbar</h4>
                            </div>
                            <div class="card-body">
                                <div id="progrss-wizard" class="twitter-bs-wizard">
                                    <ul class="twitter-bs-wizard-nav nav nav-pills nav-justified">
                                        <li class="nav-item">
                                            <a href="#progress-seller-details" class="nav-link" data-toggle="tab">
                                                <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Part 1">
                                                    <i class="bx bx-list-ul"></i>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#progress-company-document" class="nav-link" data-toggle="tab">
                                                <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Part 2">
                                                    <i class="bx bx-book-bookmark"></i>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#progress-hse-summary" class="nav-link" data-toggle="tab">
                                                <div class="step-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Part 3">
                                                    <i class="bx bx-detail"></i>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>

                                    <div id="bar" class="progress mt-4">
                                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"></div>
                                    </div>
                                    <form method="post" action="backend/hse_daily_report.php" id="mainReportForm">
                                        <?php if ($report_id): ?>
                                            <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($report_id); ?>">
                                        <?php endif; ?>
                                        <div class="tab-content twitter-bs-wizard-tab-content">
                                            <div class="tab-pane" id="progress-seller-details">
                                                <div class="text-center mb-4">
                                                    <h5>Daily Report Form</h5>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="company_name" class="form-label">Company Name</label>
                                                            <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo htmlspecialchars($company_name); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="report_date" class="form-label">Report Date</label>
                                                            <input type="date" class="form-control" id="report_date" name="report_date" value="<?php echo htmlspecialchars($report_date); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="zyh_startup_date" class="form-label">ZYM Start-up Date</label>
                                                            <input type="date" class="form-control" id="zyh_startup_date" name="zyh_startup_date" value="<?php echo htmlspecialchars($zyh_startup_date); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="phm_startup_date" class="form-label">PHM Start-up Date</label>
                                                            <input type="date" class="form-control" id="phm_startup_date" name="phm_startup_date" value="<?php echo htmlspecialchars($phm_startup_date); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="no_lti_days_zyh" class="form-label">No LTI Days (ZYM)</label>
                                                            <input type="number" class="form-control" id="no_lti_days_zyh" name="no_lti_days_zyh" value="<?php echo htmlspecialchars($no_lti_days_zyh); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="no_lti_days_phm" class="form-label">No LTI Days (PHM)</label>
                                                            <input type="number" class="form-control" id="no_lti_days_phm" name="no_lti_days_phm" value="<?php echo htmlspecialchars($no_lti_days_phm); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="total_bsoc_cards" class="form-label">Total BSOC Cards</label>
                                                            <input type="number" class="form-control" id="total_bsoc_cards" name="total_bsoc_cards" value="<?php echo htmlspecialchars($total_bsoc_cards); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="safe_cards" class="form-label">Safe Cards</label>
                                                            <input type="number" class="form-control" id="safe_cards" name="safe_cards" value="<?php echo htmlspecialchars($safe_cards); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="unsafe_bsoc" class="form-label">Unsafe BSOC</label>
                                                            <input type="number" class="form-control" id="unsafe_bsoc" name="unsafe_bsoc" value="<?php echo htmlspecialchars($unsafe_bsoc); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <label for="best_bsoc_title" class="form-label">Best BSOC Title</label>
                                                            <input type="text" class="form-control" id="best_bsoc_title" name="best_bsoc_title" value="<?php echo htmlspecialchars($best_bsoc_title); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <label for="best_bsoc_description" class="form-label">Best BSOC Description</label>
                                                            <textarea class="form-control" id="best_bsoc_description" name="best_bsoc_description" rows="3"><?php echo htmlspecialchars($best_bsoc_description); ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="total_monthly_bsoc_cards" class="form-label">Total Monthly BSOC Cards</label>
                                                            <input type="number" class="form-control" id="total_monthly_bsoc_cards" name="total_monthly_bsoc_cards" value="<?php echo htmlspecialchars($total_monthly_bsoc_cards); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="prepared_by" class="form-label">Prepared By</label>
                                                            <input type="text" class="form-control" id="prepared_by" name="prepared_by" value="<?php echo htmlspecialchars($prepared_by); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Category</th>
                                                                    <th>Description</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="observations">
                                                                <?php foreach ($bsoc_cards_data as $index => $card): ?>
                                                                <tr>
                                                                    <td><input type="number" class="form-control" name="observation[<?php echo $index; ?>][no]" value="<?php echo htmlspecialchars($card['entry_number']); ?>"></td>
                                                                    <td>
                                                                        <select class="form-control" name="observation[<?php echo $index; ?>][category]">
                                                                            <option value="U/C" <?php echo ($card['category'] == 'U/C') ? 'selected' : ''; ?>>U/C</option>
                                                                            <option value="U/A" <?php echo ($card['category'] == 'U/A') ? 'selected' : ''; ?>>U/A</option>
                                                                        </select>
                                                                    </td>
                                                                    <td><input type="text" class="form-control" name="observation[<?php echo $index; ?>][description]" value="<?php echo htmlspecialchars($card['description']); ?>"></td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                        <button type="button" class="btn btn-secondary" onclick="addObservationRow()">Add Observation</button>
                                                    </div>
                                                </div>
                                                <ul class="pager wizard twitter-bs-wizard-pager-link">
                                                    <li class="next"><a href="javascript: void(0);" class="btn btn-primary">Next <i class="bx bx-chevron-right ms-1"></i></a></li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="progress-company-document">
                                                <div class="text-center mb-4">
                                                    <h5>Company Document</h5>
                                                    <p class="card-title-desc">Fill all information below</p>
                                                </div>
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Entity</th>
                                                            <th>Hazard</th>
                                                            <th>Unsafe Act</th>
                                                            <th>Near Miss</th>
                                                            <th>Safe Work</th>
                                                            <th>Total / Percentage</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="entries">
                                                        <?php foreach ($monthly_contribution_data as $index => $row): ?>
                                                        <tr>
                                                            <td><input type="text" class="form-control" name="entity[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['entity_name']); ?>" readonly></td>
                                                            <td><input type="number" class="form-control" name="hazard[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['hazard']); ?>"></td>
                                                            <td><input type="number" class="form-control" name="unsafe_act[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['unsafe_act']); ?>"></td>
                                                            <td><input type="number" class="form-control" name="near_miss[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['near_miss']); ?>"></td>
                                                            <td><input type="number" class="form-control" name="safe_work[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['safe_work']); ?>"></td>
                                                            <td><input type="text" class="form-control" name="total[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['total']); ?>" readonly></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <label class="form-label">Concern</label>
                                                            <textarea class="form-control" id="concern" name="concern" rows="3" placeholder="Concern details"><?php echo htmlspecialchars($concern); ?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <ul class="pager wizard twitter-bs-wizard-pager-link">
                                                    <li class="previous"><a href="javascript: void(0);" class="btn btn-primary"><i class="bx bx-chevron-left me-1"></i> Previous</a></li>
                                                    <li class="next"><a href="javascript: void(0);" class="btn btn-primary">Next <i class="bx bx-chevron-right ms-1"></i></a></li>
                                                </ul>
                                            </div>
                                            <div class="tab-pane" id="progress-hse-summary">
                                                <div class="text-center mb-4">
                                                    <h5>HSE Summary</h5>
                                                </div>
                                                <div class="table-responsive">
                                                    <table id="summaryTable" class="table table-bordered table-striped">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th class="text-center">Item No.</th>
                                                                <th class="text-center">Activity</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($hse_summary_data as $index => $activity): ?>
                                                            <tr>
                                                                <td class="text-center align-middle"><?php echo $index + 1; ?>.</td>
                                                                <td><input type="text" class="form-control" name="activity[<?php echo $index + 1; ?>]" value="<?php echo htmlspecialchars($activity['activity_description']); ?>" required></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-3">
                                                    <button type="button" class="btn btn-primary" onclick="addRow()">Add Row</button>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <label for="doctor" class="form-label">Doctor</label>
                                                            <input type="text" class="form-control" id="doctor" name="doctor" value="<?php echo htmlspecialchars($doctor); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="submit_daily_report" value="1">
                                                <ul class="pager wizard twitter-bs-wizard-pager-link mt-3">
                                                    <li class="previous"><a href="javascript: void(0);" class="btn btn-primary"><i class="bx bx-chevron-left me-1"></i> Previous</a></li>
                                                    <li class="float-end"><a href="javascript: void(0);" class="btn btn-success" data-bs-toggle="modal" data-bs-target=".confirmModal">Save Changes</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade confirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-bottom-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="bx bx-check-circle display-4 text-success"></i>
                            </div>
                            <h5>Confirm Save Changes</h5>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-light w-md" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary w-md" form="mainReportForm" data-bs-dismiss="modal">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'layouts/footer.php'; ?>
    </div>
</div>

<?php include 'layouts/right-sidebar.php'; ?>
<?php include 'layouts/vendor-scripts.php'; ?>

<!-- twitter-bootstrap-wizard js -->
<script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
<script src="assets/libs/twitter-bootstrap-wizard/prettify.js"></script>

<!-- form wizard init -->
<script src="assets/js/pages/form-wizard.init.js"></script>
<script src="assets/js/app.js"></script>

<script>
    let observationCount = 1;
    function addObservationRow() {
        const tbody = document.getElementById('observations');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="number" class="form-control" name="observation[${observationCount}][no]" placeholder="No"></td>
            <td>
                <select class="form-control" name="observation[${observationCount}][category]">
                    <option value="U/C">U/C</option>
                    <option value="U/A">U/A</option>
                </select>
            </td>
            <td><input type="text" class="form-control" name="observation[${observationCount}][description]" placeholder="Enter Description"></td>
        `;
        tbody.appendChild(row);
        observationCount++;
    }

    let rowCount = 3; // Adjusted to match the initial number of rows
    function addRow() {
        rowCount++;
        const table = document.getElementById("summaryTable");
        const newRow = table.insertRow(-1);
        const cell1 = newRow.insertCell(0);
        const cell2 = newRow.insertCell(1);
        cell1.innerHTML = `<td class="text-center align-middle">${rowCount}.</td>`;
        cell2.innerHTML = `<td><input type="text" class="form-control" name="activity[${rowCount}]" required></td>`;
    }
</script>
</body>
</html>
