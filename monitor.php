<?php
// monitor.php - اسکریپت کرون جاب برای بررسی وضعیت سرورها
require_once 'config.php';

// تنظیمات لاگینگ
$logFile = __DIR__ . '/monitor_log.txt';
function writeLog($message) {
    global $logFile;
    $date = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$date] $message" . PHP_EOL, FILE_APPEND);
}

writeLog("شروع بررسی وضعیت سرورها");

try {
    $db = connectDB();
    
    // دریافت لیست سرورها
    $stmt = $db->query("SELECT * FROM servers");
    $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    writeLog("تعداد " . count($servers) . " سرور برای بررسی پیدا شد");
    
    foreach ($servers as $server) {
        $id = $server['id'];
        $name = $server['name'];
        $subdomain = $server['subdomain'];
        $main_ip = $server['main_ip'];
        $backup_ip = $server['backup_ip'];
        $current_ip = $server['current_ip'];
        $status = $server['status'];
        
        writeLog("بررسی سرور $name با آدرس $main_ip");
        
        // بررسی پینگ سرور اصلی
        $isMainServerUp = pingServer($main_ip);
        
        // بروزرسانی زمان آخرین بررسی
        $stmt = $db->prepare("UPDATE servers SET last_checked = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($isMainServerUp) {
            writeLog("سرور $name در دسترس است");
            
            // اگر سرور اصلی در دسترس است و قبلاً آفلاین بوده
            if ($status == 'down') {
                writeLog("سرور $name مجدداً آنلاین شده است. تغییر DNS به آی‌پی اصلی");
                
                // تغییر رکورد DNS به آی‌پی اصلی
                if (updateCloudflareRecord($subdomain, $main_ip)) {
                    // بروزرسانی وضعیت در پایگاه داده
                    $stmt = $db->prepare("UPDATE servers SET status = 'up', current_ip = ? WHERE id = ?");
                    $stmt->execute([$main_ip, $id]);
                    
                    // ثبت لاگ
                    logEvent($id, "سرور مجدداً آنلاین شد. DNS به آی‌پی اصلی تغییر کرد: $main_ip");
                    
                    // ارسال نوتیفیکیشن به تلگرام
                    sendTelegramNotification("🟢 سرور $name مجدداً آنلاین شد و DNS به آی‌پی اصلی تغییر کرد.");
                    
                    writeLog("DNS برای سرور $name با موفقیت به آی‌پی اصلی $main_ip تغییر کرد");
                } else {
                    writeLog("خطا در تغییر DNS برای سرور $name به آی‌پی اصلی $main_ip");
                }
            }
        } else {
            writeLog("سرور $name در دسترس نیست");
            
            // یک بررسی مجدد برای اطمینان
            sleep(10);
            $isMainServerUp = pingServer($main_ip);
            
            if (!$isMainServerUp) {
                writeLog("سرور $name در بررسی مجدد نیز در دسترس نیست");
                
                // اگر سرور آنلاین بوده و الان در دسترس نیست
                if ($status == 'up') {
                    writeLog("سرور $name از دسترس خارج شده است. تغییر DNS به آی‌پی بکاپ");
                    
                    // تغییر رکورد DNS به آی‌پی بکاپ
                    if (updateCloudflareRecord($subdomain, $backup_ip)) {
                        // بروزرسانی وضعیت در پایگاه داده
                        $stmt = $db->prepare("UPDATE servers SET status = 'down', current_ip = ? WHERE id = ?");
                        $stmt->execute([$backup_ip, $id]);
                        
                        // ثبت لاگ
                        logEvent($id, "سرور از دسترس خارج شد. DNS به آی‌پی بکاپ تغییر کرد: $backup_ip");
                        
                        // ارسال نوتیفیکیشن به تلگرام
                        sendTelegramNotification("🔴 سرور $name از دسترس خارج شد و DNS به آی‌پی بکاپ تغییر کرد.");
                        
                        writeLog("DNS برای سرور $name با موفقیت به آی‌پی بکاپ $backup_ip تغییر کرد");
                    } else {
                        writeLog("خطا در تغییر DNS برای سرور $name به آی‌پی بکاپ $backup_ip");
                    }
                }
            } else {
                writeLog("سرور $name در بررسی مجدد در دسترس است");
            }
        }
    }
    
    writeLog("بررسی وضعیت سرورها با موفقیت به پایان رسید");
    
} catch (PDOException $e) {
    writeLog("خطا در بررسی وضعیت سرورها: " . $e->getMessage());
}