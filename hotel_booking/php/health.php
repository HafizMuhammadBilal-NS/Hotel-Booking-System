<?php
header('Content-Type: text/plain');

try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=DEN102_A1;charset=utf8mb4',
        'root', 
        '',     
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "✅ Connection successful!\n\n";

    $stmt = $pdo->query("SELECT room_type, price, capacity, total_rooms FROM rooms");

    echo "Available Rooms in Database:\n";
    echo "-----------------------------\n";

    foreach ($stmt as $row) {
        echo $row['room_type']
           . " | $" . $row['price']
           . " per night"
           . " | Capacity: " . $row['capacity']
           . " | Total Rooms: " . $row['total_rooms']
           . "\n";
    }

} catch (Throwable $e) {
    http_response_code(500);
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
