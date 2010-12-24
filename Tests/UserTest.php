<?php
require_once 'agorae/libs/HypertopicMap.php';

class UserTest extends PHPUnit_Framework_TestCase
{
  private static $map;

  private static $user;
  private static $corpus;
  private static $viewpoint;

  public static function setUpBeforeClass()
  {
    self::$map = new HypertopicMap("http://192.168.1.141:5984/argos/_design/argos/_rewrite/");
    self::$user = self::$map->getUser("me");
  }

  public static function tearDownAfterClass()
  {
    if(isset(self::$corpus))
      self::$corpus->destroy();
    if(isset(self::$viewpoint))
      self::$viewpoint->destroy();
  }

  public function testCreateCorpus()
  {
    try{
      self::$corpus = self::$user->createCorpus('my corpus');
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertTrue(TRUE);
  }

  /**
   * @depends testCreateCorpus
   */
  public function testListCorpora()
  {
    try{
      $corpora = self::$user->listCorpora();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertGreaterThan(0, count($corpora));
  }

  public function testCreateViewpoint()
  {
    try{
      self::$viewpoint = self::$user->createViewpoint('my viewpoint');
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertTrue(TRUE);
  }

  /**
   * @depends testCreateViewpoint
   */
  public function testListViewpoints()
  {
    try{
      $viewpoints = self::$user->listViewpoints();
    }catch(Exception $e){
      $this->fail($e->getMessage());
      return;
    }
    $this->assertGreaterThan(0, count($viewpoints));
  }

}