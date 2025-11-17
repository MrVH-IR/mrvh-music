<?php

class Database {
    private $host = 'localhost';
    private $port = '3306';
    private $user = 'mrvhirwg_mrvh_music';
    private $password = ',JtI-Je&mfMKV,U!';
    private $database = 'mrvhirwg_musics';
    private $pdo;

    public function connect()
    {
        if (!$this->pdo) {
            try {
                $this->pdo = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->database", $this->user, $this->password);
            } catch (PDOException $e) {
                $this->createDatabase();
                $this->pdo = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->database", $this->user, $this->password);
            }
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $this->pdo;
    }

    public function migrate()
    {
        $this->createDatabase();
        $pdo = $this->connect();
        
        $migrations = [
            __DIR__ . '/migration/migrations.sql',
            __DIR__ . '/migration/users.sql',
            __DIR__ . '/migration/albums.sql',
            __DIR__ . '/migration/musics.sql'
        ];
        
        foreach ($migrations as $file) {
            if (!file_exists($file)) {
                throw new Exception("Migration file not found: $file");
            }
            $sql = file_get_contents($file);
            $pdo->exec($sql);
        }
    }

    public function seed()
    {
        $pdo = $this->connect();
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        if ($stmt->fetchColumn() > 0) {
            return;
        }
        
        $seedFile = __DIR__ . '/seed/users_seed.sql';
        if (!file_exists($seedFile)) {
            throw new Exception("Seed file not found: $seedFile");
        }
        $sql = file_get_contents($seedFile);
        $pdo->exec($sql);
    }

    private function createDatabase()
    {
        try {
            $pdo = new PDO("mysql:host=$this->host;port=$this->port", $this->user, $this->password);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$this->database`");
        } catch (PDOException $e) {
            throw new Exception("Cannot connect to MySQL: " . $e->getMessage());
        }
    }

    public function provider()
    {
        $this->createDatabase();
        $this->migrate();
        $this->seed();
    }
}

if (basename($_SERVER['PHP_SELF']) === 'database.php') {
    try {
        $db = new Database();
        $db->provider();
        echo "Database setup completed!";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}