<?php

namespace App\Library\Benzina\Stream;

class PdoStream implements StreamInterface
{
    private int $size = 0;
    private int $currentBatch = 0;

    private \PDO $db;
    private \PDOStatement $query;

    public function __construct(
        string $database,
        private string $tablename,
        private int $sizeOfBatch = 99
    ) {
        $parsedUrl = parse_url($database);
        $dbdata = [
            'name' => ltrim($parsedUrl['path'], '/'),
            ...$parsedUrl,
        ];

        $this->db = new \PDO(
            dsn: sprintf('%s:host=%s;dbname=%s', $dbdata['scheme'], $dbdata['host'], $dbdata['name']),
            username: $dbdata['user'],
            password: $dbdata['pass'],
            options: [
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );

        $this->size = $this->db->query("SELECT COUNT(*) FROM `$tablename`;")->fetchColumn();

        $this->query = $this->db->prepare("SELECT * FROM `$tablename` LIMIT ? OFFSET ?;");
    }

    public function eof(): bool
    {
        return $this->currentBatch > $this->size();
    }

    public function read(?int $length = null): mixed
    {
        $length = $length ?? $this->sizeOfBatch;
        $this->query->execute([
            $length,
            $this->currentBatch,
        ]);

        $this->currentBatch += $length;

        return $this->query->fetchAll();
    }

    public function close(): void
    {
        return;
    }

    public function tell(): int
    {
        return $this->currentBatch;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function rewind(): void
    {
        $this->currentBatch = 0;
    }
}
