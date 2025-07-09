<?php
include 'layouts/session.php';
include 'layouts/head-main.php';

// Database connection
$conn = new mysqli("localhost", "root", "", "gdap");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entities = $_POST["entity"] ?? [];
    $hazards = $_POST["hazard"] ?? [];
    $unsafe_acts = $_POST["unsafe_act"] ?? [];
    $near_misses = $_POST["near_miss"] ?? [];
    $safe_works = $_POST["safe_work"] ?? [];
    $totals = $_POST["total"] ?? [];
    $activities = $_POST["activity"] ?? [];

    // Handle daily report creation
    if (isset($_POST['submit_daily_report'])) {
        $company_name = $_POST['company_name'] ?? null;
        $report_date = $_POST['report_date'] ?? date('Y-m-d');
        $zyh_startup_date = $_POST['zyh_startup_date'] ?? null;
        $phm_startup_date = $_POST['phm_startup_date'] ?? null;
        $no_lti_days_zyh = $_POST['no_lti_days_zyh'] ?? null;
        $no_lti_days_phm = $_POST['no_lti_days_phm'] ?? null;
        $total_bsoc_cards = $_POST['total_bsoc_cards'] ?? null;
        $safe_cards = $_POST['safe_cards'] ?? null;
        $unsafe_bsoc = $_POST['unsafe_bsoc'] ?? null;
        $best_bsoc_title = $_POST['best_bsoc_title'] ?? null;
        $best_bsoc_description = $_POST['best_bsoc_description'] ?? null;
        $total_monthly_bsoc_cards = $_POST['total_monthly_bsoc_cards'] ?? null;
        $doctor = $_POST['doctor'] ?? null; // Added doctor field
        $prepared_by = $_POST['prepared_by'] ?? null;

        $stmt = $conn->prepare("INSERT INTO daily_reports (company_name, report_date, zyh_startup_date, phm_startup_date, no_lti_days_zyh, no_lti_days_phm, total_bsoc_cards, safe_cards, unsafe_bsoc, best_bsoc_title, best_bsoc_description, total_monthly_bsoc_cards, doctor, prepared_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiiiiissssi", $company_name, $report_date, $zyh_startup_date, $phm_startup_date, $no_lti_days_zyh, $no_lti_days_phm, $total_bsoc_cards, $safe_cards, $unsafe_bsoc, $best_bsoc_title, $best_bsoc_description, $total_monthly_bsoc_cards, $doctor, $prepared_by);
        $stmt->execute();
        $report_id = $conn->insert_id;

        // Handle BSOC card reports
        if (isset($_POST['observation'])) {
            foreach ($_POST['observation'] as $obs) {
                $entry_number = $obs['no'] ?? null;
                $category = $obs['category'] ?? null;
                $description = $obs['description'] ?? null;
                $action_taken = null; // Placeholder, can be added later
                $observer = null; // Placeholder, can be added later
                $stmt = $conn->prepare("INSERT INTO bsoc_card_reports (report_id, entry_number, category, description, action_taken, observer) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissss", $report_id, $entry_number, $category, $description, $action_taken, $observer);
                $stmt->execute();
            }
        }

        // Handle BSOC monthly contribution
        for ($i = 0; $i < count($entities); $i++) {
            $entity_name = $entities[$i] ?? null;
            $hazard = $hazards[$i] ?? 0;
            $unsafe_act = $unsafe_acts[$i] ?? 0;
            $near_miss = $near_misses[$i] ?? 0;
            $safe_work = $safe_works[$i] ?? 0;
            $total = $totals[$i] ?? 0;
            $percentage = 0; // Calculate if needed
            $stmt = $conn->prepare("INSERT INTO bsoc_monthly_contribution (report_id, entity_name, hazard, unsafe_act, near_miss, safe_work, total, percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isiiiiid", $report_id, $entity_name, $hazard, $unsafe_act, $near_miss, $safe_work, $total, $percentage);
            $stmt->execute();
        }

        // Handle HSE Summary
        foreach ($activities as $index => $activity) {
            if (!empty($activity)) {
                $stmt = $conn->prepare("INSERT INTO hse_summary (report_id, activity_description) VALUES (?, ?)");
                $stmt->bind_param("is", $report_id, $activity);
                $stmt->execute();
            }
        }
        // Redirect to tables-datatable.php after successful submission
        header("Location: tables-datatable.php");
        exit();
    }
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
                                    <form method="post" action="backend/process_form.php" id="mainReportForm">
                                        <div class="tab-content twitter-bs-wizard-tab-content">
                                            <div class="tab-pane" id="progress-seller-details">
                                                <div class="text-center mb-4">
                                                    <h5>Daily Report Form</h5>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="company_name" class="form-label">Company Name</label>
                                                            <input type="text" class="form-control" id="company_name" name="company_name" value="GWDC Indonesia">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="report_date" class="form-label">Report Date</label>
                                                            <input type="date" class="form-control" id="report_date" name="report_date" value="<?php echo date('Y-m-d'); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="zyh_startup_date" class="form-label">ZYM Start-up Date</label>
                                                            <input type="date" class="form-control" id="zyh_startup_date" name="zyh_startup_date" value="2025-06-01">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="phm_startup_date" class="form-label">PHM Start-up Date</label>
                                                            <input type="date" class="form-control" id="phm_startup_date" name="phm_startup_date" value="2025-06-15">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="no_lti_days_zyh" class="form-label">No LTI Days (ZYM)</label>
                                                            <input type="number" class="form-control" id="no_lti_days_zyh" name="no_lti_days_zyh" value="150">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="no_lti_days_phm" class="form-label">No LTI Days (PHM)</label>
                                                            <input type="number" class="form-control" id="no_lti_days_phm" name="no_lti_days_phm" value="145">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="total_bsoc_cards" class="form-label">Total BSOC Cards</label>
                                                            <input type="number" class="form-control" id="total_bsoc_cards" name="total_bsoc_cards" value="20">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="safe_cards" class="form-label">Safe Cards</label>
                                                            <input type="number" class="form-control" id="safe_cards" name="safe_cards" value="15">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="mb-3">
                                                            <label for="unsafe_bsoc" class="form-label">Unsafe BSOC</label>
                                                            <input type="number" class="form-control" id="unsafe_bsoc" name="unsafe_bsoc" value="5">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <label for="best_bsoc_title" class="form-label">Best BSOC Title</label>
                                                            <input type="text" class="form-control" id="best_bsoc_title" name="best_bsoc_title" value="Safety Equipment Usage">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <label for="best_bsoc_description" class="form-label">Best BSOC Description</label>
                                                            <textarea class="form-control" id="best_bsoc_description" name="best_bsoc_description" rows="3">Ensured all workers used proper PPE during operations.</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="total_monthly_bsoc_cards" class="form-label">Total Monthly BSOC Cards</label>
                                                            <input type="number" class="form-control" id="total_monthly_bsoc_cards" name="total_monthly_bsoc_cards" value="80">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="mb-3">
                                                            <label for="prepared_by" class="form-label">Prepared By</label>
                                                            <input type="text" class="form-control" id="prepared_by" name="prepared_by" value="John Doe">
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
                                                                <tr>
                                                                    <td><input type="number" class="form-control" name="observation[0][no]" value="1"></td>
                                                                    <td>
                                                                        <select class="form-control" name="observation[0][category]">
                                                                            <option value="U/C" selected>U/C</option>
                                                                            <option value="U/A">U/A</option>
                                                                        </select>
                                                                    </td>
                                                                    <td><input type="text" class="form-control" name="observation[0][description]" value="Uncontrolled equipment movement"></td>
                                                                </tr>
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
                                                        <tr>
                                                            <td><input type="text" class="form-control" name="entity[0]" value="GWDC" readonly></td>
                                                            <td><input type="number" class="form-control" name="hazard[0]" value="2"></td>
                                                            <td><input type="number" class="form-control" name="unsafe_act[0]" value="1"></td>
                                                            <td><input type="number" class="form-control" name="near_miss[0]" value="0"></td>
                                                            <td><input type="number" class="form-control" name="safe_work[0]" value="5"></td>
                                                            <td><input type="text" class="form-control" name="total[0]" value="8" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="text" class="form-control" name="entity[1]" value="Client" readonly></td>
                                                            <td><input type="number" class="form-control" name="hazard[1]" value="1"></td>
                                                            <td><input type="number" class="form-control" name="unsafe_act[1]" value="0"></td>
                                                            <td><input type="number" class="form-control" name="near_miss[1]" value="1"></td>
                                                            <td><input type="number" class="form-control" name="safe_work[1]" value="3"></td>
                                                            <td><input type="text" class="form-control" name="total[1]" value="5" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="text" class="form-control" name="entity[2]" value="3rd Party" readonly></td>
                                                            <td><input type="number" class="form-control" name="hazard[2]" value="0"></td>
                                                            <td><input type="number" class="form-control" name="unsafe_act[2]" value="2"></td>
                                                            <td><input type="number" class="form-control" name="near_miss[2]" value="0"></td>
                                                            <td><input type="number" class="form-control" name="safe_work[2]" value="4"></td>
                                                            <td><input type="text" class="form-control" name="total[2]" value="6" readonly></td>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="text" class="form-control" name="entity[3]" value="Catering" readonly></td>
                                                            <td><input type="number" class="form-control" name="hazard[3]" value="0"></td>
                                                            <td><input type="number" class="form-control" name="unsafe_act[3]" value="0"></td>
                                                            <td><input type="number" class="form-control" name="near_miss[3]" value="1"></td>
                                                            <td><input type="number" class="form-control" name="safe_work[3]" value="2"></td>
                                                            <td><input type="text" class="form-control" name="total[3]" value="3" readonly></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="mb-3">
                                                            <label class="form-label">Concern</label>
                                                            <textarea class="form-control" id="concern" name="concern" rows="3" placeholder="Concern details">Need to improve equipment safety checks.</textarea>
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
                                                            <tr>
                                                                <td class="text-center align-middle">1.</td>
                                                                <td><input type="text" class="form-control" name="activity[1]" value="Attended GWDC and PHM supervisor morning meeting."></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-center align-middle">2.</td>
                                                                <td><input type="text" class="form-control" name="activity[2]" value="Attended and lead pre-tour meeting for day and night crew."></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="text-center align-middle">3.</td>
                                                                <td><input type="text" class="form-control" name="activity[3]" value="Daily walk through at the cement silo area."></td>
                                                            </tr>
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
                                                            <input type="text" class="form-control" id="doctor" name="doctor" value="Dr. Smith">
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
<?php
include 'layouts/session.php';
include 'layouts/head-main.php';

