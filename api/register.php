<?php
// api/register.php
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

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user = $_SESSION['user'];
$action = $data['action'] ?? '';

if ($action === 'register_course') {
    if ($user['role'] !== 'student') {
        echo json_encode(['status' => 'error', 'message' => 'Only students can register for courses']);
        exit();
    }

    $course_id = (int)($data['course_id'] ?? 0);

    if ($course_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid course ID']);
        exit();
    }

    // 1. Fetch course details
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch();

    if (!$course) {
        echo json_encode(['status' => 'error', 'message' => 'Course not found']);
        exit();
    }

    // 2. Check if already registered
    $stmt = $pdo->prepare("SELECT id FROM registrations WHERE user_id = ? AND course_id = ? AND status = 'registered'");
    $stmt->execute([$user['id'], $course_id]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Already registered for this course']);
        exit();
    }

    // 3. Check Prerequisites
    $stmt = $pdo->prepare("SELECT prerequisite_course_id, c.code FROM prerequisites p JOIN courses c ON p.prerequisite_course_id = c.id WHERE p.course_id = ?");
    $stmt->execute([$course_id]);
    $prereqs = $stmt->fetchAll();

    foreach ($prereqs as $prereq) {
        $stmt_check = $pdo->prepare("SELECT id FROM completed_courses WHERE user_id = ? AND course_id = ? AND passed = 1");
        $stmt_check->execute([$user['id'], $prereq['prerequisite_course_id']]);
        if (!$stmt_check->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Missing prerequisite: ' . $prereq['code']]);
            exit();
        }
    }

    // 4. Check Credit Limits
    $stmt = $pdo->prepare("SELECT SUM(c.credits) as total_credits FROM registrations r JOIN courses c ON r.course_id = c.id WHERE r.user_id = ? AND r.status = 'registered'");
    $stmt->execute([$user['id']]);
    $current_credits = (int)$stmt->fetchColumn();

    if ($current_credits + $course['credits'] > $user['max_credits']) {
        echo json_encode(['status' => 'error', 'message' => 'Credit limit exceeded. Max: ' . $user['max_credits'] . ', Current: ' . $current_credits . ', Required: ' . $course['credits']]);
        exit();
    }

    // 5. Check Timetable Clashes
    $stmt = $pdo->prepare("
        SELECT c.code 
        FROM registrations r 
        JOIN courses c ON r.course_id = c.id 
        WHERE r.user_id = ? 
        AND r.status = 'registered' 
        AND c.schedule_day = ? 
        AND ((? >= c.start_time AND ? < c.end_time) OR (? > c.start_time AND ? <= c.end_time) OR (? <= c.start_time AND ? >= c.end_time))
    ");
    $stmt->execute([
        $user['id'], 
        $course['schedule_day'], 
        $course['start_time'], $course['start_time'], 
        $course['end_time'], $course['end_time'],
        $course['start_time'], $course['end_time']
    ]);
    
    $clash = $stmt->fetch();
    if ($clash) {
        echo json_encode(['status' => 'error', 'message' => 'Timetable clash with ' . $clash['code']]);
        exit();
    }

    // 6. Check Capacity
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE course_id = ? AND status = 'registered'");
    $stmt->execute([$course_id]);
    $enrolled = (int)$stmt->fetchColumn();

    if ($enrolled >= $course['capacity']) {
        echo json_encode(['status' => 'error', 'message' => 'Course is full']);
        exit();
    }

    // 7. Register
    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, course_id, status) VALUES (?, ?, 'registered')");
    if ($stmt->execute([$user['id'], $course_id])) {
        echo json_encode(['status' => 'success', 'message' => 'Successfully registered for ' . $course['code']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to register']);
    }
    exit();
}

if ($action === 'drop_course') {
    $course_id = (int)($data['course_id'] ?? 0);
    
    $stmt = $pdo->prepare("UPDATE registrations SET status = 'dropped' WHERE user_id = ? AND course_id = ? AND status = 'registered'");
    $stmt->execute([$user['id'], $course_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Course dropped successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Course not found in active registrations']);
    }
    exit();
}

if ($action === 'my_schedule') {
    $stmt = $pdo->prepare("
        SELECT r.id as registration_id, c.* 
        FROM registrations r 
        JOIN courses c ON r.course_id = c.id 
        WHERE r.user_id = ? AND r.status = 'registered'
    ");
    $stmt->execute([$user['id']]);
    $schedule = $stmt->fetchAll();
    
    echo json_encode(['status' => 'success', 'schedule' => $schedule]);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>
