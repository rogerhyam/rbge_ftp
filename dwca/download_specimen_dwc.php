<?php

require_once('../config.php');
require_once('../SolrConnection.php');
require_once('common_specimen_dwc.php');

// this might take some time so give use 5 minutes to think about it
set_time_limit(0);

if(!@$_REQUEST['barcodes']){
    // they didn't pass a list of barcodes
    http_response_code(400);
    echo "You need to provide a comma separated list of barcodes that you'd like data for. GET and POST accepted.";
    exit;
}else{
    $barcodes = str_replace(',', ' OR ', $_REQUEST['barcodes']);
}

$solr = new SolrConnection();
$query = (object)array(
    "query" => "barcode_s:($barcodes)",
    "filter" => "record_type_s:specimen"
);

$random_dir = 'data/downloads/' . rand() . '/';
mkdir($random_dir, 0777, true);

// write the specimens
$out_specimens = fopen($random_dir . 'herbarium_specimens.csv', 'w');
// put in a header row - makes things easier
fputcsv($out_specimens, array_keys($dwc_dynamic_fields));

$out_images = fopen($random_dir . 'herbarium_specimen_images.csv', 'w');
// put in a header row - makes things easier
fputcsv($out_images, $image_fields);

// do the pages
$page_number = 0;
$specimen_count = 0;
while($records = $solr->query_paged($query)){

    // report progress
    $specimen_count += count($records);
    $page_number++;

    // work through records in page
    foreach($records as $record){
        write_specimen_record($out_specimens, $record, $dwc_dynamic_fields);
        write_image_record($out_images, $record, $image_fields);
    }

}


// metadata files - stolen from the live one
$meta = file_get_contents('zip://data/edinburgh_herbarium_dwc.zip#meta.xml');
file_put_contents($random_dir . 'meta.xml', $meta);
$eml = file_get_contents('zip://data/edinburgh_herbarium_dwc.zip#eml.xml');
file_put_contents($random_dir . 'eml.xml', $eml);

// zip them up
$zip = new ZipArchive();
$zipFile = $random_dir . "edinburgh_herbarium_specimen_download_dwc.zip";

if ($zip->open($zipFile, ZIPARCHIVE::CREATE)!==TRUE) {
    exit("cannot open <$zipFile>\n");
}

$zip->addFile($random_dir . "herbarium_specimens.csv", "herbarium_specimens.csv");
$zip->addFile($random_dir . "herbarium_specimen_images.csv", "herbarium_specimen_images.csv");
    
$zip->addFile($random_dir . "eml.xml", "eml.xml");
$zip->addFile($random_dir . "meta.xml", "meta.xml");

if ($zip->close()!==TRUE) {
    exit("cannot close <$zipFile>\n". $zip->getStatusString());
}

// remove the zipped files 
unlink($random_dir . "herbarium_specimens.csv");
unlink($random_dir . "herbarium_specimen_images.csv");
unlink($random_dir . "eml.xml");
unlink($random_dir . "meta.xml");

// get ready to download
header('Content-disposition: attachment; filename=edinburgh_herbarium_specimen_download_dwc.zip');
header("Content-type: application/octet-stream");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($zipFile));
ob_end_flush();
@readfile($zipFile);

unlink($zipFile);
rmdir($random_dir);