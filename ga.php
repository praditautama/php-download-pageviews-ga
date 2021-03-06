<?php
date_default_timezone_set('Asia/Jakarta');

$dirRawData = "data/";
$dirCompiled = "compiled/";

$executionStartTime = microtime(true);
//$startDate = "2016-01";
//$endDate = "2016-03";
if (!isset($argv[1]) || !isset($argv[1])) {
    echo "Empty arguments. Example: 2016-06-30 2016-12-31\n";
    exit(1);
} else {
    $startDate = $argv[1];
    $endDate = $argv[2];
}


if ($argv[1] == '' || $argv[2] == '') {
    echo "Empty arguments. Example: 2016-06-30 2016-12-31\n";
    exit(1);
}

function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') == $date;
}

if (!validateDate($argv[1]) || !validateDate($argv[2])) {
    echo "Invalid date format. Example: 2016-06-30 2016-12-31\n";
    exit(1);
}

$startMonth = date("m", strtotime($startDate));
$startYear = date("Y", strtotime($startDate));
$endMonth = date("m", strtotime($endDate));
$endYear = date("Y", strtotime($endDate));
$startLastDay = date("t", strtotime($startDate));
$endLastDay = date("t", strtotime($endDate));

if (intval($endYear) > intval($startYear)) {
    echo "Currently only support same year\n";
    exit(1);
}

if (intval($endMonth) < intval($startMonth)) {
    echo "End date must be greater than start date\n";
    exit(1);
}

$gaID = "ga:76665870";
$startIndex = 1;
$maxResults = 10000;
$accessToken = "ya29.GlzABHc594hZOYhcQNTCEgGUY4u4TkNapDS5gh9ZcdS_mgrYTMgYPyFvmukofZ1m1VV9J4C27gbE2776bcjs-wlfd2DdVsbAIOyENF0qCQactbe-WnxuqTU0fKwoaQ";
$metrics = "ga:pageviews";
$dimension = "ga:pagePath";

$totalMonths = intval(date("n", strtotime($endDate))) - intval(date("n", strtotime($startDate))) + 1;

echo "-------------------------------------------------\n";
echo "Start Date\t\t: ".$startDate."\n";
echo "End Date\t\t: ".$endDate."\n";
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

    echo "   Month\t\t: ".strtoupper(date('F', mktime(0, 0, 0, $month, 10)))."\n";
    echo "   Total Results\t: ".$totalResults."\n";
    echo "   Total Batches\t: ".$totalPages."\n";
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
        echo "      Batch\t\t: ".$i."\n";
        echo "      Filename\t\t: ".$fileName."\n";
        echo "      Total Rows\t: ".$totalRows."\n";
        //echo "URL\t\t\t: ".$urlBatchPerMonth."\n";
        echo "-------------------------------------------------\n";

        $fp = fopen($fileName, 'w');
        
        foreach ($dataBatchPerMonth['rows'] as $fields) {
            $fields['period'] = date("Y-m", strtotime($startDateRequest));
            fputcsv($fp, $fields);
        }
        
        fclose($fp);
    }
    $compiledFilePath = $dirCompiled."PV-".$year."-".strtoupper(date("M", strtotime($startDateRequest)))."-compiled.csv";
    system("cat ".$dirRawData."PV-".$year."-".strtoupper(date("M", strtotime($startDateRequest)))."*.csv > ".$compiledFilePath);
    $month++;

    echo "   Compiled File\t: ".$compiledFilePath."\n";
    echo "-------------------------------------------------\n";
}
$compiledFilePath = $dirCompiled."PV-compiled-ALL.csv";
system("cat ".$dirRawData."PV*.csv > ".$compiledFilePath);
$month++;

echo "All Compiled File\t: ".$compiledFilePath."\n";
echo "-------------------------------------------------\n";

$executionEndTime = microtime(true);
$seconds = $executionEndTime - $executionStartTime;
echo "This script took $seconds to execute.\n";