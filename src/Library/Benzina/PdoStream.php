<?php

namespace App\Library\Benzina\Pdo;

use App\Library\Benzina\StreamInterface;

class PdoStream implements StreamInterface
{
    private int $currentBatch = 0;

    private \PDO $db;

    public function __construct(
        string $database,
        private string $tablename,
        private int $sizeOfBatch = 99
    ) {
        $parsedUrl = parse_url($database);
        $dbdata = [
            'name' => ltrim($parsedUrl['path'], '/'),
            ...$parsedUrl
        ];

        $this->db = new \PDO(
            dsn: sprintf("%s:host=%s;dbname=%s", $dbdata['scheme'], $dbdata['host'], $dbdata['name']),
            username: $dbdata['user'],
            password: $dbdata['pass'],
            options: [
                \PDO::ATTR_EMULATE_PREPARES   => false,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]
        );
    }

    public function eof(): bool
    {
        return $this->currentBatch > $this->length();
    }

    public function read(?int $length = null): mixed
    {
        $length = $length ?? $this->sizeOfBatch;
        $query = $this->db->prepare("SELECT * FROM `$this->tablename` LIMIT ? OFFSET ?;");

        $query->execute([
            $length,
            $this->currentBatch
        ]);

        $this->currentBatch += $length;

        return $query->fetchAll();
    }

    public function close(): void
    {
        return;
    }

    public function tell(): int
    {
        return $this->currentBatch;
    }

    public function length(): int
    {
        return $this->db->query("SELECT COUNT(*) FROM `$this->tablename`;")->fetchColumn();
    }
}
