<?php
class Topic extends Named {
  private $isReserved = array("highlight", "name", "resource", "thumbnail", "topic", "upper", "user");
  private $Viewpoint;

  public function __construct($id, $viewpoint) {
    $this->Viewpoint = $viewpoint;
  	parent::__construct($id, $viewpoint->map);
  }

  protected function getViewpointID() {
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

  public void destroy(){
    $viewpoint = $this->Viewpoint->getRaw();
    $id = $this->getID();
    unset($viewpoint->$id);
    foreach($viewpoint as $k => $v)
    {
      if(!property_exists("broader"))
        continue;
      $broader = $viewpoint->$k->broader;
      if(!in_array($id, $broader))
        continue;
      for($i=0; $i < count($broader); $i++)
        if($broader[$i] == $id)
        {
          $broader.splice($i, 1);
          $i--;
        }
      $viewpoint->$k->broader = $broader;
    }
  	$this->db->put($viewpoint);
  }

public Collection<Topic> getNarrower(){
	Collection<Topic> result = new ArrayList<Topic>();
	for (JSONObject narrower:this.getView().getAllJSONObjects("narrower")) {
		result.add(Viewpoint.this.getTopic(narrower));
	}
	return result;
}

public Collection<Topic> getBroader(){
	Collection<Topic> result = new ArrayList<Topic>();
	for (JSONObject broader : this.getView().getAllJSONObjects("broader")) {
		result.add(Viewpoint.this.getTopic(broader));
	}
	return result;
}

/**
 * Recursive. Could be optimized with a cache.
 * Precondition: narrower topics graph must be acyclic.
 */
public Collection<Corpus.Item> getItems(){
	Collection<Corpus.Item> result = new HashSet<Corpus.Item>();
	JSONObject topic = this.getView();
	for (JSONObject item : topic.getAllJSONObjects("item")) {
		result.add(
			HypertopicMap.this.getItem(item)
		);
	}
	for (JSONObject narrower : topic.getAllJSONObjects("narrower")) {
		result.addAll(
			Viewpoint.this.getTopic(narrower)
				.getItems()
		);
	}
	return result;
}

public void moveTopics(Topic... narrowerTopics){
	JSONArray broader = new JSONArray();
	broader.put(this.getID());
	JSONObject viewpoint = Viewpoint.this.getRaw();
	JSONObject topics = viewpoint.getJSONObject("topics");
	for (Topic t : narrowerTopics) {
		topics.getJSONObject(t.getID()).put("broader", broader);
	}
	HypertopicMap.this.db.put(viewpoint);
}

/**
 * Unlink from broader topics
 */
public void unlink(){
	JSONObject viewpoint = Viewpoint.this.getRaw();
	viewpoint.getJSONObject("topics")
		.getJSONObject(this.getID())
		.put("broader", new JSONArray());
	HypertopicMap.this.db.put(viewpoint);
}

public void linkTopics(Topic... narrowerTopics){
	JSONObject viewpoint = Viewpoint.this.getRaw();
	JSONObject topics = viewpoint.getJSONObject("topics");
	for (Topic t : narrowerTopics) {
		topics.getJSONObject(t.getID()).append("broader", this.getID());
	}
	HypertopicMap.this.db.put(viewpoint);
}