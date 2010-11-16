<?php
class HypertopicMap{

  public function send($action, $url, $body)
  {
    $action = ($action) ? strtoupper($action) : "GET";
    $headers = array('Content-type: application/json', "Accept: application/json");
    if(strlen($body) > 0)
      array_push($headers, "Content-length: ". strlen($body));

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
      return false;

    return (strlen($response) > 0) ? json_decode($response) : true;
  }

  public function getUser($UserID){
    return new User($UserID);
  }
}

class Identified{
  private $id;

  public function __construct($id) {
  	$this->id = $id;
  }

  public function getID() {
  	return $this->id;
  }

  public function equals($that) {
  	return is_a($that, "Identified")
  		&& $this->id == $that->id;
  }

  public function hashCode() {
  	return spl_object_hash($this);
  }
}

class User extends Identified {
  public function __construct($id) {
  	parent::__construct(id);
  }
}

abstract class Named extends Identified {

  public function __construct($id) {
  	parent::__construct(id);
  }

  protected abstract function getView();

  public function getName(){
  	$obj = $this.getView();
  	return $obj->name;
  }
}

/*public abstract class Located extends Named {

  public function __construct($id) {
  	parent::__construct(id);
  }

  protected function getRaw(){
  	return this.db.get(this.getID());
  }

  public void destroy() throws Exception {
  	HypertopicMap.this.db.delete(this.getRaw());
  }

}*/
?>