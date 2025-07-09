<?php
// backend/hse_daily_report.php

// Include necessary files
include '../layouts/session.php'; // For session management (adjust if not needed)
include '../layouts/head-main.php'; // For any shared configuration (adjust if not needed)

// Database connection
$conn = new mysqli("localhost", "root", "", "gdap");
if ($conn->connect_error) {
    header("Location: ../hse_form.php?status=error&message=" . urlencode("Database connection failed"));
    exit();
}

// Check if the request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_daily_report'])) {
    // Collect form data
    $entities = $_POST["entity"] ?? [];
    $hazards = $_POST["hazard"] ?? [];
    $unsafe_acts = $_POST["unsafe_act"] ?? [];
    $near_misses = $_POST["near_miss"] ?? [];
    $safe_works = $_POST["safe_work"] ?? [];
    $totals = $_POST["total"] ?? [];
    $activities = $_POST["activity"] ?? [];

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
    $doctor = $_POST['doctor'] ?? null;
    $prepared_by = $_POST['prepared_by'] ?? null;

    // Insert into daily_reports table
    $stmt = $conn->prepare("INSERT INTO daily_reports (company_name, report_date, zyh_startup_date, phm_startup_date, no_lti_days_zyh, no_lti_days_phm, total_bsoc_cards, safe_cards, unsafe_bsoc, best_bsoc_title, best_bsoc_description, total_monthly_bsoc_cards, doctor, prepared_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiiiisssiss", $company_name, $report_date, $zyh_startup_date, $phm_startup_date, $no_lti_days_zyh, $no_lti_days_phm, $total_bsoc_cards, $safe_cards, $unsafe_bsoc, $best_bsoc_title, $best_bsoc_description, $total_monthly_bsoc_cards, $doctor, $prepared_by);
    
    if (!$stmt->execute()) {
        header("Location: ../hse_form.php?status=error&message=" . urlencode("Failed to insert daily report"));
        exit();
    }
    $report_id = $conn->insert_id;

    // Insert into bsoc_card_reports table
    if (isset($_POST['observation'])) {
        foreach ($_POST['observation'] as $obs) {
            $entry_number = $obs['no'] ?? null;
            $category = $obs['category'] ?? null;
            $description = $obs['description'] ?? null;
            $action_taken = null;
            $observer = null;
            $stmt = $conn->prepare("INSERT INTO bsoc_card_reports (report_id, entry_number, category, description, action_taken, observer) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iissss", $report_id, $entry_number, $category, $description, $action_taken, $observer);
            if (!$stmt->execute()) {
                header("Location: ../hse_form.php?status=error&message=" . urlencode("Failed to insert BSOC card report"));
                exit();
            }
        }
    }

    // Insert into bsoc_monthly_contribution table
    for ($i = 0; $i < count($entities); $i++) {
        $entity_name = $entities[$i] ?? null;
        $hazard = $hazards[$i] ?? 0;
        $unsafe_act = $unsafe_acts[$i] ?? 0;
        $near_miss = $near_misses[$i] ?? 0;
        $safe_work = $safe_works[$i] ?? 0;
        $total = $totals[$i] ?? 0;
        $percentage = 0;
        $stmt = $conn->prepare("INSERT INTO bsoc_monthly_contribution (report_id, entity_name, hazard, unsafe_act, near_miss, safe_work, total, percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiiiiid", $report_id, $entity_name, $hazard, $unsafe_act, $near_miss, $safe_work, $total, $percentage);
        if (!$stmt->execute()) {
            header("Location: ../hse_form.php?status=error&message=" . urlencode("Failed to insert monthly contribution"));
            exit();
        }
    }

    // Insert into hse_summary table
    foreach ($activities as $index => $activity) {
        if (!empty($activity)) {
            $stmt = $conn->prepare("INSERT INTO hse_summary (report_id, activity_description) VALUES (?, ?)");
            $stmt->bind_param("is", $report_id, $activity);
            if (!$stmt->execute()) {
                header("Location: ../hse_form.php?status=error&message=" . urlencode("Failed to insert HSE summary"));
                exit();
            }
        }
    }

    // Redirect on success
    header("Location: ../hse_form.php?status=success");
    exit();
} else {
    // Handle non-POST requests or missing submit_daily_report
    header("Location: ../hse_form.php?status=error&message=" . urlencode("Invalid request"));
    exit();
}

// Close the database connection
$conn->close();
?>