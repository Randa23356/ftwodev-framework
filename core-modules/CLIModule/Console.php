<?php

namespace CoreModules\CLIModule;

class Console
{
    private $args;
    private $colors = [
        'green' => "\033[32m",
        'blue' => "\033[34m",
        'yellow' => "\033[33m",
        'red' => "\033[31m",
        'reset' => "\033[0m"
    ];

    public function __construct($argv)
    {
        $this->args = $argv;
    }

    public function run()
    {
        $this->banner();

        if (!isset($this->args[1])) {
            $this->help();
            return;
        }

        $command = $this->args[1];
        
        switch ($command) {
            case 'craft:controller':
                $this->makeController($this->args[2] ?? null);
                break;
            case 'craft:model':
                $this->makeModel($this->args[2] ?? null);
                break;
            case 'craft:view':
                $this->makeView($this->args[2] ?? null);
                break;
            case 'craft:service':
                $this->makeService($this->args[2] ?? null);
                break;
            case 'craft:migration':
                $this->makeMigration($this->args[2] ?? null);
                break;
            case 'ignite:migrate':
                $this->migrate();
                break;
            case 'ignite:rollback':
                $this->rollback();
                break;
            case 'ignite': // Creative rename for serve
            case 'serve':  // Keep strict alias
                $this->serve();
                break;
            default:
                echo "{$this->colors['red']}Unknown command: $command{$this->colors['reset']}\n";
                $this->help();
        }
    }

