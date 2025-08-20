<?php
function getConnection() {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "fleurskin";
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

function getPDOConnection() {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "fleurskin";
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        throw new Exception("PDO Connection failed: " . $e->getMessage());
    }
}
?>