<?php
require 'bootstrap.php';

$statement = <<<EOS
    DROP TABLE IF EXISTS event;
    
    CREATE TABLE IF NOT EXISTS event (
        id INT NOT NULL AUTO_INCREMENT,
        description VARCHAR(1000) NOT NULL,
        fromToDate DATE NOT NULL,
        location VARCHAR(100) NOT NULL,
        PRIMARY KEY (id)
    );
    
    INSERT INTO event
        (description, fromToDate, location)
    VALUES
        ('descriere1', '2020-05-15', 'Cluj-Napoca'),
        ('descriere2', '1999-12-15', 'Gherla'),
        ('descriere3', '2000-06-12', 'Dej');
EOS;

try {
    $createTable = $dbConnection->exec($statement);
    echo "Success!\n";
} catch (\PDOException $e) {
    exit($e->getMessage());
}