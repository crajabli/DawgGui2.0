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

      <!-- <a class="navbar-brand" href="#">Dawg<sub class="uiSub">ui</sub></a> -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Feed</a>
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
  <h3>Feed</h3>
  <button id="newPostButton" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postSubmit">Share a Pic</button>
  <div class="modal fade" id="postSubmit" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5 text-dark" id="postSubmitLabel">Make a post:</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form action="home.php" enctype="multipart/form-data" method="post">
          <label>Select image:</label>
          <input type="file" id="image" name="image" accept="image/*">
          <input type="text" id="caption" name="caption" placeholder="Insert Caption">
          <input type="submit" value="Upload" name="postSubmit">
        </form>
      </div>

      <?php
        // Checks if the post submission form has been submitted.
        // Also checks that an image for the post is submitted as well.
        if (isset($_FILES['image']) && isset($_POST['postSubmit'])) {
          $file_name = $_FILES['image']['name'];
          $file_name = $_FILES['image']['name'];
          $file_tmp = $_FILES['image']['tmp_name'];
          // Moves image into appropriate file location.
          move_uploaded_file($file_tmp,"../backend/database/photos/".$file_name);

          // Fetch the latest image_id and add 1 to it for new post submission.
          $queryForImageID = "SELECT MAX(image_id) FROM image";
          $statement = $connect->prepare($queryForImageID);
          $statement->execute();

          $newImageId = $statement->fetchAll()[0][0];
          $newImageId = (int) $newImageId + 1;

          // Insert new image into 'image' table using the new id and file name.
          $queryInsertImage = "INSERT INTO image VALUES ({$newImageId}, '{$file_name}')";
          $statement = $connect->prepare($queryInsertImage);
          $statement->execute();

          // Same as image_id, gets latest post_id and add 1 to it.
          $queryPostID = "SELECT MAX(post_id) FROM post";
          $statement = $connect->prepare($queryPostID);
          $statement->execute();

          $newPostID = $statement->fetchAll()[0][0];
          $newPostID = (int) $newPostID + 1;

          // We didn't get to implement our channels feature properly.
          // Using channel_id 601 for now.
          $channelID = 601;

          $username = $_SESSION['username'];

          // Fetch the user_id associated with the username of the logged in user.
          $queryForUserID = "SELECT user_id, username FROM users";
          $statement = $connect->prepare($queryForUserID);
          $statement->execute();

          $userID = 0;

          // For the above query, gets the user_id
          foreach($statement->fetchAll() as $row) {
            if ($username == $row["username"]) {
              $userID = $row["user_id"];
              break;
            }
          }

          $caption = $_POST['caption'];

          // Query for inserting new post into database using our new post/image ids, user_id, 
          // channel_id, and 0 for the like/reaction count.
          $queryInsertPost = "INSERT INTO post VALUES ({$newPostID}, {$userID}, {$newImageId}, {$channelID}, '{$caption}', 0)";
          
          // Used for the check below to prevent duplicate form submissions.
          $postIDCheck = (int) $newPostID - 1;
          $imageIDCheck = (int) $newImageId - 1;


          /////////////////////////
          //                     //
          //     EXPLANATION     //
          //                     //
          ////////////////////////

          // EXPLANATION: PHP doesn't reset $_POST variable upon refreshing the page/submitting a form.
          // The way our page is set up, if a user refreshes the page/submits some other form (Either
          // by uploading a new post, commenting, or liking a post) the current form is still counted
          // as being submitted. This causes the form to be submitted again upon the page refresh, 
          // resulting in duplicate posts/comments/likes or just breaking the page in general.
          // Our workaround for this, is checking if the post/comment/like has already been
          // submitted into the database (hence the name checkForPost), and if it has we don't
          // execute the insert query (queryInsertPost). We know it's a little confusing and
          // there is probably a better way to solve this problem, but this is the solution
          // we were able to come up with. Thanks for reading all this!
          $checkForPost = "SELECT post_id, user_id, image_id, channel_id, caption, reaction FROM post WHERE post_id = {$postIDCheck} AND user_id = {$userID} AND image_id = {$imageIDCheck} AND channel_id = {$channelID} AND caption = '{$caption}'";
          $statement = $connect->prepare($checkForPost);
          $statement->execute();
          $result = $statement->fetchAll();

          // If post isn't already submitted into database, then submit it.
          if (count($result) == 0) {
            $statement = $connect->prepare($queryInsertPost);
            $statement->execute();
          }
        }
      ?>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
  </div>
  <div class="container">
	<div class="posts">
    <?php
      // Fetches the username, image source, caption, like count (reaction), and post_id needed
      // to begin building each post. Fetches every post in the database.
      $query = "SELECT users.username, image.src, post.caption, post.post_id, post.reaction FROM users INNER JOIN image INNER JOIN post WHERE post.user_id = users.user_id AND post.image_id = image.image_id";
      $statement = $connect->prepare($query);
      $statement->execute();
      $result = $statement->fetchAll();

      // Main loop for building each post
		  foreach($result as $row) {
		    $user = $row[0];
		    $img = '..//backend/database/photos/' . $row[1];
		    $caption = $row[2];
        $likes = $row[4];
        $postID = $row[3];

        // Query for fetching the user_id of the currently logged in user.
        $user_id = "SELECT `user_id` FROM `users` WHERE `username` LIKE '" . $_SESSION["username"] . "';";
		    $uid = "";
		    $statement_2 = $connect->prepare($user_id);
        $statement_2->execute();
        $uid = $statement_2->fetchAll()[0][0];

        // Checks if the comment submission form has been submitted.
        // Form is created within the very large echo statement below.
        // IMPORTANT: Each post has it's own submission form respectively, which is why
        // every $_POST variable relating to comments/likes has ". $postID" appended to it.
        if(isset($_POST['commentSubmit' . $postID])) {
          // Get the latest comment_id and add 1 to it for the new comment being submitted.
          $commentIDQuery = "SELECT MAX(comment_id) FROM status";
          $statement = $connect->prepare($commentIDQuery);
          $statement->execute();
          $newCommentID = $statement->fetchAll()[0][0];
          $newCommentID = (int) $newCommentID + 1;
          $comment = $_POST['comment' . $postID];

          // Query for inserting a new comment into the database.
          $insertCommentQuery = "INSERT INTO status VALUES ({$postID}, {$uid}, {$newCommentID}, '{$comment}')";

          // Used for the check below to prevent duplicate form submissions, for a more
          // in depth explanation of the check, look for "EXPLANATION" in all caps above.
          // Don't worry about the variable name I love sql.
          $IHATESQL = (int) $newCommentID - 1;
        
          // Checks if the comment form has already been submitted, to prevent duplicates.
          $checkForComment = "SELECT post_id, user_id, comment_id, comment FROM status WHERE post_id = {$postID} AND user_id = {$uid} AND comment_id = {$IHATESQL} AND comment = '{$comment}'";
          $statement = $connect->prepare($checkForComment);
          $statement->execute();
          $result = $statement->fetchAll();

          // If the form hasn't already been submitted then submit the new comment.
          if (count($result) == 0) {
            $statement = $connect->prepare($insertCommentQuery);
            $statement->execute();
          }
        }
			
        // Post container start
        echo '<div class="post">';
        echo '<h4 class="card-title">'. $user  .'</h4>';
        echo '<img src="' . $img . '" class="card-img-top" alt="dog">';
        echo '<p>' . $caption . '</p>';	
        echo '<form method="post"><button name="like' . $postID . '" type="submit">Like</button></form>';

        // Checks if form for liking a post has already been submitted.
        // Same idea for the comment form as seen above.
        if (array_key_exists('like' . $postID, $_POST)) {
          // Query used to prevent duplicate form submissions, for a more
          // in depth explanation of the check, look for "EXPLANATION" in all caps above.
          $checkForLike = "SELECT user_id, post_id FROM likes WHERE user_id = {$uid} AND post_id = {$postID}";
          $statement = $connect->prepare($checkForLike);
          $statement->execute();

          // If like form hasn't already been submitted, then submit it.
          if (count($statement->fetchAll()) == 0) {
            // Query for inserting like into "likes" table, which records 
            // the user_id of the logged in user and the id of the post they are liking.
            // Table used to prevent user from liking post more than once.
            $insertLikeQuery = "INSERT INTO `likes` VALUES (" . $postID . ", " . $uid . ");";
            $statement2 = $connect->prepare($insertLikeQuery);
            $statement2->execute();

            // Query for fetching current like/reaction count of the post being liked.
            $getLikeCount = "SELECT reaction FROM post WHERE post_id = {$postID}";
            $statement = $connect->prepare($getLikeCount);
            $statement->execute();

            // Given the current like count, add 1 to it.
            $likes = $statement->fetchAll()[0][0];
            $likes = (int) $likes + 1;
      
            // Query for updating the like/reaction count of the post using our new
            // like count ($likes). A unique user can only like a post a single time.
            $addLikeQuery = "UPDATE post SET reaction = {$likes} WHERE post_id = {$postID}";
            $statement = $connect->prepare($addLikeQuery);
            $statement->execute();
          }
	      }

        // Query for fetching the like/reaction count for the current post being displayed.
        $getLikeCount = "SELECT reaction FROM post WHERE post_id = {$postID}";
        $statement = $connect->prepare($getLikeCount);
        $statement->execute();
        $likes = $statement->fetchAll()[0][0];
        echo '<p>Likes: ' . $likes . '</p>';

        // For each unique post (post_id), fetch every associated comment (along with 
        // the commenter username and user_id).
        $commentQuery = "SELECT status.comment, post.post_id, users.username, users.user_id FROM status INNER JOIN post INNER JOIN users WHERE status.post_id = post.post_id AND post.post_id = {$postID} AND users.user_id = status.user_id";
        $statement = $connect->prepare($commentQuery);
        $statement->execute();
        foreach($statement->fetchAll() as $row) {
          $comment = $row[0];
          $username = $row[2];
          echo '<p>' . $username . ': ' . $comment . '</p>';
        }

        // Builds modal allowing for the currently logged in user to make a commment on a post.
        echo '<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#post'. $postID . '">Make a Comment</button>';
		    echo '<!-- Modal -->
        <div class="modal fade" id="post' . $postID .'" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Write a Comment</h5>
              </div>
              <div class="modal-body">
                <form action="home.php" method="post">
                  <input type="text" id="comment' . $postID .'" name="comment' . $postID . '" placeholder="Insert Comment">
                  <input type="submit" value="Upload" name="commentSubmit' . $postID . '">
                </form>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>';

        // Post container end
        echo '</div>';
		  } 
	  ?>
	</div>
    </div>
</body>
</html>

