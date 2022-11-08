<?php
    
    require_once('../config.php');
    require_once('common.php');
    require_once('../SolrConnection.php');
    
    /*
        Generates RDF for a living specimen from data in the darwin_core table
    */
    
    $accession = @$_GET['accession'];
    $solr = new SolrConnection();
    
    // do nothing if we don't a guid
    if(!$accession){
       header("HTTP/1.0 400 Bad Request");
       echo "You must provide an accession number parameters";
       exit();
    }

    $guid = 'http://data.rbge.org.uk/living/' . $accession;

    // try and get the solr record for it
    $result = $solr->query_object((object)array(
        "query" => "id_s:$accession"
    ));

    // fail to find a solr record for it
    if($result->response->numFound == 0){
        header("HTTP/1.0 404 Not Found");
        echo "<h1>Not Found</h1>";
        echo "There is no record with accession number $accession";
        exit();
    }

    // thunderbirds are go we have a record
    $record = $result->response->docs[0];

    // what about names for the record?
    $result = $solr->query_object((object)array(
        "query" => "acc_num_ss:$accession"
    ));
    if($result->response->numFound > 0){
        $names = $result->response->docs;
    }

	header("Access-Control-Allow-Origin: *");
    header("Content-type: application/rdf+xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    rdfHeader();
    rdfDocumentMetadata();
    rdfData($accession, $record);
    rdfFooter();
    
function rdfData($accession, $record){
	
	// no matter what we are asked for we use the https uri as the main identifier
    $http_uri = "http://data.rbge.org.uk/herb/$accession";
	$https_uri = "https://data.rbge.org.uk/herb/$accession";

?>

    <!--This is metadata about the accession-->
    <rdf:Description rdf:resource="<?php echo $http_uri ?>">
        <!-- Assertions made in simple Dublin Core -->
        <?php
            $collectorString = isset($record->collector_ni) ? $record->collector_ni . ": " : "";
        ?>
        <dc:title><?php echo $accession . ': ' . $collectorString . htmlspecialchars(strip_tags($record->current_name_ni)) ?></dc:title>
        <dc:description>A living collections accession of <?php echo htmlspecialchars(strip_tags($record->current_name_ni)) . " This is a temporary data response till we update the data pipeline from IrisBG." ?></dc:description>
    </rdf:Description>
    
<?php

}

?>
    
   

    
    
