#!/usr/bin/php
<?php
function getData($yearMonth) {
    $gaID = "ga:76665870";
    $startIndex = 1;
    $maxResults = 10000;
    $startDate = "2016-03-01";
    $endDate = "2016-03-31";
    $accessToken = "ya29.Glu_BBzy6qV42pHQaIkY2UKc9dcGN7kvBBsrjrmwNoZPZ5ReIfy5VLblec2DJjbZ6ZZA8dSR3bmCK2JXdPuEvdtDMC5a-rzZ0JI5gs0IyeACQK5ZN2gvyaGQ8pZL";
    $metrics = "ga:pageviews";
    $dimension = "ga:pagePath";
    $totalResults = 0;
    
    $startMonth = 1;
    $endMonth = 12;
    $year = "2016";
    
    
    
    /*
    for ($m = $startMonth; $m <= $endMonth; $m++) {
        if (strlen($m) < 2) {
            $month = "0".$m;
        }
    */
        //if (strlen($argv[0]) < 2) 
        //    $month = "0".$argv[1];
        //else
        $split = explode("-", $yearMonth);
        //$month = $argv[1];
        $month = $split[1];
        $year = $split[0];
        switch ($month) {
            case "01":
            case "03":
            case "05":
            case "07":
            case "08":
            case "10":
            case "12":
                $date = "31";
                break;
            case "02":
                $date = "28";
                break;
            case "04":
            case "06":
            case "09":
            case "11":
                $date = "30";
                break;
    
        }
        $startDate = $year."-".$month."-01";
        $endDate = $year."-".$month."-".$date;
        echo $startDate.":".$endDate."\n";
        $url = "https://www.googleapis.com/analytics/v3/data/ga?ids=".$gaID."&start-date=".$startDate."&end-date=".$endDate."&metrics=".$metrics."&dimensions=".$dimension."&start-index=".$startIndex."&max-results=".$maxResults."&access_token=".$accessToken;
        //echo $url."\n";
        $json = file_get_contents($url);
        $data = json_decode($json, TRUE);
        
        $numberToRun = ceil($data['totalResults'] / $maxResults);
        echo "itemsPerPage: ".$data['itemsPerPage']."\n";
        echo "totalResults: ".$data['totalResults']."\n";
        echo "Total Run: ".$numberToRun."\n";
        //echo "Total Rows: ".count($data['rows'])."\n";
        echo "Start Retrieve Data: \n";
        for ($i = 0; $i < $numberToRun; $i++) {
            $startIndex = ($i * $maxResults)+1;
            echo "Start Index: ".$startIndex."\n";
            $url2 = "https://www.googleapis.com/analytics/v3/data/ga?ids=".$gaID."&start-date=".$startDate."&end-date=".$endDate."&metrics=".$metrics."&dimensions=".$dimension."&start-index=".$startIndex."&max-results=".$maxResults."&access_token=".$accessToken;
            echo $url."\n";
            $json2 = file_get_contents($url2);
            $data2 = json_decode($json2, TRUE);
            
            $batch = $i + 1;
            $fileName = "batchs/PV-".$month."-".$year."-batch-".$batch.".csv";
            $fp = fopen($fileName, 'w');
            
            foreach ($data2['rows'] as $fields) {
                fputcsv($fp, $fields);
            }
            
            fclose($fp);
        }
        system("cat batchs/PV-".$month."*.csv > compiled/PV-".$year."-".$month."-compiled.csv");
    //}
}

$splitStart = explode("-", $argv[1]);
$start = $splitStart[1];
$splitEnd = explode("-", $argv[2]);
$end = $splitEnd[1];
$subs = intval($splitEnd[0]) - intval($splitStart[0]);
//if ($subs > 0) {
//    
//}
$run = (intval($end) - intval($start))+1;
echo "Total Months: ".$run."\n";
system("mkdir compiled");
system("mkdir batchs");
for ($i = 1; $i <= $run; $i++) {
    echo $i."\n";
    if ($i < 10) {
        $month = "0".$i;
    } else {
        $month = $i;
    }
    $yearMonth = $splitStart[0]."-".$month;
    echo $yearMonth."\n";
    getData($yearMonth);
   
}