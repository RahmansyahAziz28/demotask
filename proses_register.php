<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

csrf_verify();

$badge_number     = trim($_POST['badge_number'] ?? '');
$full_name        = trim($_POST['full_name'] ?? '');
$rank             = trim($_POST['rank'] ?? '');
$department       = trim($_POST['department'] ?? '');
$phone            = trim($_POST['phone'] ?? '');
$email            = trim($_POST['email'] ?? '');
$password         = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

if ($badge_number === '') {
    $errors['badge_number'] = 'Badge number is required.';
}
if ($full_name === '') {
    $errors['full_name'] = 'Full name is required.';
}
if ($rank === '') {
    $errors['rank'] = 'Rank is required.';
}
if ($department === '') {
    $errors['department'] = 'Department unit is required.';
}
if ($email === '') {
    $errors['email'] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email address format.';
}
if (strlen($password) < 6) {
    $errors['password'] = 'Password must be at least 6 characters.';
}
if ($password !== $confirm_password) {
    $errors['confirm_password'] = 'Password confirmation does not match.';
}

if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_old']    = $_POST;
    header('Location: register.php');
    exit;
}

try {
    $db = getDB();

    $checkBadge = $db->prepare("SELECT COUNT(*) FROM investigators WHERE UPPER(badge_number) = UPPER(:badge)");
    $checkBadge->execute([':badge' => $badge_number]);
    if ($checkBadge->fetchColumn() > 0) {
        $errors['badge_number'] = 'Badge number "' . e($badge_number) . '" is already registered.';
    }

    $checkEmail = $db->prepare("SELECT COUNT(*) FROM investigators WHERE LOWER(email) = LOWER(:email)");
    $checkEmail->execute([':email' => $email]);
    if ($checkEmail->fetchColumn() > 0) {
        $errors['email'] = 'Email "' . e($email) . '" is already registered to another officer.';
    }

    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_old']    = $_POST;
        header('Location: register.php');
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("
        INSERT INTO investigators (badge_number, full_name, rank, department, phone, email, password)
        VALUES (:badge, :name, :rank, :dept, :phone, :email, :password)
    ");
    $stmt->execute([
        ':badge'    => $badge_number,
        ':name'     => $full_name,
        ':rank'     => $rank,
        ':dept'     => $department,
        ':phone'    => $phone !== '' ? $phone : null,
        ':email'    => $email,
        ':password' => $hashed_password,
    ]);

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => 'Investigator account successfully registered! Please sign in using your credentials.'
    ];
    $_SESSION['old_login_id'] = $badge_number;

    header('Location: login.php');
    exit;

} catch (PDOException $e) {
    error_log('Registration DB Error: ' . $e->getMessage());
    $_SESSION['register_errors'] = ['_general' => 'A database error occurred during enrollment: ' . $e->getMessage()];
    $_SESSION['register_old']    = $_POST;
    header('Location: register.php');
    exit;
}
