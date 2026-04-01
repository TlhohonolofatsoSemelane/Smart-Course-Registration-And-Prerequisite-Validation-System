<?php
// api/admin.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    if (isset($_GET['action'])) {
        $data = $_GET;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        exit();
    }
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Admin privileges required']);
    exit();
}

$action = $data['action'] ?? '';

if ($action === 'course_enrollment_report') {
    $stmt = $pdo->query("
        SELECT 
            c.code, 
            c.name, 
            c.capacity,
            COUNT(CASE WHEN r.status = 'registered' THEN 1 END) as enrolled,
            c.capacity - COUNT(CASE WHEN r.status = 'registered' THEN 1 END) as available_seats
        FROM courses c
        LEFT JOIN registrations r ON c.id = r.course_id
        GROUP BY c.id
        ORDER BY c.code ASC
    ");
    $report = $stmt->fetchAll();
    
    echo json_encode(['status' => 'success', 'report' => $report]);
    exit();
}

if ($action === 'student_registrations') {
    $stmt = $pdo->query("
        SELECT 
            u.id as user_id,
            u.name as student_name, 
            u.email,
            COUNT(r.id) as registered_courses,
            COALESCE(SUM(c.credits), 0) as total_credits
        FROM users u
        LEFT JOIN registrations r ON u.id = r.user_id AND r.status = 'registered'
        LEFT JOIN courses c ON r.course_id = c.id
        WHERE u.role = 'student'
        GROUP BY u.id
        ORDER BY u.name ASC
    ");
    $students = $stmt->fetchAll();
    
    echo json_encode(['status' => 'success', 'students' => $students]);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>
