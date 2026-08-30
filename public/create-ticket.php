<?php

require_once "../config/database.php";
require_once "../includes/auth.php";

requireLogin();
require_once "../includes/header.php";

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

        $title = "";
        $description = "";
        $category_id = "";
        $priority = "Medium";
    }
}

$categories = $pdo->query(
    "SELECT id, name FROM categories ORDER BY name"
)->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="row justify-content-center">

    <div class="col-lg-8">

        <div class="mb-4">

            <h2 class="fw-bold mb-1">
                Create Ticket
            </h2>

            <p class="text-muted mb-0">
                Describe your issue and submit it to the IT support team.
            </p>

        </div>

        <?php if ($error): ?>

            <div class="alert alert-danger" role="alert">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>

        <?php if ($success): ?>

            <div class="alert alert-success" role="alert">

                <?php echo htmlspecialchars($success); ?>

                <a
                    href="/my-tickets.php"
                    class="alert-link"
                >
                    View My Tickets
                </a>

            </div>

        <?php endif; ?>


        <div class="card shadow-sm border-0">

            <div class="card-body p-4">

                <form method="POST">

                    <div class="mb-3">

                        <label
                            for="title"
                            class="form-label fw-semibold"
                        >
                            Ticket Title
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="form-control"
                            maxlength="200"
                            placeholder="Example: Laptop Wi-Fi not working"
                            value="<?php echo htmlspecialchars($title ?? ""); ?>"
                            required
                        >

                        <div class="form-text">
                            Enter a short title describing the problem.
                        </div>

                    </div>


                    <div class="mb-3">

                        <label
                            for="description"
                            class="form-label fw-semibold"
                        >
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="form-control"
                            rows="6"
                            placeholder="Describe the issue in detail..."
                            required
                        ><?php echo htmlspecialchars($description ?? ""); ?></textarea>

                    </div>


                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label
                                for="category_id"
                                class="form-label fw-semibold"
                            >
                                Category
                            </label>

                            <select
                                id="category_id"
                                name="category_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Category
                                </option>

                                <?php foreach ($categories as $category): ?>

                                    <option
                                        value="<?php echo htmlspecialchars($category["id"]); ?>"
                                        <?php
                                        if (
                                            isset($category_id) &&
                                            (string) $category_id === (string) $category["id"]
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >

                                        <?php echo htmlspecialchars($category["name"]); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="col-md-6 mb-3">

                            <label
                                for="priority"
                                class="form-label fw-semibold"
                            >
                                Priority
                            </label>

                            <select
                                id="priority"
                                name="priority"
                                class="form-select"
                            >

                                <option
                                    value="Low"
                                    <?php echo (($priority ?? "Medium") === "Low") ? "selected" : ""; ?>
                                >
                                    Low
                                </option>

                                <option
                                    value="Medium"
                                    <?php echo (($priority ?? "Medium") === "Medium") ? "selected" : ""; ?>
                                >
                                    Medium
                                </option>

                                <option
                                    value="High"
                                    <?php echo (($priority ?? "Medium") === "High") ? "selected" : ""; ?>
                                >
                                    High
                                </option>

                                <option
                                    value="Critical"
                                    <?php echo (($priority ?? "Medium") === "Critical") ? "selected" : ""; ?>
                                >
                                    Critical
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="d-flex gap-2 mt-3">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Create Ticket
                        </button>

                        <a
                            href="/my-tickets.php"
                            class="btn btn-outline-secondary"
                        >
                            Cancel
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php require_once "../includes/footer.php"; ?>