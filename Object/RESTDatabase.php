<?php
class RESTDatabase{
  private $baseUrl;

  public function __construct($baseUrl) {
  	$this->baseUrl = $baseUrl;
  }

  public function send($action, $url, $body)
  {
    $action = ($action) ? strtoupper($action) : "GET";
    $headers = array('Content-type: application/json', "Accept: application/json");
    if(strlen($body) > 0)
      array_push($headers, "Content-length: ". strlen($body));

    $url = (strpos($url, $this->baseUrl) === 0) ? $url : $this->baseUrl . $url;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $action);
    curl_setopt($ch, CURLOPT_ENCODING, "gzip" );
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30 );
    curl_setopt($ch, CURLOPT_URL, $url );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $response = curl_exec( $ch );
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if(substr($status_code, 0, 1) != "2")
      throw new Exception("$action $url \n$body", $status_code);

    return (strlen($response) > 0) ? json_decode($response) : true;
  }

  public function get($url){
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
}
?>