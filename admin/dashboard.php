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

<div class="mb-4">

    <h2 class="fw-bold mb-1">
        Admin Dashboard
    </h2>

    <p class="text-muted mb-0">
        Monitor and manage all help desk tickets.
    </p>

</div>


<div class="row g-3 mb-5">

    <div class="col-md-6 col-lg-2">

        <div class="card shadow-sm border-0 h-100">

            <div class="card-body">

                <p class="text-muted mb-1">
                    Total Tickets
                </p>

                <h2 class="fw-bold mb-0">

                    <?php echo htmlspecialchars($stats["total"]); ?>

                </h2>

            </div>

        </div>

    </div>


    <div class="col-md-6 col-lg-2">

        <div class="card shadow-sm border-0 h-100">

            <div class="card-body">

                <p class="text-muted mb-1">
                    Open
                </p>

                <h2 class="fw-bold text-primary mb-0">

                    <?php echo htmlspecialchars($stats["open"]); ?>

                </h2>

            </div>

        </div>

    </div>


    <div class="col-md-6 col-lg-2">

        <div class="card shadow-sm border-0 h-100">

            <div class="card-body">

                <p class="text-muted mb-1">
                    Assigned
                </p>

                <h2 class="fw-bold text-info mb-0">

                    <?php echo htmlspecialchars($stats["assigned"]); ?>

                </h2>

            </div>

        </div>

    </div>


    <div class="col-md-6 col-lg-2">

        <div class="card shadow-sm border-0 h-100">

            <div class="card-body">

                <p class="text-muted mb-1">
                    In Progress
                </p>

                <h2 class="fw-bold text-warning mb-0">

                    <?php echo htmlspecialchars($stats["in_progress"]); ?>

                </h2>

            </div>

        </div>

    </div>


    <div class="col-md-6 col-lg-2">

        <div class="card shadow-sm border-0 h-100">

            <div class="card-body">

                <p class="text-muted mb-1">
                    Resolved
                </p>

                <h2 class="fw-bold text-success mb-0">

                    <?php echo htmlspecialchars($stats["resolved"]); ?>

                </h2>

            </div>

        </div>

    </div>


    <div class="col-md-6 col-lg-2">

        <div class="card shadow-sm border-0 h-100">

            <div class="card-body">

                <p class="text-muted mb-1">
                    Closed
                </p>

                <h2 class="fw-bold text-secondary mb-0">

                    <?php echo htmlspecialchars($stats["closed"]); ?>

                </h2>

            </div>

        </div>

    </div>

</div>


<div class="mb-3">

    <h3 class="fw-bold mb-1">
        All Tickets
    </h3>

    <p class="text-muted mb-0">
        Review, assign, close, and view ticket conversations.
    </p>

</div>


<?php if (count($tickets) === 0): ?>

    <div class="alert alert-info">

        No tickets found.

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

                            <th>Assigned To</th>

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

                                    <?php if ($ticket["assigned_name"] === null): ?>

                                        <span class="text-muted">
                                            Not Assigned
                                        </span>

                                    <?php else: ?>

                                        <?php echo htmlspecialchars($ticket["assigned_name"]); ?>

                                    <?php endif; ?>

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

                                        <?php if ($ticket["status"] === "Resolved"): ?>

                                            <a
                                                href="/admin/close-ticket.php?id=<?php echo urlencode($ticket["id"]); ?>"
                                                class="btn btn-sm btn-outline-danger"
                                            >
                                                Close Ticket
                                            </a>

                                        <?php elseif ($ticket["assigned_name"] === null): ?>

                                            <a
                                                href="/admin/assign-ticket.php?id=<?php echo urlencode($ticket["id"]); ?>"
                                                class="btn btn-sm btn-primary"
                                            >
                                                Assign
                                            </a>

                                        <?php else: ?>

                                            <span class="text-muted small align-self-center">
                                                No Action
                                            </span>

                                        <?php endif; ?>

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