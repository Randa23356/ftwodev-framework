<?php

// Include autoloader first
require_once __DIR__ . '/vendor/autoload.php';

$colors = [
    'green'  => "\033[38;5;48m",    // Emerald
    'blue'   => "\033[38;5;45m",     // Cyan-Blue
    'yellow' => "\033[38;5;220m",   // Gold
    'red'    => "\033[38;5;196m",      // Critical Red
    'gray'   => "\033[38;5;244m",     // Slate Gray
    'white'  => "\033[1;37m",        // Bold White
    'reset'  => "\033[0m"
];

function ask($question, $default = '', $colors = []) {
    echo "  {$colors['white']}? $question{$colors['reset']} " . ($default ? "({$colors['gray']}$default{$colors['reset']})" : "") . ": ";
    $input = trim(fgets(STDIN));
    return $input ?: $default;
}

function confirm($question, $default = 'y', $colors = []) {
    $choice = ask("$question [y/n]", $default, $colors);
    return strtolower($choice) === 'y';
}

echo "\n";
echo "  {$colors['green']} ███████╗████████╗██╗    ██╗ ██████╗ ██████╗ ███████╗██╗   ██╗ {$colors['reset']}\n";
echo "  {$colors['green']} ██╔════╝╚══██╔══╝██║    ██║██╔═══██╗██╔══██╗██╔════╝██║   ██║ {$colors['reset']}\n";
echo "  {$colors['green']} █████╗     ██║   ██║ █╗ ██║██║   ██║██║  ██║█████╗  ██║   ██║ {$colors['reset']}\n";
echo "  {$colors['green']} ██╔══╝     ██║   ██║███╗██║██║   ██║██║  ██║██╔══╝  ╚██╗ ██╔╝ {$colors['reset']}\n";
echo "  {$colors['green']} ██║        ██║   ╚███╔███╔╝╚██████╔╝██████╔╝███████╗ ╚████╔╝  {$colors['reset']}\n";
echo "  {$colors['green']} ╚═╝        ╚═╝    ╚══╝╚══╝  ╚═════╝ ╚═════╝ ╚══════╝  ╚═══╝   {$colors['reset']}\n";
echo "  {$colors['gray']} ---------------------------------------------------------------- {$colors['reset']}\n";
echo "  {$colors['white']}   CONFIGURING PROJECT... {$colors['gray']} | {$colors['green']} FTwoDev Framework v" . \Engine\Boot::VERSION . " {$colors['reset']}\n\n";

// 1. Project Info
echo "  {$colors['blue']}Project Identification{$colors['reset']}\n";
$appName = ask("What is your Project Name?", "FTwoDev App", $colors);
$appUrl = ask("Application URL?", "http://localhost:8000", $colors);

// 2. Database Info
echo "\n  {$colors['blue']}Database Configuration{$colors['reset']}\n";
if (confirm("Setup database connection now?", "y", $colors)) {
    $dbHost = ask("DB Host", "localhost", $colors);
    $dbName = ask("DB Name", "ftwodev_db", $colors);
    $dbUser = ask("DB Username", "root", $colors);
    $dbPass = ask("DB Password", "", $colors);
}

// 3. Optional Features
echo "\n  {$colors['blue']}Optional Features{$colors['reset']}\n";
$installBloom = confirm("Install Bloom Auth Starter Kit immediately?", "n", $colors);

// 4. Execution
echo "\n  {$colors['white']}STEP 4: Finalizing Installation...{$colors['reset']}\n";

// Generate .env file
if (!file_exists('.env') && file_exists('.env.example')) {
    copy('.env.example', '.env');
    
    // Generate APP_KEY
    $key = 'base64:' . base64_encode(random_bytes(32));
    $envContent = file_get_contents('.env');
    $envContent = str_replace('APP_KEY=', "APP_KEY=$key", $envContent);
    
    // Update .env with user input
    $envContent = str_replace('APP_NAME="FTwoDev Application"', "APP_NAME=\"$appName\"", $envContent);
    $envContent = str_replace('APP_URL=http://localhost:8000', "APP_URL=$appUrl", $envContent);
    
    if (isset($dbHost)) {
        $envContent = str_replace('DB_HOST=127.0.0.1', "DB_HOST=$dbHost", $envContent);
        $envContent = str_replace('DB_DATABASE=ftwodev_db', "DB_DATABASE=$dbName", $envContent);
        $envContent = str_replace('DB_USERNAME=root', "DB_USERNAME=$dbUser", $envContent);
        $envContent = str_replace('DB_PASSWORD=', "DB_PASSWORD=$dbPass", $envContent);
    }
    
    file_put_contents('.env', $envContent);
    echo "  {$colors['green']}✔{$colors['reset']} .env file generated with APP_KEY.\n";
}

// Structure
$directories = ['storage', 'storage/logs', 'public', 'config'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) mkdir($dir, 0755, true);
    @chmod($dir, 0777); 
}
echo "  {$colors['green']}✔{$colors['reset']} Directory structure verified.\n";

// Run composer dump-autoload to ensure classes are loaded
echo "  {$colors['green']}✔{$colors['reset']} Refreshing autoload classes...\n";
shell_exec('composer dump-autoload');

// Run Bloom if selected
if ($installBloom) {
    echo "  {$colors['green']}✔{$colors['reset']} Executing Bloom installation...\n";
    passthru("php ftwo ignite:bloom");
}

// Final Message
echo "\n  {$colors['green']}----------------------------------------------------------------{$colors['reset']}\n";
echo "  {$colors['white']}  FTwoDev Framework Successfully Configured! 🚀 {$colors['reset']}\n";
echo "  {$colors['green']}----------------------------------------------------------------{$colors['reset']}\n\n";

echo "  {$colors['white']}NEXT STEPS:{$colors['reset']}\n";
echo "  1. {$colors['blue']}php ftwo ignite:refresh{$colors['reset']}  - Sync framework classes\n";

echo "  2. {$colors['blue']}php ftwo ignite{$colors['reset']}            - Start dev engine\n";
if (!$installBloom) {
    echo "  3. {$colors['blue']}php ftwo ignite:bloom{$colors['reset']}      - Install Auth (Whenever you need it)\n";
}
echo "\n";


