<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: /login.php");
        exit;
    }
}

function requireRole(string $role): void
{
    requireLogin();

    if (($_SESSION["user_role"] ?? "") !== $role) {
        http_response_code(403);
        die("Access denied.");
    }
}