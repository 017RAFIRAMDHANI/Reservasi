<?php
require_once __DIR__ . '/config/auth.php';
if (isLoggedIn()) {
    redirect('dashboard.php');
}
redirect('login.php');
