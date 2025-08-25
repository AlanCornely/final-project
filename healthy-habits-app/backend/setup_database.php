<?php
require_once 'config.php';

try {
    $pdo = getConnection();
    
    // Create tables
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);
    
    // Insert sample badges
    $badges = [
        ['name' => 'Primeiro Passo', 'description' => 'Complete seu primeiro hábito', 'points_threshold' => 10],
        ['name' => 'Dedicado', 'description' => 'Complete 10 hábitos', 'points_threshold' => 100],
        ['name' => 'Consistente', 'description' => 'Complete hábitos por 7 dias seguidos', 'points_threshold' => 200],
        ['name' => 'Mestre dos Hábitos', 'description' => 'Complete 50 hábitos', 'points_threshold' => 500],
        ['name' => 'Lenda', 'description' => 'Complete 100 hábitos', 'points_threshold' => 1000]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO badges (name, description, points_threshold) VALUES (?, ?, ?)");
    foreach ($badges as $badge) {
        $stmt->execute([$badge['name'], $badge['description'], $badge['points_threshold']]);
    }
    
    // Insert sample user (password: 123456)
    $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute(['demo_user', 'demo@example.com', $hashedPassword]);
    
    echo "Database setup completed successfully!\n";
    echo "Sample user created:\n";
    echo "Username: demo_user\n";
    echo "Password: 123456\n";
    
} catch(PDOException $e) {
    echo "Error setting up database: " . $e->getMessage() . "\n";
}
?>
