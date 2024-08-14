<?php


function write_image_record($out, $record, $fields){

    // no images no go
    if(!isset($record->image_filename_nis) || !isset($record->barcode_s)) return;

    // if it has an image then we add a single row for the IIIF Manifest
    $iiif_row = array();
    $iiif_row["coreId"] = "https://data.rbge.org.uk/herb/{$record->barcode_s}"; // core id
    $iiif_row["type"] = "InteractiveResource"; // type
    $iiif_row["format"] = "application/ld+json"; // format
    $iiif_row["accessURI"] = "https://iiif.rbge.org.uk/herb/iiif/{$record->barcode_s}/manifest"; // identifier
    $iiif_row["associatedSpecimenReference"] = "https://iiif.rbge.org.uk/viewers/mirador/?manifest=https://iiif.rbge.org.uk/herb/iiif/{$record->barcode_s}/manifest"; // reference
    $iiif_row["identifier"] =  $record->barcode_s;
    $iiif_row["description"] = "IIIF Manifest for specimen {$record->barcode_s}"; // description
    $iiif_row["serviceExpectation"] = "IIIF"; // serviceExpectation

    $ordered_row = array();
    foreach($fields as $field){
        if(isset($iiif_row[$field])) $ordered_row[] = $iiif_row[$field];
        else $ordered_row[] = null;
    }
    fputcsv($out, $ordered_row);

    // but we add one row for each of the files - may be more than one
    foreach($record->image_filename_nis as $file_name){

        $image_name = pathinfo($file_name, PATHINFO_FILENAME);
        $imageUri = "https://iiif.rbge.org.uk/herb/iiif/$image_name/full/300,/0/default.jpg";

        $jpeg_row = array();
        $jpeg_row["coreId"] = "https://data.rbge.org.uk/herb/{$record->barcode_s}"; // core id
        $jpeg_row["type"] = "StillImage"; // type
        $jpeg_row["format"] = "image/jpeg"; // format
        //$jpeg_row["accessURI"] = "https://iiif.rbge.org.uk/herb/iiif/{$record->barcode_s}/manifest"; // identifier
        $jpeg_row["accessURI"] = $imageUri;
        $jpeg_row["associatedSpecimenReference"] = $imageUri; // reference
        $jpeg_row["identifier"] =  $image_name;
        $jpeg_row["description"] = "JPEG Image of specimen {$record->barcode_s}"; // description
        $jpeg_row["serviceExpectation"] = "JPEG"; // serviceExpectation

        $ordered_row = array();
        foreach($fields as $field){
            if(isset($jpeg_row[$field])) $ordered_row[] = $jpeg_row[$field];
            else $ordered_row[] = null;
        }
        fputcsv($out, $ordered_row);

        
    }

}

