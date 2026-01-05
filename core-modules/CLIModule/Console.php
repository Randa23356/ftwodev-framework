<?php

namespace CoreModules\CLIModule;

class Console
{
    private $args;
    private $colors = [
        'green' => "\033[38;5;48m",    // Emerald
        'blue' => "\033[38;5;45m",     // Cyan-Blue
        'yellow' => "\033[38;5;220m",   // Gold
        'red' => "\033[38;5;196m",      // Critical Red
        'gray' => "\033[38;5;244m",     // Slate Gray
        'white' => "\033[1;37m",        // Bold White
        'reset' => "\033[0m"
    ];

    public function __construct($argv)
    {
        $this->args = $argv;
    }

    public function run()
    {
        if (!isset($this->args[1])) {
            $this->banner();
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
            case 'ignite:fresh':
                $this->migrateFresh();
                break;
            case 'ignite:bloom':
                $this->installBloom();
                break;
            case 'ignite:setup':
                $this->setupFramework();
                break;
            case 'ignite:env':
                $this->generateEnv();
                break;
            case 'db:check':
                $this->checkDatabase();
                break;
            case 'db:setup':
                $this->setupDatabase();
                break;
            case 'make:session-table':
                $this->makeSessionTable();
                break;
            case 'version':
            case '--version':
            case '-v':
                $this->showVersion();
                break;
            case 'ignite:refresh':
                $this->refresh();
                break;
            case 'ignite':
            case 'serve':
                $this->serve();
                break;
            default:
                $this->banner();
                $this->error("Unknown command: $command");
                $this->help();
        }
    }

    private function banner()
    {
        echo "\n";
        echo "  {$this->colors['green']} ███████╗████████╗██╗    ██╗ ██████╗ ██████╗ ███████╗██╗   ██╗ {$this->colors['reset']}\n";
        echo "  {$this->colors['green']} ██╔════╝╚══██╔══╝██║    ██║██╔═══██╗██╔══██╗██╔════╝██║   ██║ {$this->colors['reset']}\n";
        echo "  {$this->colors['green']} █████╗     ██║   ██║ █╗ ██║██║   ██║██║  ██║█████╗  ██║   ██║ {$this->colors['reset']}\n";
        echo "  {$this->colors['green']} ██╔══╝     ██║   ██║███╗██║██║   ██║██║  ██║██╔══╝  ╚██╗ ██╔╝ {$this->colors['reset']}\n";
        echo "  {$this->colors['green']} ██║        ██║   ╚███╔███╔╝╚██████╔╝██████╔╝███████╗ ╚████╔╝  {$this->colors['reset']}\n";
        echo "  {$this->colors['green']} ╚═╝        ╚═╝    ╚══╝╚══╝  ╚═════╝ ╚═════╝ ╚══════╝  ╚═══╝   {$this->colors['reset']}\n";
        echo "  {$this->colors['gray']} ---------------------------------------------------------------- {$this->colors['reset']}\n";
        echo "  {$this->colors['white']}   FTwoDev Engine v" . \Engine\Boot::VERSION . " {$this->colors['gray']} | {$this->colors['green']} Advanced Native PHP Framework {$this->colors['reset']}\n\n";
    }

