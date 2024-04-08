<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>To-do</title>
</head>

<body>
  <form action="index.php" method="post">
    <input type="text" name="task" id="task">
    <input type="submit" value="Add">
  </form>
  <?php
  // TODO: Put this into .env file
  $hostname = "localhost";
  $username = "root";
  $password = "";
  $database = "todos";

  // Create connection
  $conn = mysqli_connect(
    $hostname,
    $username,
    $password,
    $database
  );

  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Add a task
    $task = filter_var(trim($_POST["task"]), FILTER_SANITIZE_STRING);
    if ($task) {
      $sql = "INSERT INTO task (title) VALUES ('{$task}')";
      // $sql = "DELETE FROM task WHERE 1";
      mysqli_query($conn, $sql);
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
  }

  // List all tasks
  $sql = "SELECT title FROM task";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) > 0) {

    echo "<ul>";
    // output data of each row
    while ($row = mysqli_fetch_assoc($result)) {
      echo "<li>" . $row["title"] . "</li>";
    }
    echo "</ul>";
  } else {
    echo "0 results";
  }

  // Close the connection
  mysqli_close($conn); 
  ?>

</body>

</html>