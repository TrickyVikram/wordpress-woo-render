<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Optional: Increase limits
ini_set('upload_max_filesize', '512M');
ini_set('post_max_size', '512M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);
ini_set('memory_limit', '512M');

// === LOGIN ===
if (!isset($_SESSION['logged_in'])) {
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
    <!DOCTYPE html>
    <html><head>
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head><body class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="card p-4 shadow" style="width: 300px;">
        <h4 class="text-center">🔐 Login</h4>
        <form method="POST">
            <input type="text" name="username" class="form-control my-2" placeholder="Username" required>
            <input type="password" name="password" class="form-control my-2" placeholder="Password" required>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="text-danger mt-2 text-center">{$error ?? ''}</div>
    </div></body></html>
HTML;
    exit;
}

// === LOGOUT ===
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Paths
$uploadDir = rtrim(__DIR__ . '/upload', '/') . '/';
$rootDir = rtrim(__DIR__, '/') . '/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// === UPLOAD FILE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    $fileName = basename($_FILES['upload_file']['name']);
    $targetLocation = $_POST['upload_to'] === 'root' ? $rootDir : $uploadDir;
    $target = $targetLocation . $fileName;

    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $target)) {
        chmod($target, 0777);
        if (pathinfo($fileName, PATHINFO_EXTENSION) === 'zip' && isset($_POST['unzip'])) {
            $zip = new ZipArchive;
            if ($zip->open($target) === TRUE) {
                $zip->extractTo($targetLocation);
                $zip->close();
                unlink($target);
            }
        }
    } else {
        http_response_code(500);
        echo "Upload failed.";
    }
    exit;
}

