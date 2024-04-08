<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="style.css">

  <title>To-do</title>
</head>

<body>
  <form action="index.php" method="post">
    <input type="text" name="task" id="task">
    <input type="submit" name="add" value="Add">
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
    if (isset($_POST["add"])) {
      // Trim and sanitize 
      $task = filter_var(trim($_POST["task"]), FILTER_SANITIZE_STRING);
      $date = date("Y-m-d H:i:s");
      if ($task) {
        $sql = "INSERT INTO task (title, descr, completed, created_at) VALUES ('{$task}', NULL, 0, '{$date}')";
        // $sql = "DELETE FROM task WHERE 1"; // !> DELETE LATER
        mysqli_query($conn, $sql);
      }
    }

    // Delete completed tasks
    else if (isset($_POST["clear_completed"])) {
      $sql = "DELETE FROM task WHERE completed = 1";
      mysqli_query($conn, $sql);
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
  }

  // List all tasks
  $sql = "SELECT id, title, completed  FROM task";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) > 0) {

    // Output data of each row as a button
    echo "<form action='index.php' method='post'>";
    echo "<ul>";
    while ($row = mysqli_fetch_assoc($result)) {
      if ($row["completed"] == 1) {
        $class = "strikethrough";
      } else {
        $class = "";
      }
      echo "<li><button type='submit' class='{$class}' name='task_{$row['id']}'>" . $row["title"] . "</button></li>";
    }
    echo "</ul>";
    echo "</form>";
  } else {
    echo "No results";
  }

  // Close the connection
  mysqli_close($conn);
  ?>

  <form action="index.php" method="post">
    <input type="submit" name="clear_completed" value="Clear Completed">
  </form>

</body>

</html>