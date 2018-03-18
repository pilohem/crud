<?php
session_start();
require_once "pdo.php";
require_once "util.php";
require_once "head.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Porfirio Hernandez</title>
</head>
<body>
<div class="container">
<h1>Porfirio's Resume Registry</h1>
<?php
flashMessages();  //get function from util.php
?>

<table border="1">
<th>Name</th>
<th>Headline</th>
<?php
if (! isset($_SESSION['name'])) {
    ?>
    <p><a href="login.php">Please log in</a></p>
    <?php
    $stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id FROM profile");
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
        echo "<tr><td>";
        echo('<a href="view.php?profile_id='.$row['profile_id'].'">');
        echo(htmlentities($row['first_name']) . " " . htmlentities($row['last_name']));
        echo("</td><td>");
        echo(htmlentities($row['headline']));
        echo("</td></tr>");}
    ?>
    </table>
<?php
} else {
    ?>
    <p><a href="logout.php">Logout</a></p>
    <th>Action</th>
    <?php
    $stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id FROM profile");
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
        echo "<tr><td>";
        echo('<a href="view.php?profile_id='.$row['profile_id'].'">');
        echo(htmlentities($row['first_name']) . " " . htmlentities($row['last_name']));
        echo("</td><td>");
        echo(htmlentities($row['headline']));
        echo("</td><td>");
        echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
        echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');
        echo("</td></tr>\n");}
    ?>
    </table>
    <p><a href="add.php">Add New Entry</a></p>
<?php
}
?>
</div>
</body>
</html>
