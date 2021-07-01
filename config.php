<?php
  date_default_timezone_set('UTC');
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  // service directory URL
  $serviceURL = 'https://data.rbge.org.uk/service';
  // $serviceURL = 'http://localhost/~rogerhyam/rbge_service/';
  //$herbariumCatalogueURL = 'http://elmer.rbge.org.uk/bgbase/vherb/bgbasevherb.php?cfg=bgbase/vherb/bgbasevherb.cfg&specimens_barcode=';
  //$herbariumCatalogueURL = 'http://elmer.rbge.org.uk/bgbase/vherb/bgbasevherb.php?cfg=bgbase/vherb/fulldetails.cfg&specimens_specimen__num=';
  $herbariumCatalogueURL = 'https://data.rbge.org.uk/search/herbarium?cfg=fulldetails.cfg&specimen_num=';
 
  //$livingCatalogueURL = 'http://elmer.rbge.org.uk/bgbase/livcol/bgbaselivcol.php?cfg=bgbase%2Flivcol%2Fbgbaseallacc.cfg&acc__num=';
  $livingCatalogueURL = 'https://data.rbge.org.uk/search/livingcollection?cfg=allacc.cfg&acc_num=';
  
  include('/var/www/html/roger_secret.php');

  // create and initialise the database connection
  $mysqli = new mysqli($db_host, $db_user, $db_password, $db_database);    

  // connect to the database
  if ($mysqli->connect_error) {
      $returnObject['error'] = $mysqli->connect_error;
      sendResults($returnObject);
  }

  if (!$mysqli->set_charset("utf8")) {
      printf("Error loading character set utf8: %s\n", $mysqli->error);
  }
  