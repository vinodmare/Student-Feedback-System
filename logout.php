<?php
require_once __DIR__ . '/includes/auth.php';
logout_user();
session_start();
flash('success', 'Logged out successfully.');
redirect('/login.php');

