<?php
require_once 'Object/HypertopicMap.php';

class TopicTest extends PHPUnit_Framework_TestCase
{
  private static $map;

  private static $user;
  private static $corpus;
  private static $item;
  private static $otherItem;
  private static $viewpoint;
  private static $topic;
  private static $otherTopic;
  private static $childTopic;

  public static function setUpBeforeClass()
  {
    self::$map = new HypertopicMap("http://192.168.1.141:5984/argos/_design/argos/_rewrite/");
    self::$user = self::$map->getUser("me");
    self::$corpus = self::$user->createCorpus("my corpus");
    self::$item = self::$corpus->createItem("my item");
    self::$otherItem = self::$corpus->createItem("another item");
    self::$viewpoint = self::$user->createViewpoint("my viewpoint");
    self::$topic = self::$viewpoint->createTopic();
    self::$otherTopic = self::$viewpoint->createTopic();
    self::$childTopic = self::$viewpoint->createTopic(self::$topic);
    self::$item->tag(self::$topic);
    self::$otherItem->tag(self::$childTopic);
  }

  public static function tearDownAfterClass()
  {
    if(isset(self::$corpus))
      self::$corpus->destroy();
    if(isset(self::$viewpoint))
      self::$viewpoint->destroy();
  }

  public function testGetViewpointID()
  {
    $viewpointID = self::$topic->getViewpointID();
    try{
      $this->assertGreaterThan(0, strlen($viewpointID));
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
  }

  public function testRename()
  {
    try{
      self::$topic->rename("topic");
      self::$otherTopic->rename("otherTopic");
      self::$childTopic->rename("childTopic");
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertTrue(TRUE);
  }

  /**
   * @depends testRename
   */
  public function testGetName()
  {
    try{
      $name = self::$topic->getName();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals($name, "topic");
  }

  public function testMoveTopics()
  {
    try{
      self::$topic->moveTopics(self::$otherTopic);
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertTrue(TRUE);
  }

  /**
   * @depends testMoveTopics
   */
  public function testGetBroader()
  {
    try{
      $topics = self::$otherTopic->getBroader();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(count($topics), 1);
  }

  /**
   * @depends testMoveTopics
   */
  public function testGetNarrower()
  {
    try{
      $topics = self::$topic->getNarrower();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(count($topics), 2);
  }

  /**
   * @depends testMoveTopics
   */
  public function testUnlink()
  {
    try{
      self::$otherTopic->unlink();
      $topics = self::$otherTopic->getBroader();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(count($topics), 0);
  }

  /**
   * @depends testUnlink
   */
  public function testLinkTopics()
  {
    try{
      self::$otherTopic->linkTopics(array(self::$topic, self::$childTopic));
      $topics = self::$otherTopic->getNarrower();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(count($topics), 2);
  }

  public function testGetItems()
  {
    try{
      $items = self::$topic->getItems();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(count($items), 2);
  }

  /**
   * @depends testLinkTopics
   */
  public function testDestroy()
  {
    try{
      self::$otherTopic->destroy();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertTrue(True);
  }
}