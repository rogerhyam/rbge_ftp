<?php

/*
    CHANGE LOG
    2012-07-31: added checks for locking tables
*/
    require_once('config.php');
    require_once('SolrConnection.php');

    /*
        This file handles redirecting of calls that come in to
        
        http://data.rbge.org.uk/herb
        http://data.rbge.org.uk/living
        http://data.rbge.org.uk/taxa
    

        apache has these rewrite rules

        RewriteEngine On
        RewriteRule "^/herb$" https://data.rbge.org.uk/search/herbarium [L]
        RewriteRule "^/living$" https://data.rbge.org.uk/search/livingcollection [L]
        RewriteCond "%{REQUEST_URI}" ^/(?:herb|taxa|living)/.*$
        RewriteRule ^/(.+)$  /service/index.php?path=$1 [QSA,PT]
    
    
     */
    
    // we need to check that the table exists and if they don't send a wait for me
    $path = $_GET['path'];
    $guid = 'http://data.rbge.org.uk/' . $path;
    $solr = new SolrConnection();
    
    
    // the namespace is the first part of the path information  and objectID the second   
    $namespace = substr($path, 0, strpos($path, '/'));
    $objectID = substr($path, strpos($path, '/')+1);
    
    // if it is a herb then
    if($namespace == 'herb'){    
        
        // check it is in SOLR
        $result = $solr->query_object((object)array(
    		"query" => "barcode_s:$objectID"
        ));

        if($result->response->numFound == 0){
            header("HTTP/1.0 404 Not Found");
            header("Status: 404 Not Found");
            echo "Status: 404 Not Found";
            exit(0);
        }

        // 2) see if they are human and if they are redirect to herbarium catalogue
        // 3) if they are machine then redirect to rdf/herb.php?guid=***
        if(humanIsCalling()){
            
            //URL structure changed by MP 3/11/2022
            //$url = $herbariumCatalogueURL . $row['id'];
            $url = $herbariumCatalogueURL . $objectID;
            header("Location: $url",TRUE,303);
            exit();
            
        }else{
			header("Access-Control-Allow-Origin: *");
            header("Location: $serviceURL/rdf/herb.php?barcode=$objectID",TRUE,303);
            exit();
        }
        
    }
    
    // if it is an accession (living is a legacy support) then
    if($namespace == 'living'){
        
        // the accession number may include letters because this is a plant not just an accession	
        if(strlen($objectID) > 8){
            $accessionNumber = substr($objectID, 0,8);
            $qualifier = substr($objectID, 8);            
        }else{
            $isPlant = false;
            $accessionNumber = $objectID;
        }

        // check it is in SOLR
        $result = $solr->query_object((object)array(
    		"query" => "id:\"accession:$objectID\""
        ));

        if($result->response->numFound == 0){
            header("HTTP/1.0 404 Not Found");
            header("Status: 404 Not Found");
            echo "Status: 404 Not Found";
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


?>
