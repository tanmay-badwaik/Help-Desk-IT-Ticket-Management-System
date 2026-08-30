<?php

session_start();

require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Email and password are required.";

    } else {

        $stmt = $pdo->prepare(
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["user_role"] = $user["role"];

            if ($user["role"] === "admin") {

                header("Location: /admin/dashboard.php");
                exit;

            } elseif ($user["role"] === "support") {

                header("Location: /support/assigned-tickets.php");
                exit;

            } else {

                header("Location: /dashboard.php");
                exit;
            }

        } else {

            $error = "Invalid email or password.";
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

    <title>Login - IT Help Desk</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

    <div class="container">

        <div
            class="row justify-content-center align-items-center"
            style="min-height: 100vh;"
        >

            <div class="col-md-7 col-lg-5">

                <div class="text-center mb-4">

                    <h1 class="fw-bold">
                        IT Help Desk
                    </h1>

                    <p class="text-muted mb-0">
                        Sign in to manage your support tickets.
                    </p>

                </div>


                <div class="card shadow border-0">

                    <div class="card-body p-4 p-md-5">

                        <h3 class="fw-bold text-center mb-4">
                            Login
                        </h3>


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


                            <div class="mb-4">

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
                                    placeholder="Enter your password"
                                    required
                                >

                            </div>


                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                Login
                            </button>

                        </form>


                        <hr class="my-4">


                        <p class="text-center text-muted mb-0">

                            Don't have an account?

                            <a
                                href="/register.php"
                                class="text-decoration-none fw-semibold"
                            >
                                Register
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