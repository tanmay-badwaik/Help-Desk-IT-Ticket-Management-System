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

<div class="mb-4">

    <h2 class="fw-bold mb-1">
        My Assigned Tickets
    </h2>

    <p class="text-muted mb-0">
        View and manage tickets assigned to you.
    </p>

</div>


<?php if (count($tickets) === 0): ?>

    <div class="alert alert-info">

        No tickets are currently assigned to you.

    </div>

<?php else: ?>

    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-dark">

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

                            <?php

                            $priorityClass = match ($ticket["priority"]) {
                                "Low" => "bg-secondary",
                                "Medium" => "bg-info text-dark",
                                "High" => "bg-warning text-dark",
                                "Critical" => "bg-danger",
                                default => "bg-secondary"
                            };

                            $statusClass = match ($ticket["status"]) {
                                "Open" => "bg-primary",
                                "Assigned" => "bg-info text-dark",
                                "In Progress" => "bg-warning text-dark",
                                "Resolved" => "bg-success",
                                "Closed" => "bg-secondary",
                                default => "bg-secondary"
                            };

                            ?>

                            <tr>

                                <td>

                                    #<?php echo htmlspecialchars($ticket["id"]); ?>

                                </td>

                                <td class="fw-semibold">

                                    <?php echo htmlspecialchars($ticket["creator_name"]); ?>

                                </td>

                                <td>

                                    <?php echo htmlspecialchars($ticket["title"]); ?>

                                </td>

                                <td>

                                    <?php echo htmlspecialchars($ticket["category_name"]); ?>

                                </td>

                                <td>

                                    <span class="badge <?php echo $priorityClass; ?>">

                                        <?php echo htmlspecialchars($ticket["priority"]); ?>

                                    </span>

                                </td>

                                <td>

                                    <span class="badge <?php echo $statusClass; ?>">

                                        <?php echo htmlspecialchars($ticket["status"]); ?>

                                    </span>

                                </td>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        date(
                                            "d M Y, h:i A",
                                            strtotime($ticket["created_at"])
                                        )
                                    );
                                    ?>

                                </td>

                                <td>

                                    <div class="d-flex flex-wrap gap-2">

                                        <a
                                            href="/support/update-ticket.php?id=<?php echo urlencode($ticket["id"]); ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            View / Update
                                        </a>

                                        <a
                                            href="/ticket-conversation.php?id=<?php echo urlencode($ticket["id"]); ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                        >
                                            Conversation
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

<?php endif; ?>


<?php require_once "../includes/footer.php"; ?>