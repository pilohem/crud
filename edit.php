<?php
require_once "pdo.php";
require_once 'util.php';
session_start();

//Demand a user to be logged in
if (! isset($_SESSION['name']) ) {  //this name is the name in the user table
    die('ACCESS DENIED');
}

// Guardian: Make sure that profile_id is present
if ( ! isset($_REQUEST['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}
//Makesure profile_id is in the database
$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :prof_id");
$stmt->execute(array(":prof_id" => $_REQUEST['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Could not load profile';
    header( 'Location: index.php' ) ;
    return;
}

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}
//updating data
if ( isset($_POST['first_name']) && isset($_POST['last_name'])
     && isset($_POST['email']) && isset($_POST['headline'])
     && isset($_POST['summary']) && isset($_POST['profile_id']) ) {
    // Data validation
    $msg = validateProfile();
    if (is_string($msg)){
      $_SESSION['error'] = $msg;
      header("Location: edit.php?profile_id=".$_POST['profile_id']);
      return;
    }
    //Positions validation if present
    $msg = validatePos();
    if (is_string($msg)) {
      $_SESSION['error'] = $msg;
      header("Location: edit.php?profile_id=".$_POST['profile_id']);
      return;
    }
    //Validate education data
    $msg = validateEdu();
    if (is_string($msg)) {
      $_SESSION['error'] = $msg;
      header("Location: edit.php?profile_id=".$_POST['profile_id']);
      return;
    }
    //Everything is correct, then update the data
    //Update profile data
    $sql = "UPDATE Profile SET first_name = :first_name,
            last_name = :last_name, email = :email, headline = :headline,
            summary = :summary
            WHERE profile_id = :profile_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
            ':first_name' => $_POST['first_name'],
            ':last_name' => $_POST['last_name'],
            ':email' => $_POST['email'],
            ':headline' => $_POST['headline'],
            ':summary' => $_POST['summary'],
            ':profile_id' => $_REQUEST['profile_id'],
            'user_id' => $_SESSION['user_id'])
    );
    //Update position data
    // Clear out the old position entries (makes it easier)
    $stmt = $pdo->prepare('DELETE FROM Position
                          WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    //insert Positions entries
    insertPositions($pdo, $_REQUEST['profile_id']);

    //Clear out the old education entries
    $stmt = $pdo->prepare('DELETE FROM Education
                          WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    //Insert Educations entries
    insertEducations($pdo, $_REQUEST['profile_id']);

    $_SESSION['success'] = 'Profile updated';
    header( 'Location: index.php' ) ;
    return;
}


$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);
$em = htmlentities($row['email']);
$hl = htmlentities($row['headline']);
$sm = htmlentities($row['summary']);
$profile_id = $row['profile_id'];

//load positions and educations data
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

?>

<!DOCTYPE html>
<html>
<head>
<title>Porfirio Hernandez</title>
<?php require_once "head.php" ?>
</head>
<body>
<div class="container">
<?php
if ( isset($_SESSION['name']) ) {
    echo "<h1>Editing Profile for ";
    echo htmlentities($_SESSION['name']);
    echo "</h1>\n";
}
flashMessages();
?>
<form method="post">
<p>First Name:
<input type="text" name="first_name" size="40" value="<?= $fn ?>"></p>
<p>Last Name:
<input type="text" name="last_name" size="40" value="<?= $ln ?>"></p>
<p>Email:
<input type="text" name="email" value="<?= $em ?>"></p>
<p>Headline:
<input type="text" name="headline" value="<?= $hl ?>"></p>
<p>Summary:</br>
<textarea name="summary" rows="8" cols="80"><?= $sm ?></textarea></p>
<input type="hidden" name="profile_id" value="<?= $profile_id ?>">
<p>Education:<input type="submit" id="addEdu" value="+">
<div id="edu_fields">
<?php
//Showing current schools
//set counter
$countEdu = 0;
if (count($schools) > 0) {
    foreach ($schools as $school ) {
      $countEdu++;
      echo ('<div id="edu'.$countEdu.'">');
      echo('<p>Year: <input type="text" name="edu_year'.$countEdu.'" value="'.htmlentities($school['year']).'" />');
      echo('<input type="button" value="-"
              onclick="$(\'#edu'.$countEdu.'\').remove();return false;"></p>');
      echo('<p><input type="text" name="edu_school'.$countEdu.'" class="school" value="'.htmlentities($school['name']).'"></p>');
      echo('</div>');
    }
}
?>
</div>
</p>
<p>Position: <input type="submit" id="addPos" value="+">
<div id="position_fields">
<?php
//Showing current positions
//set counter
$countPos = 0;
if (count($positions) > 0) {
    foreach ($positions as $position ) {
      $countPos++;
      echo ('<div id="position'.$countPos.'">');
      echo('<p>Year: <input type="text" name="year'.$countPos.'" value="'.htmlentities($position['year']).'" />');
      echo('<input type="button" value="-"
            onclick="$(\'#position'.$countPos.'\').remove();return false;"></p>');
      echo('<textarea name="desc'.$countPos.'" rows="8" cols="80">'.htmlentities($position['description']).'</textarea>');
      echo('</div>');
    }
}
?>
</div>
</p>
<p><input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel"></p>
</form>
</div>
<script>
countPos = <?php echo $countPos; ?>;
countEdu = <?php echo $countEdu; ?>;

// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');

    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);

        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });

    $('#addEdu').click(function(event){
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding education "+countEdu);

        // Grab some HTML with hot spots and insert into the DOM
        var source  = $("#edu-template").html();
        $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));

        // Add the even handler to the new ones
        $('.school').autocomplete({
            source: "school.php"
        });

    });

    $('.school').autocomplete({
        source: "school.php"
    });

});
</script>
</script>
<!-- HTML with Substitution hot spots -->
<script id="edu-template" type="text">
  <div id="edu@COUNT@">
    <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
    <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
    <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
    </p>
  </div>
</script>
</body>
</html>
