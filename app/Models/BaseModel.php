<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Базовая модель.
 *
 * Единственная задача — дать наследникам доступ к $this->db.
 * Никакой магии, никаких ActiveRecord-методов.
 * Каждая модель пишет SQL явно — это читаемо и предсказуемо.
 */
abstract class BaseModel
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}