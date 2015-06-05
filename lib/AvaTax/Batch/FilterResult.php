<?php
/**
 * FilterResult.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
namespace Avatax\Batch;
class FilterResult {
  private $Count; // int

  public function setCount($value){$this->Count=$value;} // int
  public function getCount(){return $this->Count;} // int

}
