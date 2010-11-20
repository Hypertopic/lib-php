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
        array_push($result, $this->getTopic($k));
  	return $result;
  }

  public function getItems(){
  	$result = array();
  	$topics = $this->getTopics();
  	foreach ($topics as $topic)
  	{
  	  $items = $topic->getItems();
  	  foreach($items as $item)
  		  array_push($result, $item);
  	}
  	return $result;
  }

  public function listUsers(){
    $view = $this->getView();
    if(!property_exists($view, "user"))
      return array();
  	return $view->user;
  }

  public function createTopic($broaderTopics = null){
  	$topicID = HypertopicMap::getGUID();
  	$viewpoint = $this->getRaw();
  	$broader = array();
  	if(isset($broaderTopics))
  	{
  	  if(is_string($broaderTopics))
    	    array_push($broader, $broaderTopics);
    	else
    	  if(is_array($broaderTopics))
    	    foreach($broaderTopics as $topic)
    	      array_push($broader, $topic->getID());
    	  else
    	    array_push($broader, $broaderTopics->getID());
    }
  	if(!property_exists($viewpoint, "topics"))
  	  $viewpoint->topics = new stdClass;
  	$viewpoint->topics->$topicID = new stdClass;
  	$viewpoint->topics->$topicID->broader = $broader;
  	$result = $this->db->put($viewpoint);
  	return $this->getTopic($topicID);
  }

  public function getTopic($topic) {
    if(is_string($topic))
    {
      $topic = new Topic($topic, $this);
  	  return $topic;
  	}
  	else
  	{
  	  $id = isset($topic->id) ? $topic->id : $topic->getID();
  	  return new Topic($id, $this);
  	}
  }
}