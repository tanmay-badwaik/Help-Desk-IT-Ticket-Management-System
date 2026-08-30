<?php

require_once "../config/database.php";
require_once "../includes/auth.php";

requireLogin();

$ticket_id = $_GET["id"] ?? "";

if (!ctype_digit($ticket_id)) {
    die("Invalid ticket ID.");
}

/*
 * Check that the logged-in user is allowed to access this ticket.
 *
 * Employee:
 *     Can access tickets they created.
 *
 * Support:
 *     Can access tickets assigned to them.
 *
 * Admin:
 *     Can access every ticket.
 */
if ($_SESSION["user_role"] === "employee") {

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

} elseif ($_SESSION["user_role"] === "support") {

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
           AND tickets.assigned_to = ?"
    );

    $stmt->execute([
        $ticket_id,
        $_SESSION["user_id"]
    ]);

} elseif ($_SESSION["user_role"] === "admin") {

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
         WHERE tickets.id = ?"
    );

    $stmt->execute([
        $ticket_id
    ]);

} else {

    http_response_code(403);
    die("Access denied.");
}

$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {

    http_response_code(404);
    die("Ticket not found or you do not have access.");
}

$error = "";
$success = "";

/*
 * Add new comment
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($ticket["status"] === "Closed") {

        $error = "Comments cannot be added to a closed ticket.";

    } else {

        $comment = trim($_POST["comment"] ?? "");

        if ($comment === "") {

            $error = "Comment cannot be empty.";

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO ticket_comments
                    (ticket_id, user_id, comment)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $ticket_id,
                $_SESSION["user_id"],
                $comment
            ]);

            $success = "Comment added successfully.";
        }
    }
}

/*
 * Get all comments
 */
$stmt = $pdo->prepare(
    "SELECT
        ticket_comments.id,
        ticket_comments.comment,
        ticket_comments.created_at,
        users.name,
        users.role
     FROM ticket_comments
     JOIN users
        ON ticket_comments.user_id = users.id
     WHERE ticket_comments.ticket_id = ?
     ORDER BY ticket_comments.created_at ASC"
);

$stmt->execute([
    $ticket_id
]);

$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
 * Bootstrap classes for priority
 */
$priorityClass = match ($ticket["priority"]) {
    "Low" => "bg-secondary",
    "Medium" => "bg-info text-dark",
    "High" => "bg-warning text-dark",
    "Critical" => "bg-danger",
    default => "bg-secondary"
};

/*
 * Bootstrap classes for status
 */
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

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold mb-1">

            Ticket #<?php echo htmlspecialchars($ticket["id"]); ?>

        </h2>

        <p class="text-muted mb-0">

            <?php echo htmlspecialchars($ticket["title"]); ?>

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


<div class="card shadow-sm border-0 mb-4">

    <div class="card-body p-4">

        <h5 class="fw-bold mb-3">
            Ticket Details
        </h5>

        <div class="row g-3">

            <div class="col-md-4">

                <small class="text-muted">
                    Category
                </small>

                <div class="fw-semibold">

                    <?php echo htmlspecialchars($ticket["category_name"]); ?>

                </div>

            </div>


            <div class="col-md-4">

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


            <div class="col-md-4">

                <small class="text-muted">
                    Created At
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

        </div>

        <hr>

        <h6 class="fw-bold">
            Description
        </h6>

        <p class="mb-0">

            <?php echo nl2br(
                htmlspecialchars($ticket["description"])
            ); ?>

        </p>

    </div>

</div>


<div class="card shadow-sm border-0 mb-4">

    <div class="card-body p-4">

        <h4 class="fw-bold mb-4">
            Conversation
        </h4>


        <?php if (count($comments) === 0): ?>

            <div class="text-center py-4 text-muted">

                No comments yet.

            </div>

        <?php else: ?>

            <?php foreach ($comments as $comment): ?>

                <?php

                $isCurrentUser =
                    $comment["name"] === $_SESSION["user_name"];

                ?>

                <div class="d-flex mb-4 <?php echo $isCurrentUser ? "justify-content-end" : "justify-content-start"; ?>">

                    <div
                        class="p-3 rounded shadow-sm <?php echo $isCurrentUser ? "bg-primary text-white" : "bg-light"; ?>"
                        style="max-width: 75%;"
                    >

                        <div class="d-flex justify-content-between gap-4 mb-2">

                            <strong>

                                <?php echo htmlspecialchars($comment["name"]); ?>

                            </strong>

                            <small class="<?php echo $isCurrentUser ? "text-white-50" : "text-muted"; ?>">

                                <?php
                                echo htmlspecialchars(
                                    date(
                                        "d M Y, h:i A",
                                        strtotime($comment["created_at"])
                                    )
                                );
                                ?>

                            </small>

                        </div>

                        <div class="mb-2">

                            <span
                                class="badge <?php echo $isCurrentUser ? "bg-light text-dark" : "bg-secondary"; ?>"
                            >

                                <?php echo htmlspecialchars(
                                    ucfirst($comment["role"])
                                ); ?>

                            </span>

                        </div>

                        <div>

                            <?php echo nl2br(
                                htmlspecialchars($comment["comment"])
                            ); ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>


<?php if ($success): ?>

    <div class="alert alert-success">

        <?php echo htmlspecialchars($success); ?>

    </div>

<?php endif; ?>


<?php if ($error): ?>

    <div class="alert alert-danger">

        <?php echo htmlspecialchars($error); ?>

    </div>

<?php endif; ?>


<?php if ($ticket["status"] !== "Closed"): ?>

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">
                Add Comment
            </h5>

            <form method="POST">

                <div class="mb-3">

                    <textarea
                        id="comment"
                        name="comment"
                        class="form-control"
                        rows="4"
                        placeholder="Write your message..."
                        required
                    ></textarea>

                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add Comment
                </button>

            </form>

        </div>

    </div>

<?php else: ?>

    <div class="alert alert-secondary">

        <strong>Ticket Closed.</strong>

        This ticket is closed. No further comments can be added.

    </div>

<?php endif; ?>


<div class="mt-3">

    <?php if ($_SESSION["user_role"] === "employee"): ?>

        <a
            href="/my-tickets.php"
            class="btn btn-outline-secondary"
        >
            ← Back to My Tickets
        </a>

    <?php elseif ($_SESSION["user_role"] === "support"): ?>

        <a
            href="/support/assigned-tickets.php"
            class="btn btn-outline-secondary"
        >
            ← Back to Assigned Tickets
        </a>

    <?php else: ?>

        <a
            href="/admin/dashboard.php"
            class="btn btn-outline-secondary"
        >
            ← Back to Admin Dashboard
        </a>

    <?php endif; ?>

</div>


<?php require_once "../includes/footer.php"; ?>