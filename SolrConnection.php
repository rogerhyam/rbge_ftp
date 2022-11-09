<?php

require_once("config.php");

/**
* Simple class to abstract calls to Solr index of specimens
*
*/
class SolrConnection
{
	private $cursor_mark = "*";
	private $paged_query = null;
	private $page_size = 2000;
	
	function get_specimen($barcode){
		
		$back = $this->query('barcode_s:' . $barcode);		
		if(isset($back->response) && isset($back->response->docs) && count($back->response->docs)){
			return $back->response->docs[0];
		}
		return null;
		
	}

	/**
	 * Call repeatedly with the same 
	 * query string and it will page through
	 * the results till it returns null
	 * 
	 */
	function query_paged($query){

		// if we are given a new query then we reset the paging
		if($query != $this->paged_query){
			$this->paged_query = $query;
			$this->cursor_mark = "*";
		}
		
		if(!isset($query->params)) $query->params = (object)array();
		$query->params->rows = $this->page_size;
		$query->params->sort = "id asc";
		$query->params->cursorMark = $this->cursor_mark; 

		$result = $this->query_object($query);

		if($result->response->numFound == 0){
			// didn't find anything so reset the paging
			// and return false
			$this->paged_query = null;
			$this->cursor_mark = "*";
			return false;
		}else{
			// found something so return the docs and save the cursor position
			if(isset($result->nextCursorMark)) $this->cursor_mark = $result->nextCursorMark;
			return $result->response->docs;
		}
		
	}

	function query($query, $cursor_mark = null){
    
	    $uri = SOLR_QUERY_URI . '?q=' . urlencode($query) . '&sort=id+asc&rows=' . $this->page_size;

		if($cursor_mark) $uri .= '&cursorMark=' . $cursor_mark;
		
	    $ch = curl_init( $uri );
	    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    
	    // Send request.
	    $result = curl_exec($ch);
	    curl_close($ch);	
		
	    return json_decode($result);
	}
	
	function query_object($q){
		
		$json = json_encode($q);

		$ch = curl_init( SOLR_QUERY_URI );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    'Content-Length: ' . strlen($json))
		);
		// Send request.
		$result = curl_exec($ch);
		curl_close($ch);
		
		return json_decode($result);
		
	}
	


}
	
	
?>