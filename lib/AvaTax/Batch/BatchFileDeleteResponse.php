<?php
/**
 * BatchFileDeleteResponse.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
namespace Avatax\Batch;
class BatchFileDeleteResponse {
  private $BatchFileDeleteResult; // DeleteResult

  public function setBatchFileDeleteResult($value){$this->BatchFileDeleteResult=$value;} // DeleteResult
  public function getBatchFileDeleteResult(){return $this->BatchFileDeleteResult;} // DeleteResult

}
