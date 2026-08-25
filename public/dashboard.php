<?php
require_once "../includes/header.php";
require_once "../includes/auth.php";

requireLogin();

?>

<h2>
    Dashboard
</h2>

<h3>
    Welcome,
    <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
</h3>

<p>
    Email:
    <?php echo htmlspecialchars($_SESSION["user_email"]); ?>
</p>

<p>
    Role:
    <?php echo htmlspecialchars($_SESSION["user_role"]); ?>
</p>

<?php require_once "../includes/footer.php"; ?>