<?php

require_once "../config/database.php";
require_once "../includes/auth.php";

requireLogin();
require_once "../includes/header.php";

$ticket_id = $_GET["id"] ?? "";

if (!ctype_digit($ticket_id)) {

    die("Invalid ticket ID.");
}

/*
 * Get the ticket.

 * An employee can only view their own ticket.
 */
$stmt = $pdo->prepare(
    "SELECT
        tickets.id,
        tickets.title,
        tickets.description,
        tickets.priority,
        tickets.status,
        tickets.created_at,
        tickets.updated_at,
        categories.name AS category_name,
        assignee.name AS assigned_name
     FROM tickets
     JOIN categories
        ON tickets.category_id = categories.id
     LEFT JOIN users AS assignee
        ON tickets.assigned_to = assignee.id
     WHERE tickets.id = ?
       AND tickets.user_id = ?"
);

$stmt->execute([
    $ticket_id,
    $_SESSION["user_id"]
]);

$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {

    http_response_code(404);
    die("Ticket not found.");
}

?>

    <h2>
        Ticket #<?php echo htmlspecialchars($ticket["id"]); ?>
    </h2>

    <p>
        <strong>Title:</strong>
        <?php echo htmlspecialchars($ticket["title"]); ?>
    </p>

    <p>
        <strong>Category:</strong>
        <?php echo htmlspecialchars($ticket["category_name"]); ?>
    </p>

    <p>
        <strong>Priority:</strong>
        <?php echo htmlspecialchars($ticket["priority"]); ?>
    </p>

    <p>
        <strong>Status:</strong>
        <?php echo htmlspecialchars($ticket["status"]); ?>
    </p>

    <p>
        <strong>Assigned To:</strong>

        <?php if ($ticket["assigned_name"]): ?>

            <?php echo htmlspecialchars($ticket["assigned_name"]); ?>

        <?php else: ?>

            Not Assigned

        <?php endif; ?>

    </p>

    <h3>Description</h3>

    <p>
        <?php echo nl2br(
            htmlspecialchars($ticket["description"])
        ); ?>
    </p>

    <p>
        <strong>Created At:</strong>
        <?php echo htmlspecialchars($ticket["created_at"]); ?>
    </p>

    <p>
        <strong>Last Updated:</strong>
        <?php echo htmlspecialchars($ticket["updated_at"]); ?>
    </p>

    <hr>

    <a href="/my-tickets.php">
        Back to My Tickets
    </a>

<?php require_once "../includes/footer.php"; ?>