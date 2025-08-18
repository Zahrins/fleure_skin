<?php
// Mulai session
session_start();

// Hapus semua variabel session
$_SESSION = array();

// Jika menggunakan cookie session, hapus juga cookie-nya
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Hapus cookie login jika ada (opsional)
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// Redirect ke halaman login atau home
header("Location: login.php");
exit();
?>