<?php
date_default_timezone_set('Asia/Jakarta');

$dirRawData = "data/";
$dirCompiled = "compiled/";

$executionStartTime = microtime(true);
//$startDate = "2016-01";
//$endDate = "2016-03";
if (!isset($argv[1]) || !isset($argv[1])) {
    exit(1);
} else {
    $startDate = $argv[1];
    $endDate = $argv[2];
}


if ($argv[1] == '' || $argv[2] == '') {
    exit(1);
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') == $date;
}

if (!validateDate($argv[1]) || !validateDate($argv[2])) {
    exit(1);
}

$startMonth = date("m", strtotime($startDate));
$endMonth = date("m", strtotime($endDate));
$startLastDay = date("t", strtotime($startDate));
$endLastDay = date("t", strtotime($endDate));

$gaID = "ga:76665870";
$startIndex = 1;
$maxResults = 10000;
$accessToken = "ya29.Gly_BDSK4ELWQozvhZiKT1S1S-agEVUD2Z8xPO9ShZAvvyrNm4yeybqRjBGupUsiqUhFMq6gc2lzhDOx_d0trDuHhCEWZfSa8Z_0saEBcfT-9WbgSFQsZqDqsUmN8w";
$metrics = "ga:pageviews";
$dimension = "ga:pagePath";

$totalMonths = intval(date("n", strtotime($endDate))) - intval(date("n", strtotime($startDate))) + 1;

echo "-------------------------------------------------\n";
echo "Start Date\t\t: ".$startDate."-01\n";
echo "End Date\t\t: ".$endDate."-".$endLastDay."\n";
echo "Total Months\t\t: ".$totalMonths."\n";
echo "-------------------------------------------------\n";

$month = intval(date("n", strtotime($startDate)));
$year = intval(date("Y", strtotime($startDate)));

for ($j = 1; $j <= $totalMonths; $j++) {
    if ($month < 10) {
        $startDateRequest = $year."-0".$month."-01";
    } else {
        $startDateRequest = $year."-".$month."-01";
    }

    if ($month > 12) {
        $month = 1;
        $year++;
        $startDateRequest = $year."-0".$month."-01";
    }

    $endDateRequest = $year."-".date("m", strtotime($startDateRequest))."-".date("t", strtotime($startDateRequest));
    
    $urlBatch = "https://www.googleapis.com/analytics/v3/data/ga?ids=".$gaID
        ."&start-date=".$startDateRequest
        ."&end-date=".$endDateRequest
        ."&metrics=".$metrics
        ."&dimensions=".$dimension
        ."&start-index=1"
        ."&max-results=1"
        ."&access_token=".$accessToken;

    $jsonBatch = file_get_contents($urlBatch);
    $dataBatch = json_decode($jsonBatch, TRUE);
    $totalResults = $dataBatch['totalResults'];
    $totalPages = ceil(intval($totalResults) / $maxResults);

    echo "Month\t\t\t: ".strtoupper(date('F', mktime(0, 0, 0, $month, 10)))."\n";
    echo "Total Results\t\t: ".$totalResults."\n";
    echo "Total Batches\t\t: ".$totalPages."\n";
    //echo "URL\t\t\t: ".$urlBatch."\n";
    echo "-------------------------------------------------\n";
    
    for ($i = 1; $i <= $totalPages; $i++) {
        $startIndex = (($i - 1) * $maxResults) + 1;
        $urlBatchPerMonth = "https://www.googleapis.com/analytics/v3/data/ga?ids=".$gaID
            ."&start-date=".$startDateRequest
            ."&end-date=".$endDateRequest
            ."&metrics=".$metrics
            ."&dimensions=".$dimension
            ."&start-index=".$startIndex
            ."&max-results=".$maxResults
            ."&access_token=".$accessToken;
    
        $jsonBatchPerMonth = file_get_contents($urlBatchPerMonth);
        $dataBatchPerMonth = json_decode($jsonBatchPerMonth, TRUE);
    
        $totalRows = count($dataBatchPerMonth['rows']);
        $fileName = $dirRawData."PV-".date("Y", strtotime($startDate))."-".strtoupper(date("M", strtotime($startDateRequest)))."-batch-".$i.".csv";
        echo "Batch\t\t\t: ".$i."\n";
        echo "Filename\t\t: ".$fileName."\n";
        echo "Total Rows\t\t: ".$totalRows."\n";
        echo "URL\t\t\t: ".$urlBatchPerMonth."\n";
        echo "-------------------------------------------------\n";

        $fp = fopen($fileName, 'w');
        
        foreach ($dataBatchPerMonth['rows'] as $fields) {
            $fields['period'] = date("Y-m", strtotime($startDateRequest));
            fputcsv($fp, $fields);
        }
        
        fclose($fp);
    }
    system("cat ".$dirRawData."PV-".$year."-".strtoupper(date("M", strtotime($startDateRequest)))."*.csv > ".$dirCompiled."PV-".$year."-".strtoupper(date("M", strtotime($startDateRequest)))."-compiled.csv");
    $month++;
}

$executionEndTime = microtime(true);
$seconds = $executionEndTime - $executionStartTime;
echo "This script took $seconds to execute.\n";