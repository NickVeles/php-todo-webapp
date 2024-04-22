<?php
  // Include Composer libs
  require_once __DIR__ . '/vendor/autoload.php';

  // Load environment variables from .env
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="style.css">
  <script src="script.js"></script>

  <title>To-do</title>
</head>

<body>
  <form action="index.php" method="post">
    <input type="text" name="task" id="task">
    <input type="submit" name="add" value="Add">
  </form>

  <form id="sortingForm" action="index.php" method="get">
    <select id="sortingOption" name="sortingOption">
      <option disabled selected value="default">Sort by:</option>
      <option value="alphAsc">A-Z</option>
      <option value="alphDesc">Z-A</option>
      <option value="dateAsc">Latest first</option>
      <option value="dateDesc">Oldest first</option>
    </select>
  </form>

  <?php
  // Start or resume the session
  session_start();

  // Retrieve environmental variables
  $dbHost = $_ENV["DB_HOST"] ?? "localhost";
  $dbUser = $_ENV["DB_USER"] ?? "root";
  $dbPass = $_ENV["DB_PASS"] ?? "";
  $dbName = $_ENV["DB_NAME"] ?? "todos";

  // Create connection
  $conn = mysqli_connect(
    $dbHost,
    $dbUser,
    $dbPass,
    $dbName
  );

  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Add a task
    if (isset($_POST["add"])) {
      // Trim and sanitize 
      $task = filter_var(trim($_POST["task"]), FILTER_SANITIZE_SPECIAL_CHARS);
      $date = date("Y-m-d H:i:s");
      if ($task) {
        $sql = "INSERT INTO task (title, descr, completed, created_at, user_id) VALUES ('{$task}', NULL, 0, '{$date}', 1)";
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

  // Set the sorting option
  if (isset($_GET["sortingOption"])) {
    // ...when the user selects a new sorting option
    $sortingOption = $_GET["sortingOption"];
    $_SESSION["sortingOption"] = $_GET["sortingOption"];

  } else if (isset($_SESSION["sortingOption"])) {
    // ...when there was a sorting option selected previously in session
    $sortingOption = $_SESSION["sortingOption"];

  } else {
    // ...when there isn't any sorting option selected yet
    $sortingOption = "dateAsc";
  }

  // List all tasks based on the selected sorting option
  switch ($sortingOption) {
    case "alphAsc":
      $orderBy = "title ASC";
      break;
    case "alphDesc":
      $orderBy = "title DESC";
      break;
    case "dateDesc":
      $orderBy = "created_at DESC"; // Latest first
      break;
    default:
      $orderBy = "created_at ASC"; // Oldest first
  }

  $sql = "SELECT id, title, completed FROM task ORDER BY {$orderBy}";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) > 0) {

    // Output data of each row as a button
    echo "<form action='index.php' method='post'>";
    echo "<ul id='taskList'>";
    foreach ($result as $row) {
      $class = ($row['completed'] ? "strikethrough" : "");
      $title = $row["title"];

      echo "<li><button type='submit' class='{$class}' name='task_{$row['id']}'>" . $title . "</button></li>";
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