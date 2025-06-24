<?php
// Koneksi ke database MySQL di dalam Docker
$conn = new mysqli("mysql-db", "user", "user123", "todo_db");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tambah tugas baru
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $conn->query("INSERT INTO tasks (title) VALUES ('$title')");
    header("Location: /"); // redirect agar tidak kirim ulang
    exit();
}

// Hapus tugas
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM tasks WHERE id = $id");
    header("Location: /"); // redirect agar tidak kirim ulang
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>To-Do List</title>
  <style>
    body {
      background: #f5f7fa;
      font-family: Arial, sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 40px 20px;
    }

    h1 {
      margin-bottom: 20px;
      color: #333;
    }

    form {
      display: flex;
      gap: 10px;
      margin-bottom: 30px;
      max-width: 500px;
      width: 100%;
    }

    input[type="text"] {
      flex: 1;
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    button:hover {
      background-color: #0056b3;
    }

    ul {
      list-style: none;
      padding: 0;
      max-width: 500px;
      width: 100%;
    }

    li {
      background: #fff;
      border: 1px solid #ddd;
      padding: 12px;
      border-radius: 5px;
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
    }

    a {
      color: #dc3545;
      text-decoration: none;
      font-size: 14px;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <h1>📋 To-Do List</h1>

  <form method="POST">
    <input type="text" name="title" placeholder="Tambahkan tugas..." required>
    <button type="submit">Tambah</button>
  </form>

  <ul>
    <?php
    $result = $conn->query("SELECT * FROM tasks ORDER BY id DESC");
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['title']) .
             " <a href='?delete=" . $row['id'] . "' onclick=\"return confirm('Yakin hapus tugas ini?')\">hapus</a></li>";
    }
    ?>
  </ul>
</body>
</html>
