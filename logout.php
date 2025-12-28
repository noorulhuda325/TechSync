<?php
/**
 * Logout Handler
 * Destroys session and redirects to login
 */

require_once 'includes/config.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header('Location: ' . BASE_URL . 'index.php');
exit();

