<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
<h2>Register</h2>
<?php if (isset($errors)) { ?>
    <?php foreach ($errors as $error): ?>
        <div style="color: red;"><?php echo $error; ?></div>
    <?php endforeach; ?>
<?php } ?>
<form method="POST" action="/signup">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br><br>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br><br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br><br>
    <label for="password2">Password 2:</label>
    <input type="password" id="password2" name="password2" required><br><br>
    <input type="submit" value="Register">
</form>
</body>
</html>