// === UNZIP
if (isset($_GET['unzip'])) {
    $file = basename($_GET['unzip']);
    $location = $_GET['scope'] === 'root' ? $rootDir : $uploadDir;
    $path = $location . $file;
    if (file_exists($path)) {
        $zip = new ZipArchive;
        if ($zip->open($path)) {
            $zip->extractTo($location);
            $zip->close();
            unlink($path);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === DELETE
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $location = $_GET['scope'] === 'root' ? $rootDir : $uploadDir;
    $path = $location . $file;
    if (file_exists($path)) unlink($path);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === EDIT VIEW
if (isset($_GET['edit'])) {
    $file = basename($_GET['edit']);
    $location = $_GET['scope'] === 'root' ? $rootDir : $uploadDir;
    $path = $location . $file;
    if (file_exists($path)) {
        $content = htmlspecialchars(file_get_contents($path));
        echo <<<HTML
        <!DOCTYPE html><html><head><title>Edit $file</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="p-4">
        <h2>Editing: $file</h2>
        <form method="POST">
            <textarea name="content" rows="25" class="form-control" style="font-family: monospace;">$content</textarea><br>
            <input type="hidden" name="file" value="$file">
            <input type="hidden" name="scope" value="{$_GET['scope']}">
            <button type="submit" name="save" class="btn btn-success">💾 Save</button>
            <a href="{$_SERVER['PHP_SELF']}" class="btn btn-secondary">Back</a>
        </form></body></html>
HTML;
        exit;
    }
}

// === SAVE FILE
if (isset($_POST['save'], $_POST['file'], $_POST['content'], $_POST['scope'])) {
    $location = $_POST['scope'] === 'root' ? $rootDir : $uploadDir;
    file_put_contents($location . basename($_POST['file']), $_POST['content']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === CREATE FILE/FOLDER
if (isset($_POST['create_file'], $_POST['file_name'], $_POST['file_scope'])) {
    $location = $_POST['file_scope'] === 'root' ? $rootDir : $uploadDir;
    $filePath = $location . basename($_POST['file_name']);
    file_put_contents($filePath, '');
    chmod($filePath, 0777);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
if (isset($_POST['create_folder'], $_POST['folder_name'], $_POST['folder_scope'])) {
    $location = $_POST['folder_scope'] === 'root' ? $rootDir : $uploadDir;
    $folderPath = $location . basename($_POST['folder_name']);
    mkdir($folderPath, 0777);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === MOVE / COPY
if (isset($_POST['move_copy'], $_POST['file_name'], $_POST['from_scope'], $_POST['to_scope'], $_POST['action'])) {
    $from = $_POST['from_scope'] === 'root' ? $rootDir : $uploadDir;
    $to = $_POST['to_scope'] === 'root' ? $rootDir : $uploadDir;
    $src = $from . basename($_POST['file_name']);
    $dest = $to . basename($_POST['file_name']);
    ($_POST['action'] === 'copy') ? copy($src, $dest) : rename($src, $dest);
    chmod($dest, 0777);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$uploadFiles = array_diff(scandir($uploadDir), ['.', '..']);
$rootFiles = array_diff(scandir($rootDir), ['.', '..']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

<h1 class="mb-4">📤 Upload File</h1>
<form id="uploadForm">
    <input type="file" name="upload_file" class="form-control mb-2" required />
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" name="unzip" id="unzip">
        <label class="form-check-label" for="unzip">Unzip if .zip</label>
    </div>
    <select name="upload_to" class="form-select mb-3">
        <option value="upload">Upload to /upload</option>
        <option value="root">Upload to ROOT</option>
    </select>
    <button type="submit" class="btn btn-primary">Upload</button>
</form>

<div id="progressBox" class="mt-3" style="display:none;">
    <div class="progress">
        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
    </div>
</div>

<hr>
<h2>📄 Create New File / 📁 Folder</h2>
<form method="POST" class="row g-2 mb-3">
    <div class="col-auto"><input type="text" name="file_name" class="form-control" placeholder="newfile.txt" required></div>
    <div class="col-auto">
        <select name="file_scope" class="form-select">
            <option value="upload">In /upload</option>
            <option value="root">In ROOT</option>
        </select>
    </div>
    <div class="col-auto"><button name="create_file" class="btn btn-success">➕ Create File</button></div>
</form>
<form method="POST" class="row g-2 mb-3">
    <div class="col-auto"><input type="text" name="folder_name" class="form-control" placeholder="newfolder" required></div>
    <div class="col-auto">
        <select name="folder_scope" class="form-select">
            <option value="upload">In /upload</option>
            <option value="root">In ROOT</option>
        </select>
    </div>
    <div class="col-auto"><button name="create_folder" class="btn btn-secondary">📁 Create Folder</button></div>
</form>

<hr>
<h2>🔁 Move/Copy Files</h2>
<form method="POST" class="row g-2 mb-4">
    <div class="col-auto"><input type="text" name="file_name" class="form-control" placeholder="filename.txt" required></div>
    <div class="col-auto">
        <select name="from_scope" class="form-select">
            <option value="upload">From /upload</option>
            <option value="root">From ROOT</option>
        </select>
    </div>
    <div class="col-auto">
        <select name="to_scope" class="form-select">
            <option value="upload">To /upload</option>
            <option value="root">To ROOT</option>
        </select>
    </div>
    <div class="col-auto">
        <select name="action" class="form-select">
            <option value="copy">Copy</option>
            <option value="move">Move</option>
        </select>
    </div>
    <div class="col-auto"><button name="move_copy" class="btn btn-warning">🚚 Execute</button></div>
</form>

<hr>
<h2>📁 Files in /upload</h2>
<ul class="list-group">
    <?php foreach ($uploadFiles as $file): ?>
        <li class="list-group-item">
            <a href="upload/<?= urlencode($file) ?>" target="_blank"><?= htmlspecialchars($file) ?></a>
            | <a href="?edit=<?= urlencode($file) ?>&scope=upload">✏️ Edit</a>
            | <a href="?delete=<?= urlencode($file) ?>&scope=upload" onclick="return confirm('Delete?')">🗑️ Delete</a>
            <?php if (pathinfo($file, PATHINFO_EXTENSION) === 'zip'): ?>
                | <a href="?unzip=<?= urlencode($file) ?>&scope=upload" onclick="return confirm('Unzip this file?')">🧩 Unzip</a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>

<hr>
<h2>🛠️ Files in Root Directory</h2>
<ul class="list-group">
    <?php foreach ($rootFiles as $file): ?>
        <?php if (is_file($rootDir . $file)): ?>
            <li class="list-group-item">
                <a href="<?= htmlspecialchars($file) ?>" target="_blank"><?= htmlspecialchars($file) ?></a>
                | <a href="?edit=<?= urlencode($file) ?>&scope=root">✏️ Edit</a>
                | <a href="?delete=<?= urlencode($file) ?>&scope=root" onclick="return confirm('Delete ROOT file?')">🗑️ Delete</a>
                <?php if (pathinfo($file, PATHINFO_EXTENSION) === 'zip'): ?>
                    | <a href="?unzip=<?= urlencode($file) ?>&scope=root" onclick="return confirm('Unzip this file?')">🧩 Unzip</a>
                <?php endif; ?>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>

<hr>
<p><a href="?logout=1" class="btn btn-danger">🚪 Logout</a></p>

<script>
document.getElementById('uploadForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.upload.addEventListener('progress', function (e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            const bar = document.getElementById('progressBar');
            bar.style.width = percent + '%';
            bar.textContent = percent + '%';
            document.getElementById('progressBox').style.display = 'block';
        }
    });
    xhr.onload = function () {
        if (xhr.status === 200) {
            window.location.reload();
        } else {
            alert('Upload failed!');
        }
    };
    xhr.send(formData);
});
</script>

</body>
</html>
