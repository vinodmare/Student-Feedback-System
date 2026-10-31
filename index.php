<?php
require_once __DIR__ . '/includes/auth.php';

if (current_user()) {
    $role = current_user()['role'];
    redirect("/{$role}/dashboard.php");
}

redirect('/login.php');

