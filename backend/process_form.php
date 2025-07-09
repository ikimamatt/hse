<?php
include '../layouts/session.php';
include '../layouts/head-main.php';
require_once '../layouts/conf.php'; // Assuming conf.php contains PDO connection

// Database connection (using PDO from conf.php)
// $conn = new mysqli("localhost", "root", "", "gdap"); // Old mysqli connection, replaced by PDO

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

        $stmt = $pdo->prepare("INSERT INTO daily_reports (company_name, report_date, zyh_startup_date, phm_startup_date, no_lti_days_zyh, no_lti_days_phm, total_bsoc_cards, safe_cards, unsafe_bsoc, best_bsoc_title, best_bsoc_description, total_monthly_bsoc_cards, doctor, prepared_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$company_name, $report_date, $zyh_startup_date, $phm_startup_date, $no_lti_days_zyh, $no_lti_days_phm, $total_bsoc_cards, $safe_cards, $unsafe_bsoc, $best_bsoc_title, $best_bsoc_description, $total_monthly_bsoc_cards, $doctor, $prepared_by]);
        $report_id = $pdo->lastInsertId();

        // Handle BSOC card reports
        if (isset($_POST['observation'])) {
            foreach ($_POST['observation'] as $obs) {
                $entry_number = $obs['no'] ?? null;
                $category = $obs['category'] ?? null;
                $description = $obs['description'] ?? null;
                $action_taken = null; // Placeholder, can be added later
                $observer = null; // Placeholder, can be added later
                $stmt = $pdo->prepare("INSERT INTO bsoc_card_reports (report_id, entry_number, category, description, action_taken, observer) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$report_id, $entry_number, $category, $description, $action_taken, $observer]);
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
            $stmt = $pdo->prepare("INSERT INTO bsoc_monthly_contribution (report_id, entity_name, hazard, unsafe_act, near_miss, safe_work, total, percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$report_id, $entity_name, $hazard, $unsafe_act, $near_miss, $safe_work, $total, $percentage]);
        }

        // Handle HSE Summary
        foreach ($activities as $index => $activity) {
            if (!empty($activity)) {
                $stmt = $pdo->prepare("INSERT INTO hse_summary (report_id, activity_description) VALUES (?, ?)");
                $stmt->execute([$report_id, $activity]);
            }
        }
        // Redirect to tables-datatable.php after successful submission
        header("Location: ../tables-datatable.php");
        exit();
    }
}
?>