    private function help()
    {
        echo "  {$this->colors['white']}USAGE:{$this->colors['reset']} php ftwo <command> [arguments]\n\n";

        echo "  {$this->colors['blue']}IGNITE (System){$this->colors['reset']}\n";
        echo "    {$this->colors['green']}ignite{$this->colors['reset']}              Start development engine\n";
        echo "    {$this->colors['green']}ignite:migrate{$this->colors['reset']}      Run database migrations\n";
        echo "    {$this->colors['green']}ignite:rollback{$this->colors['reset']}     Rollback last migration batch\n";
        echo "    {$this->colors['green']}ignite:fresh{$this->colors['reset']}        Drop all tables & re-run migrations\n";
        echo "    {$this->colors['green']}ignite:bloom{$this->colors['reset']}        Plant Bloom Auth Starter Kit\n";
        echo "    {$this->colors['green']}ignite:setup{$this->colors['reset']}        Setup basic framework structure\n";
        echo "    {$this->colors['green']}ignite:env{$this->colors['reset']}          Generate .env file from .env.example\n";
        echo "    {$this->colors['green']}ignite:refresh{$this->colors['reset']}      Refresh & sync framework classes\n\n";

        echo "  {$this->colors['blue']}DATABASE (Setup & Check){$this->colors['reset']}\n";
        echo "    {$this->colors['green']}db:check{$this->colors['reset']}            Check database connection\n";
        echo "    {$this->colors['green']}db:setup{$this->colors['reset']}            Setup database and create database\n\n";

        echo "  {$this->colors['blue']}CRAFT (Scaffolding){$this->colors['reset']}\n";
        echo "    {$this->colors['green']}craft:controller{$this->colors['reset']}   Create a new Controller\n";
        echo "    {$this->colors['green']}craft:model{$this->colors['reset']}        Create a new Model\n";
        echo "    {$this->colors['green']}craft:view{$this->colors['reset']}         Create a new View\n";
        echo "    {$this->colors['green']}craft:service{$this->colors['reset']}      Create a new Service class\n";
        echo "    {$this->colors['green']}craft:migration{$this->colors['reset']}    Create a new Migration file\n";
        echo "    {$this->colors['green']}make:session-table{$this->colors['reset']}  Create session table migration\n\n";
    }

    private function success($msg) { echo "  {$this->colors['green']}✔ SUCCESS:{$this->colors['reset']} $msg\n"; }
    private function info($msg) { echo "  {$this->colors['blue']}ℹ INFO:{$this->colors['reset']} $msg\n"; }
    private function warning($msg) { echo "  {$this->colors['yellow']}⚠ WARNING:{$this->colors['reset']} $msg\n"; }
    private function error($msg) { echo "  {$this->colors['red']}✖ ERROR:{$this->colors['reset']} $msg\n"; }

    private function serve()
    {
        $port = 8000;
        $this->banner();
        $this->success("FTwoDev engine ignited at {$this->colors['white']}http://localhost:$port{$this->colors['reset']}");
        $this->info("Press Ctrl+C to stop the engine.");
        passthru("php -S localhost:$port -t " . __DIR__ . '/../../public');
    }

    private function makeController($name)
    {
        if (!$name) die($this->error("Name required."));
        
        // Ensure directory exists
        $dir = __DIR__ . '/../../projects/Controllers';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $path = $dir . '/' . $name . '.php';
        if (file_exists($path)) die($this->error("Controller $name already exists."));

        $template = "<?php\n\nnamespace Projects\\Controllers;\n\nuse Engine\\ControllerBase;\n\nclass $name extends ControllerBase\n{\n    public function index()\n    {\n        return \$this->view('welcome');\n    }\n}\n";
        
        file_put_contents($path, $template);
        $this->success("Controller $name crafted successfully.");
    }

    private function makeModel($name)
    {
        if (!$name) die($this->error("Name required."));
        
        // Ensure directory exists
        $dir = __DIR__ . '/../../projects/Models';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $path = $dir . '/' . $name . '.php';
        if (file_exists($path)) die($this->error("Model $name already exists."));

        $template = "<?php\n\nnamespace Projects\\Models;\n\nuse Engine\\ModelBase;\n\nclass $name extends ModelBase\n{\n    protected \$table = '" . strtolower($name) . "s';\n    protected \$timestamps = true;\n    protected \$softDeletes = false;\n}\n";
        
        file_put_contents($path, $template);
        $this->success("Model $name crafted successfully.");
    }

    private function makeView($name)
    {
        if (!$name) die($this->error("Name required."));
        
        // Ensure directory exists
        $dir = __DIR__ . '/../../projects/Views';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $path = $dir . '/' . $name . '.ftwo.php';
        if (file_exists($path)) die($this->error("View $name already exists."));

        $template = "<h1>$name</h1>\n<p>Welcome to $name view.</p>";
        
        file_put_contents($path, $template);
        $this->success("View $name crafted successfully.");
    }

    private function makeService($name)
    {
        if (!$name) die($this->error("Name required."));
        
        // Ensure directory exists
        $dir = __DIR__ . '/../../projects/Services';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $path = $dir . '/' . $name . '.php';
        if (file_exists($path)) die($this->error("Service $name already exists."));

        $template = "<?php\n\nnamespace Projects\\Services;\n\nclass $name\n{\n    public function execute()\n    {\n        // ...\n    }\n}\n";
        
        file_put_contents($path, $template);
        $this->success("Service $name crafted successfully.");
    }

