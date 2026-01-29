<?php

$csvFile = 'D:\home\leaguemanager.cw.local\player-tryout-data.csv';

if (($handle = fopen($csvFile, 'r')) !== false) {
    // Read header
    $headers = fgetcsv($handle, 0, ',', '"');
    echo "Header columns: " . count($headers) . "\n";
    echo "First 5 headers:\n";
    for ($i = 0; $i < 5 && $i < count($headers); $i++) {
        echo "  [$i] " . trim($headers[$i]) . "\n";
    }

    // Read first few data rows and check column count
    echo "\nFirst 10 data rows:\n";
    for ($rowNum = 0; $rowNum < 10; $rowNum++) {
        $data = fgetcsv($handle, 0, ',', '"');
        if ($data === false) break;
        echo "  Row $rowNum: " . count($data) . " columns, Player Name: " . ($data[1] ?? '?') . ", Email: " . ($data[2] ?? '?') . "\n";
    }

    fclose($handle);
}
