<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = null;
for ($i = 0; $i < 10; $i++) {
    try {
        $conn = new mysqli("db", "user", "user123", "todo_db");
        break;
    } catch (mysqli_sql_exception $e) {
        sleep(2);
    }
}
if (!$conn || $conn->connect_error) {
    die("Koneksi gagal: " . ($conn ? $conn->connect_error : "Tidak bisa terhubung ke database"));
}

// Pastikan tabel tasks ada
$conn->query("CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Cek apakah kolom completed sudah ada, jika belum tambahkan
$result = $conn->query("SHOW COLUMNS FROM tasks LIKE 'completed'");
if ($result->num_rows == 0) {
    $conn->query("ALTER TABLE tasks ADD COLUMN completed TINYINT(1) DEFAULT 0");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $conn->query("INSERT INTO tasks (title) VALUES ('$title')");
    header("Location: /");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM tasks WHERE id = $id");
    header("Location: /");
    exit();
}

// Toggle status completed
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE tasks SET completed = NOT completed WHERE id = $id");
    header("Location: /");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Todos</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to bottom right, #fffde7, #fff176);
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
      padding: 50px 20px;
    }

    h1 {
      font-size: 48px;
      color: #555;
      margin-bottom: 20px;
    }

    form {
      width: 100%;
      max-width: 500px;
      display: flex;
      gap: 10px;
      margin-bottom: 30px;
    }

    input[type="text"] {
      flex: 1;
      padding: 14px;
      font-size: 16px;
      border: none;
      border-radius: 30px;
      outline: none;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    button {
      padding: 14px 24px;
      border: none;
      background-color: #fbc02d;
      color: #fff;
      border-radius: 30px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background-color: #f9a825;
    }

    ul {
      list-style: none;
      padding: 0;
      width: 100%;
      max-width: 500px;
    }

    li {
      background-color: white;
      padding: 16px 20px;
      margin-bottom: 15px;
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      transition: 0.3s;
    }

    li.completed {
      opacity: 0.7;
      background-color: #f5f5f5;
    }

    .task-title {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 16px;
      color: #444;
      flex: 1;
      cursor: pointer;
    }

    .task-title.completed {
      text-decoration: line-through;
      color: #888;
    }

    .circle {
      width: 20px;
      height: 20px;
      border: 2px solid #fbc02d;
      border-radius: 50%;
      cursor: pointer;
      transition: 0.3s;
      position: relative;
    }

    .circle.completed {
      background-color: #4CAF50;
      border-color: #4CAF50;
    }

    .circle.completed::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      font-size: 12px;
      font-weight: bold;
    }

    .task-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .delete {
      color: #f44336;
      text-decoration: none;
      font-size: 14px;
      padding: 5px 8px;
      border-radius: 50%;
      transition: 0.3s;
    }

    .delete:hover {
      background-color: #ffebee;
    }
  </style>
</head>
<body>
  <h1>todos</h1>

  <form method="POST">
    <input type="text" name="title" placeholder="What do you need to do?" required>
    <button type="submit">+</button>
  </form>

  <ul>
    <?php
    $result = $conn->query("SELECT * FROM tasks ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
        $completed = $row['completed'] ? 'completed' : '';
        $circleClass = $row['completed'] ? 'circle completed' : 'circle';
        $titleClass = $row['completed'] ? 'task-title completed' : 'task-title';
        
        echo "<li class='$completed'>";
        echo "<div class='$titleClass' onclick=\"window.location.href='?toggle=" . $row['id'] . "'\">";
        echo "<div class='$circleClass'></div>";
        echo htmlspecialchars($row['title']);
        echo "</div>";
        echo "<div class='task-actions'>";
        echo "<a class='delete' href='?delete=" . $row['id'] . "' onclick=\"return confirm('Yakin hapus?')\">✕</a>";
        echo "</div>";
        echo "</li>";
    }
    ?>
  </ul>
</body>
</html>