function write_specimen_record($out, $record, $fields){

    $row = array();

    if(!isset($record->barcode_t)) return; // ignore few with missing barcodes

    // <field index="0" term="http://rs.tdwg.org/dwc/terms/occurrenceID" />
    $row['occurrenceID'] = "https://data.rbge.org.uk/herb/" . $record->barcode_t;
   
    // <field index="1" term="http://rs.tdwg.org/dwc/terms/catalogNumber" />
    $row['catalogNumber'] = $record->barcode_t;

    $row['basisOfRecord'] = isset($record->specimen_kind_ni) ? $record->specimen_kind_ni : 'specimen';
    
    // it location_sensitive_t is not set or it is set to LL then it is OK to display location
    if( 
        !(isset($record->cultivated_i) && $record->cultivated_i) // not cultivated
        &&
        (
            !isset($record->location_sensitive_t) // sensitive info not set
            || 
            (isset($record->location_sensitive_t) && $record->location_sensitive_t == 'LL') // of if set set to 'LL'
        )
    ){
        // can display location info
         // <field index="3" term="http://rs.tdwg.org/dwc/terms/informationWithheld" />
        $row['informationWithheld'] = null;
        $row['decimalLongitude'] = isset($record->longitude_decimal_ni) ? $record->longitude_decimal_ni : null;
        $row['decimalLatitude'] = isset($record->latitude_decimal_ni) ? $record->latitude_decimal_ni : null;
    }else{
        // can't display location info
        // <field index="3" term="http://rs.tdwg.org/dwc/terms/informationWithheld" />
        $row['informationWithheld'] = 'Sensitive location data withheld';
        $row['decimalLongitude'] = null;
        $row['decimalLatitude'] = null;
    }

    //$row['scientificName'] = isset($record->current_name_plain_ni) ? $record->current_name_plain_ni : null;
    $row['scientificName'] = isset($record->accepted_current_name_plain_ni) ? $record->accepted_current_name_plain_ni : null;
    
    //$row['family'] = isset($record->family_t) ? $record->family_t : null;
    $row['family'] = isset($record->accepted_family_t) ? $record->accepted_family_t : null;

    //$row['genus'] = isset($record->genus_t) ? $record->genus_t : null;
    $row['genus'] = isset($record->accepted_genus_t) ? $record->accepted_genus_t : null;

    // $row['specificEpithet'] = isset($record->species_t) ? $record->species_t : null;
    $row['specificEpithet'] = isset($record->accepted_species_t) ? $record->accepted_species_t : null;

    $row['higherGeography'] = isset($record->region_name_s) ? $record->region_name_s : null;
    $row['country'] = isset($record->country_t) ? $record->country_t : null;
    $row['countryCode'] = isset($record->country_code_t) ? $record->country_code_t : null;

    $row['stateProvince'] = isset($record->sub_country1_ni) ? $record->sub_country1_ni : null;
    $row['county'] = isset($record->sub_country2_ni) ? $record->sub_country2_ni : null;

    $row['locality'] = isset($record->locality_ni) ? $record->locality_ni : null;
    $row['eventDate'] =  isset($record->collection_date_iso_s) ? $record->collection_date_iso_s : null;
    $row['recordedBy'] =  isset($record->collector_full_s) ? $record->collector_full_s : null;
    
    $row['recordNumber'] =  isset($record->collector_num_s) ? $record->collector_num_s : null;
    $row['CatalogNumberNumeric'] =  isset($record->id_s) ? $record->id_s : null;
    $row['verbatimEventDate'] =  isset($record->collection_date_s) ? $record->collection_date_s : null;
    $row['verbatimElevation'] =  isset($record->altitude_metres_ni) ? $record->altitude_metres_ni  . "m" : null;
    $row['minimumElevationInMeters'] =  isset($record->altitude_metres_ni) ? $record->altitude_metres_ni : null;
    $row['maximumElevationInMeters'] =  isset($record->altitude_metres_ni) ? $record->altitude_metres_ni : null;
    $row['habitat'] =  isset($record->habitat_ni) ? $record->habitat_ni : null;
    $row['modified'] =  isset($record->modified_date_8601_ni) ? $record->modified_date_8601_ni : null;


    // types of types
    if(isset($record->istype_i) &&  $record->istype_i){
        $vals = array();
        if(isset($record->kind_of_type_nis)){
            for($i = 0; $i < count($record->kind_of_type_nis); $i++) {
                $val = $record->kind_of_type_nis[$i];
                if(isset($record->type_of_nis[$i])){
                    $val .= ": " . strip_tags($record->type_of_nis[$i]);
                }

                $vals[] = $val;
            }
            $row['typeStatus'] = implode(' | ', $vals);
        }else{
            $row['typeStatus'] = 'Type';
        }
    }else{
        $row['typeStatus'] = null;
    }

    // preparations - we include any associated materials if they are different
    $preps = array();

    // firstly the prep of the actual specimen
    if(isset($record->specimen_kind_ni)) $preps[] = $record->specimen_kind_ni;

    // associated material preparation types
    if(isset($record->associated_material_kind_nis) &&  count($record->associated_material_kind_nis) > 0){
       $preps = array_merge($preps, $record->associated_material_kind_nis);
    }

    // only one of each kind
    $preps = array_unique($preps);

    // now add it in
    $row['preparations'] = implode('|', $preps);

    if(isset($record->image_filename_nis)){
        $vals = array();
        foreach($record->image_filename_nis as $file_name){
            $image_name = pathinfo($file_name, PATHINFO_FILENAME);
            $imageUri = "https://iiif.rbge.org.uk/herb/iiif/$image_name/full/300,/0/default.jpg";
            $vals[] = $imageUri;
        }
        $row['associatedMedia'] = implode(' | ', $vals);
    }else{
        $row['associatedMedia'] = null;
    }
    
    // make sure the fields are output in the
    // order they are in the config file
    // and none are missing
    $ordered_row = array();
    foreach(array_keys($fields) as $field){
        if(isset($row[$field])) $ordered_row[] = $row[$field];
        else $ordered_row[] = null;
    }
    fputcsv($out, $ordered_row);

} 