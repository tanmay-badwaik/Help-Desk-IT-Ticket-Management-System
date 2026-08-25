<?php

require_once "../config/database.php";
require_once "../includes/auth.php";

requireLogin();
require_once "../includes/header.php";
/*
 * Get tickets created by the logged-in employee
 */
$stmt = $pdo->prepare(
    "SELECT
        tickets.id,
        tickets.title,
        tickets.priority,
        tickets.status,
        tickets.created_at,
        categories.name AS category_name,
        assignee.name AS assigned_name
     FROM tickets
     JOIN categories
        ON tickets.category_id = categories.id
     LEFT JOIN users AS assignee
        ON tickets.assigned_to = assignee.id
     WHERE tickets.user_id = ?
     ORDER BY tickets.created_at DESC"
);

$stmt->execute([
    $_SESSION["user_id"]
]);

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h2>My Tickets</h2>

<p>
    You can view your tickets and their conversations below.
</p>

<p>
    <a href="/create-ticket.php">
        Create New Ticket
    </a>
</p>

<?php if (count($tickets) === 0): ?>

    <p>
        You have not created any tickets yet.
    </p>

<?php else: ?>

    <table border="1" cellpadding="8" cellspacing="0">

        <thead>

            <tr>

                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Action</th>

            </tr>

        </thead>

        <tbody>

            <?php foreach ($tickets as $ticket): ?>

                <tr>

                    <td>
                        <?php echo htmlspecialchars($ticket["id"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($ticket["title"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($ticket["category_name"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($ticket["priority"]); ?>
                    </td>

                    <td>

                        <?php if ($ticket["assigned_name"]): ?>

                            <?php echo htmlspecialchars($ticket["assigned_name"]); ?>

                        <?php else: ?>

                            Not Assigned

                        <?php endif; ?>

                    </td>

                    <td>
                        <?php echo htmlspecialchars($ticket["status"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($ticket["created_at"]); ?>
                    </td>

                    <td>

                        <a href="/ticket-conversation.php?id=<?php echo urlencode($ticket["id"]); ?>">
                            View
                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

<?php endif; ?>

<?php require_once "../includes/footer.php"; ?>