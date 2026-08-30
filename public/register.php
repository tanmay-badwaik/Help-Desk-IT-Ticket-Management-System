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

            $message = "Registration successful! You can now log in.";

            // Clear form after successful registration
            $name = "";
            $email = "";
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

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

    <div class="container">

        <div
            class="row justify-content-center align-items-center py-5"
            style="min-height: 100vh;"
        >

            <div class="col-md-8 col-lg-5">

                <div class="text-center mb-4">

                    <h1 class="fw-bold">
                        IT Help Desk
                    </h1>

                    <p class="text-muted mb-0">
                        Create your account to submit and track support tickets.
                    </p>

                </div>


                <div class="card shadow border-0">

                    <div class="card-body p-4 p-md-5">

                        <h3 class="fw-bold text-center mb-4">
                            Create Account
                        </h3>


                        <?php if ($message): ?>

                            <div
                                class="alert alert-success"
                                role="alert"
                            >
                                <?php echo htmlspecialchars($message); ?>

                                <a
                                    href="/login.php"
                                    class="alert-link"
                                >
                                    Login
                                </a>
                            </div>

                        <?php endif; ?>


                        <?php if ($error): ?>

                            <div
                                class="alert alert-danger"
                                role="alert"
                            >
                                <?php echo htmlspecialchars($error); ?>
                            </div>

                        <?php endif; ?>


                        <form method="POST">

                            <div class="mb-3">

                                <label
                                    for="name"
                                    class="form-label fw-semibold"
                                >
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    placeholder="Enter your full name"
                                    value="<?php echo htmlspecialchars($name ?? ""); ?>"
                                    required
                                >

                            </div>


                            <div class="mb-3">

                                <label
                                    for="email"
                                    class="form-label fw-semibold"
                                >
                                    Email Address
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter your email"
                                    value="<?php echo htmlspecialchars($email ?? ""); ?>"
                                    required
                                >

                            </div>


                            <div class="mb-3">

                                <label
                                    for="password"
                                    class="form-label fw-semibold"
                                >
                                    Password
                                </label>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Create a password"
                                    minlength="6"
                                    required
                                >

                                <div class="form-text">
                                    Password must be at least 6 characters long.
                                </div>

                            </div>


                            <div class="mb-4">

                                <label
                                    for="confirm_password"
                                    class="form-label fw-semibold"
                                >
                                    Confirm Password
                                </label>

                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    class="form-control"
                                    placeholder="Enter your password again"
                                    minlength="6"
                                    required
                                >

                            </div>


                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                Create Account
                            </button>

                        </form>


                        <hr class="my-4">


                        <p class="text-center text-muted mb-0">

                            Already have an account?

                            <a
                                href="/login.php"
                                class="text-decoration-none fw-semibold"
                            >
                                Login
                            </a>

                        </p>

                    </div>

                </div>


                <p class="text-center text-muted small mt-4">
                    IT Help Desk System
                </p>

            </div>

        </div>

    </div>


    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    ></script>

</body>

</html>