<?php
require_once 'Object/HypertopicMap.php';

class ItemTest extends PHPUnit_Framework_TestCase
{
  private static $map;

  private static $user;
  private static $corpus;
  private static $item;
  private static $viewpoint;
  private static $topic;
  private static $otherTopic;

  public static function setUpBeforeClass()
  {
    self::$map = new HypertopicMap("http://192.168.1.141:5984/argos/_design/argos/_rewrite/");
    self::$user = self::$map->getUser("me");
    self::$corpus = self::$user->createCorpus("my corpus");
    self::$item = self::$corpus->createItem("my item");
    self::$viewpoint = self::$user->createViewpoint("my viewpoint");
    self::$topic = self::$viewpoint->createTopic();
    self::$otherTopic = self::$viewpoint->createTopic();
  }

  public static function tearDownAfterClass()
  {
    /*if(isset(self::$corpus))
      self::$corpus->destroy();*/
  }

  public function testGetCorpusID()
  {
    $corpusID = self::$corpus->getID();
    try{
      $this->assertEquals($corpusID, self::$item->getCorpusID());
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
  }

  public function testRename()
  {
    try{
      self::$item->rename("new name");
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
      $name = self::$item->getName();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals($name, "new name");
  }

  public function testDescribe()
  {
    try{
      self::$item->describe("resource", "http://example.com/document/");
      self::$item->describe("author", "chao");
      self::$item->describe("author", "aurelien");
      self::$item->describe("date", "2010-11-22");
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(2, count(self::$item->getAttributes()));
  }
  /**
   * @depends testDescribe
   */
  public function testUndescribe()
  {
    try{
      self::$item->undescribe("author", "chao");
      self::$item->undescribe("date", "2010-11-22");
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(1, count(self::$item->getAttributes()));
  }
    /**
   * @depends testDescribe
   */
  public function testGetResource()
  {
    try{
      $this->assertEquals(self::$item->getResource(), "http://example.com/document/");
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
  }

  public function testTag()
  {
    try{
      self::$item->tag(self::$topic);
      self::$item->tag(self::$otherTopic);
      j(self::$item->getRaw());
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    //$this->assertEquals(2, count(self::$item->getTopics()));
  }
  /**
   * @depends testTag
   */
  public function testUntag()
  {
    try{
      self::$item->untag(self::$topic);
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    //$this->assertEquals(1, count(self::$item->getTopics()));
  }
}