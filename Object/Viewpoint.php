<?php
class Viewpoint extends Registered {
  private $isReserved = array("highlight", "name", "resource", "thumbnail", "topic", "upper", "user");

  public function __construct($id, $map) {
  	parent::__construct($id, $map);
  }

  protected function getView(){
    $id = $this->getID();
  	$result = $this->db->get("viewpoint/" . $id);
  	return $result->$id;
  }

  public function rename($name){
    $raw = $this->getRaw();
    $raw->viewpoint_name = $name;
  	$this->db->put($raw);
  }

  public function getUpperTopics(){
    $result = array();
    $view = $this->getView();
    if(!property_exists($view, "upper"))
      return $result;
    foreach($view->upper as $topic)
      array_push($result, $this->getTopic($topic));
  	return $result;
  }

  public function getTopics(){
    $result = array();
    $view = $this->getView();
    foreach($view as $k => $v)
      if(!in_array($k, $this->isReserved))
        array_push($result, $this->getTopic($v));
  	return $result;
  }

  public function getItems(){
  	$result = array();
  	$topics = $this->getTopics();
  	foreach ($topics as $topic) {
  		array_push($result, $topic->getItems());
  	}
  	return $result;
  }

  public function listUsers(){
    $view = $this->getView();
    if(!property_exists($view, "user"))
      return array();
  	return $view->user;
  }

  public function createTopic($broaderTopics){
  	$topicID = HypertopicMap::getGUID();
  	$viewpoint = $this->getRaw();
  	$broader = array();
  	foreach($broaderTopics as $topic)
  	  if(is_string($topic))
  	    array_push($broader, $topic);
  	  else
  	    array_push($broader, $topic->getID());
  	if(!property_exists($viewpoint, "topics"))
  	  $viewpoint->topics = new stdClass;
  	$viewpoint->topics->$topicID = new stdClass;
  	$viewpoint->topics->$topicID->broader = $broader;
  	$this->db->put($viewpoint);
  	return $this->getTopic($topicID);
  }

  public function getTopic($topic) {
    if(is_string($topic))
  	  return new Topic($topic, $this);
  	else
  	  return new Topic($topic->getID(), $this);
  }
}