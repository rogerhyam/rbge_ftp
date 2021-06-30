#!/usr/bin/php
<?php
    
    require_once('../config.php');
    set_time_limit(0);
    
    $fp = fopen("tmp/tmp.xml", 'w');
    
    // write out the header
    writeHeader($fp);
    
    // fetch a list of all the specimens with images
    $sql = "SELECT
         dc.*,
         oi.barcode,
         di.id as image_id,
         oi.date as created,
         di.date as modified
         FROM image_archive.derived_images as di
         JOIN image_archive.original_images as oi
         on di.derived_from = oi.id
         JOIN bgbase_dump.darwin_core as dc on dc.GloballyUniqueIdentifier = concat('http://data.rbge.org.uk/herb/', oi.barcode)
         WHERE di.image_type = 'JPG'
         ";
        
    $response = $mysqli->query($sql, MYSQLI_USE_RESULT);
    while($row = $response->fetch_assoc()){
     
        // write out a n image block
        fwrite($fp, getTaxon($row));
     
    }
    
    // write out the footer
    writeFooter($fp);
    
    // close the file
    fclose($fp);
    
    $zip = new ZipArchive();
    $zipFile = "tmp/tmp.xml.zip";

    if ($zip->open($zipFile, ZIPARCHIVE::CREATE)!==TRUE) {
        exit("cannot open <$zipFile>\n");
    }
    
    $zip->addFile("tmp/tmp.xml", "rbge_herbarium_images.xml");

    if ($zip->close()!==TRUE) {
        exit("cannot close <$zipFile>\n". $zip->getStatusString());
    }
    
    unlink("tmp/tmp.xml");
    
    // move the file
    if(file_exists("data/rbge_herbarium_images.xml.zip")){
        unlink("data/rbge_herbarium_images.xml.zip");
    }
    
    if(rename("tmp/tmp.xml.zip", "data/rbge_herbarium_images.xml.zip")){
         echo 'Zip File Created! <a href="data/rbge_herbarium_images.xml.zip">data/rbge_herbarium_images.xml.zip</a>';
    }else{
        echo 'Something bad happened';
    }
    
        
    function getTaxon($row){
        
        global $herbariumCatalogueURL;
        
        $imageID = $row['image_id'];
        $created = $row['created'];
        $modified = $row['modified'];
        
        $taxonGUID = "http://data.rbge.org.uk/taxa/" . $row['Genus'] . '/' . $row['SpecificEpithet'];
        
        $scientificName = htmlspecialchars(strip_tags($row['ScientificName']));
        
        //$scientificName = str_replace('&', '&amp;', $row['ScientificName']); // keep the <i> tags
        
        $family = $row['Family'];
        $genus = $row['Genus'];
        
        $imageURL = "http://data.rbge.org.uk/images/$imageID/700";
        $imageCreated = 'FIXME';
        
        $specimenBarcode = $row['CatalogNumber'];
        
        $specimenLocation =  htmlspecialchars($row['HigherGeography']);
        if($row['StateProvince']) $specimenLocation . ", " . htmlspecialchars($row['StateProvince']);
        if($row['County']) $specimenLocation . ", " . htmlspecialchars($row['County']);
        if($row['Locality']) $specimenLocation . ", " . htmlspecialchars($row['Locality']);
        
        $collector = "";
        if($row['Collector']){
            $collector = 'Collected by: ' . htmlspecialchars($row['Collector']);
            if($row['CollectorNumber']){
                $collector .= ' #' . htmlspecialchars($row['CollectorNumber']);
            }
        } 
        
        if($row['EarliestDateCollected']){
            $collector = $collector . " Collected on: " . $row['EarliestDateCollected'];
        }
        
        $specimenPoint = "";
        if(
        $row['DecimalLongitude']
        && is_numeric($row['DecimalLongitude'])
        && $row['DecimalLongitude'] >= -180
        && $row['DecimalLongitude'] <= 180
        && $row['DecimalLatitude']
        && is_numeric($row['DecimalLatitude'])
        && $row['DecimalLatitude'] > -90
        && $row['DecimalLatitude'] < 90
        ){
            $specimenPoint = "<geo:Point><geo:lat>" . $row['DecimalLatitude'] . "</geo:lat><geo:long>" . $row['DecimalLongitude'] . "</geo:long></geo:Point>\n";   
        }

        $herbariumCatalogueURLEscaped = htmlspecialchars($herbariumCatalogueURL);

        $s = <<<TAXON
      
<taxon>
   <dcterms:identifier>$taxonGUID</dcterms:identifier>
   <dc:source>$taxonGUID</dc:source>
   <dwc:Kingdom>Plantae</dwc:Kingdom>
   <dwc:Family>$family</dwc:Family>
   <dwc:Genus>$genus</dwc:Genus>
   <dwc:ScientificName>$scientificName</dwc:ScientificName>
   <dataObject>
       <dcterms:identifier>$imageURL</dcterms:identifier>
       <dataType>http://purl.org/dc/dcmitype/StillImage</dataType>
       <mimeType>image/jpeg</mimeType>
       <agent role="publisher" homepage="http://www.rbge.org.uk">Royal Botanic Garden
           Edinburgh</agent> 
       <dcterms:created>$created</dcterms:created>
       <dcterms:modified>$modified</dcterms:modified>
       <dc:title>Herbarium Specimen Image</dc:title>
       <dc:language>en</dc:language>
       <license>http://creativecommons.org/licenses/by-nc/3.0/</license>
       <dc:rights>Please contact rights holder before commercial use.</dc:rights>
       <dcterms:rightsHolder>Royal Botanic Garden Edinburgh</dcterms:rightsHolder>
       <audience>Expert users</audience>
       <dc:source>$herbariumCatalogueURLEscaped$specimenBarcode</dc:source>
       <dc:description xml:lang="en">
        $scientificName ($family).
        Image of herbarium specimen held at Royal Botanic Garden Edinburgh (E).
        Specimen barcode number $specimenBarcode.
        Specimen collected from: $specimenLocation.
        $collector
        This is a low resolution version of the image.
       </dc:description>
       <mediaURL>$imageURL</mediaURL>
       <location>$specimenLocation</location>
       $specimenPoint    
   </dataObject>
</taxon>
       
TAXON;

    return $s;
        
    }
    
    
    function writeHeader($fp){
        
        $header = '<?xml version="1.0" standalone="yes" ?>
        <response
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://rs.tdwg.org/dwc/dwcore/ http://services.eol.org/schema/content_0_3.xsd"
            xmlns="http://www.eol.org/transfer/content/0.3"
            xmlns:dc="http://purl.org/dc/elements/1.1/"
            xmlns:dcterms="http://purl.org/dc/terms/"
            xmlns:dwc="http://rs.tdwg.org/dwc/dwcore/"
            xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" >';
    
        fwrite($fp, $header);
        
    }
    
    function writeFooter($fp){
        
        $footer = '</response>';
        fwrite($fp, $footer);        
        
    }
    

?>