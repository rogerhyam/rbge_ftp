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
    
        <?php if(function_exists('hasVersion')) echo hasVersion( $_GET['guid'] )?>
    
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

$collectors = array(
	"Poulsen, A.D." => array("http://www.wikidata.org/entity/Q26712452","https://orcid.org/0000-0002-7651-6439"),
	"Poulsen, Axel Dalberg" => array("http://www.wikidata.org/entity/Q26712452","https://orcid.org/0000-0002-7651-6439"),
	"Miller, A G." => array("http://www.wikidata.org/entity/Q11679869"),
	"Miller, A. G." => array("http://www.wikidata.org/entity/Q11679869"),
	"Miller, A.G." => array("http://www.wikidata.org/entity/Q11679869"),
	"Miller, Anthony G." => array("http://www.wikidata.org/entity/Q11679869"),
	"Miller, Anthony George" => array("http://www.wikidata.org/entity/Q11679869"),
	"Adqa" => array("http://www.wikidata.org/entity/Q16143738"),
	"Agnew, A.D.Q." => array("http://www.wikidata.org/entity/Q16143738"),
	"Agnew, Andrew David Quentin" => array("http://www.wikidata.org/entity/Q16143738"),
	"A. Henry" => array("http://www.wikidata.org/entity/Q2585626"),
	"Forrest, George" => array("http://www.wikidata.org/entity/Q2585626"),
	"Henry, A." => array("http://www.wikidata.org/entity/Q2585626"),
	"Henry, A. (Dr.)" => array("http://www.wikidata.org/entity/Q2585626"),
	"Henry, Augustine" => array("http://www.wikidata.org/entity/Q2585626"),
	"Henry, Dr. Aug." => array("http://www.wikidata.org/entity/Q2585626"),
	"Fryday, A." => array("http://www.wikidata.org/entity/Q21339181"),
	"Fryday, A.M." => array("http://www.wikidata.org/entity/Q21339181"),
	"Fryday, Alan Michael" => array("http://www.wikidata.org/entity/Q21339181"),
	"Argent"  => array("http://www.wikidata.org/entity/Q5884115"),
	"Argent, G." => array("http://www.wikidata.org/entity/Q5884115"),
	"Argent, G.C.G." => array("http://www.wikidata.org/entity/Q5884115"),
	"Argent, George C." => array("http://www.wikidata.org/entity/Q5884115"),
	"Argent, George C.G." => array("http://www.wikidata.org/entity/Q5884115"),
	"Dr. G. Argent" => array("http://www.wikidata.org/entity/Q5884115"),
	"Watt, G." => array("http://www.wikidata.org/entity/Q3101998"),
	"Watt, George" => array("http://www.wikidata.org/entity/Q3101998"),
	"Balls"  => array("http://www.wikidata.org/entity/Q21505603"),
	"Balls, E.K." => array("http://www.wikidata.org/entity/Q21505603"),
	"Balls, Edward K." => array("http://www.wikidata.org/entity/Q21505603"),
	"Balls, Edward Kent" => array("http://www.wikidata.org/entity/Q21505603"),
	"Davis, Peter Hadland" => array("http://www.wikidata.org/entity/Q21505603"),
	"E.K. Balls" => array("http://www.wikidata.org/entity/Q21505603"),
	"Ball, J" => array("http://www.wikidata.org/entity/Q957519"),
	"Ball, J." => array("http://www.wikidata.org/entity/Q957519"),
	"Ball, John" => array("http://www.wikidata.org/entity/Q957519"),
	"Ballantyne, G.H." => array("http://www.wikidata.org/entity/Q957519"),
	"J. Ball" => array("http://www.wikidata.org/entity/Q957519"),
	"Willkomm."  => array("http://www.wikidata.org/entity/Q957519"),
	"Birks, H. John B." => array("http://www.wikidata.org/entity/Q2252186"),
	"Birks, H.J." => array("http://www.wikidata.org/entity/Q2252186"),
	"Birks, H.J.B." => array("http://www.wikidata.org/entity/Q2252186"),
	"Birks, H.John" => array("http://www.wikidata.org/entity/Q2252186"),
	"Birks, H.John & Lees, Hilary, H." => array("http://www.wikidata.org/entity/Q2252186"),
	"B.L.Burtt & Saeed A. Khan" => array("http://www.wikidata.org/entity/Q2897760"),
	"Burtt, B.L." => array("http://www.wikidata.org/entity/Q2897760"),
	"Burtt, Brian L." => array("http://www.wikidata.org/entity/Q2897760"),
	"J.B.Gillett, B.L.Burtt & R.M.Osborn" => array("http://www.wikidata.org/entity/Q2897760"),
	"Lamond, Jennifer, M." => array("http://www.wikidata.org/entity/Q2897760"),
	"Bornmäller, J." => array("http://www.wikidata.org/entity/Q68637"),
	"Bornmüller, J." => array("http://www.wikidata.org/entity/Q68637"),
	"Bornmüller, Joseph Friedrich Nicolaus" => array("http://www.wikidata.org/entity/Q68637"),
	"Brown, R." => array("http://www.wikidata.org/entity/Q155764"),
	"Brown, Robert" => array("http://www.wikidata.org/entity/Q155764"),
	"Chamberlain, D." => array("http://www.wikidata.org/entity/Q6421167"),
	"Chamberlain, D.F." => array("http://www.wikidata.org/entity/Q6421167"),
	"Chamberlain, D.F. & E.M. Kungu" => array("http://www.wikidata.org/entity/Q6421167"),
	"Chamberlain, D.F. & G.P. Rothero" => array("http://www.wikidata.org/entity/Q6421167"),
	"Chamberlain, D.F. & Kungu, E.M." => array("http://www.wikidata.org/entity/Q6421167"),
	"Chamberlain, D.F. & M.K. and Kungu, E.M." => array("http://www.wikidata.org/entity/Q6421167"),
	"Chamberlain, D.F. et al. (LWIC Excursion)" => array("http://www.wikidata.org/entity/Q6421167"),
	"Chamberlain, D.F., D. Schill & E. Kungu" => array("http://www.wikidata.org/entity/Q6421167"),
	"Chamberlain, D.F., M.K. & Kungu, E.M." => array("http://www.wikidata.org/entity/Q6421167"),
	"Chamberlain, David F." => array("http://www.wikidata.org/entity/Q6421167"),
	"D. & M. Chamberlain and E. Kungu" => array("http://www.wikidata.org/entity/Q6421167"),
	"D. & M. Chamberlain and E.M. Kungu" => array("http://www.wikidata.org/entity/Q6421167"),
	"D. Chamberlain, G. Rothero et al." => array("http://www.wikidata.org/entity/Q6421167"),
	"D. Chamberlain, G. Rothero, et al." => array("http://www.wikidata.org/entity/Q6421167"),
	"D.F. Chamberlain & E. Kungu" => array("http://www.wikidata.org/entity/Q6421167"),
	"D.F. Chamberlain & E.M. Kungu" => array("http://www.wikidata.org/entity/Q6421167"),
	"D.F. Chamberlain et al." => array("http://www.wikidata.org/entity/Q6421167"),
	"D.F. Chamberlain, E. Kungu, D. Schill et al." => array("http://www.wikidata.org/entity/Q6421167"),
	"E. Kungu & D.F. Chamberlain" => array("http://www.wikidata.org/entity/Q6421167"),
	"G. Rothero, D. Chamberlain et Al." => array("http://www.wikidata.org/entity/Q6421167"),
	"G. Rothero, et al." => array("http://www.wikidata.org/entity/Q6421167"),
	"Collenette, I.S." => array("http://www.wikidata.org/entity/Q5920305"),
	"Collenette, Iris Sheila (Mrs)." => array("http://www.wikidata.org/entity/Q5920305"),
	"Collenette, Sheila" => array("http://www.wikidata.org/entity/Q5920305"),
	"Comber, H.F." => array("http://www.wikidata.org/entity/Q1547403"),
	"Comber, Harold F." => array("http://www.wikidata.org/entity/Q1547403"),
	"B.J. Coppins" => array("http://www.wikidata.org/entity/Q21338943"),
	"Coppins, B.J." => array("http://www.wikidata.org/entity/Q21338943"),
	"Coppins, Brian J." => array("http://www.wikidata.org/entity/Q21338943"),
	"Bridges, Thomas Charles" => array("http://www.wikidata.org/entity/Q718866"),
	"Cuming, H." => array("http://www.wikidata.org/entity/Q718866"),
	"Cuming, Hugh" => array("http://www.wikidata.org/entity/Q718866"),
	"Matthews, A" => array("http://www.wikidata.org/entity/Q718866"),
	"Davis" => array("http://www.wikidata.org/entity/Q1313483"),
	"Davis, P." => array("http://www.wikidata.org/entity/Q1313483"),
	"Davis, P.D." => array("http://www.wikidata.org/entity/Q1313483"),
	"Davis, P.H." => array("http://www.wikidata.org/entity/Q1313483"),
	"Davis, Peter H." => array("http://www.wikidata.org/entity/Q1313483"),
	"Davis, Peter Hadland" => array("http://www.wikidata.org/entity/Q1313483"),
	"Davis, Peter.H." => array("http://www.wikidata.org/entity/Q1313483"),
	"Gathorne-Hardy, E." => array("http://www.wikidata.org/entity/Q1313483"),
	"Irvine, D.E.G." => array("http://www.wikidata.org/entity/Q21516782"),
	"Dobremez, J.F." => array("http://www.wikidata.org/entity/Q55620731"),
	"Maire, E.E." => array("http://www.wikidata.org/entity/Q24203363"),
	"Maire, Edouard-Ernest" => array("http://www.wikidata.org/entity/Q24203363"),
	"Corner, E.H." => array("http://www.wikidata.org/entity/Q4233315"),
	"Corner, E.J.H." => array("http://www.wikidata.org/entity/Q4233315"),
	"Corner, Edred John Henry" => array("http://www.wikidata.org/entity/Q4233315"),
	"C.R. Fraser-Jenkins" => array("http://www.wikidata.org/entity/Q4492613"),
	"Fraser-Jenkins, Christopher Roy" => array("http://www.wikidata.org/entity/Q4492613"),
	"Forrest, G." => array("http://www.wikidata.org/entity/Q204566"),
	"Forrest, George" => array("http://www.wikidata.org/entity/Q204566"),
	"Goodwin, Zoë A." => array("https://orcid.org/0000-0003-2926-1645"),
	"Goodwin, Zoë Africa" => array("https://orcid.org/0000-0003-2926-1645"),
	"Harris, D.J." => array("http://www.wikidata.org/entity/Q43583674"),
	"Harris, David J." => array("http://www.wikidata.org/entity/Q43583674"),
	"Handel - Mazetti" => array("http://www.wikidata.org/entity/Q134118"),
	"Handel-Mazzetti"  => array("http://www.wikidata.org/entity/Q134118"),
	"Handel-Mazzetti, H." => array("http://www.wikidata.org/entity/Q134118"),
	"Handel-Mazzetti, Heinrich" => array("http://www.wikidata.org/entity/Q134118"),
	"Hooker, J.D." => array("http://www.wikidata.org/entity/Q19046212"),
	"Hooker, Joseph Dalton" => array("http://www.wikidata.org/entity/Q19046212"),
	"Craven, L.A." => array("http://www.wikidata.org/entity/Q5933310"),
	"J.H. Lace" => array("http://www.wikidata.org/entity/Q5933310"),
	"Lace, J.H." => array("http://www.wikidata.org/entity/Q5933310"),
	"Lace, John H." => array("http://www.wikidata.org/entity/Q5933310"),
	"Lace, John Henry" => array("http://www.wikidata.org/entity/Q5933310"),
	"Watt, George" => array("http://www.wikidata.org/entity/Q5933310"),
	"King, R." => array("http://www.wikidata.org/entity/Q321094"),
	"Kingdon Ward, F" => array("http://www.wikidata.org/entity/Q321094"),
	"Kingdon Ward, F." => array("http://www.wikidata.org/entity/Q321094"),
	"Kingdon Ward, J." => array("http://www.wikidata.org/entity/Q321094"),
	"Kingdon-Ward Frank" => array("http://www.wikidata.org/entity/Q321094"),
	"Kingdon-Ward, F." => array("http://www.wikidata.org/entity/Q321094"),
	"Kingdon-Ward, Francis" => array("http://www.wikidata.org/entity/Q321094"),
	"Kingdon-Ward, Frank" => array("http://www.wikidata.org/entity/Q321094"),
	"Jennifer Lamond" => array("http://www.wikidata.org/entity/Q21446934"),
	"Lamond, J." => array("http://www.wikidata.org/entity/Q21446934"),
	"Lamond, Jennifer" => array("http://www.wikidata.org/entity/Q21446934"),
	"Lamond, Jennifer, M." => array("http://www.wikidata.org/entity/Q21446934"),
	"Schimper, Georg Heinrich Wilhelm" => array("http://www.wikidata.org/entity/Q21446934"),
	"D.G. Long" => array("http://www.wikidata.org/entity/Q5800006","https://orcid.org/0000-0003-0816-0124"),
	"Long, D.G." => array("http://www.wikidata.org/entity/Q5800006","https://orcid.org/0000-0003-0816-0124"),
	"Long, David G" => array("http://www.wikidata.org/entity/Q5800006","https://orcid.org/0000-0003-0816-0124"),
	"Long, David G." => array("http://www.wikidata.org/entity/Q5800006","https://orcid.org/0000-0003-0816-0124"),
	"Middleton, D." => array("http://www.wikidata.org/entity/Q8354130"),
	"Middleton, David J." => array("http://www.wikidata.org/entity/Q8354130"),
	"Richardson, M. J." => array("http://www.wikidata.org/entity/Q21607187"),
	"Richardson, M.J." => array("http://www.wikidata.org/entity/Q21607187"),
	"Richardson, Michael J." => array("http://www.wikidata.org/entity/Q21607187"),
	"McLeish, I." => array("http://www.wikidata.org/entity/Q1349394"),
	"McLeish, I.M." => array("http://www.wikidata.org/entity/Q1349394"),
	"McLeish, Ian" => array("http://www.wikidata.org/entity/Q1349394"),
	"McLeish, Ian M." => array("http://www.wikidata.org/entity/Q1349394"),
	"James Sinclair" => array("http://www.wikidata.org/entity/Q5925865"),
	"Sinclair, J" => array("http://www.wikidata.org/entity/Q5925865"),
	"Sinclair, J." => array("http://www.wikidata.org/entity/Q5925865"),
	"Sinclair, James" => array("http://www.wikidata.org/entity/Q5925865"),
	"Newman, M." => array("http://www.wikidata.org/entity/Q5999494","https://orcid.org/0000-0003-4851-015X"),
	"Newman, Mark" => array("http://www.wikidata.org/entity/Q5999494","https://orcid.org/0000-0003-4851-015X"),
	"Newman, Mark F." => array("http://www.wikidata.org/entity/Q5999494","https://orcid.org/0000-0003-4851-015X"),
	"Newman, Mark F. (photo)" => array("http://www.wikidata.org/entity/Q5999494","https://orcid.org/0000-0003-4851-015X"),
	"Orton, P." => array("http://www.wikidata.org/entity/Q7116981"),
	"Orton, P.D." => array("http://www.wikidata.org/entity/Q7116981"),
	"Orton, Peter D." => array("http://www.wikidata.org/entity/Q7116981"),
	"Pringle, C G" => array("http://www.wikidata.org/entity/Q3009492"),
	"Pringle, C.G." => array("http://www.wikidata.org/entity/Q3009492"),
	"Pringle, Cyrus Guernsey" => array("http://www.wikidata.org/entity/Q3009492"),
	"K.H. Rechinger" => array("http://www.wikidata.org/entity/Q78738"),
	"Rechinger, K" => array("http://www.wikidata.org/entity/Q78738"),
	"Rechinger, K H" => array("http://www.wikidata.org/entity/Q78738"),
	"Rechinger, K.H." => array("http://www.wikidata.org/entity/Q78738"),
	"Rechinger, Karl H." => array("http://www.wikidata.org/entity/Q78738"),
	"Rechinger, Karl Heinz" => array("http://www.wikidata.org/entity/Q78738"),
	"J.F.Rock"  => array("http://www.wikidata.org/entity/Q78631"),
	"Rock, J.F." => array("http://www.wikidata.org/entity/Q78631"),
	"Rock, Joseph F." => array("http://www.wikidata.org/entity/Q78631"),
	"Rock, Joseph Francis Charles" => array("http://www.wikidata.org/entity/Q78631"),
	"Rock, Joseph, F." => array("http://www.wikidata.org/entity/Q78631"),
	"R. Spruce" => array("http://www.wikidata.org/entity/Q1349394"),
	"Spence (Dr)" => array("http://www.wikidata.org/entity/Q1349394"),
	"Spruce, R." => array("http://www.wikidata.org/entity/Q1349394"),
	"Spruce, Richard" => array("http://www.wikidata.org/entity/Q1349394"),
	"Burton Smith, Chamberlain, Rushforth" => array("http://www.wikidata.org/entity/Q5957408"),
	"P & V Burton Smith, D Chamberlain & K Rushforth" => array("http://www.wikidata.org/entity/Q5957408"),
	"Rushforth, K D" => array("http://www.wikidata.org/entity/Q5957408"),
	"Rushforth, K.D." => array("http://www.wikidata.org/entity/Q5957408"),
	"Rushforth, Keith D." => array("http://www.wikidata.org/entity/Q5957408"),
	"Rushforth, Keith. D." => array("http://www.wikidata.org/entity/Q5957408"),
	"Haines, R. Wheeler" => array("http://www.wikidata.org/entity/Q6108730"),
	"Haines, R.W." => array("http://www.wikidata.org/entity/Q6108730"),
	"Haradjian, Manoog" => array("http://www.wikidata.org/entity/Q6108730"),
	"Wheeler Haines, R." => array("http://www.wikidata.org/entity/Q6108730"),
	"Shevock, J.R." => array("http://www.wikidata.org/entity/Q13415756"),
	"Shevock, James Robert" => array("http://www.wikidata.org/entity/Q13415756"),
	"Clemensi"  => array("http://www.wikidata.org/entity/Q2688176"),
	"Sintenis, P." => array("http://www.wikidata.org/entity/Q2688176"),
	"Sintenis, P.E.E." => array("http://www.wikidata.org/entity/Q2688176"),
	"Sintenis, Paul Ernst Emil" => array("http://www.wikidata.org/entity/Q2688176"),
	"Sintensis, P.E.E." => array("http://www.wikidata.org/entity/Q2688176"),
	"Särkinen, Tiina" => array("http://www.wikidata.org/entity/Q22668649"),
	"T. Sarkinen" => array("http://www.wikidata.org/entity/Q22668649"),
	"T.Sarkinen"  => array("http://www.wikidata.org/entity/Q22668649"),
	"Cavalerie, J." => array("http://www.wikidata.org/entity/Q3385648"),
	"Cavalerie, Julien" => array("http://www.wikidata.org/entity/Q3385648"),
	"Cavalerie, P.J." => array("http://www.wikidata.org/entity/Q3385648"),
	"Cavalerie, Pierre Julien" => array("http://www.wikidata.org/entity/Q3385648"),
	"Wight, R." => array("http://www.wikidata.org/entity/Q1375864"),
	"Wight, Robert" => array("http://www.wikidata.org/entity/Q1375864"),
	"Chow, Ho-ch'ang" => array("http://www.wikidata.org/entity/Q1356381"),
	"Wilson, E.H." => array("http://www.wikidata.org/entity/Q1356381"),
	"Wilson, Ernest Henry" => array("http://www.wikidata.org/entity/Q1356381"),
	"Wallich"  => array("http://www.wikidata.org/entity/Q730310"),
	"Wallich, N." => array("http://www.wikidata.org/entity/Q730310"),
	"Wallich, Nathaniel" => array("http://www.wikidata.org/entity/Q730310"),
	"Wight, Robert" => array("http://www.wikidata.org/entity/Q730310"),
	"Clark, M.C." => array("http://www.wikidata.org/entity/Q15406995"),
	"Roy Watling" => array("http://www.wikidata.org/entity/Q15406995"),
	"Taylor, J." => array("http://www.wikidata.org/entity/Q15406995"),
	"Watkins, J." => array("http://www.wikidata.org/entity/Q15406995"),
	"Watling, R." => array("http://www.wikidata.org/entity/Q15406995"),
	"Watling, Roy" => array("http://www.wikidata.org/entity/Q15406995"),
	"Watling, Roy (Prof.)" => array("http://www.wikidata.org/entity/Q15406995"),
	"Rock, J.F." => array("http://www.wikidata.org/entity/Q18207964"),
	"T.T. Yü" => array("http://www.wikidata.org/entity/Q18207964"),
	"T.T.Yü"  => array("http://www.wikidata.org/entity/Q18207964"),
	"Yu, T T" => array("http://www.wikidata.org/entity/Q18207964"),
	"Yu, T.T." => array("http://www.wikidata.org/entity/Q18207964"),
	"Yü, Tse-tsun" => array("http://www.wikidata.org/entity/Q18207964"),
	'Gardner, Martin F.' => array("http://www.wikidata.org/entity/Q67437570", "https://orcid.org/0000-0002-1457-8802"),
	"Gardner, Martin Fraser" => array("http://www.wikidata.org/entity/Q67437570", "https://orcid.org/0000-0002-1457-8802"),
	'Gardner, M.F.' => array("http://www.wikidata.org/entity/Q67437570","https://orcid.org/0000-0002-1457-8802")
);

?>