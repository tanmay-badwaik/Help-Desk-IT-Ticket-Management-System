<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>IT Help Desk</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">

    <div class="container">

        <a class="navbar-brand fw-bold" href="#">
            IT Help Desk
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div
            class="collapse navbar-collapse"
            id="mainNavbar"
        >

            <ul class="navbar-nav me-auto">

                <?php if ($_SESSION["user_role"] === "employee"): ?>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="/dashboard.php"
                        >
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="/my-tickets.php"
                        >
                            My Tickets
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="/create-ticket.php"
                        >
                            Create Ticket
                        </a>
                    </li>

                <?php elseif ($_SESSION["user_role"] === "support"): ?>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="/support/assigned-tickets.php"
                        >
                            Assigned Tickets
                        </a>
                    </li>

                <?php elseif ($_SESSION["user_role"] === "admin"): ?>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="/admin/dashboard.php"
                        >
                            Admin Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/users.php">
                            User Management
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

            <div class="d-flex align-items-center text-white gap-3">

                <span>
                    <?php echo htmlspecialchars($_SESSION["user_name"]); ?>

                    <span class="badge bg-primary">
                        <?php echo htmlspecialchars(ucfirst($_SESSION["user_role"])); ?>
                    </span>
                </span>

                <a
                    href="/logout.php"
                    class="btn btn-outline-light btn-sm"
                >
                    Logout
                </a>

            </div>

        </div>

    </div>

</nav>

<main class="container py-4">