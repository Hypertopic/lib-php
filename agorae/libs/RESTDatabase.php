<?php
class RESTDatabase{
  private $baseUrl;
  private $memcache;

  public function __construct($baseUrl) {
    //Connect to memcache server, if connection failed, memcache will be
    //disabled.
    $this->memcache = @memcache_connect('127.0.0.1', 11211);
  	$this->baseUrl = $baseUrl;
  }

  public function send($action, $url, $body)
  {
    $result = false;
    //Default HTTP request method is GET
    $action = ($action) ? strtoupper($action) : "GET";
    //All data should be json formatted, and expect json result from CouchDB
    $headers = array('Content-type: application/json',
                                      "Accept: application/json");
    //Including content length for HTTP request
    if(strlen($body) > 0)
      array_push($headers, "Content-length: ". strlen($body));
    //Check the given url if it doesn't incude the server URL, add it.
    $url = (strpos($url, $this->baseUrl) === 0) ? $url : $this->baseUrl . $url;

    //If the request is a GET request, then should read the cached result and
    //Etag value from memcache server.
    if($action == "GET" && isset($this->memcache) && $this->memcache !== false)
    {
      $cache = $this->memcache->get($url);
      if($cache !== false)
      {
        $cache = json_decode($cache);
        //Add Etag to the HTTP request headers
        array_push($headers, "If-None-Match: " . $cache->Etag);
        $result = $cache->result;
      }
    }

    //Init curl request
    $ch = curl_init();
    //Add the HTTP request Headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //The response information should include HTTP response headers
    curl_setopt($ch, CURLOPT_HEADER, 1);
    //If the url is transfered
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $action);
    //Compress data
    curl_setopt($ch, CURLOPT_ENCODING, "gzip" );
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30 );
    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $response = curl_exec( $ch );
    //Get HTTP status code
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    //Get HTTP response headers and body
    $header = substr($response, 0, $header_size);
    $body = trim(substr($response, $header_size));
    curl_close($ch);

    //If the data is not changed then return the cached data.
    if($status_code == "304")
      return $result;

    //Something goes wrong
    if(substr($status_code, 0, 1) != "2")
      throw new Exception("$action $url \n$body", $status_code);

    //decode HTTP response body to object
    $result = (strlen($body) > 0) ? json_decode($body) : true;

    //If it's a get request, should cache the data
    if($action == "GET" && isset($this->memcache)
      && $this->memcache !== false && isset($headers["Etag"]))
    {
      //Parse HTTP response headers raw text to array
      $headers = $this->http_parse_headers($header);
      $cache = new stdClass;
      $cache->Etag = $headers["Etag"];
      $cache->result = $result;
      $cache = json_encode($cache);
      //Encode the cache data for saving it in memcache
      $this->memcache->set($url, $cache);
    }

    //If an update happened (PUT/POST/DELETE) then remove the cached data from
    //memcache server.
    //TODO: Should cached data be removed when it's a PUT or POST request?
    if($action != "GET" && isset($this->memcache) && $this->memcache !== false
      && $this->memcache->get($url))
      $this->memcache->delete($url, 10);

    return $result;
  }

  public function get($url){
    //Parse CouchDB response data in memory.
    return $this->parseObject($this->send('GET', $url, ''));
  }

  public function post($object){
    $body = (is_string($object)) ? $object : json_encode($object);
    $body = $this->send('POST', $this->baseUrl, $body);
    $object->_id = $body->id;
    if(isset($body->rev))
      $object->_rev = $body->rev;
    return $object;
  }

  public function put($object){
    $body = (is_string($object)) ? $object : json_encode($object);
    $url = $this->baseUrl . $object->_id;
    $result = $this->send('PUT', $url, $body);
    $object->_rev = $result->rev;
    return $object;
  }

  public function delete($object){
    $url = $this->baseUrl . $object->_id;
    $url .= isset($object->_rev) ? "?rev=" . $object->_rev : "";
    return $this->send('DELETE', $url, '');
  }

  //This function is ported from Prophyry RESTDatabase class.
  private function parseObject($object)
  {
    if(!isset($object) || !property_exists($object, "rows"))
      return $object;
    $rows = $object->rows;
    $result = new stdClass();

    if(property_exists($object, "_id")) $result->_id = $object->_id;
    if(property_exists($object, "_rev")) $result->_rev = $object->_rev;
	  foreach($rows as $row)
	  {
			$current = $result;
			foreach($row->key as $k){
				if(!property_exists($current, $k))
				  $current->$k = new stdClass();
				$current = $current->$k;
			}

		  foreach($row->value as $k => $v)
		  {
		    if(!property_exists($current, $k))
		      $current->$k = array();
		    array_push($current->$k, $v);
      }
		}
		return $result;
  }

  //Parse HTTP response headers into array.
  private function http_parse_headers( $header )
  {
      $retVal = array();
      $fields = explode("\r\n",
              preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
      foreach( $fields as $field ) {
        if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
          $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e',
              'strtoupper("\0")', strtolower(trim($match[1])));
          if( isset($retVal[$match[1]]) )
              $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
          else
              $retVal[$match[1]] = trim($match[2]);
        }
      }
      return $retVal;
  }
}