<?php
require_once __DIR__ . '/../includes/config.php';
unset($_SESSION['socio_id'], $_SESSION['socio_nome']);
header('Location: /soci/login.php');
exit;
