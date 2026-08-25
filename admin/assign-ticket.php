<?php
require_once "../config/database.php";
require_once "../includes/auth.php";

requireRole("admin");
require_once "../includes/header.php";

$ticket_id = $_GET["id"] ?? "";

if (!ctype_digit($ticket_id)) {

    die("Invalid ticket ID.");
}

/*
 * Get ticket information
 */
$stmt = $pdo->prepare(
    "SELECT
        tickets.id,
        tickets.title,
        tickets.status,
        tickets.assigned_to
     FROM tickets
     WHERE tickets.id = ?"
);

$stmt->execute([$ticket_id]);

$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {

    http_response_code(404);
    die("Ticket not found.");
}

/*
 * Get all support users
 */
$stmt = $pdo->query(
    "SELECT id, name, email
     FROM users
     WHERE role = 'support'
     ORDER BY name ASC"
);

$support_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$error = "";
$success = "";

/*
 * Handle assignment
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $support_id = $_POST["support_id"] ?? "";

    if (!ctype_digit($support_id)) {

        $error = "Please select a valid support user.";

    } else {

        /*
         * Make sure selected user is actually a support user
         */
        $stmt = $pdo->prepare(
            "SELECT id
             FROM users
             WHERE id = ?
               AND role = 'support'"
        );

        $stmt->execute([$support_id]);

        $support_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$support_user) {

            $error = "Invalid support user.";

        } else {

            /*
             * Assign ticket and change status
             */
            $stmt = $pdo->prepare(
                "UPDATE tickets
                 SET assigned_to = ?,
                     status = 'Assigned'
                 WHERE id = ?"
            );

            $stmt->execute([
                $support_id,
                $ticket_id
            ]);

            $success = "Ticket assigned successfully.";

            /*
             * Refresh ticket information
             */
            $stmt = $pdo->prepare(
                "SELECT
                    tickets.id,
                    tickets.title,
                    tickets.status,
                    tickets.assigned_to
                 FROM tickets
                 WHERE tickets.id = ?"
            );

            $stmt->execute([$ticket_id]);

            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

?>


    <h2>Assign Ticket</h2>

    <p>
        <strong>Ticket ID:</strong>
        <?php echo htmlspecialchars($ticket["id"]); ?>
    </p>

    <p>
        <strong>Title:</strong>
        <?php echo htmlspecialchars($ticket["title"]); ?>
    </p>

    <p>
        <strong>Status:</strong>
        <?php echo htmlspecialchars($ticket["status"]); ?>
    </p>

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

    <?php if (count($support_users) === 0): ?>

        <p>
            No support users are available.
        </p>

    <?php else: ?>

        <form method="POST">

            <div>

                <label for="support_id">
                    Assign to Support User:
                </label>

                <select
                    id="support_id"
                    name="support_id"
                    required
                >

                    <option value="">
                        -- Select Support User --
                    </option>

                    <?php foreach ($support_users as $support): ?>

                        <option
                            value="<?php echo htmlspecialchars($support["id"]); ?>"
                            <?php
                            if (
                                $ticket["assigned_to"] !== null &&
                                (int) $ticket["assigned_to"] === (int) $support["id"]
                            ) {
                                echo "selected";
                            }
                            ?>
                        >

                            <?php echo htmlspecialchars($support["name"]); ?>

                            -
                            <?php echo htmlspecialchars($support["email"]); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <br>

            <button type="submit">
                Assign Ticket
            </button>

        </form>

    <?php endif; ?>

    <br>

    <a href="/admin/dashboard.php">
        Back to Admin Dashboard
    </a>
<?php require_once "../includes/footer.php"; ?>