<?php
session_start();

// Define the correct password
$correct_password = '110621';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';

    // Validate password
    if ($password === $correct_password) {
        $_SESSION['loggedin'] = true;
        header('Location: analytics.php'); // Redirect to the main page
        exit();
    } else {
        $error_message = 'Invalid password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background-color: #f4f4f9;
    }
    .login-container {
      background: white;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .error {
      color: red;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2 class="text-center">Login</h2>
    <?php if (isset($error_message)): ?>
      <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>
    <form method="post" action="login.php">
      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
  </div>
</body>
</html>
