<?php

require_once "../config/database.php";
require_once "../includes/auth.php";

requireRole("support");

$ticket_id = $_GET["id"] ?? "";

if (!ctype_digit($ticket_id)) {
    die("Invalid ticket ID.");
}

/*
 * Get the ticket, but only if it is assigned
 * to the currently logged-in support user.
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
        creator.name AS creator_name,
        categories.name AS category_name
     FROM tickets
     JOIN users AS creator
        ON tickets.user_id = creator.id
     JOIN categories
        ON tickets.category_id = categories.id
     WHERE tickets.id = ?
       AND tickets.assigned_to = ?"
);

$stmt->execute([
    $ticket_id,
    $_SESSION["user_id"]
]);

$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    http_response_code(404);
    die("Ticket not found or not assigned to you.");
}

$error = "";
$success = "";

/*
 * Handle status update
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $status = $_POST["status"] ?? "";

    $allowed_statuses = [
        "Assigned",
        "In Progress",
        "Resolved"
    ];

    if (!in_array($status, $allowed_statuses, true)) {

        $error = "Invalid status selected.";

    } else {

        $stmt = $pdo->prepare(
            "UPDATE tickets
             SET status = ?
             WHERE id = ?
               AND assigned_to = ?"
        );

        $stmt->execute([
            $status,
            $ticket_id,
            $_SESSION["user_id"]
        ]);

        $success = "Ticket status updated successfully.";

        /*
         * Refresh ticket information
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
                creator.name AS creator_name,
                categories.name AS category_name
             FROM tickets
             JOIN users AS creator
                ON tickets.user_id = creator.id
             JOIN categories
                ON tickets.category_id = categories.id
             WHERE tickets.id = ?
               AND tickets.assigned_to = ?"
        );

        $stmt->execute([
            $ticket_id,
            $_SESSION["user_id"]
        ]);

        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

/*
 * Priority badge
 */
$priorityClass = match ($ticket["priority"]) {
    "Low" => "bg-secondary",
    "Medium" => "bg-info text-dark",
    "High" => "bg-warning text-dark",
    "Critical" => "bg-danger",
    default => "bg-secondary"
};

/*
 * Status badge
 */
$statusClass = match ($ticket["status"]) {
    "Assigned" => "bg-info text-dark",
    "In Progress" => "bg-warning text-dark",
    "Resolved" => "bg-success",
    default => "bg-secondary"
};

require_once "../includes/header.php";

?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            Ticket #<?php echo htmlspecialchars($ticket["id"]); ?>
        </h2>

        <p class="text-muted mb-0">
            View ticket details and update its status.
        </p>

    </div>

    <div class="d-flex gap-2">

        <span class="badge <?php echo $priorityClass; ?> fs-6">

            <?php echo htmlspecialchars($ticket["priority"]); ?>

        </span>

        <span class="badge <?php echo $statusClass; ?> fs-6">

            <?php echo htmlspecialchars($ticket["status"]); ?>

        </span>

    </div>

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


<div class="row g-4">

    <div class="col-lg-8">

        <div class="card shadow-sm border-0 h-100">

            <div class="card-body p-4">

                <h4 class="fw-bold mb-4">
                    Ticket Details
                </h4>

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
                            Category
                        </small>

                        <div class="fw-semibold">

                            <?php echo htmlspecialchars($ticket["category_name"]); ?>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <small class="text-muted">
                            Created
                        </small>

                        <div class="fw-semibold">

                            <?php
                            echo htmlspecialchars(
                                date(
                                    "d M Y, h:i A",
                                    strtotime($ticket["created_at"])
                                )
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <small class="text-muted">
                            Last Updated
                        </small>

                        <div class="fw-semibold">

                            <?php
                            echo htmlspecialchars(
                                date(
                                    "d M Y, h:i A",
                                    strtotime($ticket["updated_at"])
                                )
                            );
                            ?>

                        </div>

                    </div>

                </div>

                <hr>

                <h5 class="fw-bold">
                    <?php echo htmlspecialchars($ticket["title"]); ?>
                </h5>

                <p class="text-muted mb-2">
                    Description
                </p>

                <p class="mb-0">

                    <?php echo nl2br(
                        htmlspecialchars($ticket["description"])
                    ); ?>

                </p>

            </div>

        </div>

    </div>


    <div class="col-lg-4">

        <div class="card shadow-sm border-0">

            <div class="card-body p-4">

                <h4 class="fw-bold mb-3">
                    Update Status
                </h4>

                <p class="text-muted">
                    Change the current progress of this ticket.
                </p>

                <form method="POST">

                    <div class="mb-3">

                        <label
                            for="status"
                            class="form-label fw-semibold"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-select"
                            required
                        >

                            <option
                                value="Assigned"
                                <?php echo $ticket["status"] === "Assigned" ? "selected" : ""; ?>
                            >
                                Assigned
                            </option>

                            <option
                                value="In Progress"
                                <?php echo $ticket["status"] === "In Progress" ? "selected" : ""; ?>
                            >
                                In Progress
                            </option>

                            <option
                                value="Resolved"
                                <?php echo $ticket["status"] === "Resolved" ? "selected" : ""; ?>
                            >
                                Resolved
                            </option>

                        </select>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Update Status
                    </button>

                </form>

                <hr>

                <a
                    href="/ticket-conversation.php?id=<?php echo urlencode($ticket["id"]); ?>"
                    class="btn btn-outline-secondary w-100"
                >
                    Open Conversation
                </a>

            </div>

        </div>

    </div>

</div>


<div class="mt-4">

    <a
        href="/support/assigned-tickets.php"
        class="btn btn-outline-secondary"
    >
        ← Back to Assigned Tickets
    </a>

</div>

<?php require_once "../includes/footer.php"; ?>