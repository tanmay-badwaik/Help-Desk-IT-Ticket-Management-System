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

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            My Tickets
        </h2>

        <p class="text-muted mb-0">
            View your submitted tickets and track their current status.
        </p>

    </div>

    <a
        href="/create-ticket.php"
        class="btn btn-primary"
    >
        + Create Ticket
    </a>

</div>


<?php if (count($tickets) === 0): ?>

    <div class="alert alert-info">

        You have not created any tickets yet.

        <a
            href="/create-ticket.php"
            class="alert-link"
        >
            Create your first ticket
        </a>.

    </div>

<?php else: ?>

    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-dark">

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

                                    #<?php echo htmlspecialchars($ticket["id"]); ?>

                                </td>


                                <td class="fw-semibold">

                                    <?php echo htmlspecialchars($ticket["title"]); ?>

                                </td>


                                <td>

                                    <?php echo htmlspecialchars($ticket["category_name"]); ?>

                                </td>


                                <td>

                                    <?php

                                    $priorityClass = match ($ticket["priority"]) {
                                        "Low" => "bg-secondary",
                                        "Medium" => "bg-info text-dark",
                                        "High" => "bg-warning text-dark",
                                        "Critical" => "bg-danger",
                                        default => "bg-secondary"
                                    };

                                    ?>

                                    <span class="badge <?php echo $priorityClass; ?>">

                                        <?php echo htmlspecialchars($ticket["priority"]); ?>

                                    </span>

                                </td>


                                <td>

                                    <?php if ($ticket["assigned_name"]): ?>

                                        <?php echo htmlspecialchars($ticket["assigned_name"]); ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Not Assigned
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php

                                    $statusClass = match ($ticket["status"]) {

                                        "Open" => "bg-primary",

                                        "Assigned" => "bg-info text-dark",

                                        "In Progress" => "bg-warning text-dark",

                                        "Resolved" => "bg-success",

                                        "Closed" => "bg-secondary",

                                        default => "bg-secondary"

                                    };

                                    ?>

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

                                    <a
                                        href="/ticket-conversation.php?id=<?php echo urlencode($ticket["id"]); ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        View
                                    </a>

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