<?php
function rdfHeader(){
?>
    <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:xs="http://www.w3.org/2001/XMLSchema"
		xmlns:dwc="http://rs.tdwg.org/dwc/terms/"
		xmlns:dwcc="http://rs.tdwg.org/dwc/curatorial/"
        xmlns:dc="http://purl.org/dc/terms/"
        xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"
		xmlns:owl ="http://www.w3.org/2002/07/owl#"
		xmlns:dwciri="http://rs.tdwg.org/dwc/iri/"
		>

<?php
}

function rdfDocumentMetadata(){

    $currentURL = 'banana';
    
?>
    <!--This is metadata about this metadata document-->
    <rdf:Description
        rdf:about="<?php echo getPageURL() ?>">
        <dc:creator>Simple PHP RDF Script</dc:creator>
        <dc:created><?php echo date(DATE_ATOM); ?></dc:created>   
    </rdf:Description>
    
<?php    
}

function rdfFooter(){
    echo "</rdf:RDF>";
}

function getPageURL(){
    $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    if ($_SERVER["SERVER_PORT"] != "80")
    {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } 
    else 
    {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
    
}


?>