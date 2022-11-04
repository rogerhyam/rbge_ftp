<?php
  date_default_timezone_set('UTC');
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  // service directory URL
  //$serviceURL = 'https://data.rbge.org.uk/service';
  $serviceURL = 'http://localhost:9000';

  //CATALOG URL changed by MP 3/11/2022
  $herbariumCatalogueURL = 'https://data.rbge.org.uk/search/herbarium?cfg=fulldetails.cfg&barcode=';
  $livingCatalogueURL = 'https://data.rbge.org.uk/search/livingcollection?cfg=allacc.cfg&acc_num=';

  // all data comes out of SOLR now
  define('SOLR_QUERY_URI', "http://webstorage.rbge.org.uk:8983/solr/bgbase/select");
