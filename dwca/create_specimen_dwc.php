<?php

require_once('../config.php');
require_once('../SolrConnection.php');

// this might take some time so give use 5 minutes to think about it
set_time_limit(0);

$solr = new SolrConnection();

// have we been passed a list of barcodes to process
$barcodes = @$_REQUEST['barcodes'];
if($barcodes) $barcodes = explode(',',$barcodes);

$query = (object)array("query" => "record_type_s:specimen");

$out = fopen("tmp/herbarium_specimens.csv", 'w');
if(!$out) exit("Couldn't open file:tmp/herbarium_specimens.csv ");

// put in a header row - makes things easier
fputcsv($out, array_keys($dwc_dynamic_fields));

$out_images = fopen("tmp/herbarium_specimen_images.csv", 'w');
if(!$out_images) exit("Couldn't open file:tmp/herbarium_specimen_images.csv ");

// put the header row in 
$image_fields = array(
    "coreId",
    "type",
    "format",
    "accessURI",
    "associatedSpecimenReference",
    "identifier",
    "description",
    "serviceExpectation"
);

// put in a header row - makes things easier
fputcsv($out_images, array_keys($image_fields));

    
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

function write_image_record($out, $record, $fields){

    // no images no go
    if(!isset($record->image_filename_nis) || !isset($record->barcode_s)) return;

    // if it has an image then we add a single row for the IIIF Manifest
    $iiif_row = array();
    $iiif_row["coreId"] = "https://data.rbge.org.uk/herb/{$record->barcode_s}"; // core id
    $iiif_row["type"] = "InteractiveResource"; // type
    $iiif_row["format"] = "application/ld+json"; // format
    $iiif_row["accessURI"] = "https://iiif.rbge.org.uk/herb/iiif/{$record->barcode_s}/manifest"; // identifier
    $iiif_row["associatedSpecimenReference"] = "https://iiif.rbge.org.uk/viewers/mirador/?manifest=https://iiif.rbge.org.uk/herb/iiif/{$record->barcode_s}/manifest"; // reference
    $iiif_row["identifier"] =  $record->barcode_s;
    $iiif_row["description"] = "IIIF Manifest for specimen {$record->barcode_s}"; // description
    $iiif_row["serviceExpectation"] = "IIIF"; // serviceExpectation

    $ordered_row = array();
    foreach($fields as $field){
        if(isset($iiif_row[$field])) $ordered_row[] = $iiif_row[$field];
        else $ordered_row[] = null;
    }
    fputcsv($out, $ordered_row);

    // but we add one row for each of the files - may be more than one
    foreach($record->image_filename_nis as $file_name){

        $image_name = pathinfo($file_name, PATHINFO_FILENAME);
        $imageUri = "https://iiif.rbge.org.uk/herb/iiif/$image_name/full/300,/0/default.jpg";

        $jpeg_row = array();
        $jpeg_row["coreId"] = "https://data.rbge.org.uk/herb/{$record->barcode_s}"; // core id
        $jpeg_row["type"] = "StillImage"; // type
        $jpeg_row["format"] = "image/jpeg"; // format
        $jpeg_row["accessURI"] = "https://iiif.rbge.org.uk/herb/iiif/{$record->barcode_s}/manifest"; // identifier
        $jpeg_row["associatedSpecimenReference"] = $imageUri; // reference
        $jpeg_row["identifier"] =  $image_name;
        $jpeg_row["description"] = "JPEG Image of specimen {$record->barcode_s}"; // description
        $jpeg_row["serviceExpectation"] = "JPEG"; // serviceExpectation

        $ordered_row = array();
        foreach($fields as $field){
            if(isset($jpeg_row[$field])) $ordered_row[] = $jpeg_row[$field];
            else $ordered_row[] = null;
        }
        fputcsv($out, $ordered_row);

        
    }

}

function write_specimen_record($out, $record, $fields){

    $row = array();

    if(!isset($record->barcode_t)) return; // ignore few with missing barcodes

    // <field index="0" term="http://rs.tdwg.org/dwc/terms/occurrenceID" />
    $row['occurrenceID'] = "https://data.rbge.org.uk/herb/" . $record->barcode_t;
        
    // <field index="1" term="http://rs.tdwg.org/dwc/terms/catalogNumber" />
    $row['catalogNumber'] = $record->barcode_t;

    // it location_sensitive_t is not set or it is set to LL then it is OK to display location
    if( 
        !(isset($record->cultivated_i) && $record->cultivated_i) // not cultivated
        &&
        (
            !isset($record->location_sensitive_t) // sensitive info not set
            || 
            (isset($record->location_sensitive_t) && $record->location_sensitive_t == 'LL') // of if set set to 'LL'
        )
    ){
        // can display location info
         // <field index="3" term="http://rs.tdwg.org/dwc/terms/informationWithheld" />
        $row['informationWithheld'] = null;
        $row['decimalLongitude'] = isset($record->decimal_longitude_ni) ? $record->decimal_longitude_ni : null;
        $row['decimalLatitude'] = isset($record->decimal_latitude_ni) ? $record->decimal_latitude_ni : null;
    }else{
        // can't display location info
        // <field index="3" term="http://rs.tdwg.org/dwc/terms/informationWithheld" />
        $row['informationWithheld'] = 'Sensitive location data withheld';
        $row['decimalLongitude'] = null;
        $row['decimalLatitude'] = null;
    }

    $row['scientificName'] = isset($record->current_name_plain_ni) ? $record->current_name_plain_ni : null;
    $row['family'] = isset($record->family_t) ? $record->family_t : null;
    $row['genus'] = isset($record->genus_t) ? $record->genus_t : null;
    $row['specificEpithet'] = isset($record->species_t) ? $record->species_t : null;
    $row['higherGeography'] = isset($record->region_name_s) ? $record->region_name_s : null;
    $row['country'] = isset($record->country_code_t) ? $record->country_code_t : null;
    $row['locality'] = isset($record->country_t) ? $record->country_t : null;
    $row['eventDate'] =  isset($record->collection_date_iso_s) ? $record->collection_date_iso_s : null;
    $row['recordedBy'] =  isset($record->collector_t) ? $record->collector_t : null;
    $row['recordNumber'] =  isset($record->collector_num_t) ? $record->collector_num_t : null;
    $row['CatalogNumberNumeric'] =  isset($record->id_s) ? $record->id_s : null;
    $row['verbatimEventDate'] =  isset($record->collection_date_s) ? $record->collection_date_s : null;
    $row['verbatimElevation'] =  isset($record->altitude_metres_ni) ? $record->altitude_metres_ni  . "m" : null;
    $row['minimumElevationInMeters'] =  isset($record->altitude_metres_ni) ? $record->altitude_metres_ni : null;
    $row['maximumElevationInMeters'] =  isset($record->altitude_metres_ni) ? $record->altitude_metres_ni : null;
    $row['typeStatus'] =  isset($record->istype_id) &&  $record->istype_id ? "Type Specimen" : null;
    

    // make sure the fields are output in the
    // order they are in the config file
    // and none are missing
    $ordered_row = array();
    foreach(array_keys($fields) as $field){
        if(isset($row[$field])) $ordered_row[] = $row[$field];
        else $ordered_row[] = null;
    }
    fputcsv($out, $ordered_row);

}