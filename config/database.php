<?php
$db_host = getenv('DB_HOST') ?: 'aws-0-ap-southeast-1.pooler.supabase.com';
$db_port = getenv('DB_PORT') ?: '5432';
$db_name = getenv('DB_NAME') ?: 'postgres';
$db_user = getenv('DB_USER') ?: 'postgres.bkfylzfwsdlhfgjohlqi';
$db_pass = getenv('DB_PASS') ?: 'Sakkarep123!';

function getDB(): PDO {
    global $db_host, $db_port, $db_name, $db_user, $db_pass;

    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = "pgsql:host={$db_host};port={$db_port};dbname={$db_name}";

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        error_log('DB Connection failed: ' . $e->getMessage());
        die('
<!DOCTYPE html><html><head><title>Connection Error</title>
<style>body{background:#0B0F14;color:#E8EDF2;font-family:monospace;
display:flex;align-items:center;justify-content:center;height:100vh;margin:0;}
.box{background:#18212B;border:1px solid #B94A48;border-radius:6px;padding:2rem;max-width:480px;text-align:center;}
h2{color:#B94A48;margin:0 0 1rem;}p{color:#91A0AE;line-height:1.6;}</style></head>
<body><div class="box"><h2>Database Offline</h2>
<p>Gotham Crime Records could not connect to the database.<br>
Check your credentials in <code>config/database.php</code> and ensure PostgreSQL is running.</p></div></body></html>');
    }

    return $pdo;
}
