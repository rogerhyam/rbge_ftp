<?php
    
    require_once('../config.php');
    require_once('common.php');
    require_once('../SolrConnection.php');
    
    /*
        Generates RDF for a herbarium specimen from data in the darwin_core table
    */
    
    $barcode = @$_GET['barcode'];
    $solr = new SolrConnection();

    // do nothing if we don't have the pair
    if(!$barcode){
       header("HTTP/1.0 400 Bad Request");
       echo "You must provide a barcode parameters";
       exit();
    }

    // try and get the solr record for it
    $result = $solr->query_object((object)array(
        "query" => "barcode_s:$barcode"
    ));

    // fail to find a solr record for it
    if($result->response->numFound == 0){
        header("HTTP/1.0 404 Not Found");
        echo "<h1>Not Found</h1>";
        echo "There is no specimen with barcode $barcode";
        exit();
    }
    
    // thunderbirds are go we have a record
    $record = $result->response->docs[0];

	header("Access-Control-Allow-Origin: *");
    header("Content-type: application/rdf+xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    rdfHeader();
    rdfDocumentMetadata();
    rdfData($barcode, $record);
    rdfFooter();
    
    
function rdfData($barcode, $record){
	
	
	// no matter what we are asked for we use the https uri as the main identifier
    $http_uri = "http://data.rbge.org.uk/herb/$barcode";
	$https_uri = "https://data.rbge.org.uk/herb/$barcode";
 
?>

    <!--This is metadata about this specimen-->
    <rdf:Description rdf:about="<?php echo $https_uri ?>">
		
		<owl:sameAs rdf:resource="<?php echo $http_uri ?>"/>
    
        <!-- Assertions made in simple Dublin Core -->
        <dc:publisher rdf:resource="http://www.rbge.org.uk" />
        <?php
            $collectorString = "";
            if(isset($record->collector_s)){
                $collectorString = htmlspecialchars($record->collector_s);
                if(isset($record->collector_num_s)) $collectorString .= " #" . $record->collector_num_s;                
            }
            if($collectorString) $collectorString .= " ";

            $current_name = isset($record->current_name_plain_ni)? $record->current_name_plain_ni : 'indet.';
            $current_name =  htmlspecialchars(strip_tags($current_name));

            echo "<dc:title>$collectorString $current_name</dc:title>";

            if($collectorString) $collectorString = ' collected by ' . $collectorString;

            echo " <dc:description>A herbarium specimen of $current_name $collectorString ?></dc:description>";
            
            if(isset($record->collector_s)){
               echo "<dc:creator>". htmlspecialchars($record->collector_s) . "</dc:creator>";
            }
        
        if(isset($record->collection_date_iso_s)){
           echo "<dc:created>" . htmlspecialchars($record->collection_date_iso_s) . "</dc:created>";
        }
        
        echo "\n<!-- Assertions based on Darwin Core and Dublin Core -->\n";
        echo "<dwc:sampleID>https://data.rbge.org.uk/herb/$barcode</dwc:sampleID>";
        echo "<dc:modified>" . date(DATE_ATOM) . "</dc:modified>";
        echo "<dwc:basisOfRecord>Specimen</dwc:basisOfRecord>";
        echo "<dc:type>Specimen</dc:type>";
        echo "<dwc:institutionCode>http://biocol.org/urn:lsid:biocol.org:col:15670</dwc:institutionCode>";
        echo "<dwc:collectionCode>E</dwc:collectionCode>";
        echo "<dwc:catalogNumber>$barcode</dwc:catalogNumber>";

        if(isset($record->current_name_plain_ni)) echo "\t<dwc:scientificName>". htmlspecialchars(strip_tags($record->current_name_plain_ni)) ."</dwc:scientificName>\n";
        if(isset($record->family_ni)) echo "\t<dwc:family>" .  htmlspecialchars($record->family_ni) ." </dwc:family>\n";
        if(isset($record->genus_ni)) echo "\t<dwc:genus>" .  htmlspecialchars($record->genus_ni) . "</dwc:genus>\n";
        if(isset($record->species_ni)) echo "\t<dwc:specificEpithet>" .  htmlspecialchars($record->species_ni) ."</dwc:specificEpithet>\n";
        if(isset($record->region_name_s)) echo "\t<dwc:higherGeography>" . htmlspecialchars($record->region_name_s) ."</dwc:higherGeography>\n";
        if(isset($record->country_s)) echo "\t<dwc:country>".  htmlspecialchars($record->country_s) ."</dwc:country>\n";
        if(isset($record->country_code_t)) echo "\t<dwc:countryCode>".  htmlspecialchars($record->country_code_t) . "</dwc:countryCode>\n";
        if(isset($record->locality_ni)) echo "\t<dwc:stateProvince>" .  htmlspecialchars($record->locality_ni) ."</dwc:stateProvince>\n";
        if(isset($record->collection_date_iso_s)) echo "\t<dwc:earliestDateCollected>" .  htmlspecialchars($record->collection_date_iso_s) ."</dwc:earliestDateCollected>\n";
        if(isset($record->collector_s)) echo "\t<dwc:recordedBy>" . htmlspecialchars($record->collector_s) . "</dwc:recordedBy>\n";
        if(isset($record->collector_num_s)) echo "\t<dwc:recordNumber>" . htmlspecialchars($record->collector_num_s) ."</dwc:recordNumber>\n";
        
        // elevation
        if(isset($record->altitude_metres_ni)) echo "\t<dwc:minimumElevationInMeters>" . htmlspecialchars($record->altitude_metres_ni) ."</dwc:minimumElevationInMeters>\n";
        if(isset($record->altitude_metres_ni)) echo "\t<dwc:maximumElevationInMeters>" . htmlspecialchars($record->altitude_metres_ni) ."</dwc:maximumElevationInMeters>\n";

        // long/lat
        if(isset($record->decimal_longitude_ni)) echo "\t<dwc:decimalLongitude>" . htmlspecialchars($record->decimal_longitude_ni) ."</dwc:decimalLongitude>\n"; 
        if(isset($record->decimal_latitude_ni)) echo "\t<dwc:decimalLatitude>" . htmlspecialchars($record->decimal_latitude_ni) ."</dwc:decimalLatitude>\n";

        if(isset($record->decimal_longitude_ni)) echo "\t<geo:long>" . htmlspecialchars($record->decimal_longitude_ni) ."</geo:long>\n"; 
        if(isset($record->decimal_latitude_ni)) echo "\t<geo:lat>" . htmlspecialchars($record->decimal_latitude_ni) ."</geo:lat>\n";


    	echo "\n\t<!-- Images associated with the specimen -->\n";
        relatedImages($barcode, $record);

    	echo "\n\t<!-- IIIF resources associated with the specimen -->\n";
        iiifImages($barcode, $record);

?>
      
    </rdf:Description>
    
<?php

}

function hasVersion($guid){
    
    global $mysqli;
    global $herbariumCatalogueURL;

    // link to the human readable version as another form of the metadata
    $humanURI = str_replace('&', "&amp;", $herbariumCatalogueURL . substr($guid, 29));
    echo "<dc:hasVersion rdf:resource=\"$humanURI\" />\n";
    
    // check to see if we have a JSTOR version for this specimen.
    $sql ="SELECT project_code, CatalogNumber FROM `darwin_core`
        JOIN specimens_mv ON substr(`OtherCatalogNumbers`, 8) = `specimens_mv`.`specimen_num`
        WHERE project_code IS NOT NULL
        AND `GloballyUniqueIdentifier` = '$guid'";
    
    $response = $mysqli->query($sql);
    if($response->num_rows > 0){
        $row = $response->fetch_assoc();
        $pc = (int)$row['project_code'];
        $barcode = strtolower($row['CatalogNumber']);
        if($pc > 100){
            echo "<dc:hasVersion rdf:resource=\"http://plants.jstor.org/specimen/$barcode\" />\n";
        }
    }


    //echo "<dc:hasVersion>doesn't work</dc:hasVersion>";
}


/*
    If there are any images associated with this specimen add them in.
*/
function relatedImages($barcode, $record){
    
    if(isset($record->image_filename_nis)){

        foreach($record->image_filename_nis as $file_name){
    
            $image_name = pathinfo($file_name, PATHINFO_FILENAME);

            $imageUri = "https://iiif.rbge.org.uk/herb/iiif/$image_name/full/300,/0/default.jpg";
                        
            echo "<dc:relation>\n";
            
            echo "\t<rdf:Description  rdf:about=\"$imageUri\" >\n";
            echo "\t\t<dc:identifier rdf:resource=\"$imageUri\" />\n";
            echo "\t\t<dc:type rdf:resource=\"http://purl.org/dc/dcmitype/Image\" />\n";
            echo "\t\t<dc:subject rdf:resource=\"https://data.rbge.org.uk/herb/$barcode\" />\n";
            echo "\t\t<dc:format>image/jpeg</dc:format>\n";
            echo "\t\t<dc:description xml:lang=\"en\">Image of herbarium specimen</dc:description>\n";
            echo "\t\t<dc:license rdf:resource=\"https://creativecommons.org/publicdomain/zero/1.0/\" />\n";
            
            echo "\t</rdf:Description>\n";
            
            echo "</dc:relation>\n";
            
            // also echo it out just as a dwc link           
            echo "<dwc:associatedMedia rdf:resource=\"$imageUri\" />\n";
            
        }
    }
      
}


/*
    If there is an image add the IIIF manifest link
*/
function iiifImages($barcode, $record){
	

    // E00855041

    if(isset($record->image_filename_nis) && count($record->image_filename_nis) > 0){
   
        $imageUri = "https://iiif.rbge.org.uk/herb/iiif/$barcode/manifest";
        
        echo "<dc:relation>\n";
        
        echo "\t<rdf:Description  rdf:about=\"$imageUri\" >\n";
        echo "\t\t<dc:identifier rdf:resource=\"$imageUri\" />\n";
        echo "\t\t<dc:type rdf:resource=\"http://iiif.io/api/presentation/3#Manifest\" />\n";
        echo "\t\t<dc:subject rdf:resource=\"https://data.rbge.org.uk/herb/$barcode\" />\n";
        echo "\t\t<dc:format>application/ld+json</dc:format>\n";
        echo "\t\t<dc:description xml:lang=\"en\">A IIIF resource for this specimen.</dc:description>\n";
        
        echo "\t\t<dc:license rdf:resource=\"https://creativecommons.org/publicdomain/zero/1.0/\" />\n";
        
        echo "\t</rdf:Description>\n";
        
        echo "</dc:relation>\n";

     }
	
	
}
    
	
?>
    
   

    
    
