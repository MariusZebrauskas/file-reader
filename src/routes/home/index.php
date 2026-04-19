<?php

declare(strict_types=1);

/**
 * Home route entry: MVC wiring lives in {@see \App\Controller\HomeController}.
 */
require_once dirname(__DIR__, 3) . '/bootstrap.php';

\App\Controller\HomeController::index();
