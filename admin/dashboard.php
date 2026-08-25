<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

requireRole("admin");
require_once "../includes/header.php";

$stmt = $pdo->query(
    "SELECT
        tickets.id,
        tickets.title,
        tickets.priority,
        tickets.status,
        tickets.created_at,
        creator.name AS creator_name,
        categories.name AS category_name,
        assignee.name AS assigned_name
     FROM tickets
     JOIN users AS creator
        ON tickets.user_id = creator.id
     JOIN categories
        ON tickets.category_id = categories.id
     LEFT JOIN users AS assignee
        ON tickets.assigned_to = assignee.id
     ORDER BY tickets.created_at DESC"
);

$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statsStmt = $pdo->query(
    "SELECT
        COUNT(*) AS total,
        SUM(status = 'Open') AS open,
        SUM(status = 'Assigned') AS assigned,
        SUM(status = 'In Progress') AS in_progress,
        SUM(status = 'Resolved') AS resolved,
        SUM(status = 'Closed') AS closed
     FROM tickets"
);

$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

?>

    <h2>Admin Dashboard</h2>
    <h3>Ticket Summary</h3>

    <table border="1" cellpadding="8" cellspacing="0">

        <tr>
            <th>Total Tickets</th>
            <th>Open</th>
            <th>Assigned</th>
            <th>In Progress</th>
            <th>Resolved</th>
            <th>Closed</th>
        </tr>

        <tr>

            <td>
                <?php echo htmlspecialchars($stats["total"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($stats["open"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($stats["assigned"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($stats["in_progress"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($stats["resolved"]); ?>
            </td>

            <td>
                <?php echo htmlspecialchars($stats["closed"]); ?>
            </td>

        </tr>

    </table>
    <h3>All Tickets</h3>

    <?php if (count($tickets) === 0): ?>

        <p>
            No tickets found.
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
                    <th>Assigned To</th>
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

                            <?php if ($ticket["assigned_name"] === null): ?>

                                Not Assigned

                            <?php else: ?>

                                <?php echo htmlspecialchars($ticket["assigned_name"]); ?>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?php echo htmlspecialchars($ticket["created_at"]); ?>
                        </td>
                        <td>

                            <?php if ($ticket["status"] === "Resolved"): ?>

                                <a href="/admin/close-ticket.php?id=<?php echo urlencode($ticket["id"]); ?>">
                                    Close Ticket
                                </a>

                            <?php elseif ($ticket["assigned_name"] === null): ?>

                                <a href="/admin/assign-ticket.php?id=<?php echo urlencode($ticket["id"]); ?>">
                                    Assign
                                </a>

                            <?php else: ?>

                                No Action

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

    <br>

<?php require_once "../includes/footer.php"; ?>