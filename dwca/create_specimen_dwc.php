<?php

// refuse to run if we are called through HTTP
if(php_sapi_name() != 'cli'){
    http_response_code(400);
    echo "You can't call this script over the web. It is for command line use only.";
    exit;
}

require_once('../config.php');
require_once('../SolrConnection.php');
require_once('common_specimen_dwc.php');

// this might take some time so give use 5 minutes to think about it
set_time_limit(0);

$solr = new SolrConnection();

$query = (object)array("query" => "record_type_s:specimen");
 
$out = fopen("tmp/herbarium_specimens.csv", 'w');
if(!$out) exit("Couldn't open file:tmp/herbarium_specimens.csv ");

// put in a header row - makes things easier
fputcsv($out, array_keys($dwc_dynamic_fields));

$out_images = fopen("tmp/herbarium_specimen_images.csv", 'w');
if(!$out_images) exit("Couldn't open file:tmp/herbarium_specimen_images.csv ");

// put in a header row - makes things easier
fputcsv($out_images, $image_fields);

// do the pages
$page_number = 0;
$specimen_count = 0;
while($records = $solr->query_paged($query)){

    // report progress
    $specimen_count += count($records);
    echo "\nPages: $page_number\tSpecimens: " . number_format($specimen_count,0);
    $page_number++;

    // work through records in page
    foreach($records as $record){
        write_specimen_record($out, $record, $dwc_dynamic_fields);
        write_image_record($out_images, $record, $image_fields);
    }

    // dev time
    // if($specimen_count > 10000) break;
}

fclose($out);

// Make the eml file - just change the created date 
$eml_string = file_get_contents("metadata/darwin_core_eml.xml");
$eml_string = str_replace('{{date}}', date('Y-m-d'), $eml_string);
$fp = fopen('tmp/eml.xml', 'w');
fwrite($fp, $eml_string);
fclose($fp);

// Make the metadata file - inserting the correct dynamic fields from the
// configuration file
$field_definitions = "\n";
$count = 0;
foreach($dwc_dynamic_fields as $field => $uri){
    $field_definitions .= "\t\t<field index=\"$count\" term=\"$uri\" />\n";
    $count++;
}
$metaString = file_get_contents("metadata/darwin_core.xml");
$metaString = str_replace('{{DYNAMIC_FIELDS}}', $field_definitions, $metaString);
$fp = fopen('tmp/meta.xml', 'w');
fwrite($fp, $metaString);
fclose($fp);

    
echo "Creating zip\n";
$zip = new ZipArchive();
$zipFile = "tmp/edinburgh_herbarium_dwc.zip";

if ($zip->open($zipFile, ZIPARCHIVE::CREATE)!==TRUE) {
    exit("cannot open <$zipFile>\n");
}
    
$zip->addFile("tmp/herbarium_specimens.csv", "herbarium_specimens.csv");
$zip->addFile("tmp/herbarium_specimen_images.csv", "herbarium_specimen_images.csv");
    
$zip->addFile("tmp/eml.xml", "eml.xml");
$zip->addFile("tmp/meta.xml", "meta.xml");

if ($zip->close()!==TRUE) {
    exit("cannot close <$zipFile>\n". $zip->getStatusString());
}

echo "Removing temp files\n";
unlink("tmp/herbarium_specimens.csv");
unlink("tmp/herbarium_specimen_images.csv");
unlink("tmp/meta.xml");
unlink("tmp/eml.xml");

// move the file
if(file_exists("data/edinburgh_herbarium_dwc.zip")){
    unlink("data/edinburgh_herbarium_dwc.zip");
}
rename("tmp/edinburgh_herbarium_dwc.zip", "data/edinburgh_herbarium_dwc.zip");

echo "Archive created for herbarium \n";