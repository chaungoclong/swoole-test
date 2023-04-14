<?php

$timestamp = microtime(true);

$email = 'test' . $timestamp . '@example.com';
$name = 'test' . $timestamp;

if ($_SERVER['REQUEST_URI'] === '/hello') {
    try {
        error_log("Request: $timestamp started\n");
//        sleep(10);
        // create a PDO connection
        $dsn = "mysql:host=localhost;dbname=test";
        $username = "long";
        $password = "tnt";
        $pdo = new PDO($dsn, $username, $password);

        // prepare and execute a SQL statement
        $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);

        error_log("Request: $timestamp successfully\n");
        header('Content-Type: application/json');
        echo json_encode(['message' => 'User created successfully']);
    } catch (PDOException $e) {
        header('Content-Type', 'text/plain');
        echo('Error creating user');
    }
}



