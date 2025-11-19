<?php
session_start(); ?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Geheimer Chat</title>
  <style>
    body {font-family: Arial, sans-serif; background:#111; color:#fff; display:flex; justify-content:center; align-items:center; height:100vh; margin:0;}
    input {padding:10px; font-size:18px; width:250px;}
    button {padding:10px 20px; font-size:18px; background:#ff4d4d; border:none; color:white; cursor:pointer;}
  </style>
</head>
<body>
  <?php if (!isset($_SESSION['authorized'])): ?>
    <form method="post">
      <h2>Passwort erforderlich</h2>
      <input type="password" name="pw" placeholder="Passwort" required>
      <button type="submit">Weiter</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $passwort = "1234"; // <<<<<< HIER ÄNDERN !!!!!
      if ($_POST['pw'] === $passwort) {
        $_SESSION['authorized'] = true;
        header("Location: chat.php");
        exit;
      } else {
        echo "<p style='color:red'>Falsches Passwort!</p>";
      }
    }
    ?>

  <?php else: header("Location: chat.php"); endif; ?>
</body>
</html>