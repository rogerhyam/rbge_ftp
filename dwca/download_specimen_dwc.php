<?php

if(!@$_REQUEST['barcodes']){
    // they didn't pass a list of barcodes
    http_response_code(400);
    echo "You need to provide a comma separated list of barcodes that you'd like data for. GET and POST accepted.";
    exit;
}else{
    $barcodes = explode(',', $_REQUEST['barcodes']);
}

$random_dir = 'data/downloads/' . rand() . '/';
mkdir($random_dir, 0777, true);

// write the specimens
$out = fopen($random_dir . 'herbarium_specimens.csv', 'w');
$in = fopen('zip://data/edinburgh_herbarium_dwc.zip#herbarium_specimens.csv' , 'r');

// header 
fputcsv($out, fgetcsv($in));

while($line = fgetcsv($in)){
    if(in_array($line[1], $barcodes)){
        fputcsv($out, $line);
    }
}

fclose($in);
fclose($out);

// same for images
$out = fopen($random_dir . 'herbarium_specimen_images.csv', 'w');
$in = fopen('zip://data/edinburgh_herbarium_dwc.zip#herbarium_specimen_images.csv' , 'r');

// header 
fputcsv($out, fgetcsv($in));

while($line = fgetcsv($in)){
    if(in_array(substr($line[0], -9), $barcodes)){
        fputcsv($out, $line);
    }
}

fclose($in);
fclose($out);

// metadata files
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