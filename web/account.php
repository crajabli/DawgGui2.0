<?php
session_start();
include "database_connection.php";
?>

<!DOCTYPE html>
<html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
      <link rel="stylesheet" href="style.css">
      <link rel="stylesheet" href="post.css">
      <link href='https://fonts.googleapis.com/css?family=Sriracha' rel='stylesheet'>
      <link href='https://fonts.googleapis.com/css?family=Roboto+Slab' rel='stylesheet'>
      <link href='https://fonts.googleapis.com/css?family=Source+Code+Pro' rel='stylesheet'>
      <link rel="icon" type="image/x-icon" href="/dawg/backend/database/photos/dawg.png">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
      <title>dawg</title>
  </head>
  <body>
  <nav class="navbar navbar-expand-lg navbar-light shadow-5-strong">
    <div class="container-fluid">
    <h1 id="logo">DAWG<sub class="uiSub">ui</sub></h1>

      <!-- <a class="navbar-brand" href="#">Dawg<sub>ui</sub></a> -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="home.php">Feed</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/dawg/web/account.php">Your account</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="index.php">Logout</a>
          </li>
        </ul>

      </div>
    </div>
  </nav>
  <h3>Your Posts</h3>

  <div class="container">
	<div class="posts">
    <?php
      // Fetches the user_id, username, image source, caption, like count (reaction), and post_id needed
      // to begin building each post. Only fetches the posts of the currently logged in user.
      $query = "SELECT users.user_id, users.username, image.src, post.caption, post.post_id, post.reaction FROM users INNER JOIN image INNER JOIN post WHERE post.user_id = users.user_id AND post.image_id = image.image_id AND users.username = '{$_SESSION['username']}'";
      $statement = $connect->prepare($query);
      $statement->execute();
      $result = $statement->fetchAll();

      // Main loop for building each post.
		  foreach($result as $row) {
        $uid = $row[0];
        $user = $row[1];
        $img = '..//backend/database/photos/' . $row[2];
        $caption = $row[3];
        $likes = $row[5];
        $postID = $row[4];
			
        // Post container start
        echo '<div class="post">';
        echo '<h4 class="card-title">'. $user  .'</h4>';
        echo '<img src="' . $img . '" class="card-img-top" alt="dog">';
        echo '<p>' . $caption . '</p>';
        echo '<p>Likes: ' . $likes . '</p>';
	
        // For each unique post (post_id), fetch every associated comment (along with 
        // the commenter username and user_id).
        $commentQuery = "SELECT status.comment, post.post_id, users.username, users.user_id FROM status INNER JOIN post INNER JOIN users WHERE status.post_id = post.post_id AND post.post_id = {$postID} AND users.user_id = status.user_id";
        $statement = $connect->prepare($commentQuery);
        $statement->execute();
        // Display each comment for the current post.
        foreach($statement->fetchAll() as $row) {
          $comment = $row[0];
          $username = $row[2];
          echo '<p>' . $username . ': ' . $comment . '</p>';
        }
        // Post container end
        echo "</div>";
		  }
	  ?>
	</div>
    </div>
</body>
</html>

