<?php
require_once 'Object/HypertopicMap.php';

class CorpusTest extends PHPUnit_Framework_TestCase
{
  private $map;

  private $user;
  private $corpus;

  protected function setUp()
  {
    $this->map = new HypertopicMap("http://192.168.1.141:5984/argos/");
  }

  protected function tearDown()
  {
  }

  public function testRegister()
  {

    $this->corpus->register($this->map->getUser("him"));
    $this->assertEquals(2, count($this->corpus->listUsers()));
  }
}