    private function banner()
    {
        echo $this->colors['blue'];
        echo "
    ___________               ________               
    \_   _____/___________  __\______ \   _______  __
     |    __)   \____ \_  \/ / |    |  \_/ __ \  \/ /
     |     \    |  |_> >    /  |    `   \  ___/\   / 
     \___  /    |   __/ \/\_/ /_______  /\___  >\_/  
         \/     |__|                  \/     \/      
        ";
        echo $this->colors['reset'] . "\n\n";
    }

    private function help()
    {
        echo "{$this->colors['yellow']}Usage:{$this->colors['reset']}\n";
        echo "  php ftwo ignite                Start the development server\n";
        echo "  php ftwo ignite:migrate        Run database migrations\n";
        echo "  php ftwo ignite:rollback       Rollback last migration\n";
        echo "  php ftwo craft:controller Name Create a new controller\n";
        echo "  php ftwo craft:model Name      Create a new model\n";
        echo "  php ftwo craft:view Name       Create a new view\n";
        echo "  php ftwo craft:service Name    Create a new service\n";
        echo "  php ftwo craft:migration Name  Create a new migration\n";
    }

    private function serve()
    {
        $port = 8000;
        echo "{$this->colors['green']}🔥 FTwoDev engine currently running at http://localhost:$port{$this->colors['reset']}\n";
        echo "{$this->colors['yellow']}Press Ctrl+C to stop the engine.{$this->colors['reset']}\n";
        passthru("php -S localhost:$port -t " . __DIR__ . '/../../public');
    }

    private function makeController($name)
    {
        if (!$name) die("{$this->colors['red']}Error: Name required.{$this->colors['reset']}\n");
        $path = __DIR__ . '/../../projects/Controllers/' . $name . '.php';
        if (file_exists($path)) die("{$this->colors['red']}Error: Controller already exists.{$this->colors['reset']}\n");

        $template = "<?php\n\nnamespace Projects\\Controllers;\n\nuse Engine\\ControllerBase;\n\nclass $name extends ControllerBase\n{\n    public function index()\n    {\n        return \$this->view('welcome');\n    }\n}\n";
        
        file_put_contents($path, $template);
        echo "{$this->colors['green']}Controller $name crafted successfully.{$this->colors['reset']}\n";
    }

    private function makeModel($name)
    {
        if (!$name) die("{$this->colors['red']}Error: Name required.{$this->colors['reset']}\n");
        $path = __DIR__ . '/../../projects/Models/' . $name . '.php';
        if (file_exists($path)) die("{$this->colors['red']}Error: Model already exists.{$this->colors['reset']}\n");

        $template = "<?php\n\nnamespace Projects\\Models;\n\nuse Engine\\ModelBase;\n\nclass $name extends ModelBase\n{\n    protected \$table = '" . strtolower($name) . "s';\n}\n";
        
        file_put_contents($path, $template);
        echo "{$this->colors['green']}Model $name crafted successfully.{$this->colors['reset']}\n";
    }

    private function makeView($name)
    {
        if (!$name) die("{$this->colors['red']}Error: Name required.{$this->colors['reset']}\n");
        $path = __DIR__ . '/../../projects/Views/' . $name . '.ftwo.php';
        if (file_exists($path)) die("{$this->colors['red']}Error: View already exists.{$this->colors['reset']}\n");

        $template = "<h1>$name</h1>\n<p>Welcome to $name view.</p>";
        
        file_put_contents($path, $template);
        echo "{$this->colors['green']}View $name crafted successfully.{$this->colors['reset']}\n";
    }

    private function makeService($name)
    {
        if (!$name) die("{$this->colors['red']}Error: Name required.{$this->colors['reset']}\n");
        $path = __DIR__ . '/../../projects/Services/' . $name . '.php';
        
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        if (file_exists($path)) die("{$this->colors['red']}Error: Service already exists.{$this->colors['reset']}\n");

        $template = "<?php\n\nnamespace Projects\\Services;\n\nclass $name\n{\n    public function execute()\n    {\n        // ...\n    }\n}\n";
        
        file_put_contents($path, $template);
        echo "{$this->colors['green']}Service $name crafted successfully.{$this->colors['reset']}\n";
    }

    private function makeMigration($name)
    {
        if (!$name) die("{$this->colors['red']}Error: Name required.{$this->colors['reset']}\n");
        $timestamp = date('Y_m_d_His');
        $fileName = $timestamp . '_' . $name . '.php';
        $path = __DIR__ . '/../../projects/Migrations/' . $fileName;

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        
        $template = "<?php\n\nnamespace Projects\\Migrations;\n\nuse Engine\\MigrationBase;\n\nclass $className extends MigrationBase\n{\n    public function up()\n    {\n        // \$this->execute(\"CREATE TABLE ...\");\n    }\n\n    public function down()\n    {\n        // \$this->execute(\"DROP TABLE ...\");\n    }\n}\n";

        file_put_contents($path, $template);
        echo "{$this->colors['green']}Migration $fileName crafted successfully.{$this->colors['reset']}\n";
    }

    private function migrate()
    {
        $db = $this->getDatabaseConnection();
        $this->ensureMigrationsTable($db);

        $files = glob(__DIR__ . '/../../projects/Migrations/*.php');
        sort($files);

        $stmt = $db->query("SELECT migration FROM migrations");
        $executed = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $batch = time();
        $count = 0;

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (!in_array($name, $executed)) {
                require_once $file;
                $parts = explode('_', $name);
                $className = "Projects\\Migrations\\" . str_replace(' ', '', ucwords(str_replace('_', ' ', implode('_', array_slice($parts, 4)))));
                
                $migration = new $className($db);
                $migration->up();

                $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$name, $batch]);
                
                echo "{$this->colors['green']}Migrated: $name{$this->colors['reset']}\n";
                $count++;
            }
        }

        if ($count === 0) {
            echo "{$this->colors['yellow']}Nothing to migrate.{$this->colors['reset']}\n";
        }
    }

    private function rollback()
    {
        $db = $this->getDatabaseConnection();
        $this->ensureMigrationsTable($db);

        $stmt = $db->query("SELECT MAX(batch) FROM migrations");
        $lastBatch = $stmt->fetchColumn();

        if (!$lastBatch) {
            echo "{$this->colors['yellow']}Nothing to rollback.{$this->colors['reset']}\n";
            return;
        }

        $stmt = $db->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY migration DESC");
        $stmt->execute([$lastBatch]);
        $migrations = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($migrations as $name) {
            $file = __DIR__ . '/../../projects/Migrations/' . $name . '.php';
            if (file_exists($file)) {
                require_once $file;
                $parts = explode('_', $name);
                $className = "Projects\\Migrations\\" . str_replace(' ', '', ucwords(str_replace('_', ' ', implode('_', array_slice($parts, 4)))));
                
                $migration = new $className($db);
                $migration->down();

                $stmt = $db->prepare("DELETE FROM migrations WHERE migration = ?");
                $stmt->execute([$name]);

                echo "{$this->colors['yellow']}Rolled back: $name{$this->colors['reset']}\n";
            }
        }
    }

    private function ensureMigrationsTable($db)
    {
        $db->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            batch INT,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function getDatabaseConnection()
    {
        $config = require __DIR__ . '/../../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        return new \PDO($dsn, $config['username'], $config['password'], $config['options']);
    }
}

