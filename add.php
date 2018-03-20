<?php
require_once "pdo.php";
require_once 'util.php';
session_start();

//Demand a user to be logged in
if (! isset($_SESSION['name']) ) {  //this name is the name in the user table
    die('ACCESS DENIED');
}

if (isset($_POST['cancel'])) {
    header("Location: index.php");
    return;
}
//Adding data
if ( isset($_POST['first_name']) && isset($_POST['last_name'])
     && isset($_POST['email']) && isset($_POST['headline'])
     && isset($_POST['summary']) && isset($_POST['profile_id']) ) {
    // Data validation
    $msg = validateProfile();
    if (is_string($msg)){
      $_SESSION['error'] = $msg;
      header("Location: add.php");
      return;
    }
    //Positions validation if present
    $msg = validatePos();
    if (is_string($msg)) {
      $_SESSION['error'] = $msg;
      header("Location: add.php");
      return;
    }
    //Validate education data
    $msg = validateEdu();
    if (is_string($msg)) {
      $_SESSION['error'] = $msg;
      header("Location: add.php");
      return;
    }
    //Everything correct, then insert Profile data
    $sql = "INSERT INTO Profile (user_id, first_name, email, headline, last_name, summary)
            VALUES (:user_id, :first_name, :email, :headline, :last_name, :summary)";
    //echo("<pre>\n".$sql."\n</pre>\n");
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':first_name' => $_POST['first_name'],
        ':email' => $_POST['email'],
        ':headline' => $_POST['headline'],
        ':last_name' => $_POST['last_name'],
        ':user_id' => $_SESSION['user_id'],
        ':summary' => $_POST['summary'])
    );
    $profile_id = $pdo->lastInsertId();
    //Insert Positions
    insertPositions($pdo, $profile_id);
    //Insert Educations
    insertEducations($pdo, $profile_id);

    $_SESSION['success'] = "Profile added";
    header('Location: index.php');
    return;
}
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
    echo "<h1>Adding Profile for ";
    echo htmlentities($_SESSION['name']);
    echo "</h1>\n";
}
flashMessages();
?>
<form method="post">
<p>First Name:
<input type="text" name="first_name" size="40" value=""></p>
<p>Last Name:
<input type="text" name="last_name" size="40" value=""></p>
<p>Email:
<input type="text" name="email" value=""></p>
<p>Headline:
<input type="text" name="headline" value=""></p>
<p>Summary:</br>
<textarea name="summary" rows="8" cols="80"></textarea></p>
<input type="hidden" name="profile_id" value="">
<p>Education:<input type="submit" id="addEdu" value="+">
<div id="edu_fields">
</div>
</p>
<p>Position: <input type="submit" id="addPos" value="+">
<div id="position_fields">
</div>
</p>
<p><input type="submit" value="Add">
<input type="submit" name="cancel" value="Cancel"></p>
</form>
</div>
<script>
countPos = 0;
countEdu = 0;

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
