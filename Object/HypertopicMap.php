<?php
require_once 'RESTDatabase.php';

class HypertopicMap{
  private $db;

  public function __construct($baseUrl) {
  	$this->db = new RESTDatabase($baseUrl);
  }

  public function getUser($UserID){
    return new User($UserID, $this);
  }

  public function getCorpus($CorpusID){
    return $CorpusID;
  }

  public function getViewpoint($ViewpointID){
    return $ViewpointID;
  }
}

abstract class Identified{
  private $id;
  private $db;
  private $map;

  public function __construct($id, $map) {
  	$this->id = $id;
  	$this->db = $map->db;
  	$this->map = $map;
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