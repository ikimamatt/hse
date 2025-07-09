<?php
include '../layouts/session.php';
include '../layouts/head-main.php';
require_once '../layouts/conf.php'; // Assuming conf.php contains PDO connection

// Check if the request is POST for submission/update or GET for delete
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle daily report creation/update
    if (isset($_POST['submit_daily_report'])) {
        $report_id = $_POST['report_id'] ?? null; // For updates

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
        $concern = $_POST['concern'] ?? null; // Added concern field

        if ($report_id) {
            // Update existing daily report
            $stmt = $pdo->prepare("UPDATE daily_reports SET company_name = ?, report_date = ?, zyh_startup_date = ?, phm_startup_date = ?, no_lti_days_zyh = ?, no_lti_days_phm = ?, total_bsoc_cards = ?, safe_cards = ?, unsafe_bsoc = ?, best_bsoc_title = ?, best_bsoc_description = ?, total_monthly_bsoc_cards = ?, doctor = ?, prepared_by = ?, concerns = ? WHERE id = ?");
            $stmt->execute([$company_name, $report_date, $zyh_startup_date, $phm_startup_date, $no_lti_days_zyh, $no_lti_days_phm, $total_bsoc_cards, $safe_cards, $unsafe_bsoc, $best_bsoc_title, $best_bsoc_description, $total_monthly_bsoc_cards, $doctor, $prepared_by, $concern, $report_id]);

            // Delete existing related records before inserting new ones for update
            $pdo->prepare("DELETE FROM bsoc_card_reports WHERE report_id = ?")->execute([$report_id]);
            $pdo->prepare("DELETE FROM bsoc_monthly_contribution WHERE report_id = ?")->execute([$report_id]);
            $pdo->prepare("DELETE FROM hse_summary WHERE report_id = ?")->execute([$report_id]);

        } else {
            // Insert new daily report
            $stmt = $pdo->prepare("INSERT INTO daily_reports (company_name, report_date, zyh_startup_date, phm_startup_date, no_lti_days_zyh, no_lti_days_phm, total_bsoc_cards, safe_cards, unsafe_bsoc, best_bsoc_title, best_bsoc_description, total_monthly_bsoc_cards, doctor, prepared_by, concerns) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$company_name, $report_date, $zyh_startup_date, $phm_startup_date, $no_lti_days_zyh, $no_lti_days_phm, $total_bsoc_cards, $safe_cards, $unsafe_bsoc, $best_bsoc_title, $best_bsoc_description, $total_monthly_bsoc_cards, $doctor, $prepared_by, $concern]);
            $report_id = $pdo->lastInsertId();
        }

        // Handle BSOC card reports
        if (isset($_POST['observation'])) {
            foreach ($_POST['observation'] as $obs) {
                $entry_number = $obs['no'] ?? null;
                $category = $obs['category'] ?? null;
                $description = $obs['description'] ?? null;
                $action_taken = $obs['action_taken'] ?? null; // Assuming these might be added later
                $observer = $obs['observer'] ?? null; // Assuming these might be added later
                $stmt = $pdo->prepare("INSERT INTO bsoc_card_reports (report_id, entry_number, category, description, action_taken, observer) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$report_id, $entry_number, $category, $description, $action_taken, $observer]);
            }
        }

        // Handle BSOC monthly contribution
        $entities = $_POST["entity"] ?? [];
        $hazards = $_POST["hazard"] ?? [];
        $unsafe_acts = $_POST["unsafe_act"] ?? [];
        $near_misses = $_POST["near_miss"] ?? [];
        $safe_works = $_POST["safe_work"] ?? [];
        $totals = $_POST["total"] ?? [];
        
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
        $activities = $_POST["activity"] ?? [];
        foreach ($activities as $index => $activity) {
            if (!empty($activity)) {
                $stmt = $pdo->prepare("INSERT INTO hse_summary (report_id, activity_description) VALUES (?, ?)");
                $stmt->execute([$report_id, $activity]);
            }
        }

        header("Location: ../tables-datatable.php?status=success");
        exit();
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action'])) {
    $action = $_GET['action'];
    $report_id = $_GET['id'] ?? null;

    if (!$report_id) {
        header("Location: ../tables-datatable.php?status=error&message=" . urlencode("Report ID is missing."));
        exit();
    }

    if ($action == 'delete') {
        try {
            $pdo->beginTransaction();

            // Delete related records first due to foreign key constraints
            $pdo->prepare("DELETE FROM bsoc_card_reports WHERE report_id = ?")->execute([$report_id]);
            $pdo->prepare("DELETE FROM bsoc_monthly_contribution WHERE report_id = ?")->execute([$report_id]);
            $pdo->prepare("DELETE FROM hse_summary WHERE report_id = ?")->execute([$report_id]);

            // Delete the main daily report
            $stmt = $pdo->prepare("DELETE FROM daily_reports WHERE id = ?");
            $stmt->execute([$report_id]);

            $pdo->commit();
            header("Location: ../tables-datatable.php?status=success&message=" . urlencode("Report deleted successfully."));
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            header("Location: ../tables-datatable.php?status=error&message=" . urlencode("Failed to delete report: " . $e->getMessage()));
            exit();
        }
    } elseif ($action == 'edit') {
        // Redirect to form-wizard.php with report_id for pre-filling the form
        header("Location: ../form-wizard.php?report_id=" . $report_id);
        exit();
    }
}

// If no specific action or method, redirect to a default page
header("Location: ../tables-datatable.php?status=error&message=" . urlencode("Invalid request."));
exit();
?>
