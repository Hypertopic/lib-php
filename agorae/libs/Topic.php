<?php
class Topic extends Named {
  private $isReserved = array("highlight", "name", "resource", "thumbnail", "topic", "upper", "user");
  private $Viewpoint;

  public function __construct($id, $viewpoint) {
    $this->Viewpoint = $viewpoint;
  	parent::__construct($id, $viewpoint->map);
  }

  public function getViewpointID() {
  	return $this->Viewpoint->getID();
  }

  protected function getView(){
	  $viewpoint = $this->Viewpoint->getView();
	  $id = $this->getID();
	  if(property_exists($viewpoint, $id))
	    return $viewpoint->$id;
  }

  public function rename($name){
  	$viewpoint = $this->Viewpoint->getRaw();
  	$id = $this->getID();
  	if(isset($viewpoint->topics->$id))
  	  $viewpoint->topics->$id->name = $name;
  	$this->db->put($viewpoint);
  }

  public function destroy(){
    $viewpoint = $this->Viewpoint->getRaw();
    $id = $this->getID();
    foreach($viewpoint->topics as $k => $v)
    {
      if(!property_exists($v, "broader"))
        continue;
      $broader = $viewpoint->topics->$k->broader;
      if(!in_array($id, $broader))
        continue;
      for($i=0; $i < count($broader); $i++)
        if($broader[$i] == $id)
        {
          array_splice($broader, $i, 1);
          $i--;
        }
      $viewpoint->topics->$k->broader = $broader;
    }
    unset($viewpoint->topics->$id);
  	$this->db->put($viewpoint);
  }

  public function getNarrower(){
  	$result = array();
  	$view = $this->getView();
  	if(!property_exists($view, "narrower"))
  	  return $result;
  	foreach($view->narrower as $topic)
  	  array_push($result, $this->Viewpoint->getTopic($topic));
  	return $result;
  }

  public function getBroader(){
    $result = array();
  	$view = $this->getView();
  	if(!property_exists($view, "broader"))
  	  return $result;

  	foreach($view->broader as $topic)
  	  array_push($result, $this->Viewpoint->getTopic($topic));
  	return $result;
  }

  /**
   * Recursive. Could be optimized with a cache.
   * Precondition: narrower topics graph must be acyclic.
   */
  public function getItems(){
    $result = array();
  	$topic = $this->getView();
  	if(property_exists($topic, "item"))
  	  foreach($topic->item as $item)
  	  {
  	    $item = $this->map->getItem($item);
  	    array_push($result, $item);
  	  }
    if(property_exists($topic, "narrower"))
  	  foreach($topic->narrower as $narrower)
  	  {
  	    $t = $this->Viewpoint->getTopic($narrower);
  	    $items = $t->getItems();
  	    foreach($items as $item)
  	      array_push($result, $item);
  	  }
  	return $result;
  }

  public function moveTopics($narrowerTopics){
  	$broader = array();
  	array_push($broader, $this->getID());
  	$viewpoint = $this->Viewpoint->getRaw();
  	if(!property_exists($viewpoint, "topics")) return;
  	$topics = &$viewpoint->topics;
  	if(is_array($narrowerTopics))
    	foreach($narrowerTopics as $t)
    	{
    	  $topicID = (is_string($t)) ? $t : $t->getID();
    	  $topics->$topicID->broader = $broader;
    	}
    else
      if(is_string($narrowerTopics))
        $topics->$narrowerTopics->broader = $broader;
      else
      {
        $topicID = $narrowerTopics->getID();
        $topics->$topicID->broader = $broader;
      }
  	$this->db->put($viewpoint);
  }

  /**
   * Unlink from broader topics
   */
  public function unlink(){
  	$viewpoint = $this->Viewpoint->getRaw();
  	if(!property_exists($viewpoint, "topics")) return;
  	$id = $this->getID();
  	$viewpoint->topics->$id->broader = array();
  	$this->db->put($viewpoint);
  }

  public function linkTopics($narrowerTopics){
  	$viewpoint = $this->Viewpoint->getRaw();
  	if(!property_exists($viewpoint, "topics")) return;

  	foreach($narrowerTopics as $t) {
  	  $id = (is_string($t)) ? $t : $t->getID();
  		$broader = isset($viewpoint->topics->$id->broader) ? $viewpoint->topics->$id->broader : array();
  		array_push($broader, $this->getID());
  		$viewpoint->topics->$id->broader = $broader;
  	}
  	$this->db->put($viewpoint);
  }
}