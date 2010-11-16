<?php
require_once 'Object/RESTDatabase.php';

class RESTDatabaseTest extends PHPUnit_Framework_TestCase
{
  private $db;

  protected function setUp()
  {
    $this->db = new RESTDatabase("http://192.168.1.141:5984/argos/");
    print __METHOD__ . "\n";
  }

  protected function tearDown()
  {
    print __METHOD__ . "\n";
  }

  public function testPost()
  {
    try{
      $obj = new stdClass();
      $obj->name = "chao";
      $obj = $this->db->post($obj);
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
      $result = $this->db->put($obj);
      $this->assertObjectHasAttribute("_id", $result);
      $this->assertEquals("updated", $result->name);
      return $result;
    }catch(Exception $e){
      $this->fail($e->getMessage());
    }
  }

  /**
   * @depends testPut
   */
  public function testGet($obj)
  {
    try{
      $result = $this->db->get($obj->_id);
      $this->assertObjectHasAttribute("_id", $result);
      $this->assertObjectHasAttribute("name", $result);
      return $obj;
    }catch(Exception $e){
      $this->fail($e->getMessage());
    }
  }

  /**
   * @depends testGet
   */
  public function testDelete($obj)
  {
    try{
      $result = $this->db->delete($obj);
      $this->assertObjectHasAttribute("ok", $result);
    }catch(Exception $e){
      $this->fail($e->getMessage());
    }
  }
}
?>