<?php

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Guild Master') {
    header("Location: ../login.php");
    exit;
}
?>