<?php

namespace App\Library\Benzina\Pdo;

use App\Library\Benzina\ReaderInterface;
use App\Library\Benzina\Stream;

class PdoReader implements ReaderInterface
{
    private \PDO $db;

    /**
     * @var array{name: string, schema: string, host: string, user: string, pass: string}
     */
    public readonly array $config;

    public function __construct(string $databaseUrl)
    {
        $parsedUrl = parse_url($databaseUrl);
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

        $this->config = $dbdata;
    }

    public function get(string $name): PdoStream
    {
        $count = $this->db->query("SELECT COUNT(*) FROM `$name`;")->fetchColumn();
        $query = $this->db->prepare("SELECT * FROM `$name` LIMIT ? OFFSET ?;");

        $stream = new Stream(str_repeat("0", $count));
        return new PdoStream($stream, $query);
    }
}
