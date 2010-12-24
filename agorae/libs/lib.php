<?php
/**
 * Project: Agorae V2
 * Author: Chao ZHOU <chao@zhou.fr>
 * File: lib.php
 * Version: 2.0.1
 */

/**
 * Agorae application library
 *
 */
class Agorae {
  // smarty template object
  var $tpl = null;
  // error messages
  var $error = null;

  function __construct() {
    // instantiate the template object
    $this->tpl = new Agorae_Smarty;

  }

  function displayFrontPage(){
    $this->tpl->assign('name', 'world');
    $this->tpl->display('index.tpl');
  }
}