<?php
/**
 * BatchFileSaveResult.class.php
 */

/**
 *
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
namespace Avatax\Batch;
class BatchFileSaveResult extends \Avatax\BaseResult {
  private $BatchFileId; // int

  public function setBatchFileId($value){$this->BatchFileId=$value;} // int
  public function getBatchFileId(){return $this->BatchFileId;} // int

}
