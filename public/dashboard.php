<?php

session_start();

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Dashboard - IT Help Desk</title>

</head>

<body>

    <h1>IT Help Desk Dashboard</h1>

    <h2>
        Welcome,
        <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
    </h2>

    <p>
        Email:
        <?php echo htmlspecialchars($_SESSION["user_email"]); ?>
    </p>

    <p>
        Role:
        <?php echo htmlspecialchars($_SESSION["user_role"]); ?>
    </p>

    <p>
        <a href="logout.php">Logout</a>
    </p>

</body>

</html>