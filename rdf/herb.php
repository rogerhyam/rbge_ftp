<?php
    
    require_once('../config.php');
    require_once('common.php');
    
    /*
        Generates RDF for a herbarium specimen from data in the darwin_core table
    */
    
    $guid = @$_GET['guid'];
    
    // do nothing if we don't have the pair
    if(!$guid){
       header("HTTP/1.0 400 Bad Request");
       echo "You must provide a guid parameters";
       exit();
    }

    // get the data associated with this specimen
    $sql = "SELECT * FROM bgbase_dump.darwin_core WHERE GloballyUniqueIdentifier = '$guid'";
    $response = $mysqli->query($sql);
    $row = $response->fetch_assoc();
    
    // check we have something
    if($response->num_rows == 0){
        header("HTTP/1.0 404 Not Found");
        echo "There is no specimen matching the GUID - " . $guid;
        exit();
    }

	header("Access-Control-Allow-Origin: *");
    header("Content-type: application/rdf+xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    rdfHeader();
    rdfDocumentMetadata();
    rdfData($row);
    rdfFooter();
    
    
function rdfData($row){
	
	global $collectors;
	
	// no matter what we are asked for we use the https uri as the main identifier
	if(strpos($row['GloballyUniqueIdentifier'], 'http://') !== false){
		$http_uri = $row['GloballyUniqueIdentifier'];
		$https_uri = str_replace('http://', 'https://', $row['GloballyUniqueIdentifier']);
	}else{
		$https_uri = $row['GloballyUniqueIdentifier']; 
		$http_uri = str_replace('https://', 'http://', $row['GloballyUniqueIdentifier']);
	}
 
?>

    <!--This is metadata about this specimen-->
    <rdf:Description rdf:about="<?php echo $https_uri ?>">
		
		<owl:sameAs rdf:resource="<?php echo $http_uri ?>"/>
    
        <!-- Assertions made in simple Dublin Core -->
        <dc:publisher rdf:resource="http://www.rbge.org.uk" />
        <?php
            $collectorString = "";
            if($row['Collector']){
                $collectorString = htmlspecialchars($row['Collector']);
                if($row['CollectorNumber']) $collectorString .= " #" . $row['CollectorNumber'];                
            }
            if($collectorString) $collectorString .= " ";
                    
        ?>
        
        <dc:title><?php echo $collectorString . htmlspecialchars(strip_tags($row['ScientificName'])) ?></dc:title>
        
        <?php 
            if($collectorString){
                $collectorString = ' collected by ' . $collectorString;
            }
        
        ?>
        <dc:description>A herbarium specimen of <?php echo htmlspecialchars(strip_tags($row['ScientificName'])) . $collectorString ?></dc:description>
        
        <?php if($row['Collector']){?>
               <dc:creator><?php echo htmlspecialchars($row['Collector']) ?></dc:creator>
        <?php }?>
        
        <?php if($row['EarliestDateCollected']){?>
            <dc:created><?php echo htmlspecialchars($row['EarliestDateCollected']) ?></dc:created>
        <?php }?>
        
        
        <?php if($row['DecimalLongitude'] && $row['DecimalLatitude']){?>
            <geo:lat><?php echo htmlspecialchars($row['DecimalLatitude']) ?></geo:lat>
            <geo:long><?php echo htmlspecialchars($row['DecimalLongitude']) ?></geo:long>
        <?php } ?>
        
        <!-- Assertions based on Darwin Core -->
        <dwc:sampleID><?php echo $row['GloballyUniqueIdentifier'] ?></dwc:sampleID>
        <dc:modified><?php echo $row['DateLastModified'] ?></dc:modified>
        <dwc:basisOfRecord>Specimen</dwc:basisOfRecord>
        <dc:type>Specimen</dc:type>
        <dwc:institutionCode>http://biocol.org/urn:lsid:biocol.org:col:15670</dwc:institutionCode>
        <dwc:collectionCode>E</dwc:collectionCode>
        <dwc:catalogNumber><?php echo $row['CatalogNumber'] ?></dwc:catalogNumber>
        <dwc:scientificName><?php echo htmlspecialchars(strip_tags($row['ScientificName'])) ?></dwc:scientificName>
        <dwc:family><?php echo $row['Family'] ?></dwc:family>
        <dwc:genus><?php echo $row['Genus'] ?></dwc:genus>
        <dwc:specificEpithet><?php echo $row['SpecificEpithet'] ?></dwc:specificEpithet>
        <dwc:higherGeography><?php echo htmlspecialchars($row['HigherGeography']) ?></dwc:higherGeography>

    <?php if($row['Country']){?>
        <dwc:country><?php echo htmlspecialchars($row['Country']) ?></dwc:country>
        <dwc:countryCode><?php echo htmlspecialchars($row['Country']) ?></dwc:countryCode>
    <?php }?>
    
    <?php if($row['StateProvince']){?>
        <dwc:stateProvince><?php echo htmlspecialchars($row['StateProvince']) ?></dwc:stateProvince>
    <?php }?>
        
    <?php if($row['County']){?>
        <dwc:county><?php echo htmlspecialchars($row['County']) ?></dwc:county>
    <?php }?>
        
    <?php if($row['Locality']){?>
        <dwc:locality><?php echo htmlspecialchars($row['Locality']) ?></dwc:locality>
    <?php }?>
        
    <?php if($row['DecimalLongitude'] && $row['DecimalLatitude']){?>
        <dwc:decimalLongitude><?php echo htmlspecialchars($row['DecimalLongitude']) ?></dwc:decimalLongitude>
        <dwc:decimalLatitude><?php echo htmlspecialchars($row['DecimalLatitude']) ?></dwc:decimalLatitude>
    <?php }?>       
        
    <?php if($row['MinimumElevationInMeters'] && $row['MaximumElevationInMeters']){?>
        <dwc:minimumElevationInMeters><?php echo htmlspecialchars($row['MinimumElevationInMeters']) ?></dwc:minimumElevationInMeters>
        <dwc:maximumElevationInMeters><?php echo htmlspecialchars($row['MaximumElevationInMeters']) ?></dwc:maximumElevationInMeters>
    <?php }?>
    
    <?php if($row['EarliestDateCollected']){?>
        <dwc:earliestDateCollected><?php echo htmlspecialchars($row['EarliestDateCollected']) ?></dwc:earliestDateCollected>
    <?php }?>
    
    <?php
		if($row['Collector']){
    	
			$coll_name = trim($row['Collector']);	
			
			if(isset($collectors[$coll_name])){
				foreach($collectors[$coll_name] as $coll_uri){
					echo "<dwciri:recordedBy rdf:resource=\"$coll_uri\" />\n";
				}
			}
			
			// the old dc version
			echo "<dwc:recordedBy>" . htmlspecialchars($coll_name) . "</dwc:recordedBy>\n";
        	
    	}
	
	?>

    <?php if($row['CollectorNumber']){?>
        <dwc:recordNumber><?php echo htmlspecialchars($row['CollectorNumber']) ?></dwc:recordNumber>
    <?php }?>
	
	<!-- Images associated with the specimen -->

    <?php
        relatedImages($row['CatalogNumber']);
    ?>

	<!-- IIIF resources associated with the specimen -->
	
    <?php
        iifImages($row['CatalogNumber']);
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
function relatedImages($barcode){
    
      global $mysqli;
      
      $sql = "SELECT di.id, oi.date
              FROM image_archive.derived_images as di join image_archive.original_images as oi on di.derived_from = oi.id
              WHERE di.image_type = 'JPG'
              AND oi.barcode = '$barcode'";
    
       $response = $mysqli->query($sql);
       
       while($row = $response->fetch_assoc()){
           
           $imageUri = "https://data.rbge.org.uk/images/" . $row['id'];
           $date = str_replace(' ', 'T', $row['date']) . "Z"; // convert to ISO 8601
           
           echo "<dc:relation>\n";
           
           echo "\t<rdf:Description  rdf:about=\"$imageUri\" >\n";
           echo "\t\t<dc:identifier rdf:resource=\"$imageUri\" />\n";
           echo "\t\t<dc:type rdf:resource=\"http://purl.org/dc/dcmitype/Image\" />\n";
           echo "\t\t<dc:subject rdf:resource=\"https://data.rbge.org.uk/herb/$barcode\" />\n";
           echo "\t\t<dc:format>image/jpeg</dc:format>\n";
           echo "\t\t<dc:description xml:lang=\"en\">Image of herbarium specimen</dc:description>\n";
           echo "\t\t<dc:created>$date</dc:created>\n";
		   
		   echo "\t\t<dc:license rdf:resource=\"https://creativecommons.org/publicdomain/zero/1.0/\" />\n";
		   
		   
           echo "\t</rdf:Description>\n";
           
           echo "</dc:relation>\n";
           
           // also echo it out just as a dwc link           
           echo "<dwc:associatedMedia rdf:resource=\"$imageUri\" />\n";
           
		   
       }
      
}

function iifImages($barcode){
	
	
    global $mysqli;
    
    $sql = "SELECT di.id, oi.date
            FROM image_archive.derived_images as di join image_archive.original_images as oi on di.derived_from = oi.id
            WHERE di.image_type = 'ZOOMIFY'
            AND oi.barcode = '$barcode'";
  
     $response = $mysqli->query($sql);
     
     while($row = $response->fetch_assoc()){
         
         $imageUri = "https://iiif.rbge.org.uk/herb/iiif/$barcode/manifest";
         $date = str_replace(' ', 'T', $row['date']) . "Z"; // convert to ISO 8601
         
         echo "<dc:relation>\n";
         
         echo "\t<rdf:Description  rdf:about=\"$imageUri\" >\n";
         echo "\t\t<dc:identifier rdf:resource=\"$imageUri\" />\n";
         echo "\t\t<dc:type rdf:resource=\"http://iiif.io/api/presentation/3#Manifest\" />\n";
         echo "\t\t<dc:subject rdf:resource=\"https://data.rbge.org.uk/herb/$barcode\" />\n";
         echo "\t\t<dc:format>application/ld+json</dc:format>\n";
         echo "\t\t<dc:description xml:lang=\"en\">A IIIF resource for this specimen.</dc:description>\n";
         echo "\t\t<dc:created>$date</dc:created>\n";
		 
		 echo "\t\t<dc:license rdf:resource=\"https://creativecommons.org/publicdomain/zero/1.0/\" />\n";
		 
         echo "\t</rdf:Description>\n";
         
         echo "</dc:relation>\n";
         
	   
     }
	
	
}
    
	
?>
    
   

    
    
