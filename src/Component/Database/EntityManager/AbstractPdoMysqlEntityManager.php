<?php

namespace Kaa\Component\Database\EntityManager;

use PDO;

abstract class AbstractPdoMysqlEntityManager implements EntityManagerInterface
{
    protected PDO $pdo;

    public function __construct(
        string $host,
        string $database,
        string $user,
        string $password
    ) {
        $this->pdo = new PDO("mysql:host={$host};dbname={$database}", $user, $password);
    }
}
