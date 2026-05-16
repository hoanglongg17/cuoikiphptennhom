<?php
// Load Yii framework manually
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// Create a Yii application instance with database config
$config = require __DIR__ . '/config/web.php';

// Remove web-only settings for CLI
unset($config['components']['request']);
unset($config['components']['response']);

$app = new yii\web\Application($config);

try {
    echo "Connecting to database...\n";
    $db = Yii::$app->db;
    
    // Test connection
    $db->open();
    echo "✓ Connected to database: andiflashcarddb\n\n";
    
    // Load the migration class
    $migrationFile = __DIR__ . '/migrations/m260516_BlogFeatures.php';
    if (!file_exists($migrationFile)) {
        throw new \Exception("Migration file not found: {$migrationFile}");
    }
    
    require $migrationFile;
    
    // Create migration instance with db connection
    $migration = new m260516_BlogFeatures();
    $migration->db = $db;
    
    echo "Running migration: m260516_BlogFeatures\n";
    echo str_repeat("=", 60) . "\n";
    
    // Execute migration
    $migration->up();
    
    echo str_repeat("=", 60) . "\n";
    echo "✓ Migration executed successfully!\n";
    echo "✓ All blog feature tables have been created!\n";
    
} catch (\Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "Previous error: " . $e->getPrevious()->getMessage() . "\n";
    }
    exit(1);
}
?>