    private function makeMigration($name)
    {
        if (!$name) die($this->error("Name required."));
        $timestamp = date('Y_m_d_His');
        $fileName = $timestamp . '_' . $name . '.php';
        
        // Ensure directory exists
        $dir = __DIR__ . '/../../projects/Migrations';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $path = $dir . '/' . $fileName;

        $className = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        $template = "<?php\n\nnamespace Projects\\Migrations;\n\nuse Engine\\MigrationBase;\n\nclass $className extends MigrationBase\n{\n    public function up()\n    {\n        // \$this->execute(\"CREATE TABLE ...\");\n    }\n\n    public function down()\n    {\n        // \$this->execute(\"DROP TABLE ...\");\n    }\n}\n";

        file_put_contents($path, $template);
        $this->success("Migration $fileName crafted successfully.");
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
                
                $this->success("Migrated: $name");
                $count++;
            }
        }

        if ($count === 0) $this->warning("Nothing to migrate.");
    }

    private function rollback()
    {
        $db = $this->getDatabaseConnection();
        $this->ensureMigrationsTable($db);

        $stmt = $db->query("SELECT MAX(batch) FROM migrations");
        $lastBatch = $stmt->fetchColumn();

        if (!$lastBatch) {
            $this->warning("Nothing to rollback.");
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

                $this->warning("Rolled back: $name");
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

    private function checkDatabase()
    {
        $this->banner();
        $this->info("🔍 Checking database connection...");
        
        $config = require __DIR__ . '/../../config/database.php';
        
        $this->info("Database Configuration:");
        $this->info("Host: {$config['host']}");
        $this->info("Database: {$config['dbname']}");
        $this->info("Username: {$config['username']}");
        
        try {
            // Test connection without database first
            $dsn = "mysql:host={$config['host']};charset={$config['charset']}";
            $pdo = new \PDO($dsn, $config['username'], $config['password'], $config['options']);
            $this->success("✅ MySQL server is running and accessible!");
            
            // Check if database exists
            $stmt = $pdo->query("SHOW DATABASES LIKE '{$config['dbname']}'");
            if ($stmt->rowCount() > 0) {
                $this->success("✅ Database '{$config['dbname']}' exists!");
                
                // Test full connection
                $fullDsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
                $fullPdo = new \PDO($fullDsn, $config['username'], $config['password'], $config['options']);
                $this->success("✅ Full database connection successful!");
                
                $this->info("\n🚀 Ready to run migrations:");
                $this->info("php ftwo ignite:migrate");
            } else {
                $this->error("❌ Database '{$config['dbname']}' does not exist!");
                $this->info("\n💡 To create database:");
                $this->info("php ftwo db:setup");
                $this->info("Or manually: mysql -u {$config['username']} -p -e \"CREATE DATABASE {$config['dbname']}\"");
            }
            
        } catch (\PDOException $e) {
            $this->error("❌ Database connection failed!");
            $this->error("Error: " . $e->getMessage());
            
            if (strpos($e->getMessage(), 'No such file or directory') !== false) {
                $this->error("MySQL server is not running or not installed.");
                $this->info("\n🔧 To fix this:");
                $this->info("1. Install MySQL: brew install mysql");
                $this->info("2. Start MySQL: brew services start mysql");
                $this->info("3. Check status: brew services list | grep mysql");
                $this->info("4. Alternative: Use Docker");
                $this->info("   docker run --name mysql-ftwo -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=ftwodev_db -p 3306:3306 -d mysql:8.0");
            } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
                $this->error("Access denied for user '{$config['username']}'.");
                $this->info("\n🔧 To fix this:");
                $this->info("1. Check username/password in .env file");
                $this->info("2. Grant privileges: mysql -u root -p");
                $this->info("   CREATE USER '{$config['username']}'@'localhost' IDENTIFIED BY 'password';");
                $this->info("   GRANT ALL PRIVILEGES ON *.* TO '{$config['username']}'@'localhost';");
                $this->info("   FLUSH PRIVILEGES;");
            }
        }
    }

    private function setupDatabase()
    {
        $this->banner();
        $this->info("🔧 Setting up database...");
        
        $config = require __DIR__ . '/../../config/database.php';
        
        try {
            // Connect without database
            $dsn = "mysql:host={$config['host']};charset={$config['charset']}";
            $pdo = new \PDO($dsn, $config['username'], $config['password'], $config['options']);
            
            // Create database if not exists
            $stmt = $pdo->query("CREATE DATABASE IF NOT EXISTS `{$config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->success("✅ Database '{$config['dbname']}' created successfully!");
            
            // Test full connection
            $fullDsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $fullPdo = new \PDO($fullDsn, $config['username'], $config['password'], $config['options']);
            $this->success("✅ Database connection test passed!");
            
            $this->info("\n🚀 Database is ready! Next steps:");
            $this->info("1. php ftwo ignite:migrate    # Run migrations");
            $this->info("2. php ftwo ignite:bloom      # Add auth system");
            $this->info("3. php ftwo ignite            # Start development server");
            
        } catch (\PDOException $e) {
            $this->error("❌ Database setup failed!");
            $this->error("Error: " . $e->getMessage());
            
            if (strpos($e->getMessage(), 'No such file or directory') !== false) {
                $this->error("MySQL server is not running!");
                $this->info("\n🔧 Start MySQL first:");
                $this->info("brew services start mysql");
                $this->info("Or use Docker:");
                $this->info("docker run --name mysql-ftwo -e MYSQL_ROOT_PASSWORD=password -e MYSQL_DATABASE=ftwodev_db -p 3306:3306 -d mysql:8.0");
            }
        }
    }

    private function getDatabaseConnection()
    {
        $config = require __DIR__ . '/../../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        
        try {
            return new \PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (\PDOException $e) {
            $this->error("Database Connection Failed!");
            $this->error("Error: " . $e->getMessage());
            
            if (strpos($e->getMessage(), 'No such file or directory') !== false) {
                $this->error("MySQL server is not running or not installed.");
                $this->info("To fix this:");
                $this->info("1. Install MySQL: brew install mysql");
                $this->info("2. Start MySQL: brew services start mysql");
                $this->info("3. Or use Docker: docker run --name mysql -e MYSQL_ROOT_PASSWORD=password -p 3306:3306 mysql:8.0");
            } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
                $this->error("Database '{$config['dbname']}' does not exist.");
                $this->info("Create database: mysql -u {$config['username']} -p -e \"CREATE DATABASE {$config['dbname']}\"");
            } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
                $this->error("Access denied for user '{$config['username']}'.");
                $this->info("Check your username and password in .env file");
            }
            
            $this->info("Check your .env file configuration:");
            $this->info("DB_HOST={$config['host']}");
            $this->info("DB_DATABASE={$config['dbname']}");
            $this->info("DB_USERNAME={$config['username']}");
            
            exit(1);
        }
    }

    private function installBloom()
    {
        $this->banner();
        $this->info("🌸 Installing Bloom Auth Starter Kit...");
        
        $stubsPath = __DIR__ . '/stubs/Auth/';
        $projectsPath = __DIR__ . '/../../projects/';
        
        // Check if stubs exist
        if (!is_dir($stubsPath)) {
            $this->error("Auth stubs not found. Framework installation may be incomplete.");
            return;
        }
        
        // Create necessary directories
        $directories = ['Controllers', 'Models', 'Middlewares', 'Migrations'];
        foreach ($directories as $dir) {
            $fullPath = $projectsPath . $dir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
                $this->info("Created directory: {$dir}");
            }
        }
        
        // 1. Create AuthController
        $this->info("Creating AuthController...");
        $authControllerStub = file_get_contents($stubsPath . 'AuthController.stub');
        file_put_contents($projectsPath . 'Controllers/AuthController.php', $authControllerStub);
        $this->success("AuthController created successfully.");
        
        // 2. Create DashboardController
        if (file_exists($stubsPath . 'DashboardController.stub')) {
            $this->info("Creating DashboardController...");
            $dashboardControllerStub = file_get_contents($stubsPath . 'DashboardController.stub');
            file_put_contents($projectsPath . 'Controllers/DashboardController.php', $dashboardControllerStub);
            $this->success("DashboardController created successfully.");
        }
        
        // 3. Create AuthMiddleware
        if (file_exists($stubsPath . 'AuthMiddleware.stub')) {
            $this->info("Creating AuthMiddleware...");
            $authMiddlewareStub = file_get_contents($stubsPath . 'AuthMiddleware.stub');
            file_put_contents($projectsPath . 'Middlewares/AuthMiddleware.php', $authMiddlewareStub);
            $this->success("AuthMiddleware created successfully.");
        }
        
        // 4. Create User Model
        $this->info("Creating User model...");
        if (file_exists($stubsPath . 'UserModel.stub')) {
            $userModelStub = file_get_contents($stubsPath . 'UserModel.stub');
            file_put_contents($projectsPath . 'Models/User.php', $userModelStub);
            $this->success("User model created successfully.");
        }
        
        // 5. Create User Migration
        $this->info("Creating User migration...");
        $timestamp = date('Y_m_d_His');
        $migrationFileName = $timestamp . '_create_users_table.php';
        $migrationStub = file_get_contents($stubsPath . 'migration.stub');
        file_put_contents($projectsPath . 'Migrations/' . $migrationFileName, $migrationStub);
        $this->success("User migration created: $migrationFileName");
        
        // 6. Create Auth Views
        $viewsPath = $projectsPath . 'Views/auth/';
        if (!is_dir($viewsPath)) {
            mkdir($viewsPath, 0755, true);
        }
        
        // Login view
        if (file_exists($stubsPath . 'login.stub')) {
            $this->info("Creating login view...");
            $loginStub = file_get_contents($stubsPath . 'login.stub');
            file_put_contents($viewsPath . 'login.ftwo.php', $loginStub);
            $this->success("Login view created successfully.");
        }
        
        // Register view
        if (file_exists($stubsPath . 'register.stub')) {
            $this->info("Creating register view...");
            $registerStub = file_get_contents($stubsPath . 'register.stub');
            file_put_contents($viewsPath . 'register.ftwo.php', $registerStub);
            $this->success("Register view created successfully.");
        }
        
        // Dashboard view
        if (file_exists($stubsPath . 'dashboard.stub')) {
            $this->info("Creating dashboard view...");
            $dashboardStub = file_get_contents($stubsPath . 'dashboard.stub');
            file_put_contents($viewsPath . 'dashboard.ftwo.php', $dashboardStub);
            $this->success("Dashboard view created successfully.");
        }
        
        // 6. Create Auth Module (if exists)
        $moduleStubPath = $stubsPath . 'Module/';
        if (is_dir($moduleStubPath)) {
            $authModulePath = __DIR__ . '/../AuthModule/';
            if (!is_dir($authModulePath)) {
                mkdir($authModulePath, 0755, true);
            }
            
            $moduleFiles = glob($moduleStubPath . '*.stub');
            foreach ($moduleFiles as $moduleFile) {
                $fileName = basename($moduleFile, '.stub') . '.php';
                $this->info("Creating Auth module: $fileName");
                $moduleContent = file_get_contents($moduleFile);
                file_put_contents($authModulePath . $fileName, $moduleContent);
                $this->success("Auth module $fileName created successfully.");
            }
        }
        
        // 7. Update routes (basic auth routes)
        $this->info("Adding auth routes...");
        $routesFile = __DIR__ . '/../../config/routes.php';
        if (file_exists($routesFile)) {
            $routesContent = file_get_contents($routesFile);
            
            // Check if auth routes already exist (check for actual routes, not comments)
            if (strpos($routesContent, "Router::get('/login', 'AuthController@showLogin')") === false) {
                $authRoutes = "\n\n// Auth Routes (Added by Bloom)\n";
                $authRoutes .= "Router::get('/login', 'AuthController@showLogin');\n";
                $authRoutes .= "Router::post('/login', 'AuthController@login');\n";
                $authRoutes .= "Router::get('/register', 'AuthController@showRegister');\n";
                $authRoutes .= "Router::post('/register', 'AuthController@register');\n";
                $authRoutes .= "Router::get('/logout', 'AuthController@logout');\n";
                $authRoutes .= "Router::get('/dashboard', 'DashboardController@index');\n";
                
                // Add before the Magic Routes comment
                if (strpos($routesContent, '// Magic Routes (Automatic):') !== false) {
                    $routesContent = str_replace('// Magic Routes (Automatic):', $authRoutes . "\n// Magic Routes (Automatic):", $routesContent);
                } else {
                    $routesContent .= $authRoutes;
                }
                
                file_put_contents($routesFile, $routesContent);
                $this->success("Auth routes added to routes.php");
            } else {
                $this->warning("Auth routes already exist in routes.php");
            }
        }
        
        echo "\n";
        $this->success("🌸 Bloom Auth Starter Kit installed successfully!");
        echo "\n  {$this->colors['white']}NEXT STEPS:{$this->colors['reset']}\n";
        echo "  1. {$this->colors['blue']}php ftwo ignite:migrate{$this->colors['reset']}     - Run the user migration\n";
        echo "  2. {$this->colors['blue']}php ftwo ignite:refresh{$this->colors['reset']}     - Refresh autoload classes\n";
        echo "  3. {$this->colors['blue']}php ftwo ignite{$this->colors['reset']}             - Start your development server\n";
        echo "\n  {$this->colors['white']}AUTH ROUTES AVAILABLE:{$this->colors['reset']}\n";
        echo "  • {$this->colors['green']}/login{$this->colors['reset']}      - Login page\n";
        echo "  • {$this->colors['green']}/register{$this->colors['reset']}   - Registration page\n";
        echo "  • {$this->colors['green']}/dashboard{$this->colors['reset']}  - Protected dashboard\n";
        echo "  • {$this->colors['green']}/logout{$this->colors['reset']}     - Logout\n\n";
    }

    private function setupFramework()
    {
        $this->banner();
        $this->info("🚀 Setting up basic framework structure...");
        
        $projectsPath = __DIR__ . '/../../projects/';
        
        // 1. Create WelcomeController
        $welcomeControllerPath = $projectsPath . 'Controllers/WelcomeController.php';
        if (!file_exists($welcomeControllerPath)) {
            $this->info("Creating WelcomeController...");
            $welcomeControllerContent = "<?php\n\nnamespace Projects\\Controllers;\n\nuse Engine\\ControllerBase;\n\nclass WelcomeController extends ControllerBase\n{\n    public function index()\n    {\n        return \$this->view('welcome');\n    }\n}\n";
            file_put_contents($welcomeControllerPath, $welcomeControllerContent);
            $this->success("WelcomeController created successfully.");
        } else {
            $this->warning("WelcomeController already exists.");
        }
        
        // 2. Create HomeController
        $homeControllerPath = $projectsPath . 'Controllers/HomeController.php';
        if (!file_exists($homeControllerPath)) {
            $this->info("Creating HomeController...");
            $homeControllerContent = "<?php\n\nnamespace Projects\\Controllers;\n\nuse Engine\\ControllerBase;\n\nclass HomeController extends ControllerBase\n{\n    public function index()\n    {\n        return \$this->view('welcome');\n    }\n\n    public function about()\n    {\n        return \$this->view('about');\n    }\n}\n";
            file_put_contents($homeControllerPath, $homeControllerContent);
            $this->success("HomeController created successfully.");
        } else {
            $this->warning("HomeController already exists.");
        }
        
        // 3. Create about view
        $aboutViewPath = $projectsPath . 'Views/about.ftwo.php';
        if (!file_exists($aboutViewPath)) {
            $this->info("Creating about view...");
            $aboutViewContent = "@extends('layout')\n\n@section('title', 'About')\n\n@section('content')\n<div class=\"container\">\n    <h1>About FTwoDev Framework</h1>\n    <p>This is a lightweight native PHP framework designed for rapid development.</p>\n    <p>Visit <a href=\"/\">Home</a> to get started.</p>\n    <p>Need authentication? Run <code>php ftwo ignite:bloom</code></p>\n</div>\n@endsection";
            file_put_contents($aboutViewPath, $aboutViewContent);
            $this->success("About view created successfully.");
        } else {
            $this->warning("About view already exists.");
        }
        
        // 4. Update routes
        $this->info("Updating routes...");
        $routesFile = __DIR__ . '/../../config/routes.php';
        if (file_exists($routesFile)) {
            $routesContent = file_get_contents($routesFile);
            
            // Replace closure route with controller route
            if (strpos($routesContent, "Router::get('/', function()") !== false) {
                $routesContent = str_replace(
                    "Router::get('/', function() {\n    return view('welcome');\n});",
                    "Router::get('/', 'WelcomeController@index');",
                    $routesContent
                );
                $this->success("Updated home route to use WelcomeController.");
            }
            
            // Add about route if it doesn't exist
            if (strpos($routesContent, '/about') === false) {
                $defaultRoutes = "\n// Default Routes\nRouter::get('/about', 'HomeController@about');\n";
                
                if (strpos($routesContent, '// Examples (Manual):') !== false) {
                    $routesContent = str_replace('// Examples (Manual):', $defaultRoutes . "\n// Examples (Manual):", $routesContent);
                } else {
                    $routesContent .= $defaultRoutes;
                }
                
                file_put_contents($routesFile, $routesContent);
                $this->success("Default routes added.");
            } else {
                $this->warning("Default routes already exist.");
            }
        }
        
        // 5. Refresh autoload
        $this->info("Refreshing autoload classes...");
        shell_exec('composer dump-autoload');
        $this->success("Autoload refreshed successfully.");
        
        echo "\n";
        $this->success("🚀 Basic framework structure setup complete!");
        echo "\n  {$this->colors['white']}AVAILABLE ROUTES:{$this->colors['reset']}\n";
        echo "  • {$this->colors['green']}/{$this->colors['reset']}         - Welcome page (WelcomeController)\n";
        echo "  • {$this->colors['green']}/about{$this->colors['reset']}    - About page (HomeController)\n";
        echo "\n  {$this->colors['white']}NEXT STEPS:{$this->colors['reset']}\n";
        echo "  1. {$this->colors['blue']}php ftwo ignite{$this->colors['reset']}             - Start development server\n";
        echo "  2. {$this->colors['blue']}php ftwo ignite:bloom{$this->colors['reset']}       - Add authentication (optional)\n";
        echo "  3. {$this->colors['blue']}php ftwo craft:controller{$this->colors['reset']}   - Create new controllers\n\n";
    }

    private function generateEnv()
    {
        $this->banner();
        $this->info("🔧 Generating .env file...");
        
        $exampleFile = __DIR__ . '/../../.env.example';
        $envFile = __DIR__ . '/../../.env';
        
        if (!file_exists($exampleFile)) {
            $this->error(".env.example file not found!");
            return;
        }
        
        if (file_exists($envFile)) {
            echo "  {$this->colors['white']}.env file already exists. Overwrite? [y/N]: {$this->colors['reset']}";
            $confirm = trim(fgets(STDIN));
            if (strtolower($confirm) !== 'y') {
                $this->info("Operation cancelled.");
                return;
            }
        }
        
        // Copy .env.example to .env
        copy($exampleFile, $envFile);
        
        // Generate APP_KEY
        $key = 'base64:' . base64_encode(random_bytes(32));
        $content = file_get_contents($envFile);
        $content = str_replace('APP_KEY=', "APP_KEY=$key", $content);
        file_put_contents($envFile, $content);
        
        $this->success(".env file generated successfully!");
        $this->success("APP_KEY generated: $key");
        $this->info("Please update your database and other configurations in .env file.");
    }

    private function makeSessionTable()
    {
        $this->banner();
        $this->info("📋 Creating session table migration...");
        
        $timestamp = date('Y_m_d_His');
        $fileName = $timestamp . '_create_sessions_table.php';
        $path = __DIR__ . '/../../projects/Migrations/' . $fileName;

        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $template = "<?php\n\nnamespace Projects\\Migrations;\n\nuse Engine\\MigrationBase;\n\nclass CreateSessionsTable extends MigrationBase\n{\n    public function up()\n    {\n        \$this->execute(\"CREATE TABLE sessions (\n            id VARCHAR(255) PRIMARY KEY,\n            user_id BIGINT UNSIGNED NULL,\n            ip_address VARCHAR(45) NULL,\n            user_agent TEXT NULL,\n            payload LONGTEXT NOT NULL,\n            last_activity INT NOT NULL,\n            INDEX sessions_user_id_index (user_id),\n            INDEX sessions_last_activity_index (last_activity)\n        )\");\n    }\n\n    public function down()\n    {\n        \$this->execute(\"DROP TABLE sessions\");\n    }\n}\n";

        file_put_contents($path, $template);
        $this->success("Session table migration created: $fileName");
        $this->info("Run 'php ftwo ignite:migrate' to create the sessions table.");
    }

    private function showVersion()
    {
        echo "\n";
        echo "  {$this->colors['green']} ███████╗████████╗██╗    ██╗ ██████╗ ██████╗ ███████╗██╗   ██╗ {$this->colors['reset']}\n";
        echo "  {$this->colors['green']} ██╔════╝╚══██╔══╝██║    ██║██╔═══██╗██╔══██╗██╔════╝██║   ██║ {$this->colors['reset']}\n";
        echo "  {$this->colors['green']} █████╗     ██║   ██║ █╗ ██║██║   ██║██║  ██║█████╗  ██║   ██║ {$this->colors['reset']}\n";
        echo "  {$this->colors['green']} ██╔══╝     ██║   ██║███╗██║██║   ██║██║  ██║██╔══╝  ╚██╗ ██╔╝ {$this->colors['reset']}\n";
        echo "  {$this->colors['green']} ██║        ██║   ╚███╔███╔╝╚██████╔╝██████╔╝███████╗ ╚████╔╝  {$this->colors['reset']}\n";
        echo "  {$this->colors['green']} ╚═╝        ╚═╝    ╚══╝╚══╝  ╚═════╝ ╚═════╝ ╚══════╝  ╚═══╝   {$this->colors['reset']}\n";
        echo "  {$this->colors['gray']} ---------------------------------------------------------------- {$this->colors['reset']}\n";
        echo "  {$this->colors['white']}   FTwoDev Framework v" . \Engine\Boot::VERSION . " {$this->colors['reset']}\n";
        echo "  {$this->colors['gray']}   Advanced Native PHP Framework {$this->colors['reset']}\n\n";
    }

    private function migrateFresh()
    {
        $this->banner();
        $this->warning("⚠️  This will DROP ALL TABLES and re-run migrations!");
        
        // Ask for confirmation
        echo "  {$this->colors['white']}Are you sure you want to continue? This action cannot be undone.{$this->colors['reset']}\n";
        echo "  Type 'yes' to confirm: ";
        $confirmation = trim(fgets(STDIN));
        
        if (strtolower($confirmation) !== 'yes') {
            $this->info("Operation cancelled.");
            return;
        }
        
        $this->info("🔥 Starting fresh migration...");
        
        try {
            $db = $this->getDatabaseConnection();
            
            // Get all tables in the database
            $this->info("Dropping all tables...");
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            if (!empty($tables)) {
                // Disable foreign key checks
                $db->exec("SET FOREIGN_KEY_CHECKS = 0");
                
                foreach ($tables as $table) {
                    $db->exec("DROP TABLE IF EXISTS `$table`");
                    $this->success("Dropped table: $table");
                }
                
                // Re-enable foreign key checks
                $db->exec("SET FOREIGN_KEY_CHECKS = 1");
                
                $this->success("All tables dropped successfully.");
            } else {
                $this->info("No tables found to drop.");
            }
            
            // Now run all migrations fresh
            $this->info("Running all migrations...");
            $this->ensureMigrationsTable($db);
            
            $files = glob(__DIR__ . '/../../projects/Migrations/*.php');
            sort($files);
            
            if (empty($files)) {
                $this->warning("No migration files found.");
                return;
            }
            
            $batch = time();
            $count = 0;
            
            foreach ($files as $file) {
                $name = basename($file, '.php');
                require_once $file;
                $parts = explode('_', $name);
                $className = "Projects\\Migrations\\" . str_replace(' ', '', ucwords(str_replace('_', ' ', implode('_', array_slice($parts, 4)))));
                
                if (class_exists($className)) {
                    $migration = new $className($db);
                    $migration->up();
                    
                    $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                    $stmt->execute([$name, $batch]);
                    
                    $this->success("Migrated: $name");
                    $count++;
                } else {
                    $this->error("Migration class not found: $className");
                }
            }
            
            echo "\n";
            $this->success("🚀 Fresh migration completed!");
            $this->info("Total migrations run: $count");
            
        } catch (\Exception $e) {
            $this->error("Migration failed: " . $e->getMessage());
            $this->info("Please check your database connection and migration files.");
        }
    }

    private function refresh()
    {
        $this->info("Refreshing framework classes... 🔄");
        shell_exec('composer dump-autoload');
        $this->success("Class map rebuilt successfully! Everything is synced.");
    }
}


