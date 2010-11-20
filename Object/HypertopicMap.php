<?php
require_once 'RESTDatabase.php';
require_once 'Viewpoint.php';
require_once 'Topic.php';
require_once 'Corpus.php';
require_once 'Item.php';
require_once 'User.php';

class HypertopicMap{
  public $db;

  public function __construct($baseUrl) {
  	$this->db = new RESTDatabase($baseUrl);
  }

  public function getUser($UserID){
    return new User($UserID, $this);
  }

  public function getCorpus($CorpusID){
    return new Corpus($CorpusID, $this);
  }

  public function getViewpoint($ViewpointID){
    return new Viewpoint($ViewpointID, $this);
  }

  public function getTopic($topic){
  	$viewpoint = $this->getViewpoint($topic->viewpoint);
    return $viewpoint->getTopic($topic);
  }

  public function getItem($arg){
    if(is_string($arg))
    {
      $resource = $this->db->get("resource/" + rawurlencode($arg));
  	  $resource = $resource->$arg;
  	  return $resource->item;
  	}
  	else
  	{
  	  $corpus = $this->getCorpus($arg->corpus);
  	  return $corpus->getItem($arg->id);
  	}
  }

  public static function getGUID(){
    $charid = strtoupper(md5(uniqid(rand(), true)));
    $uuid = substr($charid, 0, 8)
           .substr($charid, 8, 4)
           .substr($charid,12, 4)
           .substr($charid,16, 4)
           .substr($charid,20,12);
    return $uuid;
  }
}

abstract class Identified{
  public $id;
  public $db;
  public $map;

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

  public function __construct($id, $map) {
  	parent::__construct($id,$map);
  }

  protected abstract function getView();

  public function getName(){
  	$obj = $this->getView();
  	return $obj->name;
  }
}

abstract class Located extends Named {

  public function __construct($id, $map) {
  	parent::__construct($id,$map);
  }

  protected function getRaw(){
  	return $this->db->get($this->getID());
  }

  public function destroy(){
  	$this->db->delete($this->getRaw());
  }
}

abstract class Registered extends Located {

  public function __construct($id, $map) {
  	parent::__construct($id,$map);
  }

  public function register($user){
    $raw = $this->getRaw();
    $userID = (is_string($user)) ? $user : $user->getID();
    if(!property_exists($raw, "users"))
      $raw->users = array();
    array_push($raw->users, $userID);
  	$this->db->put($raw);
  }

  public function unregister($user){
  	$raw = $this->getRaw();
  	$userID = (is_string($user)) ? $user : $user->getID();
  	if(property_exists($raw, "users"))
  	{
  	  $found = false;
  	  for($i=0; $i < count($raw->users); $i++)
  	    if($userID == $raw->users[$i])
  	    {
  	      $found = true;
  	      array_splice($raw->users, $i, 1);
  	      $i--;
  	    }

  	  if($found)
  	    $this->db->put($raw);
    }
  }
}

function j($resource){
  echo json_encode($resource) . "\n";
}