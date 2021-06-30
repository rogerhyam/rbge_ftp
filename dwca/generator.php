#!/usr/bin/php
<?php
require_once('../config.php');

// this might take some time so give use 5 minutes to think about it
set_time_limit(0);

// do the file creation
dumpTable("darwin_core");
dumpTable("darwin_core_living");
echo 'Finished';

function dumpTable($tableName){

    global $mysqli;

    echo "Dumping table\n";

    // Make the CSV file of the table
    
    $fp = fopen("tmp/$tableName.csv", 'w');

    if(!$fp){
        exit("Couldn't open file:tmp/$tableName.csv ");
    }
    
    $sql = "SELECT * FROM $tableName";
    $response = $mysqli->query($sql, MYSQLI_USE_RESULT);
    while($row = $response->fetch_array()){       
        
        $newRow = array();
        for($i = 0; $i < $response->field_count; $i++){
            $s = str_replace("\n", " ", $row[$i]);
            $s = str_replace("\r", " ", $row[$i]);
            $s = str_replace("NULL", "", $s);
            $newRow[$i] = strip_tags($s);
        }
        
        fputcsv($fp, $newRow);
    }

    fclose($fp);
    
    // make a similar table for the images
    dumpImageTable($tableName);

    // Make the Provenance file - just change the created date and year
    $provString = file_get_contents("metadata/$tableName" . "_prov.xml");
    $provString = str_replace('{{TIMESTAMP}}', date(DATE_ATOM), $provString);
    $provString = str_replace('{{YEAR}}', date('Y'), $provString);
    $fp = fopen('tmp/prov.xml', 'w');
    fwrite($fp, $provString);
    fclose($fp);
    
    echo "Creating zip\n";
    $zip = new ZipArchive();
    $zipFile = "tmp/$tableName.zip";

    if ($zip->open($zipFile, ZIPARCHIVE::CREATE)!==TRUE) {
        exit("cannot open <$zipFile>\n");
    }
    
    $zip->addFile("tmp/$tableName.csv", "$tableName.csv");
    $zip->addFile("tmp/". $tableName. "_images.csv", $tableName. "_images.csv");
    
    $zip->addFile("tmp/prov.xml", "prov.xml");
    $zip->addFile("metadata/$tableName.xml", "meta.xml");

    if ($zip->close()!==TRUE) {
        exit("cannot close <$zipFile>\n". $zip->getStatusString());
    }
    
    echo "Removing temp files\n";
    unlink("tmp/$tableName.csv");
    unlink("tmp/". $tableName. "_images.csv");
    unlink("tmp/prov.xml");
    
    // move the file
    if(file_exists("data/$tableName.zip")){
        unlink("data/$tableName.zip");
    }
    rename("tmp/$tableName.zip", "data/$tableName.zip");
    
    echo 'Archive created for ' . $tableName . " \n";
}

function dumpImageTable($type){

    global $mysqli;

    // Make the CSV file of the table
    echo "Dumping Image Table\n";
    
    $fp = fopen("tmp/". $type. "_images.csv", 'w');

    if(!$fp){
        exit("Couldn't open file:tmp/". $type. "_images.csv");
    }
    
    $sql = "SELECT accession_barcode, image_url, photographer FROM image_archive.repo_item_images";
    if($type == 'darwin_core'){
        $sql .= " WHERE accession_barcode LIKE 'E%' ";
        $url_prefix = 'http://data.rbge.org.uk/herb/';
        $item_type = 'herbarium specimen';
    }else{
        $sql .= " WHERE accession_barcode NOT LIKE 'E%' ";
        $url_prefix = 'http://data.rbge.org.uk/living/';
        $item_type = 'living collection specimen with accession number';
    }
    $response = $mysqli->query($sql, MYSQLI_USE_RESULT);
    while($row = $response->fetch_array()){       
        
        $new_row = array();
        $new_row[] = $url_prefix . $row['accession_barcode']; // core id
        $new_row[] = "StillImage"; // type
        $new_row[] = "image/jpeg"; // format
        $new_row[] = $row['image_url']; // identifier
        $new_row[] = $url_prefix . $row['accession_barcode']; // reference
        
        $new_row[] = $row['accession_barcode']; // title
        
        // description
        $description = "Image of $item_type ". $row['accession_barcode'];
        if($row['photographer']) $description .= " by " . $row['photographer'];
        $new_row[] = $description; // title
        
        $new_row[] = "Royal Botanic Garden Edinburgh"; // publisher
		
		if($type == 'darwin_core'){
			$new_row[] = "https://creativecommons.org/publicdomain/zero/1.0/"; // license
		}else{
			$new_row[] = "http://creativecommons.org/licenses/by-nc/3.0/"; // license
		}
		
		$new_row[] = "Copyright Royal Botanic Garden Edinburgh. Contact us for rights to commercial use."; // rightsHolder
        
        $new_row[] = $row['photographer']; // creator
        
        $new_row[] = "JPEG"; // serviceExpectation

        fputcsv($fp, $new_row);
    }

    // if we are dumping the herbarium specimens we add in the
    // iiif manifests
    if($type == 'darwin_core') add_iiif_rows($fp);

    fclose($fp);

}

function add_iiif_rows($fp){

    global $mysqli;

    // Make the CSV file of the table
    echo "Adding IIIF manifests\n";
    
    $sql = "SELECT barcode FROM image_archive.derived_images WHERE image_type = 'ZOOMIFY'";
    $response = $mysqli->query($sql, MYSQLI_USE_RESULT);
    while($row = $response->fetch_array()){       
        
        $new_row = array();
        $new_row[] = "http://data.rbge.org.uk/herb/" . $row['barcode']; // core id
        $new_row[] = "InteractiveResource"; // type
        $new_row[] = "application/ld+json"; // format
        $new_row[] = "https://iiif.rbge.org.uk/herb/iiif/" . $row['barcode'] . "/manifest"; // identifier
        $new_row[] = "https://iiif.rbge.org.uk/viewers/mirador/?manifest=https://iiif.rbge.org.uk/herb/iiif/" . $row['barcode'] . "/manifest"; // reference
        $new_row[] =  $row['barcode']; // title
        $new_row[] = "IIIF Manifest for specimen " . $row['barcode']; // description
        $new_row[] = "Royal Botanic Garden Edinburgh"; // publisher
        $new_row[] = "https://creativecommons.org/publicdomain/zero/1.0/"; // license
		$new_row[] = "Copyright Royal Botanic Garden Edinburgh."; // rightsHolder
        $new_row[] = "Royal Botanic Garden Edinburgh"; // creator
        $new_row[] = "IIIF"; // serviceExpectation

        fputcsv($fp, $new_row);
    }

}

?>