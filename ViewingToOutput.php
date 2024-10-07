<?php
    opcache_reset();

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Open the original CSV file for reading
    $inputFile = fopen('ViewingActivity.csv', 'r');

    // Open a new CSV file for writing the modified data
    $outputFile = fopen('output.csv', 'w');

    $newHeader = ["profile_name", "start_date","start_time", "duration", "title", "season_number", "episode_title", "episode_number", "device_type"];

    fputcsv($outputFile, $newHeader);
    // Read the CSV file row by row
    $n=0;
    while (($row = fgetcsv($inputFile)) !== false) {

        $startTimeTab = preg_split("/[\s]/", $row[1]);
        $startDate= $startTimeTab[0];
        $startTime= $startTimeTab[1];

        $titleTab = preg_split("/[,:()]/", $row[4]);
        $title = $titleTab[0];
        $SeasonNumber = $titleTab[1];
        $EpTitle = $titleTab[2];
        $EpNumber = $titleTab[3];
        isset($SeasonNumber[1]) ? $SeasonNumber[1] : "";
        isset($EpTitle[2]) ? $EpTitle[1] : "";
        isset($EpNumber[3]) ? $EpNumber[1] : "";

        // Rebuild the row with the new structure
        $newRow = [$row[0], $startDate, $startTime, $row[2], $title,$SeasonNumber,$EpTitle, $EpNumber,$row[6]];

        fputcsv($outputFile, $newRow);
    }

    // Close the file handlers
    fclose($inputFile);
    fclose($outputFile);

    echo "CSV file processed and saved as 'output.csv'.\n";

