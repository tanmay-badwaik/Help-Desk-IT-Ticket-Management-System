 <?php
require_once "../config/database.php";
require_once "../includes/auth.php";

requireRole("support");
require_once "../includes/header.php";

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

?>

    <h2>
        Ticket #<?php echo htmlspecialchars($ticket["id"]); ?>
    </h2>

    <?php if ($error): ?>

        <p>
            <?php echo htmlspecialchars($error); ?>
        </p>

    <?php endif; ?>

    <?php if ($success): ?>

        <p>
            <?php echo htmlspecialchars($success); ?>
        </p>

    <?php endif; ?>

    <p>
        <strong>Employee:</strong>
        <?php echo htmlspecialchars($ticket["creator_name"]); ?>
    </p>

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

    <h3>Description</h3>

    <p>
        <?php echo nl2br(htmlspecialchars($ticket["description"])); ?>
    </p>

    <p>
        <strong>Current Status:</strong>
        <?php echo htmlspecialchars($ticket["status"]); ?>
    </p>

    <p>
        <strong>Created:</strong>
        <?php echo htmlspecialchars($ticket["created_at"]); ?>
    </p>

    <p>
        <strong>Last Updated:</strong>
        <?php echo htmlspecialchars($ticket["updated_at"]); ?>
    </p>

    <hr>

    <h3>Update Status</h3>

    <form method="POST">

        <label for="status">
            Status:
        </label>

        <select
            id="status"
            name="status"
            required
        >

            <option value="Assigned"
                <?php
                if ($ticket["status"] === "Assigned") {
                    echo "selected";
                }
                ?>
            >
                Assigned
            </option>

            <option value="In Progress"
                <?php
                if ($ticket["status"] === "In Progress") {
                    echo "selected";
                }
                ?>
            >
                In Progress
            </option>

            <option value="Resolved"
                <?php
                if ($ticket["status"] === "Resolved") {
                    echo "selected";
                }
                ?>
            >
                Resolved
            </option>

        </select>

        <button type="submit">
            Update Status
        </button>

    </form>

    <br>

    <a href="/support/assigned-tickets.php">
        Back to Assigned Tickets
    </a>

<?php require_once "../includes/footer.php"; ?>