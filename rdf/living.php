<?php
    
    require_once('../config.php');
    require_once('common.php');
    
    /*
        Generates RDF for a living specimen from data in the darwin_core table
    */
    
    $guid = @$_GET['guid'];
    
    // do nothing if we don't a guid
    if(!$guid){
       header("HTTP/1.0 400 Bad Request");
       echo "You must provide a guid parameters";
       exit();
    }
    
    // it may be a plant in which case we do something a little different.
    // use the accession guid as the guid
    $matches = array();
    preg_match('/([0-9]{8})([a-zA-Z]+)$/', $guid, $matches);
    if(count($matches) > 1){
        $plantGuid = $guid;
        $guid = 'http://data.rbge.org.uk/living/' . $matches[1];
    }else{
        $plantGuid = false;
    }
    
    

    // get the data associated with this specimen
    $sql = "SELECT * FROM bgbase_dump.darwin_core_living WHERE GloballyUniqueIdentifier = '$guid'";
    $response = $mysqli->query($sql);
    $row = $response->fetch_assoc();
    
    // check we have something
    if($response->num_rows == 0){
		header("Access-Control-Allow-Origin: *");
	    header("HTTP/1.0 404 Not Found");
        echo "There is no specimen matching the GUID - " . $guid;
        exit();
    }

	header("Access-Control-Allow-Origin: *");
    header("Content-type: application/rdf+xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    rdfHeader();
    rdfDocumentMetadata();
    rdfData($row, $plantGuid);
    rdfFooter();
    
function rdfData($row, $plantGuid){
    
	// no matter what we are asked for we use the https uri as the main identifier
	if(strpos($row['GloballyUniqueIdentifier'], 'http://') !== false){
		$http_uri = $row['GloballyUniqueIdentifier'];
		$https_uri = str_replace('http://', 'https://', $row['GloballyUniqueIdentifier']);
	}else{
		$https_uri = $row['GloballyUniqueIdentifier']; 
		$http_uri = str_replace('https://', 'http://', $row['GloballyUniqueIdentifier']);
	}
	
?>

    <?php if($plantGuid):?>
    <!--This is metadata about this plant -->
    <rdf:Description rdf:about="<?php echo $plantGuid ?>">
        <dc:isPartOf rdf:resource="<?php echo $https_uri ?>" />
    </rdf:Description>
    <?php endif ?>

    <!--This is metadata about the accession-->
    <rdf:Description rdf:resource="<?php echo $http_uri ?>">
	
		<!-- Assert and owl:sameAs to link the https and the http identifers -->
		<?php
			if(strpos($row['GloballyUniqueIdentifier'], 'http://') !== false){
				$alternative_uri = str_replace('http://', 'https://', $row['GloballyUniqueIdentifier']);
			}else{
				$alternative_uri = str_replace('https://', 'http://', $row['GloballyUniqueIdentifier']);
			}
		?>
    
        <!-- Assertions made in simple Dublin Core -->
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
        
            $disposition = "";
            if($row['Disposition']){
                if($row['Disposition'] == 'alive'){
                    $disposition = " - Living material is still in cultivation.";
                }else{
                    $disposition = " - No living material remains in cultivation.";
                }
            }
        
        ?>
        
        
        <dc:description>A living collections accession of <?php echo htmlspecialchars(strip_tags($row['ScientificName'])) . $collectorString . $disposition ?></dc:description>
        
        <?php if($row['Collector']){?>
               <dc:creator><?php echo htmlspecialchars($row['Collector']) ?></dc:creator>
        <?php }?>
        
        <?php if($row['EarliestDateCollected']){?>
            <dc:created><?php echo htmlspecialchars($row['EarliestDateCollected']) ?></dc:created>
        <?php }?>
        
        
        <?php if($row['DecimalLongitude'] && $row['DecimalLatitude']){?>
        <geo:Point>
            <geo:lat><?php echo htmlspecialchars($row['DecimalLatitude']) ?></geo:lat>
            <geo:long><?php echo htmlspecialchars($row['DecimalLongitude']) ?></geo:long>
        </geo:Point>
        <?php } ?>
        
        <!-- Assertions based on experimental version of Darwin Core -->
        <dwc:SampleID><?php echo $row['GloballyUniqueIdentifier'] ?></dwc:SampleID>
        <dc:modified><?php echo $row['DateLastModified'] ?></dc:modified>
        <dwc:BasisOfRecord>Specimen</dwc:BasisOfRecord>
        <dwc:InstitutionCode>http://biocol.org/urn:lsid:biocol.org:col:15670</dwc:InstitutionCode>
        <dwc:CollectionCode>E</dwc:CollectionCode>
        <dwc:CatalogNumber><?php echo $row['CatalogNumber'] ?></dwc:CatalogNumber>
        <dwc:ScientificName><?php echo htmlspecialchars(strip_tags($row['ScientificName'])) ?></dwc:ScientificName>
        <dwc:Family><?php echo $row['Family'] ?></dwc:Family>
        <dwc:Genus><?php echo $row['Genus'] ?></dwc:Genus>
        <dwc:SpecificEpithet><?php echo $row['SpecificEpithet'] ?></dwc:SpecificEpithet>
        <dwc:HigherGeography><?php echo htmlspecialchars($row['HigherGeography']) ?></dwc:HigherGeography>

    <?php if($row['Country']){?>
        <dwc:Country><?php echo htmlspecialchars($row['Country']) ?></dwc:Country>
    <?php }?>
    
    <?php if($row['StateProvince']){?>
        <dwc:StateProvince><?php echo htmlspecialchars($row['StateProvince']) ?></dwc:StateProvince>
    <?php }?>
        
    <?php if($row['County']){?>
        <dwc:County><?php echo htmlspecialchars($row['County']) ?></dwc:County>
    <?php }?>
        
    <?php if($row['Locality']){?>
        <dwc:Locality><?php echo htmlspecialchars($row['Locality']) ?></dwc:Locality>
    <?php }?>
        
    <?php if($row['DecimalLongitude'] && $row['DecimalLatitude']){?>
        <dwc:DecimalLongitude><?php echo htmlspecialchars($row['DecimalLongitude']) ?></dwc:DecimalLongitude>
        <dwc:DecimalLatitude><?php echo htmlspecialchars($row['DecimalLatitude']) ?></dwc:DecimalLatitude>
    <?php }?>       
        
    <?php if($row['MinimumElevationInMeters'] && $row['MaximumElevationInMeters']){?>
        <dwc:MinimumElevationInMeters><?php echo htmlspecialchars($row['MinimumElevationInMeters']) ?></dwc:MinimumElevationInMeters>
        <dwc:MaximumElevationInMeters><?php echo htmlspecialchars($row['MaximumElevationInMeters']) ?></dwc:MaximumElevationInMeters>
    <?php }?>
    
    <?php if($row['EarliestDateCollected']){?>
        <dwc:EarliestDateCollected><?php echo htmlspecialchars($row['EarliestDateCollected']) ?></dwc:EarliestDateCollected>
    <?php }?>
    
    <?php if($row['Collector']){?>
        <dwc:Collector><?php echo htmlspecialchars($row['Collector']) ?></dwc:Collector>
    <?php }?>
        
    <?php if($row['Disposition']){?>
        <dwc:Disposition><?php echo htmlspecialchars($row['Disposition']) ?></dwc:Disposition>
    <?php }?>    
        
    </rdf:Description>
    
<?php

}

function hasVersion($guid){
    
    global $mysqli;
    global $livingCatalogueURL;

    // link to the human readable version as another form of the metadata
    $humanURI = str_replace('&', "&amp;", $livingCatalogueURL . substr($guid, 31));
    echo "<dc:hasVersion rdf:resource=\"$humanURI\" />\n";
    
}
?>
    
   

    
    
