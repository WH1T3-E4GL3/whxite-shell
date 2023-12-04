// This is a php script that helps to vieww the database structure of the file. You just need to give the credentials of the mysql(usrname, passwd, dbname). It will display the database, tables, columns and its data in structured and ordered manner.
// After gaining access to a website through a shell backdoor, you can see the websites's files and there may be a file for connection in website. You can get credentials from there
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<?php
$dbCon = mysqli_connect("localhost", "username", "password", "db_name");

if (!$dbCon) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch databases
$result = mysqli_query($dbCon, "SHOW DATABASES");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $database = $row["Database"];
        echo "<h2>$database</h2>";

        // Fetch tables
        $result_tables = mysqli_query($dbCon, "SHOW TABLES FROM $database");
        if ($result_tables) {
            echo "<table>";
            echo "<tr><th>Table</th></tr>";
            while ($row_tables = mysqli_fetch_assoc($result_tables)) {
                $table = $row_tables["Tables_in_$database"];
                echo "<tr><td><a href='?db=$database&table=$table'>$table</a></td></tr>";
            }
            echo "</table>";
        }
    }
}

if (isset($_GET['db']) && isset($_GET['table'])) {
    $selectedDB = $_GET['db'];
    $selectedTable = $_GET['table'];

    echo "<h3>Database: $selectedDB</h3>";
    echo "<h3>Table: $selectedTable</h3>";

    // Fetch columns
    $result_columns = mysqli_query($dbCon, "SHOW COLUMNS FROM $selectedTable");
    if ($result_columns) {
        echo "<table>";
        echo "<tr><th>Column</th></tr>";
        while ($row_columns = mysqli_fetch_assoc($result_columns)) {
            $column = $row_columns["Field"];
            echo "<tr><td>$column</td></tr>";
        }
        echo "</table>";
    }

    // Fetch data
    $result_data = mysqli_query($dbCon, "SELECT * FROM $selectedTable");
    if ($result_data) {
        echo "<h3>Data:</h3>";
        echo "<table>";
        echo "<tr>";
        while ($row_data = mysqli_fetch_assoc($result_data)) {
            foreach ($row_data as $key => $value) {
                echo "<th>$key</th>";
            }
            break;
        }
        echo "</tr>";
        while ($row_data = mysqli_fetch_assoc($result_data)) {
            echo "<tr>";
            foreach ($row_data as $value) {
                echo "<td>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}

mysqli_close($dbCon);
?>

</body>
</html>
