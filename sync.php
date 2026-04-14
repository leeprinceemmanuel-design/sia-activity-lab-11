<?php
require 'vendor/autoload.php';

// UPDATED for Version 4: The 'Api' folder was added to the namespace
use Algolia\AlgoliaSearch\Api\SearchClient; 

// 1. Setup Local Database Connection (MySQL)
$host = '127.0.0.1';
$db   = 'movies_database'; 
$user = 'root'; 
$pass = '';     
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// 2. Initialize Algolia Client
$appId = 'OQL1HSCT1I'; 
$adminApiKey = '46b48709483375d85b5c40f54a312386'; 

$client = SearchClient::create($appId, $adminApiKey);

// 3. Retrieve Records from MySQL
$stmt = $pdo->query('SELECT id, title, overview, genre, vote_average, poster_url FROM moviedb');
$movies = $stmt->fetchAll();

// 4. Format Data and Push to Algolia
$records = [];
foreach ($movies as $movie) {
    // Algolia requires objectID to be a string
    $movie['objectID'] = (string)$movie['id']; 
    $records[] = $movie;
}

try {
    // UPDATED for Version 4: Pass the index name 'movies' directly into the saveObjects method
    $client->saveObjects('movies', $records);
    echo "Successfully synchronized " . count($records) . " movie records to Algolia!";
} catch (Exception $e) {
    echo "Error syncing to Algolia: " . $e->getMessage();
}
?>