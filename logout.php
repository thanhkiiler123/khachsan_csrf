<?php

require('admin/inc/essentials.php');
session_start();
$token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);
if (!$token || $token !== $_SESSION['csrf_token']) {
  echo 'Đăng xuất thất bại!';
  header($_SERVER['SERVER_PROTOCOL'] . ' 405 Thiếu CSRF token');
  exit;
} else {
  session_destroy();
  redirect('index.php');
}
?>