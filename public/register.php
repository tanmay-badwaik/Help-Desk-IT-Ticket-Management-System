<?php

require_once "../config/database.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Check required fields
    if ($name === "" || $email === "" || $password === "" || $confirm_password === "") {

        $error = "All fields are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters long.";

    } else {

        // Check whether email already exists
        $stmt = $pdo->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $error = "An account with this email already exists.";

        } else {

            // Hash password
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Insert user
            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $email,
                $hashed_password
            ]);

            $message = "Registration successful!";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Register - IT Help Desk</title>

</head>

<body>

    <h1>IT Help Desk</h1>

    <h2>Create Account</h2>

    <?php if ($message): ?>

        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>


    <?php if ($error): ?>

        <p>
            <?php echo htmlspecialchars($error); ?>
        </p>

    <?php endif; ?>


    <form method="POST">

        <div>

            <label for="name">Name:</label>

            <input
                type="text"
                id="name"
                name="name"
                required
            >

        </div>

        <br>

        <div>

            <label for="email">Email:</label>

            <input
                type="email"
                id="email"
                name="email"
                required
            >

        </div>

        <br>

        <div>

            <label for="password">Password:</label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

        </div>

        <br>

        <div>

            <label for="confirm_password">
                Confirm Password:
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
            >

        </div>

        <br>

        <button type="submit">
            Register
        </button>

    </form>

</body>

</html>