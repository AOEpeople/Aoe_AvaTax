<?php

/**
 * AvaCert2Soap class
 * 
 *  
 * 
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   AvaCert2
 */

namespace Avatax\AvaCert2;

use Avatax\AvalaraSoapClient;
use Avatax\ATConfig;
use Avatax\DynamicSoapClient;

class AvaCert2Soap extends AvalaraSoapClient {

    static $servicePath = '/AvaCert2/AvaCert2Svc.asmx';
    private static $classmap = array(
        'CustomerSave' => 'Avatax\AvaCert2\CustomerSave',
        'CustomerSaveRequest' => 'Avatax\AvaCert2\CustomerSaveRequest',
        'Customer' => 'Avatax\AvaCert2\Customer',
        'Certificate' => 'Avatax\AvaCert2\Certificate',
        'CertificateStatus' => 'Avatax\AvaCert2\CertificateStatus',
        'ReviewStatus' => 'Avatax\AvaCert2\ReviewStatus',
        'CertificateUsage' => 'Avatax\AvaCert2\CertificateUsage',
        'CertificateJurisdiction' => 'Avatax\AvaCert2\CertificateJurisdiction',
        'CustomerSaveResult' => 'Avatax\AvaCert2\CustomerSaveResult',
        'Profile' => 'Avatax\AvaCert2\Profile',
        'CertificateRequestInitiate' => 'Avatax\AvaCert2\CertificateRequestInitiate',
        'CertificateRequestInitiateRequest' => 'Avatax\AvaCert2\CertificateRequestInitiateRequest',
        'CertificateRequestInitiateResult' => 'Avatax\AvaCert2\CertificateRequestInitiateResult',
        'CertificateGet' => 'Avatax\AvaCert2\CertificateGet',
        'CertificateGetRequest' => 'Avatax\AvaCert2\CertificateGetRequest',
        'CommunicationMode' => 'Avatax\AvaCert2\CommunicationMode',
        'CertificateGetResult' => 'Avatax\AvaCert2\CertificateGetResult',
        'CertificateRequestGet' => 'Avatax\AvaCert2\CertificateRequestGet',
        'CertificateRequestGetRequest' => 'Avatax\AvaCert2\CertificateRequestGetRequest',
        'CertificateRequestGetResult' => 'Avatax\AvaCert2\CertificateRequestGetResult',
        'CertificateRequest' => 'Avatax\AvaCert2\CertificateRequest',
        'CertificateRequestStatus' => 'Avatax\AvaCert2\CertificateRequestStatus',
        'CertificateRequestStage' => 'Avatax\AvaCert2\CertificateRequestStage',
        'CertificateImageGet' => 'Avatax\AvaCert2\CertificateImageGet',
        'CertificateImageGetRequest' => 'Avatax\AvaCert2\CertificateImageGetRequest',
        'FormatType' => 'Avatax\AvaCert2\FormatType',
        'CertificateImageGetResult' => 'Avatax\AvaCert2\CertificateImageGetResult',
        'BaseRequest' => 'Avatax\AvaCert2\BaseRequest',
        'RequestType' => 'Avatax\AvaCert2\RequestType',
        'BaseResult' => 'Avatax\AvaCert2\BaseResult',
        'SeverityLevel' => 'Avatax\AvaCert2\SeverityLevel',
        'Message' => 'Avatax\AvaCert2\Message',
        'Ping' => 'Avatax\AvaCert2\Ping',
        'PingResult' => 'Avatax\AvaCert2\PingResult',
        'IsAuthorized' => 'Avatax\AvaCert2\IsAuthorized',
        'IsAuthorizedResult' => 'Avatax\AvaCert2\IsAuthorizedResult',
    );

    public function __construct($configurationName = 'Default') {
        $config = new ATConfig($configurationName);

        $this->client = new DynamicSoapClient(
                $config->avacert2WSDL, array
            (
            'location' => $config->url . $config->avacert2Service,
            'trace' => $config->trace,
            'classmap' => AvaCert2Soap::$classmap
                ), $config
        );
    }

    /**
     * This method adds an exempt customer record to AvaCert.
     *
     * <pre>
     * $customer = new Customer();
     * $customer->setCompanyCode("DEFAULT");
     * $customer->setCustomerCode("AVALARA");
     * $customer->setBusinessName("Avalara, Inc.");
     * $customer->setAddress1("435 Ericksen Ave NE");
     * $customer->setCity("Bainbridge Island");
     * $customer->setState("WA");
     * $customer->setZip("98110");
     * $customer->setCountry("US");
     * $customer->setEmail("info@avalara.com");
     * $customer->setPhone("206-826-4900");
     * $customer->setFax("206-780-5011");
     * $customer->setType("Bill_To");
     *
     * $customerSaveRequest = new CustomerSaveRequest();
     * $customerSaveRequest->setCustomer($customer);
     * 
     * $customerSaveResult= $avacert2Service->customerSave($customerSaveRequest);
     * </pre> 
     *
     * @param CustomerSave $customerSaveRequest
     * @return CustomerSaveResult
     */
    public function CustomerSave(CustomerSaveRequest $customerSaveRequest) {
        return $this->client->CustomerSave(array('CustomerSaveRequest' => $customerSaveRequest))->CustomerSaveResult;
    }

