<?php

require_once "../includes/auth.php";

requireLogin();

require_once "../includes/header.php";

?>

<div class="row justify-content-center">

    <div class="col-lg-8">

        <div class="card shadow-sm border-0">

            <div class="card-body p-4">

                <div class="mb-4">

                    <h2 class="fw-bold mb-2">
                        Employee Dashboard
                    </h2>

                    <p class="text-muted mb-0">
                        Welcome back,
                        <?php echo htmlspecialchars($_SESSION["user_name"]); ?>.
                    </p>

                </div>

                <div class="row g-3 mb-4">

                    <div class="col-md-6">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted">
                                Email
                            </small>

                            <p class="mb-0 fw-semibold">
                                <?php echo htmlspecialchars($_SESSION["user_email"]); ?>
                            </p>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="border rounded p-3 h-100">

                            <small class="text-muted">
                                Role
                            </small>

                            <p class="mb-0">

                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars(ucfirst($_SESSION["user_role"])); ?>
                                </span>

                            </p>

                        </div>

                    </div>

                </div>

                <div class="row g-3">

                    <div class="col-md-6">

                        <div class="card h-100 border-0 bg-light">

                            <div class="card-body">

                                <h5 class="card-title">
                                    Create Ticket
                                </h5>

                                <p class="card-text text-muted">
                                    Report a new technical issue to the IT support team.
                                </p>

                                <a
                                    href="/create-ticket.php"
                                    class="btn btn-primary"
                                >
                                    Create Ticket
                                </a>

                            </div>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="card h-100 border-0 bg-light">

                            <div class="card-body">

                                <h5 class="card-title">
                                    My Tickets
                                </h5>

                                <p class="card-text text-muted">
                                    View your submitted tickets and check their current status.
                                </p>

                                <a
                                    href="/my-tickets.php"
                                    class="btn btn-outline-primary"
                                >
                                    View My Tickets
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once "../includes/footer.php"; ?>