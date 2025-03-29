<?php
// logout.php - اسکریپت خروج از پنل مدیریت
require_once 'config.php';

// پاکسازی نشست کاربر
session_unset();
session_destroy();

// هدایت به صفحه ورود
header('Location: index.php');
exit;