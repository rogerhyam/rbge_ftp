<?php

    require_once('../config.php');
    require_once('rhododendron_names.php');

    $genus = @$_GET['genus'];
    $species = @$_GET['species'];
    
    // do nothing if we don't have the pair
    if(!$genus || !$species){
        header("HTTP/1.0 400 Bad Request");
        echo "You must provide a genus and species parameters";
        exit();
    }
    
    $species = $genus . ' ' . $species;
    
?>
<html>
    <head>
        <title><?php echo $species ?> @ Royal Botanic Garden Edinburgh</title>    
    </head>
    <body>
        <div id="intro">
            <a href="http://www.rbge.org.uk">&lt;&lt;&lt; To the RBGE Home Page</a>
            <h1><i><?php echo $species ?></i> @ Royal Botanic Garden Edinburgh</h1>
            <p>This page summarises some of the resources available at the Royal Botanic Garden Edinburgh for this species.</p>
        </div>
<?php if( in_array($species, $rhododendronNames) ){ ?>
        <div>
            <h2>Descriptive Data</h2>
            <p>
            <a href="../factsheets/Edinburgh_Rhododendron_Monographs.xhtml#<?php echo str_replace(' ', '_', $species) ?>">See description from the Edinburgh Rhododendron Monographs</a>
            </p>
        </div>        
<?php } // end check for in rhododendronNames ?>

        <div id="herbarium">
            <h2>Herbarium Specimens</h2>
<?php
            $sql = "SELECT '2018-06-12' as 'test', s.barcode, s.specimen_num, n.current_name, s.coll_name, s.coll_num, s.country_code FROM bgbase_dump.specimens as s join bgbase_dump.current_names as n on s.specimen_num = n.specimen_num where n.sort_name like '$species%' and length(s.barcode) >0 limit 1000";
            
            $response = $mysqli->query($sql);
            while($row = $response->fetch_assoc()){
                
                $barcode = $row['barcode'];
                $currentName = $row['current_name'];
                $url = 'http://data.rbge.org.uk/herb/' . $row['barcode'];
                
               echo "<p><a href=\"$url\">$barcode</a> $currentName";
               if($row['coll_name']){
                   echo " - ". $row['coll_name'];
               }
               if($row['coll_num']){
                  echo " #" . $row['coll_num'];
               }
               if($row['country_code']){
                  echo " - " . $row['country_code'];
               }
               
               echo " </p>";
            }        

?>
            
            
        </div>
        <div id="living">
            <h2>Living Collection Accessions</h2>
<?php

            $sql = "SELECT a.acc_num, n.name_html FROM bgbase_dump.accessions as a join bgbase_dump.names as n on a.name_num = n.name_num
             join plants as p on p.acc_num = a.acc_num 
            where
             n.sort_name like '$species%'";
    
            $response = $mysqli->query($sql);
            while($row = $response->fetch_assoc()){
        
                $accNum = $row['acc_num'];
                $currentName = $row['name_html'];
                $url = 'http://data.rbge.org.uk/living/' . $accNum;
        
               echo "<p><a href=\"$url\">$accNum</a> $currentName";
               /*
               if($row['coll_name']){
                   echo " - ". $row['coll_name'];
               }
               if($row['coll_num']){
                  echo " #" . $row['coll_num'];
               }
               if($row['country_code']){
                  echo " - " . $row['country_code'];
               }
       */
               echo " </p>";
            }


?>
            
        </div>
    </body>
</html>