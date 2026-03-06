<?php
include 'db.php';

// Check recipes table structure
$query = "DESCRIBE recipes";
$result = mysqli_query($conn, $query);

echo "<h2>Recipes Table Structure</h2>";
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . $row['Default'] . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Check categories table
$query = "SELECT * FROM categories";
$result = mysqli_query($conn, $query);

echo "<h2>Categories</h2>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Name</th><th>Description</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['description'] . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
