<?php // Do not put any HTML above this line
session_start();
require_once "pdo.php";
require_once "head.php";
$salt = 'XyZzy12*_';

// Check to see if we have some POST data, if we do process it
if ( isset($_POST['email']) && isset($_POST['pass']) ) {
    unset($_SESSION['email']); // Logout current user
    if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
        $_SESSION['error'] = "Email and password are required";
        header('Location: login.php');  //head to login.php
        return;
    //check if username has an at-sign @
    }elseif (strpos($_POST['email'], '@') === false) {
        $_SESSION['error'] = "Email must have an at-sign (@)";
        header('Location: login.php');  //head to login.php
        return;
    }else {
      $check = hash('md5', $salt.$_POST['pass']);
      $stmt = $pdo->prepare('SELECT user_id, name FROM users
                            WHERE email = :em AND password = :pw');
      $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ( $row !== false ) {
          $_SESSION['name'] = $row['name'];
          $_SESSION['user_id'] = $row['user_id'];
          //Login success
          error_log("Login success ".$_POST['email']." $check");
          // Redirect the browser to index.php
          header("Location: index.php");
          return;
      } else {
          $_SESSION['error'] = "Incorrect password";
          error_log("Login fail ".$_POST['email']." $check");
          header('Location: login.php');  //head to login.php
          return;
      }
    }
}

// Fall through into the View
?>
<!DOCTYPE html>
<html>
<head>
<title>Porfirio Hernandez</title>
</head>
<body>
<div class="container">
<h1>Please Log In</h1>
<?php
    if (isset($_SESSION['error'])) {
      echo('<p style="color:red">'.$_SESSION['error']."</p>\n");
      unset($_SESSION['error']);
    }
?>
<form method="post">
<label for="nam">User Name</label>
<input type="text" name="email" id="nam" value=""/>
<br/>
<label for="id_1723">Password</label>
<input type="password" name="pass" id="id_1723"><br/>
<input type="submit" onclick="return doValidate();" value="Log In">
<a href="index.php">Cancel</a>
</form>
<p>
For a password hint, view source and find a password hint
in the HTML comments.
<!-- Hint: The password is php
(all lower case) followed by 123. -->
</p>
<script type="text/javascript">
function doValidate() {
  console.log('Validating...');
  try {
      var pw = document.getElementById('id_1723').value;
      var nm = document.getElementById('nam').value;
      console.log("Validating pw="+pw);
      console.log("Validating nm="+nm);
      if (pw == null || pw == "" || nm == null || nm == "") {
          alert("Both fields must be filled out");
          return false;
      }
      if (nm.indexOf("@") == -1 ) {
          alert("Invalid email address");
          return false;
      }
      return true;
  } catch (e) {
      return false;
  }
  return false;
}
</script>
</div>
</body>
</html>
