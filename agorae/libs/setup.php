<?php
/**
 * Project: Agorae V2
 * Author: Chao ZHOU <chao@zhou.fr>
 * File: setup.php
 * Version: 2.0.1
 */

require(AGORAE_DIR . 'libs/lib.php');
require(SMARTY_DIR . 'Smarty.class.php');

// smarty configuration
class Agorae_Smarty extends Smarty {
    function __construct() {
      parent::__construct();
      $this->template_dir = AGORAE_DIR . 'templates';
      $this->compile_dir = AGORAE_DIR . 'templates_c';
      $this->config_dir = AGORAE_DIR . 'configs';
      $this->cache_dir = AGORAE_DIR . 'cache';
    }
}
?>
