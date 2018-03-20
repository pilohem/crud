<?php
session_start();
require_once "pdo.php";
require_once "util.php";
//print_r($_GET);
$stmt = $pdo->prepare("SELECT first_name, last_name, email, headline, summary, profile_id
                    FROM Profile WHERE profile_id = :profile_id");
$stmt->execute(array(':profile_id' => $_REQUEST['profile_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

// Guardian: Make sure that profile_id is present
if ( ! isset($_REQUEST['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}
//Makesure profile_id is in the database
$stmt = $pdo->prepare("SELECT * FROM Profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_REQUEST['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Could not load profile';
    header( 'Location: index.php' ) ;
    return;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <?php require_once "head.php"; ?>
    <meta charset="utf-8">
    <title>Porfirio Hernandez</title>
  </head>
  <body>
    <h1>Profile information</h1>
    <p>First Name: <?php echo (htmlentities($profile['first_name'])) ?></p>
    <p>Last Name: <?php echo (htmlentities($profile['last_name'])) ?></p>
    <p>Email: <?php echo (htmlentities($profile['email'])) ?></p>
    <p>Headline:</br><?php echo (htmlentities($profile['headline'])) ?></p>
    <p>Summary:</br><?php echo (htmlentities($profile['summary'])) ?></p>
    <?php
    if (count($positions) > 0) {
        echo('<p>Positions:</p>'."\n");
        echo('<ul>');
        foreach ($positions as $position ) {
          echo ('<li>'.$position['year'].': '.htmlentities($position['description']).'</li>');
        }
        echo('</ul>'."\n");
    }
    if (count($schools) > 0) {
      echo('<p>Education:</p>'."\n");
      echo('<ul>');
      foreach ($schools as $school ) {
        echo ('<li>'.$school['year'].': '.htmlentities($school['name']).'</li>');
      }
      echo('</ul>'."\n");
    }
    ?>
    <p><a href="index.php">Done</a></p>
  </body>
</html>
