<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {

    header("Location: /login.php");
    exit;
}

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

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        My Tickets - IT Help Desk
    </title>

</head>

<body>

    <h1>IT Help Desk</h1>

    <h2>My Tickets</h2>

    <p>
        Welcome,
        <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
    </p>

    <p>
        <a href="/create-ticket.php">
            Create New Ticket
        </a>
    </p>

    <?php if (count($tickets) === 0): ?>

        <p>
            You have not created any tickets yet.
        </p>

    <?php else: ?>

        <table border="1" cellpadding="8" cellspacing="0">

            <thead>

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
                            <?php echo htmlspecialchars($ticket["id"]); ?>
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

                            <?php if ($ticket["assigned_name"]): ?>

                                <?php echo htmlspecialchars($ticket["assigned_name"]); ?>

                            <?php else: ?>

                                Not Assigned

                            <?php endif; ?>

                        </td>

                        <td>
                            <?php echo htmlspecialchars($ticket["status"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($ticket["created_at"]); ?>
                        </td>

                        <td>

                            <a href="/ticket-conversation.php?id=<?php echo urlencode($ticket["id"]); ?>">
                                View
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

    <br>

    <a href="/dashboard.php">
        Dashboard
    </a>

    |

    <a href="/logout.php">
        Logout
    </a>

</body>

</html>