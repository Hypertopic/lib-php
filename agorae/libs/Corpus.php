<?php
class Corpus extends Registered {
  private $isReserved = array("highlight", "name", "resource", "thumbnail", "topic", "upper", "user");

  public function __construct($id, $map) {
  	parent::__construct($id, $map);
  }

  protected function getView(){
    $id = $this->getID();
  	$result = $this->db->get("corpus/" . $id);
  	return $result->$id;
  }

  public function rename($name){
    $raw = $this->getRaw();
    $raw->corpus_name = $name;
  	$this->db->put($raw);
  }

  public function destroy(){
  	$items = $this->getItems();
  	foreach($items as $item) {
  		$item->destroy();
  	}
  	parent::destroy();
  }

  public function listUsers(){
    $view = $this->getView();
    return property_exists($view, "user") ? $view->user : array();
  }

  public function getItems(){
    $result = array();
    $view = $this->getView();
    foreach($view as $k => $v)
    {
      if(!in_array($k, $this->isReserved))
        array_push($result, $this->getItem($k));
    }
    return $result;
  }

  public function getItem($itemID){
    return new Item($itemID, $this);
  }

  public function createItem($name){
    $item = new stdClass;
    $item->item_name = $name;
    $item->item_corpus = $this->getID();
    $result = $this->db->post($item);
    $itemID = $result->_id;
    return $this->getItem($itemID);
  }
}