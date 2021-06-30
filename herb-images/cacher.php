#!/usr/bin/php
<?php
    // This will call all the images in the image-archive so they are chached 
    
    require_once('../config.php');
    set_time_limit(0);
    
    // get the starting index so that we can stop and start the
    // script
    $counterFile = "tmp/cacher_start_point.txt";
    if(file_exists($counterFile)){
        $start = (int)file_get_contents($counterFile);
    }else{
        $start = 0;
    }
    
    $sql ="SELECT concat('http://data.rbge.org.uk/images/', id, '/700') as url FROM image_archive.derived_images where image_type = 'JPG' order by ID LIMIT $start, 10000000";
    
    $response = $mysqli->query($sql, MYSQLI_USE_RESULT);
    
    $count = $start;
    while($row = $response->fetch_assoc()){ 
       
       $p = fopen($row['url'], 'r');
       if($p){
           fclose($p);
       }else{
           echo "Problems with: ";
       }
       
       $count++;
       $c = fopen($counterFile, 'w');
       fwrite($c, $count);
       fclose($c);
       
       echo $count . "\t" . $row['url'] . "\n";
       
       //sleep(1);
       
    }
    
    // if we get to the end then delete the counter file so we can start again
    // next time
    unlink($counterFile);
    

?>