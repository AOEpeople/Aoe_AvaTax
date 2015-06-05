<?php
/**
 * BatchSvc.class.php
 */

/**
 * 
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Batch
 */

namespace Avatax\Batch;
use Avatax\ATConfig;
use Avatax\DynamicSoapClient;
class BatchSvc extends \SoapClient {

  	private $client;
	private static $classmap = array(
                                    'BatchFetch' => 'Avatax\Batch\BatchFetch',
                                    'FetchRequest' => 'Avatax\Batch\FetchRequest',
                                    'BatchFetchResponse' => 'Avatax\Batch\BatchFetchResponse',
                                    'BatchFetchResult' => 'Avatax\Batch\BatchFetchResult',
                                    'BaseResult' => 'Avatax\Batch\BaseResult',
                                    'SeverityLevel' => 'Avatax\Batch\SeverityLevel',
                                    'Message' => 'Avatax\Batch\Message',
                                    'Batch' => 'Avatax\Batch\Batch',
                                    'BatchFile' => 'Avatax\Batch\BatchFile',
                                    'Profile' => 'Avatax\Batch\Profile',
                                    'BatchSave' => 'Avatax\Batch\BatchSave',
                                    'BatchSaveResponse' => 'Avatax\Batch\BatchSaveResponse',
                                    'BatchSaveResult' => 'Avatax\Batch\BatchSaveResult',
                                    'AuditMessage' => 'Avatax\Batch\AuditMessage',
                                    'BatchDelete' => 'Avatax\Batch\BatchDelete',
                                    'DeleteRequest' => 'Avatax\Batch\DeleteRequest',
                                    'FilterRequest' => 'Avatax\Batch\FilterRequest',
                                    'BatchDeleteResponse' => 'Avatax\Batch\BatchDeleteResponse',
                                    'DeleteResult' => 'Avatax\Batch\DeleteResult',
                                    'FilterResult' => 'Avatax\Batch\FilterResult',
                                    'BatchProcess' => 'Avatax\Batch\BatchProcess',
                                    'BatchProcessRequest' => 'Avatax\Batch\BatchProcessRequest',
                                    'BatchProcessResponse' => 'Avatax\Batch\BatchProcessResponse',
                                    'BatchProcessResult' => 'Avatax\Batch\BatchProcessResult',
                                    'BatchFileFetch' => 'Avatax\Batch\BatchFileFetch',
                                    'BatchFileFetchResponse' => 'Avatax\Batch\BatchFileFetchResponse',
                                    'BatchFileFetchResult' => 'Avatax\Batch\BatchFileFetchResult',
                                    'BatchFileSave' => 'Avatax\Batch\BatchFileSave',
                                    'BatchFileSaveResponse' => 'Avatax\Batch\BatchFileSaveResponse',
                                    'BatchFileSaveResult' => 'Avatax\Batch\BatchFileSaveResult',
                                    'BatchFileDelete' => 'Avatax\Batch\BatchFileDelete',
                                    'BatchFileDeleteResponse' => 'Avatax\Batch\BatchFileDeleteResponse',
                                    'Ping' => 'Avatax\Batch\Ping',
                                    'PingResponse' => 'Avatax\Batch\PingResponse',
                                    'PingResult' => 'Avatax\Batch\PingResult',
                                    'IsAuthorized' => 'Avatax\Batch\IsAuthorized',
                                    'IsAuthorizedResponse' => 'Avatax\Batch\IsAuthorizedResponse',
                                    'IsAuthorizedResult' => 'Avatax\Batch\IsAuthorizedResult',
                                   );

	public function __construct($configurationName = 'Default')
    {
        $config = new ATConfig($configurationName);
        
        $this->client = new DynamicSoapClient   (
            $config->batchWSDL,
            array
            (
                'location' => $config->url.$config->batchService, 
                'trace' => $config->trace,
                'classmap' => BatchSvc::$classmap
            ), 
            $config
        );
    }    

  /**
   * Fetches one or more Batch 
   *
   * @param BatchFetch $parameters
   * @return BatchFetchResponse
   */  
    public function BatchFetch(&$fetchRequest) {    
      
      return $this->client->BatchFetch(array('FetchRequest' => $fetchRequest))->getBatchFetchResult();
  }

  /**
   * Saves a Batch entry 
   *
   * @param BatchSave $parameters
   * @return BatchSaveResponse
   */
  public function BatchSave(&$batch) {
   	
  	return $this->client->BatchSave(array('Batch' => $batch))->getBatchSaveResult();
  	
  }

  /**
   * Deletes one or more Batches 
   *
   * @param BatchDelete $parameters
   * @return BatchDeleteResponse
   */
  public function BatchDelete(&$deleteRequest) {
     	
  	return $this->client->BatchDelete(array('DeleteRequest' => $deleteRequest))->getBatchDeleteResult();
  	
  }

  /**
   * Processes one or more Batches 
   *
   * @param BatchProcess $parameters
   * @return BatchProcessResponse
   */
  public function BatchProcess(&$batchProcessRequest) {
    
  	return $this->client->BatchProcess(array('BatchProcessRequest' => $batchProcessRequest))->getBatchProcessResult();
  	
  }

  /**
   * Fetches one or more BatchFiles 
   *
   * @param BatchFileFetch $parameters
   * @return BatchFileFetchResponse
   */
  public function BatchFileFetch(&$fetchRequest) {
  	
  	return $this->client->BatchFileFetch(array('FetchRequest' => $fetchRequest))->getBatchFileFetchResult();
    
  }

  /**
   * Saves a Batch File 
   *
   * @param BatchFileSave $parameters
   * @return BatchFileSaveResponse
   */
  public function BatchFileSave(&$batchFile) {   
  	
  	return $this->client->BatchFileSave(array('BatchFile' => $batchFile))->getBatchFileSaveResult();
  	
  }

  /**
   * Deletes one or more BatchFiles 
   *
   * @param BatchFileDelete $parameters
   * @return BatchFileDeleteResponse
   */
  public function BatchFileDelete(&$deleteRequest) {
    
  	return $this->client->BatchFileDelete(array('DeleteRequest' => $deleteRequest))->getBatchFileDeleteResult();
  	
  }

  /**
   * Tests connectivity and version of the service 
   *
   * @param Ping $message
   * @return PingResponse
   */
  public function Ping($message = '') {    
      return $this->client->Ping(array('Message' => $message))->getPingResult();
  }

  /**
   * Checks authentication and authorization to one or more operations on the service. 
   *
   * @param IsAuthorized $operations
   * @return IsAuthorizedResponse
   */
public function IsAuthorized($operations)
    {
        return $this->client->IsAuthorized(array('Operations' => $operations))->getIsAuthorizedResult();
    }

}
