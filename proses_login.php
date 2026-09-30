<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$identity = trim($_POST['identity'] ?? '');
$password = $_POST['password'] ?? '';

if ($identity === '' || $password === '') {
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg'  => 'Please provide both Badge/Email and your Password.'
    ];
    $_SESSION['old_login_id'] = $identity;
    header('Location: login.php');
    exit;
}

try {
    $db = getDB();

    $stmt = $db->prepare("
        SELECT investigator_id, badge_number, full_name, rank, department, phone, email, password
        FROM investigators
        WHERE LOWER(email) = LOWER(:identity) 
           OR UPPER(badge_number) = UPPER(:identity)
        LIMIT 1
    ");
    $stmt->execute([':identity' => $identity]);
    $user = $stmt->fetch();

    if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id']      = $user['investigator_id'];
        $_SESSION['user_name']    = $user['full_name'];
        $_SESSION['badge_number'] = $user['badge_number'];
        $_SESSION['rank']         = $user['rank'];
        $_SESSION['department']   = $user['department'];
        $_SESSION['email']        = $user['email'];

        $_SESSION['flash'] = [
            'type' => 'success',
            'msg'  => 'Access granted. Welcome back, ' . htmlspecialchars($user['rank']) . ' ' . htmlspecialchars($user['full_name']) . '.'
        ];

        header('Location: index.php');
        exit;
    } else {
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'Invalid credentials. Please verify your Badge Number/Email and Password.'
        ];
        $_SESSION['old_login_id'] = $identity;
        header('Location: login.php');
        exit;
    }

} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    $_SESSION['flash'] = [
        'type' => 'error',
        'msg'  => 'A system error occurred during authentication. Please try again.'
    ];
    $_SESSION['old_login_id'] = $identity;
    header('Location: login.php');
    exit;
}
