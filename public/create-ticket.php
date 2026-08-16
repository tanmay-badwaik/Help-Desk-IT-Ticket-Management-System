<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category_id = $_POST["category_id"] ?? "";
    $priority = $_POST["priority"] ?? "Medium";

    if ($title === "" || $description === "" || $category_id === "") {

        $error = "Title, description and category are required.";

    } else {

        $stmt = $pdo->prepare(
            "INSERT INTO tickets
            (title, description, user_id, category_id, priority)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $title,
            $description,
            $_SESSION["user_id"],
            $category_id,
            $priority
        ]);

        $success = "Ticket created successfully.";
    }
}

$categories = $pdo->query(
    "SELECT id, name FROM categories ORDER BY name"
)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Ticket - IT Help Desk</title>

</head>

<body>

    <h1>IT Help Desk</h1>

    <h2>Create Ticket</h2>

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

    <form method="POST">

        <div>

            <label for="title">
                Title:
            </label>

            <input
                type="text"
                id="title"
                name="title"
                maxlength="200"
                required
            >

        </div>

        <br>

        <div>

            <label for="description">
                Description:
            </label>

            <br>

            <textarea
                id="description"
                name="description"
                rows="6"
                required
            ></textarea>

        </div>

        <br>

        <div>

            <label for="category_id">
                Category:
            </label>

            <select
                id="category_id"
                name="category_id"
                required
            >

                <option value="">
                    Select Category
                </option>

                <?php foreach ($categories as $category): ?>

                    <option value="<?php echo $category["id"]; ?>">

                        <?php echo htmlspecialchars($category["name"]); ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <br>

        <div>

            <label for="priority">
                Priority:
            </label>

            <select
                id="priority"
                name="priority"
            >

                <option value="Low">
                    Low
                </option>

                <option value="Medium" selected>
                    Medium
                </option>

                <option value="High">
                    High
                </option>

                <option value="Critical">
                    Critical
                </option>

            </select>

        </div>

        <br>

        <button type="submit">
            Create Ticket
        </button>

    </form>

    <br>

    <a href="dashboard.php">
        Back to Dashboard
    </a>

</body>

</html>