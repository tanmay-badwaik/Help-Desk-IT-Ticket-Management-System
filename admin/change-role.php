<?php

require_once "../config/database.php";
require_once "../includes/auth.php";

requireRole("admin");

$user_id = $_GET["id"] ?? "";

if (!ctype_digit($user_id)) {
    die("Invalid user ID.");
}

/*
 * Get selected user
 */
$stmt = $pdo->prepare(
    "SELECT
        id,
        name,
        email,
        role
     FROM users
     WHERE id = ?"
);

$stmt->execute([
    $user_id
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    die("User not found.");
}

/*
 * Do not allow changing Admin role
 */
if ($user["role"] === "admin") {
    http_response_code(403);
    die("Admin role cannot be changed from this page.");
}

$error = "";
$success = "";

/*
 * Change role
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $new_role = $_POST["role"] ?? "";

    $allowed_roles = [
        "employee",
        "support"
    ];

    if (!in_array($new_role, $allowed_roles, true)) {

        $error = "Invalid role selected.";

    } elseif ($new_role === $user["role"]) {

        $error = "The user already has this role.";

    } else {

        $stmt = $pdo->prepare(
            "UPDATE users
             SET role = ?
             WHERE id = ?
               AND role != 'admin'"
        );

        $stmt->execute([
            $new_role,
            $user_id
        ]);

        if ($stmt->rowCount() === 1) {

            $success = "User role updated successfully.";

            $user["role"] = $new_role;

        } else {

            $error = "Unable to update user role.";
        }
    }
}

$roleClass = match ($user["role"]) {
    "employee" => "bg-primary",
    "support" => "bg-success",
    default => "bg-secondary"
};

require_once "../includes/header.php";

?>

<div class="row justify-content-center">

    <div class="col-lg-7">

        <div class="mb-4">

            <h2 class="fw-bold mb-1">
                Change User Role
            </h2>

            <p class="text-muted mb-0">
                Manage Employee and Support access.
            </p>

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


        <div class="card shadow-sm border-0">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-start mb-4">

                    <div>

                        <small class="text-muted">
                            User
                        </small>

                        <h4 class="fw-bold mb-0">
                            <?php echo htmlspecialchars($user["name"]); ?>
                        </h4>

                    </div>

                    <span class="badge <?php echo $roleClass; ?> fs-6">

                        <?php echo htmlspecialchars(
                            ucfirst($user["role"])
                        ); ?>

                    </span>

                </div>


                <div class="mb-4">

                    <small class="text-muted">
                        Email
                    </small>

                    <div class="fw-semibold">

                        <?php echo htmlspecialchars($user["email"]); ?>

                    </div>

                </div>


                <hr>


                <form method="POST">

                    <div class="mb-4">

                        <label
                            for="role"
                            class="form-label fw-semibold"
                        >
                            Select Role
                        </label>

                        <select
                            id="role"
                            name="role"
                            class="form-select"
                            required
                        >

                            <option
                                value="employee"
                                <?php echo $user["role"] === "employee" ? "selected" : ""; ?>
                            >
                                Employee
                            </option>

                            <option
                                value="support"
                                <?php echo $user["role"] === "support" ? "selected" : ""; ?>
                            >
                                Support
                            </option>

                        </select>

                        <div class="form-text">
                            Support users can work on tickets assigned by Admin.
                        </div>

                    </div>


                    <div class="d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Update Role
                        </button>

                        <a
                            href="/admin/users.php"
                            class="btn btn-outline-secondary"
                        >
                            Cancel
                        </a>

                    </div>

                </form>

            </div>

        </div>


        <div class="mt-4">

            <a
                href="/admin/users.php"
                class="btn btn-outline-secondary"
            >
                ← Back to User Management
            </a>

        </div>

    </div>

</div>

<?php require_once "../includes/footer.php"; ?>