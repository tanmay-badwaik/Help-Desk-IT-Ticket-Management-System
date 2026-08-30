<?php

require_once "../config/database.php";
require_once "../includes/auth.php";

requireRole("admin");

$ticket_id = $_GET["id"] ?? "";

if (!ctype_digit($ticket_id)) {
    die("Invalid ticket ID.");
}

/*
 * Get the ticket
 */
$stmt = $pdo->prepare(
    "SELECT
        tickets.id,
        tickets.title,
        tickets.status,
        creator.name AS creator_name,
        assignee.name AS assigned_name
     FROM tickets
     JOIN users AS creator
        ON tickets.user_id = creator.id
     LEFT JOIN users AS assignee
        ON tickets.assigned_to = assignee.id
     WHERE tickets.id = ?"
);

$stmt->execute([$ticket_id]);

$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    http_response_code(404);
    die("Ticket not found.");
}

$error = "";
$success = "";

/*
 * Only Resolved tickets can be closed.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($ticket["status"] !== "Resolved") {

        $error = "Only resolved tickets can be closed.";

    } else {

        $stmt = $pdo->prepare(
            "UPDATE tickets
             SET status = 'Closed'
             WHERE id = ?
               AND status = 'Resolved'"
        );

        $stmt->execute([$ticket_id]);

        if ($stmt->rowCount() === 1) {

            $success = "Ticket closed successfully.";

            $ticket["status"] = "Closed";

        } else {

            $error = "Unable to close the ticket.";
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
                Close Ticket
            </h2>

            <p class="text-muted mb-0">
                Review the resolved ticket before closing it.
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


        <div class="card shadow-sm border-0">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-start mb-4">

                    <div>

                        <small class="text-muted">
                            Ticket
                        </small>

                        <h3 class="fw-bold mb-0">

                            #<?php echo htmlspecialchars($ticket["id"]); ?>

                        </h3>

                    </div>

                    <span class="badge <?php echo $statusClass; ?> fs-6">

                        <?php echo htmlspecialchars($ticket["status"]); ?>

                    </span>

                </div>


                <div class="row g-3 mb-4">

                    <div class="col-md-6">

                        <small class="text-muted">
                            Employee
                        </small>

                        <div class="fw-semibold">

                            <?php echo htmlspecialchars($ticket["creator_name"]); ?>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <small class="text-muted">
                            Assigned To
                        </small>

                        <div class="fw-semibold">

                            <?php if ($ticket["assigned_name"]): ?>

                                <?php echo htmlspecialchars($ticket["assigned_name"]); ?>

                            <?php else: ?>

                                <span class="text-muted">
                                    Not Assigned
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <hr>


                <small class="text-muted">
                    Title
                </small>

                <h5 class="fw-bold mb-4">

                    <?php echo htmlspecialchars($ticket["title"]); ?>

                </h5>


                <?php if ($ticket["status"] === "Resolved"): ?>

                    <div class="alert alert-success">

                        This ticket has been resolved by the support team and is ready to be closed.

                    </div>

                    <form method="POST">

                        <button
                            type="submit"
                            class="btn btn-danger"
                        >
                            Close Ticket
                        </button>

                    </form>


                <?php elseif ($ticket["status"] === "Closed"): ?>

                    <div class="alert alert-secondary mb-0">

                        <strong>Ticket Closed.</strong>

                        This ticket has already been closed.

                    </div>


                <?php else: ?>

                    <div class="alert alert-warning mb-0">

                        This ticket cannot be closed yet.

                        Current status:

                        <strong>
                            <?php echo htmlspecialchars($ticket["status"]); ?>
                        </strong>

                    </div>

                <?php endif; ?>

            </div>

        </div>


        <div class="mt-4 d-flex flex-wrap gap-2">

            <a
                href="/ticket-conversation.php?id=<?php echo urlencode($ticket["id"]); ?>"
                class="btn btn-outline-primary"
            >
                View Conversation
            </a>

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