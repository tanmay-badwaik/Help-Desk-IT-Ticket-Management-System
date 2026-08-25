<?php

require_once "../config/database.php";
require_once "../includes/header.php";
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
 * Add a new comment
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /*
     * Do not allow comments on closed tickets.
     */
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

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Ticket #<?php echo htmlspecialchars($ticket["id"]); ?>
        - IT Help Desk
    </title>

</head>

<body>

    <h1>IT Help Desk</h1>

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

    <hr>

    <h3>Conversation</h3>

    <?php if (count($comments) === 0): ?>

        <p>
            No comments yet.
        </p>

    <?php else: ?>

        <?php foreach ($comments as $comment): ?>

            <div>

                <p>

                    <strong>
                        <?php echo htmlspecialchars($comment["name"]); ?>
                    </strong>

                    (<?php echo htmlspecialchars($comment["role"]); ?>)

                    -

                    <?php echo htmlspecialchars($comment["created_at"]); ?>

                </p>

                <p>
                    <?php echo nl2br(
                        htmlspecialchars($comment["comment"])
                    ); ?>
                </p>

            </div>

            <hr>

        <?php endforeach; ?>

    <?php endif; ?>

    <?php if ($success): ?>

        <p>
            <?php echo htmlspecialchars($success); ?>
        </p>

    <?php endif; ?>

    <?php if ($error): ?>

        <p>
            <?php echo htmlspecialchars($error); ?>
        </p>

    <?php endif; ?>

    <?php if ($ticket["status"] !== "Closed"): ?>

        <h3>Add Comment</h3>

        <form method="POST">

            <div>

                <label for="comment">
                    Comment:
                </label>

                <br>

                <textarea
                    id="comment"
                    name="comment"
                    rows="5"
                    cols="60"
                    required
                ></textarea>

            </div>

            <br>

            <button type="submit">
                Add Comment
            </button>

        </form>

    <?php else: ?>

        <p>
            This ticket is closed. No further comments can be added.
        </p>

    <?php endif; ?>

    <br>

    <?php if ($_SESSION["user_role"] === "employee"): ?>

        <a href="/my-tickets.php">
            Back to My Tickets
        </a>

    <?php elseif ($_SESSION["user_role"] === "support"): ?>

        <a href="/support/assigned-tickets.php">
            Back to Assigned Tickets
        </a>

    <?php else: ?>

        <a href="/admin/dashboard.php">
            Back to Admin Dashboard
        </a>

    <?php endif; ?>

</body>

</html>
<?php require_once "../includes/footer.php"; ?>