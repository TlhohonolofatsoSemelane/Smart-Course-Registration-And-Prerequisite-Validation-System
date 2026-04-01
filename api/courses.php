<?php
// api/courses.php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    if (isset($_GET['action'])) {
        $data = $_GET;
    } elseif (isset($_POST['action'])) {
        $data = $_POST;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        exit();
    }
}

$action = $data['action'] ?? '';

// Allow anyone (even non-logged in or students) to fetch available courses
if ($action === 'fetch_all') {
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY code ASC");
    $courses = $stmt->fetchAll();
    
    // Fetch prerequisites for each course
    foreach ($courses as &$course) {
        $stmt_prereq = $pdo->prepare("
            SELECT p.prerequisite_course_id, c.code, c.name 
            FROM prerequisites p 
            JOIN courses c ON p.prerequisite_course_id = c.id 
            WHERE p.course_id = ?
        ");
        $stmt_prereq->execute([$course['id']]);
        $course['prerequisites'] = $stmt_prereq->fetchAll();
    }

    echo json_encode(['status' => 'success', 'courses' => $courses]);
    exit();
}

// Ensure the user is logged in for the following actions
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user = $_SESSION['user'];

// Ensure user is admin for the following actions
if ($user['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Admin privileges required']);
    exit();
}

if ($action === 'add_course') {
    $code = trim($data['code'] ?? '');
    $name = trim($data['name'] ?? '');
    $credits = (int)($data['credits'] ?? 0);
    $capacity = (int)($data['capacity'] ?? 0);
    $schedule_day = $data['schedule_day'] ?? '';
    $start_time = $data['start_time'] ?? '';
    $end_time = $data['end_time'] ?? '';

    if (empty($code) || empty($name) || $credits <= 0 || $capacity <= 0 || empty($schedule_day) || empty($start_time) || empty($end_time)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required and must be valid']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO courses (code, name, credits, capacity, schedule_day, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $name, $credits, $capacity, $schedule_day, $start_time, $end_time]);
        echo json_encode(['status' => 'success', 'message' => 'Course added successfully']);
    } catch(PDOException $e) {
        if ($e->getCode() == 23000) { // Duplicate entry
            echo json_encode(['status' => 'error', 'message' => 'Course code already exists']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
    exit();
}

if ($action === 'delete_course') {
    $course_id = (int)($data['course_id'] ?? 0);
    if ($course_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid course ID']);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    echo json_encode(['status' => 'success', 'message' => 'Course deleted successfully']);
    exit();
}

if ($action === 'add_prerequisite') {
    $course_id = (int)($data['course_id'] ?? 0);
    $prerequisite_course_id = (int)($data['prerequisite_course_id'] ?? 0);

    if ($course_id <= 0 || $prerequisite_course_id <= 0 || $course_id === $prerequisite_course_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid course or prerequisite']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO prerequisites (course_id, prerequisite_course_id) VALUES (?, ?)");
        $stmt->execute([$course_id, $prerequisite_course_id]);
        echo json_encode(['status' => 'success', 'message' => 'Prerequisite added successfully']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add prerequisite. Maybe it already exists?']);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>
