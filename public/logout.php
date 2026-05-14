<?php

declare(strict_types=1);

require __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/index.php';

// Cierra la sesión y envía al inicio público.
logout();
header('Location: ' . appUrl('index.php'));
exit;
