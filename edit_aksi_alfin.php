<?php
include 'koneksi_alfin.php';
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: form_login_alfin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pengguna_alfin.php");
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$nama = trim($_POST['namaAlfin'] ?? '');
$username = trim($_POST['usernameAlfin'] ?? '');
$password = $_POST['passwordAlfin'] ?? '';
$role = trim($_POST['roleAlfin'] ?? '');

if ($id <= 0 || $nama === '' || $username === '' || $role === '') {
    header("Location: edit_alfin.php?id={$id}&error=invalid");
    exit;
}

if ($password !== '') {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare(
        $koneksiAlfin,
        "UPDATE pengguna_alfin SET nama_pengguna_alfin = ?, username_alfin = ?, password_alfin = ?, role_alfin = ? WHERE id_pengguna_alfin = ?"
    );
    mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $username, $hashedPassword, $role, $id);
} else {
    $stmt = mysqli_prepare(
        $koneksiAlfin,
        "UPDATE pengguna_alfin SET nama_pengguna_alfin = ?, username_alfin = ?, role_alfin = ? WHERE id_pengguna_alfin = ?"
    );
    mysqli_stmt_bind_param($stmt, 'sssi', $nama, $username, $role, $id);
}

if (!$stmt) {
    header("Location: edit_alfin.php?id={$id}&error=db");
    exit;
}

$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($success) {
    header("Location: pengguna_alfin.php?success=updated");
} else {
    header("Location: edit_alfin.php?id={$id}&error=failed");
}
exit;