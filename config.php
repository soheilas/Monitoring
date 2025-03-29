<?php
// config.php - تنظیمات پایه برنامه
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'manitorvpn');
define('DB_USER', 'manitorvpn');
define('DB_PASS', 'manitorvpn');
define('ADMIN_PASS', 'admin123'); // رمز عبور مدیر (قابل تغییر)
define('CLOUDFLARE_EMAIL', 'your-email@example.com');
define('CLOUDFLARE_API_KEY', 'your-api-key');
define('CLOUDFLARE_ZONE_ID', 'your-zone-id');
define('TELEGRAM_BOT_TOKEN', 'your-telegram-bot-token'); // اختیاری
define('TELEGRAM_CHAT_ID', 'your-telegram-chat-id'); // اختیاری

// اتصال به پایگاه داده
function connectDB() {
    try {
        $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
    }
}

// بررسی وضعیت ورود کاربر
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// ارسال پیام به تلگرام (اختیاری)
function sendTelegramNotification($message) {
    if (!empty(TELEGRAM_BOT_TOKEN) && !empty(TELEGRAM_CHAT_ID)) {
        $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
        $data = [
            'chat_id' => TELEGRAM_CHAT_ID,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        
        $context = stream_context_create($options);
        file_get_contents($url, false, $context);
    }
}

// تغییر رکورد DNS در Cloudflare
function updateCloudflareRecord($subdomain, $newIP) {
    // ابتدا باید رکورد فعلی را دریافت کنیم
    $recordId = getCloudflareRecordId($subdomain);
    
    if (!$recordId) {
        return false;
    }
    
    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/" . CLOUDFLARE_ZONE_ID . "/dns_records/" . $recordId);
    
    $data = [
        'type' => 'A',
        'name' => $subdomain,
        'content' => $newIP,
        'ttl' => 60,
        'proxied' => false
    ];
    
    $headers = [
        "X-Auth-Email: " . CLOUDFLARE_EMAIL,
        "X-Auth-Key: " . CLOUDFLARE_API_KEY,
        "Content-Type: application/json"
    ];
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ($httpCode >= 200 && $httpCode < 300);
}

// دریافت شناسه رکورد DNS از Cloudflare
function getCloudflareRecordId($subdomain) {
    $ch = curl_init("https://api.cloudflare.com/client/v4/zones/" . CLOUDFLARE_ZONE_ID . "/dns_records?type=A&name=" . $subdomain);
    
    $headers = [
        "X-Auth-Email: " . CLOUDFLARE_EMAIL,
        "X-Auth-Key: " . CLOUDFLARE_API_KEY,
        "Content-Type: application/json"
    ];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['success']) && $data['success'] && !empty($data['result'])) {
        return $data['result'][0]['id'];
    }
    
    return false;
}

// ثبت لاگ در پایگاه داده
function logEvent($serverId, $event) {
    $db = connectDB();
    $stmt = $db->prepare("INSERT INTO logs (server_id, event, log_time) VALUES (?, ?, NOW())");
    $stmt->execute([$serverId, $event]);
}

// بررسی پینگ سرور
function pingServer($ip) {
    $pingResult = false;
    
    // برای سیستم‌های لینوکس
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        exec("ping -c 1 -W 1 " . escapeshellarg($ip), $output, $returnVar);
        $pingResult = ($returnVar === 0);
    } 
    // برای سیستم‌های ویندوز
    else {
        exec("ping -n 1 -w 1000 " . escapeshellarg($ip), $output, $returnVar);
        $pingResult = ($returnVar === 0);
    }
    
    return $pingResult;
}
?>