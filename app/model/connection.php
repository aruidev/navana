<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';

class Connection
{
    /**
     * Establish and return a PDO connection to the database.
     * @return PDO The PDO connection object.
     * @throws PDOException If there is an error in the connection.
     */
    public static function getConnection(): PDO
    {
        static $conn = null;
        if ($conn !== null) {
            return $conn;
        }

        $config = config();

        $host = $config['db_host'] ?? 'localhost';
        $dbName = $config['db_name'] ?? '';
        $user = $config['db_user'] ?? 'root';
        $pass = $config['db_pass'] ?? '';
        $charset = $config['db_charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $conn = new PDO($dsn, $user, $pass, $options);
            return $conn;
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
}
?>
