<?php
require_once 'agorae/libs/RESTDatabase.php';

class RESTDatabaseTest extends PHPUnit_Framework_TestCase
{
  private static $db;

  public static function setUpBeforeClass()
  {
    self::$db = new RESTDatabase("http://192.168.1.141:5984/argos/_design/argos/_rewrite/");
  }

  public static function tearDownAfterClass()
  {
  }

  public function testPost()
  {
    try{
      $obj = new stdClass();
      $obj->name = "chao";
      $obj->rows = array();
      $row = new stdClass();
      $row->key = array("key0", "key1");
      $row->value = array("attribute0" => "value0");
      array_push($obj->rows, $row);

      $row = new stdClass();
      $row->key = array("key0", "key1");
      $row->value = array("attribute0" => "value1");
      array_push($obj->rows, $row);

      $obj = self::$db->post($obj);
      $this->assertObjectHasAttribute("_id", $obj);
      $this->assertObjectHasAttribute("name", $obj);
      return $obj;
    }catch(Exception $e){
      $this->fail($e->getMessage());
      exit;
    }
  }

  /**
   * @depends testPost
   */
  public function testPut($obj)
  {
    try{
      $obj->name = "updated";
      $result = self::$db->put($obj);
      $this->assertObjectHasAttribute("_id", $result);
      $this->assertEquals("updated", $result->name);
      return $result;
    }catch(Exception $e){
      $this->fail($e->getMessage());
    }
  }

  /**
   * @depends testPost
   */
  public function testGet($obj)
  {
    try{
      $result = self::$db->get($obj->_id);
      $this->assertObjectHasAttribute("key0", $result);
    }catch(Exception $e){
      $this->fail($e->getMessage());
    }
  }

  /**
   * @depends testPut
   */
  public function testDelete($obj)
  {
    try{
      $result = self::$db->delete($obj);
      $this->assertObjectHasAttribute("ok", $result);
    }catch(Exception $e){
      $this->fail($e->getMessage());
    }
  }
}
?>