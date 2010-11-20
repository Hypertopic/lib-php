<?php
require_once 'Object/HypertopicMap.php';

class HypertopicMapTest extends PHPUnit_Framework_TestCase
{
  private $map;

  private $user;
  private $corpus;
  private $item;
  private $viewpoint;
  private $topic;
  private $childTopic;
  private $otherTopic;

  protected function setUp()
  {
    $this->map = new HypertopicMap("http://192.168.1.141:5984/argos/");
    print "get user\n";
    $this->user = $this->map->getUser("me");
  	print "create corpus\n";
  	$this->corpus = $this->user->createCorpus("my corpus");
  	/*this.item = this.corpus.createItem("my item");
  	this.item.describe("foo", "bar");
  	this.item.describe("resource", "http://acme.com/foo");
  	this.viewpoint = this.user.createViewpoint("my viewpoint");
  	this.topic = this.viewpoint.createTopic();
  	this.childTopic = this.viewpoint.createTopic(this.topic);
  	this.otherTopic = this.viewpoint.createTopic();
  	this.highlight = this.item.createHighlight(
  		this.childTopic, "FOO", 1024, 1096
  	);
  	this.item.tag(this.childTopic);*/
  }

  protected function tearDown()
  {
    print "destroy corpus\n";
    $this->corpus->destroy();

  }

  public function testRegister()
  {
    $this->corpus->register($this->map->getUser("him"));
    $this->assertEquals(2, count($this->corpus->listUsers()));
  }
}
?>