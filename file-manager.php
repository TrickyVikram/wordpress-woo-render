<?php
$uploadDir = __DIR__ . '/upload/';
if (!is_dir($uploadDir)) mkdir($uploadDir);

// 🔄 Handle Upload (ZIP and other files)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        // Auto-unzip if ZIP and unzip checkbox is checked
        if (pathinfo($fileName, PATHINFO_EXTENSION) === 'zip' && isset($_POST['unzip'])) {
            $zip = new ZipArchive;
            if ($zip->open($targetPath) === TRUE) {
                $zip->extractTo($uploadDir);
                $zip->close();
                unlink($targetPath); // remove zip after extraction
                $msg = "ZIP extracted and deleted.";
            } else {
                $msg = "ZIP extract failed.";
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 🗑️ Handle Delete
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $path = $uploadDir . $file;
    if (file_exists($path)) unlink($path);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ✏️ Handle Edit View
if (isset($_GET['edit'])) {
    $file = basename($_GET['edit']);
    $path = $uploadDir . $file;
    if (file_exists($path)) {
        $content = htmlspecialchars(file_get_contents($path));
        echo <<<HTML
        <h2>Editing: $file</h2>
        <form method="POST">
            <textarea name="content" rows="25" cols="100" style="font-family: monospace;">$content</textarea><br>
            <input type="hidden" name="file" value="$file">
            <button type="submit" name="save">💾 Save</button>
        </form>
        <p><a href="{$_SERVER['PHP_SELF']}">🔙 Back</a></p>
HTML;
        exit;
    }
}

// 💾 Save Edited File
if (isset($_POST['save'], $_POST['file'], $_POST['content'])) {
    file_put_contents($uploadDir . basename($_POST['file']), $_POST['content']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 📂 List files
$files = array_diff(scandir($uploadDir), ['.', '..']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Advanced File Manager</title>
</head>
<body>
    <h1>📤 Upload File</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required />
        <label><input type="checkbox" name="unzip"> Unzip if .zip</label>
        <button type="submit">Upload</button>
    </form>

    <h2>📁 Files in Upload Folder</h2>
    <ul>
    <?php foreach ($files as $file): ?>
        <li>
            <a href="upload/<?= urlencode($file) ?>" target="_blank"><?= htmlspecialchars($file) ?></a>
            <?php if (is_file($uploadDir . $file)): ?>
                | <a href="?edit=<?= urlencode($file) ?>">✏️ Edit</a>
                | <a href="?delete=<?= urlencode($file) ?>" onclick="return confirm('Delete this file?')">🗑️ Delete</a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
    </ul>
</body>
</html>