    /**
     * This method initiates a request from AvaCert to the customer for an exemption certificate.
     * The request will be sent using the designated method (email, fax, post).
     *
     * <pre>
     * $certificateRequestInitiateRequest=new CertificateRequestInitiateRequest();
     * $certificateRequestInitiateRequest->setCompanyCode("DEFAULT");
     * $certificateRequestInitiateRequest->setCustomerCode("AVALARA");
     * $certificateRequestInitiateRequest->setCommunicationMode(CommunicationMode::$EMAIL);
     * $certificateRequestInitiateRequest->setCustomMessage("Thank you!");
     *
     * $certificateRequestInitiateResult= $avacert2Service->certificateRequestInitiate($certificateRequestInitiateRequest); 
     * </pre>
     * 
     * @param CertificateRequestInitiate $certificateRequestInitiateRequest
     * @return CertificateRequestInitiateResult
     */
    public function CertificateRequestInitiate(CertificateRequestInitiateRequest $certificateRequestInitiateRequest) {
        return $this->client->CertificateRequestInitiate(array('CertificateRequestInitiateRequest' => $certificateRequestInitiateRequest))->CertificateRequestInitiateResult;
    }

    /**
     * This method retrieves all certificates from vCert for a particular customer. 
     * 
     * <pre>
     * $certificateGetRequest=new CertificateGetRequest();
     * $certificateGetRequest->setCompanyCode("DEFAULT");
     * $certificateGetRequest->setCustomerCode("AVALARA");
     *
     * $certificateGetResult= $avacert2Service->certificateGet($certificateGetRequest); 
     * </pre>
     * 
     * @param CertificateGet $certificateGetRequest
     * @return CertificateGetResult
     */
    public function CertificateGet(CertificateGetRequest $certificateGetRequest) {
        return $this->client->CertificateGet(array('CertificateGetRequest' => $certificateGetRequest))->CertificateGetResult;
    }

    /**
     * This method retrieves all certificate requests from vCert for a particular customer. 
     * 
     * <pre>
     * $certificateRequestGetRequest=new CertificateRequestGetRequest();
     * $certificateRequestGetRequest->setCompanyCode("DEFAULT");
     * $certificateRequestGetRequest->setCustomerCode("AVALARA");
     * $certificateRequestGetRequest->setRequestStatus(CertificateRequestStatus::$OPEN);
     *
     * $certificateRequestGetResult= $avacert2Service->certificateRequestGet($certificateRequestGetRequest); 
     * </pre> 
     *
     * @param CertificateRequestGet $certificateRequestGetRequest
     * @return CertificateRequestGetResult
     */
    public function CertificateRequestGet(CertificateRequestGetRequest $certificateRequestGetRequest) {
        return $this->client->CertificateRequestGet(array('CertificateRequestGetRequest' => $certificateRequestGetRequest))->CertificateRequestGetResult;
    }

    /**
     * This method retrieves all certificate requests from vCert for a particular customer. 
     * 
     * <pre>
     * $certificateImageGetRequest=new CertificateImageGetRequest();
     * $certificateImageGetRequest->setCompanyCode("DEFAULT");
     * $certificateImageGetRequest->setAvaCertId("CBSK");
     * $certificateImageGetRequest->setFormat(FormatType::$PNG);
     * $certificateImageGetRequest->setPageNumber(1);
     *
     * $certificateImageGetResult= $avacert2Service->certificateImageGet($certificateImageGetRequest); 
     * </pre>  
     *
     * @param CertificateImageGet $certificateImageGetRequest
     * @return CertificateImageGetResult
     */
    public function CertificateImageGet(CertificateImageGetRequest $certificateImageGetRequest) {
        return $this->client->CertificateImageGet(array('CertificateImageGetRequest' => $certificateImageGetRequest))->CertificateImageGetResult;
    }

    /**
     * Verifies connectivity to the web service and returns version information about the service. 
     *
     * @param Ping $message
     * @return PingResult
     */
    public function Ping($message = '') {
        return $this->client->Ping(array('Message' => $message))->PingResult;
    }

    /**
     * Checks authentication of and authorization to one or more operations on the service.
     * <p>
     * This operation allows pre-authorization checking of any or all operations.
     * It will return a comma delimited set of operation names which will be all or a subset
     * of the requested operation names.  For security, it will never return operation names
     * other than those requested, i.e. protects against phishing.
     * </p>
     * <b>Example:</b><br>
     * <code> isAuthorized("CustomerSave,CertificateRequestInitiate")</code>
     * @param IsAuthorized $operations
     * @return IsAuthorizedResult
     */
    public function IsAuthorized(IsAuthorized $operations) {
        return $this->client->IsAuthorized(array('Operations' => $operations))->IsAuthorizedResult;
    }

}
