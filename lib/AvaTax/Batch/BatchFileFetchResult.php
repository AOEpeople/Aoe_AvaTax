<?php
/**
 * BatchFileFetchResult.class.php
 */

/**
 *
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */
namespace Avatax\Batch;
class BatchFileFetchResult extends \Avatax\BaseResult {
  private $BatchFiles; // ArrayOfBatchFile
  private $RecordCount; // int

  public function setBatchFiles($value){$this->BatchFiles=$value;} // ArrayOfBatchFile
  public function getBatchFiles(){return $this->BatchFiles;} // ArrayOfBatchFile

  public function setRecordCount($value){$this->RecordCount=$value;} // int
  public function getRecordCount(){return $this->RecordCount;} // int

}
