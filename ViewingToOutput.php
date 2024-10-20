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
    $counter=0;
    while (($row = fgetcsv($inputFile)) !== false) {

        $counter++;
        if ($row[5]!=="") {
            echo "Skipping line $counter\n";
            continue;
        }

        $startTimeTab = preg_split("/[\s]/", $row[1]);
        $startDate= $startTimeTab[0];
        $startTime= $startTimeTab[1];

        $titleTab = preg_split("/[:()]/", $row[4]);
        if ($titleTab[1]==="US") {
            $title = $titleTab[0];
            $SeasonNumber = $titleTab[3];
            $EpTitle = $titleTab[4];
            $EpNumber = $titleTab[5];
            isset($SeasonNumber) ? $SeasonNumber[1] : "";
            isset($EpTitle) ? $EpTitle[1] : "";
            isset($EpNumber) ? $EpNumber[1] : "";
        }else if ($titleTab[1]===" Pays-Bas et Allemagne") {
            $title = $titleTab[0];
            $SeasonNumber = $titleTab[2];
            $EpTitle = $titleTab[3];
            $EpNumber = $titleTab[4];
            isset($SeasonNumber) ? $SeasonNumber[1] : "";
            isset($EpTitle) ? $EpTitle[1] : "";
            isset($EpNumber) ? $EpNumber[1] : "";
        }else if (!$titleTab[2] && !$titleTab[3]) {
            $title = $titleTab[0].$titleTab[1];
            $SeasonNumber = "";
            $EpTitle = "";
            $EpNumber = "";
        }else{
            $title = $titleTab[0];
            $SeasonNumber = $titleTab[1];
            $EpTitle = $titleTab[2];
            $EpNumber = $titleTab[3];
            isset($SeasonNumber) ? $SeasonNumber[1] : "";
            isset($EpTitle) ? $EpTitle[1] : "";
            isset($EpNumber) ? $EpNumber[1] : "";
        }
        
        
        

        // Rebuild the row with the new structure
        $newRow = [$row[0], $startDate, $startTime, $row[2], $title,$SeasonNumber,$EpTitle, $EpNumber,$row[6]];

        fputcsv($outputFile, $newRow);
    }

    // Close the file handlers
    fclose($inputFile);
    fclose($outputFile);

    echo "CSV file processed and saved as 'output.csv'.\n";

