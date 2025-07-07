<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

$rootDir = __DIR__ . '/upload/';
$currentPath = isset($_GET['path']) ? realpath($rootDir . $_GET['path']) : $rootDir;

if (strpos($currentPath, realpath($rootDir)) !== 0) {
    die("Invalid Path Access");
}

// Ensure upload base directory exists
if (!is_dir($rootDir)) mkdir($rootDir, 0777, true);

// === LOGIN SYSTEM ===
if (!isset($_SESSION['logged_in'])) {
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
        if ($_POST['username'] === '7870766827' && $_POST['password'] === '7870766827') {
            $_SESSION['logged_in'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }

    echo <<<HTML
    <!DOCTYPE html><html><head>
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="card p-4 shadow" style="width:300px;">
        <h4 class="text-center">🔐 Login</h4>
        <form method="POST">
            <input type="text" name="username" class="form-control my-2" placeholder="Username" required>
            <input type="password" name="password" class="form-control my-2" placeholder="Password" required>
            <button class="btn btn-primary w-100">Login</button>
        </form>
        <div class="text-danger mt-2 text-center">{$error}</div>
    </div></body></html>
HTML;
    exit;
}

// === LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === FILE/FOLDER ACTIONS ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    $uploadPath = rtrim($currentPath, '/') . '/';
    $filename = basename($_FILES['upload_file']['name']);
    $target = $uploadPath . $filename;
    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $target)) {
        chmod($target, 0777);
    }
    header("Location: ?path=" . urlencode(str_replace($rootDir, '', $currentPath)));
    exit;
}

if (isset($_POST['create_folder'])) {
    $folder = basename($_POST['folder_name']);
    $newPath = rtrim($currentPath, '/') . '/' . $folder;
    if (!is_dir($newPath)) {
        mkdir($newPath, 0777, true);
        chmod($newPath, 0777);
    }
    header("Location: ?path=" . urlencode(str_replace($rootDir, '', $currentPath)));
    exit;
}

if (isset($_GET['delete'])) {
    $delPath = realpath($currentPath . '/' . basename($_GET['delete']));
    if (is_file($delPath)) {
        unlink($delPath);
    } elseif (is_dir($delPath)) {
        rmdir($delPath);
    }
    header("Location: ?path=" . urlencode(str_replace($rootDir, '', $currentPath)));
    exit;
}

function listFolders($base, $relative = '')
{
    $html = '';
    foreach (scandir($base) as $item) {
        if ($item === '.' || $item === '..') continue;
        $fullPath = $base . '/' . $item;
        $folderPath = ltrim($relative . '/' . $item, '/');
        if (is_dir($fullPath)) {
            $html .= "<li><a href='?path=" . urlencode($folderPath) . "'>📁 $item</a>";
            $html .= "<ul>" . listFolders($fullPath, $folderPath) . "</ul></li>";
        }
    }
    return $html;
}

// === GET FILES IN CURRENT DIR
$files = array_diff(scandir($currentPath), ['.', '..']);
$currentRelative = str_replace($rootDir, '', $currentPath);
?>
<!DOCTYPE html>
<html>
<head>
    <title>📁 Advanced File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        ul { list-style: none; padding-left: 20px; }
        ul li { margin: 3px 0; }
    </style>
</head>
<body class="container py-4">

<div class="row">
    <div class="col-md-3">
        <h4>📚 Folder Tree</h4>
        <ul><?= listFolders($rootDir); ?></ul>
    </div>
    <div class="col-md-9">
        <h4>📂 Current: <code><?= htmlspecialchars($currentRelative ?: '/') ?></code></h4>

        <form method="POST" enctype="multipart/form-data" class="mb-3">
            <div class="row g-2">
                <div class="col"><input type="file" name="upload_file" class="form-control" required></div>
                <div class="col-auto"><button class="btn btn-primary">Upload</button></div>
            </div>
        </form>

        <form method="POST" class="mb-3">
            <div class="row g-2">
                <div class="col"><input type="text" name="folder_name" class="form-control" placeholder="New Folder" required></div>
                <div class="col-auto"><button name="create_folder" class="btn btn-success">📁 Create Folder</button></div>
            </div>
        </form>

        <ul class="list-group">
        <?php foreach ($files as $f): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?php if (is_dir($currentPath . '/' . $f)): ?>
                    📁 <a href="?path=<?= urlencode(trim($currentRelative . '/' . $f, '/')) ?>"><?= htmlspecialchars($f) ?></a>
                <?php else: ?>
                    📄 <a href="<?= htmlspecialchars('upload/' . trim($currentRelative . '/' . $f, '/')) ?>" target="_blank"><?= htmlspecialchars($f) ?></a>
                <?php endif; ?>
                <a href="?delete=<?= urlencode($f) ?>&path=<?= urlencode($currentRelative) ?>" onclick="return confirm('Delete this?')" class="btn btn-sm btn-danger">🗑️ Delete</a>
            </li>
        <?php endforeach; ?>
        </ul>

        <p class="mt-3"><a href="?logout=1" class="btn btn-outline-danger">🚪 Logout</a></p>
    </div>
</div>
</body>
</html>
