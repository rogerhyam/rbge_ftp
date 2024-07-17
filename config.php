<?php
  date_default_timezone_set('UTC');
  error_reporting(E_ALL);
  ini_set('display_errors', 1);

  // service directory URL
  $serviceURL = 'https://data.rbge.org.uk/service';
  //$serviceURL = 'http://localhost:9000';

  //CATALOG URL changed by MP 3/11/2022
  $herbariumCatalogueURL = 'https://data.rbge.org.uk/search/herbarium?cfg=fulldetails.cfg&barcode=';
  $livingCatalogueURL = 'https://data.rbge.org.uk/search/livingcollection?cfg=allacc.cfg&acc_num=';

  // all data comes out of SOLR now
  define('SOLR_QUERY_URI', "http://webstorage.rbge.org.uk:8983/solr/bgbase/select");

  $dwc_dynamic_fields = array(

    "occurrenceID" => "http://rs.tdwg.org/dwc/terms/occurrenceID",
    "catalogNumber" => "http://rs.tdwg.org/dwc/terms/catalogNumber",
    "informationWithheld" => "http://rs.tdwg.org/dwc/terms/informationWithheld",
    "decimalLongitude" => "http://rs.tdwg.org/dwc/terms/decimalLongitude",
    "decimalLatitude" => "http://rs.tdwg.org/dwc/terms/decimalLatitude",
    "scientificName" => "http://rs.tdwg.org/dwc/terms/scientificName",
    "family" => "http://rs.tdwg.org/dwc/terms/family",
    "genus" => "http://rs.tdwg.org/dwc/terms/genus",
    "specificEpithet" => "http://rs.tdwg.org/dwc/terms/specificEpithet",
    "higherGeography" => "http://rs.tdwg.org/dwc/terms/higherGeography",
    "country" => "http://rs.tdwg.org/dwc/terms/country",
    "countryCode" => "http://rs.tdwg.org/dwc/terms/countryCode",
    "county" => "http://rs.tdwg.org/dwc/terms/county",
    "stateProvince" => "http://rs.tdwg.org/dwc/terms/stateProvince",
    "locality" => "http://rs.tdwg.org/dwc/terms/locality",
    "eventDate" => "http://rs.tdwg.org/dwc/terms/eventDate",
    "recordedBy" => "http://rs.tdwg.org/dwc/terms/recordedBy",
    "recordNumber" => "http://rs.tdwg.org/dwc/terms/recordNumber",
    "CatalogNumberNumeric" => "http://rs.tdwg.org/dwc/curatorial/CatalogNumberNumeric",
    "verbatimEventDate" => "http://rs.tdwg.org/dwc/terms/verbatimEventDate",
    "verbatimElevation" => "http://rs.tdwg.org/dwc/terms/verbatimElevation",
    "minimumElevationInMeters" => "http://rs.tdwg.org/dwc/terms/minimumElevationInMeters",
    "maximumElevationInMeters" => "http://rs.tdwg.org/dwc/terms/maximumElevationInMeters",
    "typeStatus" => "http://rs.tdwg.org/dwc/terms/typeStatus",
    "preparations" => "http://rs.tdwg.org/dwc/terms/preparations",
    "associatedMedia" => "http://rs.tdwg.org/dwc/terms/associatedMedia",
    "habitat" => "http://rs.tdwg.org/dwc/terms/habitat"

  );

  $image_fields = array(
    "coreId",
    "type",
    "format",
    "accessURI",
    "associatedSpecimenReference",
    "identifier",
    "description",
    "serviceExpectation"
);