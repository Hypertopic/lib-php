<?php
class Item extends Located {
  private $Corpus;
  private $isReserved = array("_id", "_rev", "highlight", "name", "resource", "thumbnail", "topic", "upper", "user","item_name", "item_corpus");

  public function __construct($id, $Corpus) {
    $this->Corpus = $Corpus;
  	parent::__construct($id, $Corpus->map);
  }

  protected function getView(){
	  $view = $this->Corpus->getView();
	  $id = $this->getID();
	  if(property_exists($view, $id))
	    return $view->$id;
		return false;
  }

  public function rename($name){
  	$item = $this->getRaw();
  	$item->item_name = $name;
  	$this->db->put($item);
  }

  public function getCorpusID() {
  	return $this->Corpus->getID();
  }

  public function getResource(){
    $view = $this->getView();
    if(property_exists($view, "resource"))
    {
      $resources = $view->resource;
	    return is_array($resources) ? $resources[0] : $resources;
	  }
		return false;
  }

  public function getThumbnail(){
    $view = $this->getView();
    if(property_exists($view, $id))
	    return (string) $view->thumbnail;
		return false;
  }

  public function getTopics(){
    $view = $this->getView();
    if(!property_exists($view, "topic"))
      return array();
    $topics = $view->topic;
    $result = array();
    foreach($topics as $topic)
      array_push($result, $this->map->getTopic($topic));
  	return $result;
  }

  public function getAttributes(){
    $result = array();
    $view = $this->getRaw();
    foreach($view as $k => $v)
      if(!in_array($k, $this->isReserved))
        array_push($result, array($k=>$v));
    return $result;
  }

  public function describe($attribute, $value){
  	$item = $this->getRaw();
  	if(!property_exists($item, $attribute))
      $item->$attribute = $value;
    else
      if(is_array($item->$attribute))
  	    array_push($item->$attribute, $value);
  	  else
  	    $item->$attribute = array($item->$attribute, $value);
  	$this->db->put($item);
  }

  public function undescribe($attribute, $value){
    $item = $this->getRaw();
  	if(!property_exists($item, $attribute))
      return true;
    if(is_string($item->$attribute))
    {
      unset($item->$attribute);
      $this->db->put($item);
    }
    else
      if(in_array($value, $item->$attribute))
      {
        for($i=0; $i<count($item->$attribute); $i++)
        {
          $attributes = $item->$attribute;
          if($attributes[$i] == $value)
          {
            array_splice($item->$attribute, $i, 1);
            $i--;
          }
        }
        $this->db->put($item);
      }
  }

  public function tag($topic){
  	$item = $this->getRaw();
  	if(!property_exists($item, "topics"))
      $item->topics = new stdClass;
    $topicID = $topic->getID();
    $obj = new stdClass;
    $obj->viewpoint = $topic->getViewpointID();
  	$item->topics->$topicID = $obj;
  	$this->db->put($item);
  }

  public function untag($topic){
  	$item = $this->getRaw();
  	if(!property_exists($item, "topics"))
      return true;
    $topicID = $topic->getID();
    unset($item->topics->$topicID);
    $this->db->put($item);
  }
}