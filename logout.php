<?php
require_once __DIR__ . '/config/auth.php';
session_destroy();
session_start();
setFlash('success', 'Anda telah logout.');
redirect('login.php');
