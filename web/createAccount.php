<?php 
include "database_connection.php";

// Checks that the create account form has been submitted.
if (isset($_POST["createAccount"])) {
   // Boolean for checking if username already exists, used below.
   $usernameTaken = false;
   $fname = $_POST["fname"];
   $lname = $_POST["lname"];
   $username = $_POST["username"];
   $email = $_POST["email"];
   // We know now Salt should be random. password_hash() does include salt in the function
   $password = password_hash($_POST["password1"] . "hello", PASSWORD_DEFAULT);

   // Checks if the user actually provided a username
   if ($username != "") { 
      $query = "SELECT * FROM users";
      $statement = $connect->prepare($query);
      $statement->execute();
      session_start(); 
      $_SESSION["username"] = $_POST["username"];

      // Checks if there are any users in the database.
      if ($statement->rowCount() > 0) {
         $result = $statement->fetchAll();
         // Check if the username is already taken
         foreach($result as $row) {
            if ($_POST["username"] == $row["username"]) {
               echo '<div class="alert alert-danger">The given username already exists</div>';
               $usernameTaken = true;
               break;
            }
         }

         // If all the fields aren't filled out properly or the passwords don't match, alert the user.
         // Otherwise, if the username hasn't been taken, then create a new account for the user.
         if ($username == "" || $fname == "" || $lname == "" || $password == "" || $email == "") {
            echo '<div class="alert alert-danger">Please fill in all fields</div>';
         } else if ($_POST["password1"] != $_POST["password2"]) {
            echo '<div class="alert alert-danger">The passwords do not match</div>';
         } else if (!$usernameTaken) {
            // Query gets the id of the latest created user, and adds 1 to it.
            $query = "SELECT MAX(user_id) FROM users";
            $statement = $connect->prepare($query);
            $statement->execute();
            $result = $statement->fetchAll()[0][0];
            $result = (int) $result + 1;
            // Insert a new user entry into the database.
            $query = "INSERT INTO users VALUES ({$result}, '{$fname}', '{$lname}', '{$username}', '{$email}', '{$password}')";
            $statement = $connect->prepare($query);
            $statement->execute();
         }
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link href='https://fonts.googleapis.com/css?family=Sriracha' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Roboto+Slab' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Source+Code+Pro' rel='stylesheet'>
    <link rel="icon" type="image/x-icon" href="/dawg/backend/database/photos/dawg.png">
    <title>Create Account</title>
</head>
<body>
    <h1>DAWG<sub class="uiSub">ui</sub></h1>
    <div id="signInBox">
        <h2>Account Setup</h2>
        <span>Already have an account? <a href="index.php">Sign In</a></span>
         <form id="createAccountForm" action="createAccount.php" method="post">
            <input type="text" placeholder="Username" name="username">
            <input id="fname" placeholder="First name" name="fname">
            <input id="lname" placeholder="Last name" name="lname">
            <input type="email" id="email" name="email" placeholder="Email">
            <input type="password" id="password1" name="password1" placeholder="Password">
            <input type="password" id="password2" name="password2" placeholder="Confirm Password">
            <input type="submit" name="createAccount" id="createAccount" value="Create Account">
        </form>
    </div>
</body>
</html>
