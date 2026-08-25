<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

requireRole("support");
require_once "../includes/header.php";
/*
 * Get tickets assigned to the logged-in support user
 */
$stmt = $pdo->prepare(
    "SELECT
        tickets.id,
        tickets.title,
        tickets.priority,
        tickets.status,
        tickets.created_at,
        creator.name AS creator_name,
        categories.name AS category_name
     FROM tickets
     JOIN users AS creator
        ON tickets.user_id = creator.id
     JOIN categories
        ON tickets.category_id = categories.id
     WHERE tickets.assigned_to = ?
     ORDER BY tickets.created_at DESC"
);

$stmt->execute([
    $_SESSION["user_id"]
]);

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


    <h3>My Assigned Tickets</h3>

    <?php if (count($tickets) === 0): ?>

        <p>
            No tickets are currently assigned to you.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8" cellspacing="0">
            <thead>

                <tr>

                    <th>ID</th>
                    <th>Employee</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
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
                            <?php echo htmlspecialchars($ticket["creator_name"]); ?>
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
                            <?php echo htmlspecialchars($ticket["status"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($ticket["created_at"]); ?>
                        </td>
                        <td>
                            <a href="/support/update-ticket.php?id=<?php echo urlencode($ticket["id"]); ?>">
                                View / Update
                            </a>

                            <br>

                            <a href="/ticket-conversation.php?id=<?php echo urlencode($ticket["id"]); ?>">
                                Conversation
                            </a>
                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>
<?php require_once "../includes/footer.php"; ?>