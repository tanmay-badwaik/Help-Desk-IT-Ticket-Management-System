<?php

require_once "../config/database.php";
require_once "../includes/header.php";
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

?>



    <h2>
        Close Ticket #<?php echo htmlspecialchars($ticket["id"]); ?>
    </h2>

    <?php if ($error): ?>

        <p>
            <?php echo htmlspecialchars($error); ?>
        </p>

    <?php endif; ?>

    <?php if ($success): ?>

        <p>
            <?php echo htmlspecialchars($success); ?>
        </p>

    <?php endif; ?>

    <p>
        <strong>Employee:</strong>
        <?php echo htmlspecialchars($ticket["creator_name"]); ?>
    </p>

    <p>
        <strong>Title:</strong>
        <?php echo htmlspecialchars($ticket["title"]); ?>
    </p>

    <p>
        <strong>Assigned To:</strong>

        <?php if ($ticket["assigned_name"]): ?>

            <?php echo htmlspecialchars($ticket["assigned_name"]); ?>

        <?php else: ?>

            Not Assigned

        <?php endif; ?>

    </p>

    <p>
        <strong>Current Status:</strong>
        <?php echo htmlspecialchars($ticket["status"]); ?>
    </p>

    <?php if ($ticket["status"] === "Resolved"): ?>

        <form method="POST">

            <p>
                This ticket has been resolved by the support team.
            </p>

            <button type="submit">
                Close Ticket
            </button>

        </form>

    <?php elseif ($ticket["status"] === "Closed"): ?>

        <p>
            This ticket is already closed.
        </p>

    <?php else: ?>

        <p>
            This ticket cannot be closed yet.
            Its current status is:
            <strong>
                <?php echo htmlspecialchars($ticket["status"]); ?>
            </strong>
        </p>

    <?php endif; ?>

    <br>

    <a href="/admin/dashboard.php">
        Back to Admin Dashboard
    </a>
<?php require_once "../includes/footer.php"; ?>