// Database connection
$conn = new mysqli("localhost", "root", "", "gdap");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entities = $_POST["entity"] ?? [];
    $hazards = $_POST["hazard"] ?? [];
    $unsafe_acts = $_POST["unsafe_act"] ?? [];
    $near_misses = $_POST["near_miss"] ?? [];
    $safe_works = $_POST["safe_work"] ?? [];
    $totals = $_POST["total"] ?? [];
    $activities = $_POST["activity"] ?? [];

    // Handle daily report creation
    if (isset($_POST['submit_daily_report'])) {
        $company_name = $_POST['company_name'] ?? null;
        $report_date = $_POST['report_date'] ?? date('Y-m-d');
        $zyh_startup_date = $_POST['zyh_startup_date'] ?? null;
        $phm_startup_date = $_POST['phm_startup_date'] ?? null;
        $no_lti_days_zyh = $_POST['no_lti_days_zyh'] ?? null;
        $no_lti_days_phm = $_POST['no_lti_days_phm'] ?? null;
        $total_bsoc_cards = $_POST['total_bsoc_cards'] ?? null;
        $safe_cards = $_POST['safe_cards'] ?? null;
        $unsafe_bsoc = $_POST['unsafe_bsoc'] ?? null;
        $best_bsoc_title = $_POST['best_bsoc_title'] ?? null;
        $best_bsoc_description = $_POST['best_bsoc_description'] ?? null;
        $total_monthly_bsoc_cards = $_POST['total_monthly_bsoc_cards'] ?? null;
        $doctor = $_POST['doctor'] ?? null; // Added doctor field
        $prepared_by = $_POST['prepared_by'] ?? null;

        $stmt = $conn->prepare("INSERT INTO daily_reports (company_name, report_date, zyh_startup_date, phm_startup_date, no_lti_days_zyh, no_lti_days_phm, total_bsoc_cards, safe_cards, unsafe_bsoc, best_bsoc_title, best_bsoc_description, total_monthly_bsoc_cards, doctor, prepared_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiiiiissssi", $company_name, $report_date, $zyh_startup_date, $phm_startup_date, $no_lti_days_zyh, $no_lti_days_phm, $total_bsoc_cards, $safe_cards, $unsafe_bsoc, $best_bsoc_title, $best_bsoc_description, $total_monthly_bsoc_cards, $doctor, $prepared_by);
        $stmt->execute();
        $report_id = $conn->insert_id;

        // Handle BSOC card reports
        if (isset($_POST['observation'])) {
            foreach ($_POST['observation'] as $obs) {
                $entry_number = $obs['no'] ?? null;
                $category = $obs['category'] ?? null;
                $description = $obs['description'] ?? null;
                $action_taken = null; // Placeholder, can be added later
                $observer = null; // Placeholder, can be added later
                $stmt = $conn->prepare("INSERT INTO bsoc_card_reports (report_id, entry_number, category, description, action_taken, observer) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissss", $report_id, $entry_number, $category, $description, $action_taken, $observer);
                $stmt->execute();
            }
        }

        // Handle BSOC monthly contribution
        for ($i = 0; $i < count($entities); $i++) {
            $entity_name = $entities[$i] ?? null;
            $hazard = $hazards[$i] ?? 0;
            $unsafe_act = $unsafe_acts[$i] ?? 0;
            $near_miss = $near_misses[$i] ?? 0;
            $safe_work = $safe_works[$i] ?? 0;
            $total = $totals[$i] ?? 0;
            $percentage = 0; // Calculate if needed
            $stmt = $conn->prepare("INSERT INTO bsoc_monthly_contribution (report_id, entity_name, hazard, unsafe_act, near_miss, safe_work, total, percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isiiiiid", $report_id, $entity_name, $hazard, $unsafe_act, $near_miss, $safe_work, $total, $percentage);
            $stmt->execute();
        }

        // Handle HSE Summary
        foreach ($activities as $index => $activity) {
            if (!empty($activity)) {
                $stmt = $conn->prepare("INSERT INTO hse_summary (report_id, activity_description) VALUES (?, ?)");
                $stmt->bind_param("is", $report_id, $activity);
                $stmt->execute();
            }
        }
        // Redirect to tables-datatable.php after successful submission
        header("Location: tables-datatable.php");
        exit();
    }
}
?>

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
