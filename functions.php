<?php
include 'database.php';

$prefixes = ["Go", "My", "Elite", "Pro", "Hyper", "Smart", "Blue", "Xtra", "Neo", "Mega"];
$suffixes = ["Hub", "House", "Groupe", "Nest", "X", "Zone", "Labs", "Verse", "Gen", "Edge"];

function generate($key1, $db, $number) {
    global $prefixes, $suffixes;

    $stmt = $db->prepare("SELECT word FROM users WHERE KEY1 = :key1");
    $stmt->execute(['key1' => $key1]);
    $existingWords = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'word');
    
    if (count($existingWords) >= $number) {
        return;
    }

    $existingWordsSet = array_flip($existingWords);
    $generatedNames = [];

    while (count($generatedNames) + count($existingWords) < $number) {
        $prefix = $prefixes[array_rand($prefixes)];
        $suffix = $suffixes[array_rand($suffixes)];

        $nameOptions = [
            "$prefix $key1",
            "$key1 $suffix",
            "$prefix $key1 $suffix"
        ];

        foreach ($nameOptions as $name) {
            if (!isset($existingWordsSet[$name]) && !isset($generatedNames[$name])) {
                $generatedNames[$name] = true;
            }
            if (count($generatedNames) + count($existingWords) >= $number) {
                break 2;
            }
        }
    }

    if (!empty($generatedNames)) {
        $insertValues = [];
        $insertParams = [];

        foreach (array_keys($generatedNames) as $index => $name) {
            $insertValues[] = "(:word$index, :key$index, 0)";
            $insertParams[":word$index"] = $name;
            $insertParams[":key$index"] = $key1;
        }

        $insertQuery = "INSERT INTO users (word, KEY1, used) VALUES " . implode(", ", $insertValues);
        $stmt = $db->prepare($insertQuery);
        $stmt->execute($insertParams);
    }
}



?>
