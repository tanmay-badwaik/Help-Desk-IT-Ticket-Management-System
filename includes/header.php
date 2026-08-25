<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        IT Help Desk
    </title>

</head>

<body>

    <header>

        <h1>IT Help Desk</h1>

        <p>
            Welcome,
            <?php echo htmlspecialchars($_SESSION["user_name"]); ?>

            |

            Role:
            <?php echo htmlspecialchars($_SESSION["user_role"]); ?>
        </p>

        <nav>

            <?php if ($_SESSION["user_role"] === "employee"): ?>

                <a href="/dashboard.php">
                    Dashboard
                </a>

                |

                <a href="/my-tickets.php">
                    My Tickets
                </a>

                |

                <a href="/create-ticket.php">
                    Create Ticket
                </a>

            <?php elseif ($_SESSION["user_role"] === "support"): ?>

                <a href="/support/assigned-tickets.php">
                    Assigned Tickets
                </a>

            <?php elseif ($_SESSION["user_role"] === "admin"): ?>

                <a href="/admin/dashboard.php">
                    Admin Dashboard
                </a>

            <?php endif; ?>

            |

            <a href="/logout.php">
                Logout
            </a>

        </nav>

        <hr>

    </header>