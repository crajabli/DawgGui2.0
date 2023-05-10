<?php
include "database_connection.php";
session_start();

// If the user has attempted to sign in as a user or as a guest, redirect them.
if (isset($_POST["guestLogin"])) {
    header("Location: guest.php");
    exit();
} else if (isset($_POST["login"])) {
    // Lazy salt, we added "hello" to the end of the password.
    $username = $_POST["username"];
    $password = $_POST["password"] . "hello";
    // Check if the given username has a password associated with it (if the user exists, it has a password)
    $query = "SELECT password FROM users WHERE username = '{$username}'";
    $statement = $connect->prepare($query);
    $statement->execute();

    // If the user has an account, verify the password. If the password fails to verify or the username
    // doesn't exist in the database, displays an alert.
    if ($statement->rowCount() == 0) {
        echo '<div class="alert alert-danger">Invalid login credentials</div>';
    } else {
        $hash = $statement->fetchAll()[0][0];
        if (password_verify($password, $hash)) {
            $_SESSION["username"] = $username;
            header("Location: home.php");
            exit();
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
    <title>Document</title>
</head>
<body>
    <h1>DAWG<sub class="uiSub">ui</sub></h1>
    <div id="signInBox">
        <h2>Sign In</h2>
        <span>or <a href="createAccount.php">create an account</a></span>
        <form id="signInForm" method="post">
            <input type="text" id="username" name="username"
            placeholder="Username">
            <input type="password" id="password" name="password"
            placeholder="Password">

            <!-- Not sure how to implement these, commenting them out for now... -->


            <!-- <div id="checks">
		    <div>
                	<input type="checkbox" id="remember" name="rememeber">
                	<label for="remember">Remember Me</label>
		  </div>
		<button>Forgot Password?</button>
        <a href = "mailto:mtwellc@gmail.com?subject = Feedback&body = Message">
            Send Feedback
        </a>
            </div> -->
            <input type="submit" name="login" id="login" value="Sign In">
            <input type="submit" name="guestLogin" id="guestLogin" value="Continue as a Guest">
        </form>
    </div>
</body>
</html>
