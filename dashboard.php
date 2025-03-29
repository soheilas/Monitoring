<?php
require_once 'config.php';

// بررسی وضعیت ورود کاربر
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$db = connectDB();
$message = '';

// افزودن سرور جدید
if (isset($_POST['add_server'])) {
    $name = $_POST['name'];
    $subdomain = $_POST['subdomain'];
    $main_ip = $_POST['main_ip'];
    $backup_ip = $_POST['backup_ip'];
    
    try {
        $stmt = $db->prepare("INSERT INTO servers (name, subdomain, main_ip, backup_ip, current_ip, status, last_checked) VALUES (?, ?, ?, ?, ?, 'up', NOW())");
        $stmt->execute([$name, $subdomain, $main_ip, $backup_ip, $main_ip]);
        $serverId = $db->lastInsertId();
        logEvent($serverId, "سرور جدید اضافه شد");
        $message = '<div class="alert alert-success">سرور با موفقیت اضافه شد.</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">خطا در افزودن سرور: ' . $e->getMessage() . '</div>';
    }
}

// ویرایش سرور
if (isset($_POST['edit_server'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $subdomain = $_POST['subdomain'];
    $main_ip = $_POST['main_ip'];
    $backup_ip = $_POST['backup_ip'];
    
    try {
        $stmt = $db->prepare("UPDATE servers SET name = ?, subdomain = ?, main_ip = ?, backup_ip = ? WHERE id = ?");
        $stmt->execute([$name, $subdomain, $main_ip, $backup_ip, $id]);
        logEvent($id, "سرور ویرایش شد");
        $message = '<div class="alert alert-success">سرور با موفقیت ویرایش شد.</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">خطا در ویرایش سرور: ' . $e->getMessage() . '</div>';
    }
}

// حذف سرور
if (isset($_POST['delete_server'])) {
    $id = $_POST['id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM servers WHERE id = ?");
        $stmt->execute([$id]);
        logEvent($id, "سرور حذف شد");
        $message = '<div class="alert alert-success">سرور با موفقیت حذف شد.</div>';
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">خطا در حذف سرور: ' . $e->getMessage() . '</div>';
    }
}

// دریافت لیست سرورها
$servers = $db->query("SELECT * FROM servers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// دریافت آخرین لاگ‌ها
$logs = $db->query("SELECT logs.*, servers.name AS server_name 
                    FROM logs 
                    LEFT JOIN servers ON logs.server_id = servers.id 
                    ORDER BY log_time DESC 
                    LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مانیتورینگ سرورهای VPN</title>
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
        .server-card {
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        .online {
            color: #28a745;
            animation: pulse 1.5s infinite;
        }
        .offline {
            color: #dc3545;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
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
                            <a class="nav-link active" href="dashboard.php">داشبورد</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logs.php">تاریخچه لاگ‌ها</a>
                        </li>
                    </ul>
                    <div class="d-flex">
                        <a href="logout.php" class="btn btn-outline-danger btn-sm">خروج</a>
                    </div>
                </div>
            </div>
        </nav>
        
        <?php echo $message; ?>
        
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">لیست سرورها</h5>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServerModal">
                            <i class="bi bi-plus-lg"></i> افزودن سرور جدید
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>نام</th>
                                        <th>ساب‌دامنه</th>
                                        <th>آی‌پی اصلی</th>
                                        <th>آی‌پی بکاپ</th>
                                        <th>آی‌پی فعلی</th>
                                        <th>وضعیت</th>
                                        <th>آخرین بررسی</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($servers)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center">هیچ سروری یافت نشد</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($servers as $server): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($server['name']); ?></td>
                                                <td><?php echo htmlspecialchars($server['subdomain']); ?></td>
                                                <td><?php echo htmlspecialchars($server['main_ip']); ?></td>
                                                <td><?php echo htmlspecialchars($server['backup_ip']); ?></td>
                                                <td><?php echo htmlspecialchars($server['current_ip']); ?></td>
                                                <td>
                                                    <?php if ($server['status'] == 'up'): ?>
                                                        <span class="badge bg-success status-badge"><i class="bi bi-circle-fill online"></i> آنلاین</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger status-badge"><i class="bi bi-circle-fill offline"></i> آفلاین</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($server['last_checked']); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info edit-server" 
                                                            data-id="<?php echo $server['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($server['name']); ?>"
                                                            data-subdomain="<?php echo htmlspecialchars($server['subdomain']); ?>"
                                                            data-main-ip="<?php echo htmlspecialchars($server['main_ip']); ?>"
                                                            data-backup-ip="<?php echo htmlspecialchars($server['backup_ip']); ?>">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger delete-server" 
                                                            data-id="<?php echo $server['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($server['name']); ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
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
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">آخرین رویدادها</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>سرور</th>
                                        <th>رویداد</th>
                                        <th>زمان</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center">هیچ رویدادی ثبت نشده است</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
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
    
    <!-- Modal افزودن سرور -->
    <div class="modal fade" id="addServerModal" tabindex="-1" aria-labelledby="addServerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addServerModalLabel">افزودن سرور جدید</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">نام سرور</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="subdomain" class="form-label">ساب‌دامنه</label>
                            <input type="text" class="form-control" id="subdomain" name="subdomain" required>
                        </div>
                        <div class="mb-3">
                            <label for="main_ip" class="form-label">آی‌پی اصلی</label>
                            <input type="text" class="form-control" id="main_ip" name="main_ip" required>
                        </div>
                        <div class="mb-3">
                            <label for="backup_ip" class="form-label">آی‌پی بکاپ</label>
                            <input type="text" class="form-control" id="backup_ip" name="backup_ip" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" name="add_server" class="btn btn-primary">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal ویرایش سرور -->
    <div class="modal fade" id="editServerModal" tabindex="-1" aria-labelledby="editServerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editServerModalLabel">ویرایش سرور</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">نام سرور</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_subdomain" class="form-label">ساب‌دامنه</label>
                            <input type="text" class="form-control" id="edit_subdomain" name="subdomain" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_main_ip" class="form-label">آی‌پی اصلی</label>
                            <input type="text" class="form-control" id="edit_main_ip" name="main_ip" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_backup_ip" class="form-label">آی‌پی بکاپ</label>
                            <input type="text" class="form-control" id="edit_backup_ip" name="backup_ip" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" name="edit_server" class="btn btn-primary">بروزرسانی</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal حذف سرور -->
    <div class="modal fade" id="deleteServerModal" tabindex="-1" aria-labelledby="deleteServerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteServerModalLabel">حذف سرور</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="delete_id" name="id">
                        <p>آیا از حذف سرور <strong id="delete_server_name"></strong> اطمینان دارید؟</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="submit" name="delete_server" class="btn btn-danger">حذف</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // اسکریپت برای ویرایش سرور
        document.querySelectorAll('.edit-server').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const subdomain = this.getAttribute('data-subdomain');
                const mainIp = this.getAttribute('data-main-ip');
                const backupIp = this.getAttribute('data-backup-ip');
                
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_subdomain').value = subdomain;
                document.getElementById('edit_main_ip').value = mainIp;
                document.getElementById('edit_backup_ip').value = backupIp;
                
                const editModal = new bootstrap.Modal(document.getElementById('editServerModal'));
                editModal.show();
            });
        });
        
        // اسکریپت برای حذف سرور
        document.querySelectorAll('.delete-server').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                document.getElementById('delete_id').value = id;
                document.getElementById('delete_server_name').textContent = name;
                
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteServerModal'));
                deleteModal.show();
            });
        });
    </script>
</body>
</html>