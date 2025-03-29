<?php
require_once 'config.php';

// بررسی وضعیت ورود کاربر
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$db = connectDB();

// فیلتر کردن لاگ‌ها بر اساس سرور
$server_id = isset($_GET['server_id']) ? (int)$_GET['server_id'] : 0;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// ساخت کوئری فیلتر
$query = "SELECT logs.*, servers.name AS server_name 
          FROM logs 
          LEFT JOIN servers ON logs.server_id = servers.id 
          WHERE 1=1";

$params = [];

if ($server_id > 0) {
    $query .= " AND logs.server_id = ?";
    $params[] = $server_id;
}

if (!empty($date_from)) {
    $query .= " AND DATE(logs.log_time) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(logs.log_time) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY logs.log_time DESC LIMIT 100";

$stmt = $db->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// دریافت لیست سرورها برای فیلتر
$servers = $db->query("SELECT id, name FROM servers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تاریخچه لاگ‌های سیستم - پنل مانیتورینگ سرورهای VPN</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        @font-face {
            font-family: Vazir;
            src: url('https://github.com/Hesammousavi/PersianAdminLTE/raw/refs/heads/master/dist/fonts/Vazir.woff2') format('woff2');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'Vazir', Tahoma, Arial, sans-serif;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .nav-item {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 rounded">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">پنل مانیتورینگ سرورهای VPN</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">داشبورد</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="logs.php">تاریخچه لاگ‌ها</a>
                        </li>
                    </ul>
                    <div class="d-flex">
                        <a href="logout.php" class="btn btn-outline-danger btn-sm">خروج</a>
                    </div>
                </div>
            </div>
        </nav>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">فیلتر لاگ‌ها</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-3">
                                <label for="server_id" class="form-label">سرور</label>
                                <select class="form-select" id="server_id" name="server_id">
                                    <option value="0">همه سرورها</option>
                                    <?php foreach ($servers as $server): ?>
                                        <option value="<?php echo $server['id']; ?>" <?php echo ($server_id == $server['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($server['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label">از تاریخ</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label">تا تاریخ</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">اعمال فیلتر</button>
                                <a href="logs.php" class="btn btn-secondary">حذف فیلترها</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">تاریخچه لاگ‌ها</h5>
                        <span class="badge bg-primary"><?php echo count($logs); ?> مورد</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>سرور</th>
                                        <th>رویداد</th>
                                        <th>زمان</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">هیچ لاگی یافت نشد</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $index => $log): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($log['server_name'] ?? 'نامشخص'); ?></td>
                                                <td><?php echo htmlspecialchars($log['event']); ?></td>
                                                <td><?php echo htmlspecialchars($log['log_time']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>