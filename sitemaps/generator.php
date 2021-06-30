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
    
    $fp = gzopen("data/$tableName.xml.gz", 'w');

    if(!$fp){
        exit("Couldn't open file:data/$tableName.xml.gz ");
    }
    
    // write in the xml header stuff
    $header = file_get_contents('header.xml');
    $header = str_replace('{CREATED_DATE}', date('c'), $header);
    gzwrite($fp, $header);
    
    $sql = "SELECT * FROM $tableName";
    $response = $mysqli->query($sql, MYSQLI_USE_RESULT);
    while($row = $response->fetch_array()){    
             
        $guid = $row['GloballyUniqueIdentifier'];
        $title = !empty($row['Collector']) && !empty($row['CollectorNumber']) ? $row['Collector'] . "; " . $row['CollectorNumber'] . " - " : "";
        $title .= strip_tags($row['ScientificName']);
        $title = htmlspecialchars($title);
        $country = $row['Country'];
           
        gzwrite($fp, "\t\t\t<rdf:li>\n");
        gzwrite($fp, "\t\t\t\t<rdf:Description rdf:about=\"$guid\">\n");
        gzwrite($fp, "\t\t\t\t\t<dc:title>$title</dc:title>\n");
        
        // put the country in if we have one
        if(!empty($row['Family'])){
            gzwrite($fp, "\t\t\t\t\t<dc:subject>". $row['Family'] ."</dc:subject>\n");
        }
        
        // put the country in if we have one
        if(!empty($row['Country'])){
            gzwrite($fp, "\t\t\t\t\t<dc:spatial>". $row['Country'] ."</dc:spatial>\n");
        }
        
        gzwrite($fp, "\t\t\t\t</rdf:Description>\n");
        gzwrite($fp, "\t\t\t</rdf:li>\n");
    }

    // write in the xml footer stuff
    gzwrite($fp, file_get_contents('footer.xml'));
    gzclose($fp);
    
    
    // update the sitemap.xml file from the template 
    $fp = fopen('sitemap.xml', 'w');
    $sitemap = file_get_contents('sitemap_template.xml');
    $sitemap = str_replace('{CREATED_DATE}', date('c'), $sitemap);
    fwrite($fp, $sitemap);
    fclose($fp);
    
    echo 'Archive created for ' . $tableName . " \n";
}

?>