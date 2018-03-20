<?php
function flashMessages() {
  if ( isset($_SESSION['error']) ) {
      echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
      unset($_SESSION['error']);
  }
  if ( isset($_SESSION['success']) ) {
      echo '<p style="color:green">'.$_SESSION['success']."</p>\n";
      unset($_SESSION['success']);
  }
}
function validateProfile() {
  if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1
      || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1
      || strlen($_POST['summary']) < 1) {
        return "All fields are required";
  }
  //check if username has an at-sign @
  if (strpos($_POST['email'], '@') === false) {
        return "Email must have an at-sign (@)";
  }
  return true;
}
function validatePos() {
    for($i=1; $i<=9; $i++) {
        if ( ! isset($_POST['year'.$i]) ) continue;
        if ( ! isset($_POST['desc'.$i]) ) continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];
        if ( strlen($year) == 0 || strlen($desc) == 0 ) {
            return "All fields are required";
        }

        if ( ! is_numeric($year) ) {
            return "Position year must be numeric";
        }
    }
    return true;
}

function validateEdu() {
  for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['edu_year'.$i]) ) continue;
      if ( ! isset($_POST['edu_school'.$i]) ) continue;
      $edu_year = $_POST['edu_year'.$i];
      $school = $_POST['edu_school'.$i];
      if ( strlen($edu_year) == 0 || strlen($school) == 0 ) {
          return "All fields are required";
      }

      if ( ! is_numeric($edu_year) ) {
          return "Education year must be numeric";
      }
  }
  return true;
}

function insertPositions($pdo, $profile_id) {
  // Insert the position entries
  $rank = 1;
  for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['year'.$i]) ) continue;
      if ( ! isset($_POST['desc'.$i]) ) continue;
      $year = $_POST['year'.$i];
      $desc = $_POST['desc'.$i];

      $stmt = $pdo->prepare('INSERT INTO Position
          (profile_id, rank, year, description)
      VALUES ( :pid, :rank, :year, :desc)');
      $stmt->execute(array(
          ':pid' => $profile_id,
          ':rank' => $rank,
          ':year' => $year,
          ':desc' => $desc)
      );
      $rank++;
  }
}

function insertEducations($pdo, $profile_id) {
  // Insert the position entries
  $rank = 1;
  for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['edu_year'.$i]) ) continue;
      if ( ! isset($_POST['edu_school'.$i]) ) continue;
      $edu_year = $_POST['edu_year'.$i];
      $school = $_POST['edu_school'.$i];
      //Check if there is already a school with that name
      $stmt = $pdo->prepare('SELECT * FROM Institution WHERE name = :nm');
      $stmt->execute(array(
            ':nm' => $school)
      );
      $institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (count($institutions) === 0) {
          //The school is NOT in the database
          //Insert the name of the school in the Institution table
          $stmt = $pdo->prepare('INSERT INTO Institution (name) VALUES (:nm)');
          $stmt->execute(array(
                ':nm' => $school)
          );
          //Select the NEW entries name and institution_id from the Institution table
          $stmt = $pdo->prepare('SELECT * FROM Institution WHERE name = :nm');
          $stmt->execute(array(
                ':nm' => $school)
          );
          $institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }
      //Insert entry into the Education table
      $stmt = $pdo->prepare('INSERT INTO Education
          (profile_id, institution_id, rank, year) VALUES ( :pid, :school_id, :rank, :year)');
      $stmt->execute(array(
          ':pid' => $profile_id,
          ':school_id' => $institutions[0]['institution_id'],
          ':rank' => $rank,
          ':year' => $edu_year)
      );
      $rank++;
  }
}

function loadPos($pdo, $profile_id) {
    $stmt = $pdo->prepare("SELECT * FROM Position
                        WHERE profile_id = :profile_id ORDER BY rank ASC");
    $stmt->execute(array(':profile_id' => $profile_id));
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $positions;
}

function loadEdu($pdo, $profile_id) {
    $stmt = $pdo->prepare("SELECT year, name FROM Education JOIN Institution
                            ON Education.institution_id = Institution.institution_id
                            WHERE profile_id = :profile_id ORDER BY rank");
    $stmt->execute(array( ':profile_id' => $profile_id));
    $educations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $educations;
}
