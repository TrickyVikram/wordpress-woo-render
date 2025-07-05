<?php
$uploadDir = __DIR__ . '/upload/';
$rootDir = __DIR__ . '/';

if (!is_dir($uploadDir)) mkdir($uploadDir);

// === Handle Upload in /upload ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload_file'])) {
    $fileName = basename($_FILES['upload_file']['name']);
    $target = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $target)) {
        if (pathinfo($fileName, PATHINFO_EXTENSION) === 'zip' && isset($_POST['unzip'])) {
            $zip = new ZipArchive;
            if ($zip->open($target) === TRUE) {
                $zip->extractTo($uploadDir);
                $zip->close();
                unlink($target);
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === Handle Delete (root or upload)
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $location = $_GET['scope'] === 'root' ? $rootDir : $uploadDir;
    $path = $location . $file;
    if (file_exists($path)) unlink($path);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === Edit file (root or upload)
if (isset($_GET['edit'])) {
    $file = basename($_GET['edit']);
    $location = $_GET['scope'] === 'root' ? $rootDir : $uploadDir;
    $path = $location . $file;
    if (file_exists($path)) {
        $content = htmlspecialchars(file_get_contents($path));
        echo <<<HTML
        <h2>Editing: $file</h2>
        <form method="POST">
            <textarea name="content" rows="25" cols="100" style="font-family: monospace;">$content</textarea><br>
            <input type="hidden" name="file" value="$file">
            <input type="hidden" name="scope" value="{$_GET['scope']}">
            <button type="submit" name="save">💾 Save</button>
        </form>
        <p><a href="{$_SERVER['PHP_SELF']}">🔙 Back</a></p>
HTML;
        exit;
    }
}

// === Save Edited File
if (isset($_POST['save'], $_POST['file'], $_POST['content'], $_POST['scope'])) {
    $location = $_POST['scope'] === 'root' ? $rootDir : $uploadDir;
    file_put_contents($location . basename($_POST['file']), $_POST['content']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === Get lists
$uploadFiles = array_diff(scandir($uploadDir), ['.', '..']);
$rootFiles = array_diff(scandir($rootDir), ['.', '..']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Advanced File Manager</title>
</head>
<body>
    <h1>📤 Upload to /upload</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="upload_file" required />
        <label><input type="checkbox" name="unzip"> Unzip if .zip</label>
        <button type="submit">Upload</button>
    </form>

    <hr>

    <h2>📁 Files in /upload</h2>
    <ul>
    <?php foreach ($uploadFiles as $file): ?>
        <li>
            <a href="upload/<?= urlencode($file) ?>" target="_blank"><?= htmlspecialchars($file) ?></a>
            | <a href="?edit=<?= urlencode($file) ?>&scope=upload">✏️ Edit</a>
            | <a href="?delete=<?= urlencode($file) ?>&scope=upload" onclick="return confirm('Delete?')">🗑️ Delete</a>
        </li>
    <?php endforeach; ?>
    </ul>

    <hr>

    <h2>🛠️ Root Directory Files (Caution!)</h2>
    <ul>
    <?php foreach ($rootFiles as $file): ?>
        <?php if (is_file($rootDir . $file)) : ?>
        <li>
            <a href="<?= htmlspecialchars($file) ?>" target="_blank"><?= htmlspecialchars($file) ?></a>
            | <a href="?edit=<?= urlencode($file) ?>&scope=root">✏️ Edit</a>
            | <a href="?delete=<?= urlencode($file) ?>&scope=root" onclick="return confirm('Delete ROOT file?')">🗑️ Delete</a>
        </li>
        <?php endif; ?>
    <?php endforeach; ?>
    </ul>
</body>
</html>
