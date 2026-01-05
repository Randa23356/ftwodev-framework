<?php

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
echo "  {$colors['white']}   CONFIGURING PROJECT... {$colors['gray']} | {$colors['green']} Interactive Setup Wizard {$colors['reset']}\n\n";

// 1. Project Info
echo "  {$colors['blue']}Project Identification{$colors['reset']}\n";
$appName = ask("What is your Project Name?", "FTwoDev App", $colors);
$appUrl = ask("Application URL?", "http://localhost:8000", $colors);

$appFile = 'config/app.php';
if (file_exists($appFile)) {
    $content = file_get_contents($appFile);
    $content = preg_replace("/'name' => '.*'/", "'name' => '$appName'", $content);
    $content = preg_replace("/'url' => '.*'/", "'url' => '$appUrl'", $content);
    
    // Key Generation
    if (strpos($content, 'base64:GENERATE_YOUR_OWN_KEY_HERE') !== false) {
        $key = 'base64:' . base64_encode(random_bytes(32));
        $content = str_replace('base64:GENERATE_YOUR_OWN_KEY_HERE', $key, $content);
    }
    file_put_contents($appFile, $content);
}

// 2. Database Info
echo "\n  {$colors['blue']}Database Configuration{$colors['reset']}\n";
if (confirm("Setup database connection now?", "y", $colors)) {
    $dbHost = ask("DB Host", "localhost", $colors);
    $dbName = ask("DB Name", "ftwodev_db", $colors);
    $dbUser = ask("DB Username", "root", $colors);
    $dbPass = ask("DB Password", "", $colors);

    $dbFile = 'config/database.php';
    if (file_exists($dbFile)) {
        $content = file_get_contents($dbFile);
        $content = preg_replace("/'host' => '.*'/", "'host' => '$dbHost'", $content);
        $content = preg_replace("/'dbname' => '.*'/", "'dbname' => '$dbName'", $content);
        $content = preg_replace("/'username' => '.*'/", "'username' => '$dbUser'", $content);
        $content = preg_replace("/'password' => '.*'/", "'password' => '$dbPass'", $content);
        file_put_contents($dbFile, $content);
    }
}

// 3. Optional Features
echo "\n  {$colors['blue']}Optional Features{$colors['reset']}\n";
$installBloom = confirm("Install Bloom Auth Starter Kit immediately?", "n", $colors);

// 4. Execution
echo "\n  {$colors['white']}STEP 4: Finalizing Installation...{$colors['reset']}\n";

// Structure
$directories = ['storage', 'storage/logs', 'public', 'config'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) mkdir($dir, 0755, true);
    @chmod($dir, 0777); 
}
echo "  {$colors['green']}✔{$colors['reset']} Directory structure verified.\n";

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


