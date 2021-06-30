<?php

/*
    CHANGE LOG
    2012-07-31: added checks for locking tables
*/

    require_once('config.php');

    /*
        This file handles redirecting of calls that come in to
        
        http://data.rbge.org.uk/herb
        http://data.rbge.org.uk/living
        http://data.rbge.org.uk/taxa
    
    */
    
    // we need to check that the table exists and if they don't send a wait for me
    $path = $_GET['path'];
    $guid = 'http://data.rbge.org.uk/' . $path;
    
    // special case for the sitemap.xml file
    if($path == 'sitemap.xml'){
        header ("Content-Type:text/xml");
        echo file_get_contents('sitemaps/sitemap.xml');
        exit;
    }
    
    // special case for no 
    if($path == 'living/' || $path == 'living'){
        header("Location: https://elmer.rbge.org.uk/bgbase/livcol/bgbaselivcol.php",TRUE,303);
        exit();
    }
    
    if($path == 'herb/' || $path == 'herb'){
        header("Location: https://elmer.rbge.org.uk/bgbase/vherb/bgbasevherb.php",TRUE,303);
        exit();
    }
    
    // the namespace is the first part of the path information  and objectID the second   
    $namespace = substr($path, 0, strpos($path, '/'));
    $objectID = substr($path, strpos($path, '/')+1);
 
    // if it is a taxon then simply redirect to taxa/index.php?genus=XXX&species=YYYY
    if($namespace == 'taxa'){
    
         $pathParts = explode('/', $path);
         if(count($pathParts) < 3){
             header("HTTP/1.0 400 Bad Request");
             header("Status: 400 Bad Request");
             echo 'You must provide a Genus/species binomial';
             exit(0);
         }
         $genus = $pathParts[1];
         $species = $pathParts[2];
    
         header("Location: $serviceURL/taxa/index.php?genus=$genus&species=$species",TRUE,303);
         exit();
    
    }
    
    // if it is a herb then
    if($namespace == 'herb'){
        
        // 0) check table exists and if not ask them to come back later
        failOnlockingTable('darwin_core_lock');     
        
        // 1) check if the barcode exists and if it doesn't send a 404
        //$response = $mysqli->query("SELECT count(*) as n FROM bgbase_dump.darwin_core WHERE GloballyUniqueIdentifier = '$guid'");
        
        $response = $mysqli->query("SELECT SPECIMEN_NUM as id FROM bgbase_dump.specimens WHERE BARCODE = '$objectID'");
        
        if($response->num_rows < 1){
            header("HTTP/1.0 404 Not Found");
            header("Status: 404 Not Found");
            echo "Status: 404 Not Found";
            exit(0);
        }

        // get the specimen number used by the catalogue url
        $row = $response->fetch_assoc();
        
        // 2) see if they are human and if they are redirect to herbarium catalogue
        // 3) if they are machine then redirect to rdf/herb.php?guid=***
        if(humanIsCalling()){
            
            $url = $herbariumCatalogueURL . $row['id'];
            header("Location: $url",TRUE,303);
            exit();
            
        }else{
			header("Access-Control-Allow-Origin: *");
            header("Location: $serviceURL/rdf/herb.php?guid=$guid",TRUE,303);
            exit();
        }
        
    }
    
    // if it is an accession (living is a legacy support) then
    if($namespace == 'living'){
        
        // 0) check table exists and if not ask them to come back later
        failOnlockingTable('darwin_core_living_lock'); 
        
        // the accession number may include letters because this is a plant not just an accession	
        if(strlen($objectID) > 8){
            $isPlant = true;
            $accessionNumber = substr($objectID, 0,8);
            $qualifier = substr($objectID, 8);            
            $response = $mysqli->query("SELECT count(*) AS n FROM plants WHERE ACC_NUM = '$accessionNumber' AND ACC_NUM_QUAL = '$qualifier'");
        }else{
            $isPlant = false;
            $accessionNumber = $objectID;
            $response = $mysqli->query("SELECT count(*) AS n FROM accessions WHERE ACC_NUM = '$accessionNumber'");
        }
        
        // 1) check if the accession number exists and if it doesn't send a 404
        //$response = $mysqli->query("SELECT count(*) as n FROM bgbase_dump.darwin_core_living WHERE GloballyUniqueIdentifier = '$guid'");

        $row = $response->fetch_assoc();
        if($row['n'] < 1){
            header("HTTP/1.0 404 Not Found");
            header("Status: 404 Not Found");
            echo("Status: 404 Not Found");
            exit(0);
        }
        
        // 2) see if they are human and if they are redirect to herbarium catalogue
        // 3) if they are machine then redirect to rdf/living.php?guid=***
        if(humanIsCalling()){
                        
            $url = $livingCatalogueURL . $accessionNumber;
            
            if($isPlant){
                $url = $url . "&acc_num_qual=$qualifier";
            }
            
            header("Location: $url",TRUE,303);
            exit();
            
        }else{
			header("Access-Control-Allow-Origin: *");
            header("Location: $serviceURL/rdf/living.php?guid=$guid",TRUE,303);
            exit();
        }
        
    }
    
    // if it is a plant then
    if($namespace == 'living' || $namespace == 'accession'){
        
        // 0) check table exists and if not ask them to come back later
        failOnlockingTable('darwin_core_living_lock'); 
        
        // 1) check if the accession number exists and if it doesn't send a 404
        $response = $mysqli->query("SELECT count(*) as n FROM bgbase_dump.darwin_core_living WHERE GloballyUniqueIdentifier = '$guid'");
        $row = $response->fetch_assoc();
        if($row['n'] < 1){
            header("HTTP/1.0 404 Not Found");
            header("Status: 404 Not Found");
            exit(0);
        }
        
        // 2) see if they are human and if they are redirect to herbarium catalogue
        // 3) if they are machine then redirect to rdf/living.php?guid=***
        if(humanIsCalling()){
                        
            $url = $livingCatalogueURL . $objectID;
            header("Access-Control-Allow-Origin: *");
            header("Location: $url",TRUE,303);
            exit();
            
        }else{
			header("Access-Control-Allow-Origin: *");
            header("Location: $serviceURL/rdf/living.php?guid=$guid",TRUE,303);
            exit();
        }
        
    }
    
    
    // if we have got to here then we have failed!
    
    echo "Failed to parse GUID";
    exit;
    

