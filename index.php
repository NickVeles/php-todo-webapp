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
        mysqli_query($conn, $sql);
      }
    }

    // Delete completed tasks
    else if (isset($_POST["clear_completed"])) {
      $sql = "DELETE FROM task WHERE completed = 1";
      mysqli_query($conn, $sql);
    }

    // Find and handle clicked tasks
    else {
      $sql = "SELECT id, completed FROM task";
      $result = mysqli_query($conn, $sql);

      // Go over each task
      foreach ($result as $row) {
        $id = $row['id'];
        $completed = $row['completed'];

        if (isset($_POST["task_{$id}"])) {
          // Toggle the status of selected task
          $sql = "UPDATE task SET completed = " . ($completed ? "FALSE" : "TRUE") . " WHERE id = {$id}";
          mysqli_query($conn, $sql);
        }
      }
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
    foreach ($result as $row) {
      switch ($row["completed"]) {
        case 1:
          $class = "strikethrough";
          break;
        default:
          $class = "";
          break;
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