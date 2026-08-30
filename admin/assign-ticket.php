<?php

require_once "../config/database.php";
require_once "../includes/auth.php";

requireRole("admin");

$ticket_id = $_GET["id"] ?? "";

if (!ctype_digit($ticket_id)) {
    die("Invalid ticket ID.");
}

/*
 * Get ticket information
 */
$stmt = $pdo->prepare(
    "SELECT
        tickets.id,
        tickets.title,
        tickets.status,
        tickets.assigned_to
     FROM tickets
     WHERE tickets.id = ?"
);

$stmt->execute([$ticket_id]);

$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    http_response_code(404);
    die("Ticket not found.");
}

/*
 * Get all support users
 */
$stmt = $pdo->query(
    "SELECT id, name, email
     FROM users
     WHERE role = 'support'
     ORDER BY name ASC"
);

$support_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = "";
$success = "";

/*
 * Handle assignment
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $support_id = $_POST["support_id"] ?? "";

    if (!ctype_digit($support_id)) {

        $error = "Please select a valid support user.";

    } else {

        /*
         * Make sure selected user is actually a support user
         */
        $stmt = $pdo->prepare(
            "SELECT id
             FROM users
             WHERE id = ?
               AND role = 'support'"
        );

        $stmt->execute([$support_id]);

        $support_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$support_user) {

            $error = "Invalid support user.";

        } else {

            /*
             * Assign ticket and change status
             */
            $stmt = $pdo->prepare(
                "UPDATE tickets
                 SET assigned_to = ?,
                     status = 'Assigned'
                 WHERE id = ?"
            );

            $stmt->execute([
                $support_id,
                $ticket_id
            ]);

            $success = "Ticket assigned successfully.";

            /*
             * Refresh ticket information
             */
            $stmt = $pdo->prepare(
                "SELECT
                    tickets.id,
                    tickets.title,
                    tickets.status,
                    tickets.assigned_to
                 FROM tickets
                 WHERE tickets.id = ?"
            );

            $stmt->execute([$ticket_id]);

            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

$statusClass = match ($ticket["status"]) {
    "Open" => "bg-primary",
    "Assigned" => "bg-info text-dark",
    "In Progress" => "bg-warning text-dark",
    "Resolved" => "bg-success",
    "Closed" => "bg-secondary",
    default => "bg-secondary"
};

require_once "../includes/header.php";

?>

<div class="row justify-content-center">

    <div class="col-lg-7">

        <div class="mb-4">

            <h2 class="fw-bold mb-1">
                Assign Ticket
            </h2>

            <p class="text-muted mb-0">
                Assign this ticket to an available support user.
            </p>

        </div>


        <?php if ($error): ?>

            <div class="alert alert-danger">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <?php if ($success): ?>

            <div class="alert alert-success">

                <?php echo htmlspecialchars($success); ?>

            </div>

        <?php endif; ?>


        <div class="card shadow-sm border-0 mb-4">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-start mb-3">

                    <div>

                        <small class="text-muted">
                            Ticket
                        </small>

                        <h4 class="fw-bold mb-0">

                            #<?php echo htmlspecialchars($ticket["id"]); ?>

                        </h4>

                    </div>

                    <span class="badge <?php echo $statusClass; ?> fs-6">

                        <?php echo htmlspecialchars($ticket["status"]); ?>

                    </span>

                </div>

                <hr>

                <small class="text-muted">
                    Title
                </small>

                <p class="fw-semibold mb-0">

                    <?php echo htmlspecialchars($ticket["title"]); ?>

                </p>

            </div>

        </div>


        <div class="card shadow-sm border-0">

            <div class="card-body p-4">

                <h4 class="fw-bold mb-3">
                    Support Assignment
                </h4>

                <?php if (count($support_users) === 0): ?>

                    <div class="alert alert-warning mb-0">

                        No support users are currently available.

                    </div>

                <?php else: ?>

                    <form method="POST">

                        <div class="mb-4">

                            <label
                                for="support_id"
                                class="form-label fw-semibold"
                            >
                                Assign to Support User
                            </label>

                            <select
                                id="support_id"
                                name="support_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Support User
                                </option>

                                <?php foreach ($support_users as $support): ?>

                                    <option
                                        value="<?php echo htmlspecialchars($support["id"]); ?>"
                                        <?php
                                        if (
                                            $ticket["assigned_to"] !== null &&
                                            (int) $ticket["assigned_to"] === (int) $support["id"]
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >

                                        <?php echo htmlspecialchars($support["name"]); ?>

                                        -
                                        <?php echo htmlspecialchars($support["email"]); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                            <div class="form-text">
                                Select the support team member who will handle this ticket.
                            </div>

                        </div>


                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Assign Ticket
                            </button>

                            <a
                                href="/admin/dashboard.php"
                                class="btn btn-outline-secondary"
                            >
                                Cancel
                            </a>

                        </div>

                    </form>

                <?php endif; ?>

            </div>

        </div>


        <div class="mt-4">

            <a
                href="/admin/dashboard.php"
                class="btn btn-outline-secondary"
            >
                ← Back to Admin Dashboard
            </a>

        </div>

    </div>

</div>

<?php require_once "../includes/footer.php"; ?>