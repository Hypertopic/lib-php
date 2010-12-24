<?php
require_once 'agorae/libs/HypertopicMap.php';

class ViewpointTest extends PHPUnit_Framework_TestCase
{
  private static $map;

  private static $user;
  private static $corpus;
  private static $item;
  private static $viewpoint;
  private static $topic;
  private static $childTopic;
  private static $otherTopic;

  public static function setUpBeforeClass()
  {
    self::$map = new HypertopicMap("http://192.168.1.141:5984/argos/_design/argos/_rewrite/");
    self::$user = self::$map->getUser("me");
    self::$viewpoint = self::$user->createViewpoint("my viewpoint");
    self::$corpus = self::$user->createCorpus("my corpus");
    self::$item = self::$corpus->createItem("my item");
  }

  public static function tearDownAfterClass()
  {
    if(isset(self::$viewpoint))
      self::$viewpoint->destroy();
    if(isset(self::$corpus))
      self::$corpus->destroy();
  }

  public function testRename()
  {
    try{
      self::$viewpoint->rename("new name");
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
      $name = self::$viewpoint->getName();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals($name, "new name");
  }

  public function testRegister()
  {
    try{
      self::$viewpoint->register(self::$map->getUser("him"));
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertTrue(TRUE);
  }

  /**
   * @depends testRegister
   */
  public function testListUsers()
  {
    try{
      self::$viewpoint->unregister(self::$map->getUser("him"));
      $users = self::$viewpoint->listUsers();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(1, count($users));
  }

  public function testCreateTopic()
  {
    try{
      self::$topic = self::$viewpoint->createTopic();
      self::$childTopic = self::$viewpoint->createTopic(self::$topic);
      self::$otherTopic = self::$viewpoint->createTopic();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
  }

  /**
   * @depends testCreateTopic
   */
  public function testGetTopics()
  {
    try{
      $topics = self::$viewpoint->getTopics();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(3, count($topics));
  }

  /**
   * @depends testCreateTopic
   */
  public function testGetUpperTopics()
  {
    try{
      $topics = self::$viewpoint->getUpperTopics();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(2, count($topics));
  }

  public function testGetItems()
  {
    try{
      self::$item->tag(self::$topic);
      $items = self::$viewpoint->getItems();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(1, count($items));
  }
}