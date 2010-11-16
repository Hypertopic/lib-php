<?php
class User extends Identified {
  public function __construct($id, $map) {
  	parent::__construct($id, $map);
  }

  protected function getView(){
    $id = $this->getID();
  	$result = $this->db->get("user/" . $id);
  	return $result->$id;
  }

  public function listCorpora(){
    $corpus = array();
  	$view = $this->getView();
  	if(property_exists($view, "corpus"))
  	  return $view->corpus;
  	return $corpus;
  }

  public function listViewpoints(){
    $viewpoints = array();
  	$view = $this->getView();
  	if(property_exists($view, "viewpoint"))
  	  return $view->viewpoint;
  	return $viewpoints;
  }

  public function createCorpus($name){
    $corpus = new stdClass;
    $corpus->corpus_name = $name;
    $corpus->users = array($this->getID());
    $corpus = $this->db->post($corpus);
    $corpusID = $corpus->_id;
    return $this->map->getCorpus($corpusID);
  }

  public function createViewpoint($name){
    $viewpoint = new stdClass;
    $viewpoint->viewpoint_name = $name;
    $viewpoint->users = array($this->getID());
    $viewpoint = $this->db->post($viewpoint);
    $viewpoint = $viewpoint->_id;
    return $this->map->getViewpoint($viewpoint);
  }
}