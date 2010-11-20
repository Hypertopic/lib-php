<?php
require_once 'Object/HypertopicMap.php';

class CorpusTest extends PHPUnit_Framework_TestCase
{
  private static $map;

  private static $user;
  private static $corpus;
  private static $item;

  public static function setUpBeforeClass()
  {
    self::$map = new HypertopicMap("http://192.168.1.141:5984/argos/_design/argos/_rewrite/");
    self::$user = self::$map->getUser("me");
    self::$corpus = self::$user->createCorpus("my corpus");
  }

  public static function tearDownAfterClass()
  {
    if(isset(self::$corpus))
      self::$corpus->destroy();
  }

  public function testRegister()
  {
    try{
      self::$corpus->register(self::$map->getUser("him"));
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
      self::$corpus->unregister(self::$map->getUser("him"));
      $users = self::$corpus->listUsers();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(1, count($users));
  }

  public function testRename()
  {
    try{
      self::$corpus->rename("new name");
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
      $name = self::$corpus->getName();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals($name, "new name");
  }

  public function testCreateItem()
  {
    try{
      self::$item = self::$corpus->createItem('my item');
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertTrue(TRUE);
  }
  /**
   * @depends testCreateItem
   */
  public function testGetItems()
  {
    try{
      $items = self::$corpus->getItems();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertEquals(count($items), 1);
  }

}