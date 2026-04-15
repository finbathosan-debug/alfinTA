<?php
// Logout: hentikan session dan redirect ke halaman login
session_start();
// Kosongkan semua variabel sesi
$_SESSION = [];
// Hancurkan session di server
session_unset();
session_destroy();
// Redirect ke form login
header("Location: form_login_alfin.php");
exit;
?> 