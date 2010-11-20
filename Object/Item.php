<?php
class Item extends Located {
  private $Corpus;
  private $isReserved = array("highlight", "name", "resource", "thumbnail", "topic", "upper", "user","item_name", "item_corpus");

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
    if(property_exists($view, $id))
	    return (string) $view->resource;
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
    if(property_exists($view, "topic"))
      return array();
    $topics = $view->topic;
    $result = array();
    foreach($topics as $topic)
      array_push($result, $this->map->getTopic($topic));
  	return result;
  }

  public function getAttributes(){
    $result = array();
    $view = $this->getView();
    foreach($view as $k => $v)
      if(!in_array($k, $this->isReserved))
        $result[$k]=$v;
    return $result;
  }

  public function describe($attribute, $value){
  	$item = $this->getRaw();
  	if(!property_exists($item, $attribute))
      $item->$attribute = array();
  	array_push($item->$attribute, $value);
  	$this->db->put($item);
  }

  public function undescribe($attribute, $value){
    $item = $this->getRaw();
  	if(!property_exists($item, $attribute))
      return true;

    if(in_array($value, $item->$attribute))
    {
      if(count($item->$attribute) == 0)
        unset($item->$attribute);
      else
      {
        for($i=0; $i<count($item->$attribute); $i++)
          if($item->$attribute[$i] == $value)
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
      $item->topics = array();
    $topicID = $topic->getID();
    $obj = new stdClass;
    $obj->$topicID = new stdClass;
    $obj->$topicID->viewpoint = $topic->getViewpointID();
  	array_push($item->topics, $obj);
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