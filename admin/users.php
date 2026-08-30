<?php

require_once "../config/database.php";
require_once "../includes/auth.php";

requireRole("admin");

/*
 * Get all users except the currently logged-in admin.
 */
$stmt = $pdo->prepare(
    "SELECT
        id,
        name,
        email,
        role,
        created_at
     FROM users
     WHERE id != ?
     ORDER BY created_at DESC"
);

$stmt->execute([
    $_SESSION["user_id"]
]);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once "../includes/header.php";

?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold mb-1">
            User Management
        </h2>

        <p class="text-muted mb-0">
            View registered users and manage Employee and Support roles.
        </p>

    </div>

</div>


<?php if (count($users) === 0): ?>

    <div class="alert alert-info">
        No users found.
    </div>

<?php else: ?>

    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-dark">

                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($users as $user): ?>

                            <?php

                            $roleClass = match ($user["role"]) {
                                "employee" => "bg-primary",
                                "support" => "bg-success",
                                "admin" => "bg-danger",
                                default => "bg-secondary"
                            };

                            ?>

                            <tr>

                                <td>
                                    #<?php echo htmlspecialchars($user["id"]); ?>
                                </td>

                                <td class="fw-semibold">
                                    <?php echo htmlspecialchars($user["name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($user["email"]); ?>
                                </td>

                                <td>

                                    <span class="badge <?php echo $roleClass; ?>">

                                        <?php echo htmlspecialchars(
                                            ucfirst($user["role"])
                                        ); ?>

                                    </span>

                                </td>

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        date(
                                            "d M Y, h:i A",
                                            strtotime($user["created_at"])
                                        )
                                    );
                                    ?>

                                </td>

                                <td>

                                    <?php if ($user["role"] === "employee"): ?>

                                        <a
                                            href="/admin/change-role.php?id=<?php echo urlencode($user["id"]); ?>"
                                            class="btn btn-sm btn-outline-success"
                                        >
                                            Make Support
                                        </a>

                                    <?php elseif ($user["role"] === "support"): ?>

                                        <a
                                            href="/admin/change-role.php?id=<?php echo urlencode($user["id"]); ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Make Employee
                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No Action
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

<?php endif; ?>


<div class="mt-4">

    <a
        href="/admin/dashboard.php"
        class="btn btn-outline-secondary"
    >
        ← Back to Admin Dashboard
    </a>

</div>

<?php require_once "../includes/footer.php"; ?>