function humanIsCalling(){
    
    // check out if they are asking for rdf in the accept 
    // header
    foreach (getallheaders() as $name => $value) {

        //echo "$name => $value \n";

        if ($name == 'Accept'){
            
            //application/xhtml+xml,text/html
          
            $indexOfHtml = stripos($value, 'html');
            $indexOfRdf = stripos($value, 'rdf+xml');
            
            // echo "banana: $indexOfHtml :   $indexOfRdf  \n ";
            //exit(0);
            
            // they are asking for html but not rdf so they are human
            if ($indexOfHtml !== false && $indexOfRdf === false){
                return true;
            }
            
            // they are asking for rdf but not html so they are machine
            if ($indexOfHtml === false && $indexOfRdf !== false){
                return false;
            }
            
            // They are asking for both RDF and HTML so check the order of
            // their preference
            if($indexOfRdf !== false && $indexOfHtml !== false){
                if($indexOfRdf < $indexOfHtml){
                    return false;
                }else{
                    return true;
                }
            }
            
        }
        
    }

    
    // otherwise return human if they are using a browser and non-human if they are not.    
    return preg_match('/^((Mozilla)|(Opera))/', $_SERVER['HTTP_USER_AGENT']) ? true:false;

}


function failOnlockingTable($tablename) {

    global $mysqli;

    $response = $mysqli->query("
        SELECT COUNT(*) AS n 
        FROM information_schema.tables 
        WHERE table_schema = 'bgbase_dump' 
        AND table_name = '$tablename'
    ");
    
    $row = $response->fetch_assoc();
    if($row['n'] > 0){
        header("HTTP/1.0 503 Service Unavailable");
        header("Status: 503 Service Unavailable");
        echo "<h1>Service Temporarily Unavailable</h1>";
        echo "We are down for maintenance but will be back shortly. Please check back later.";
        exit(0);
    }

}

?>