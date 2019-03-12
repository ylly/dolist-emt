<?php

namespace YllyDoListEMT;

class DolistEMT
{

    const WSDL_CLIENT = "http://api.emt.dolist.net/V3/AuthenticationService.svc?wsdl";
    const WSDL_MESSAGE = "http://api.emt.dolist.net/V3/MessageService.svc?wsdl";

    const SOAP_CLIENT = "http://api.emt.dolist.net/V3/AuthenticationService.svc/soap1.1";
    const SOAP_MESSAGE = "http://api.emt.dolist.net/V3/MessageService.svc/soap1.1";

    private $accountId;

    private $authenticationKey;

    private $debug;

    private $token;

    /**
     * @param int $accountId
     * @param string $authenticationKey
     * @param boolean $debug
     */
    public function __construct($accountId, $authenticationKey, $debug)
    {
        $this->accountId = $accountId;
        $this->authenticationKey = $authenticationKey;
        $this->debug = $debug;
    }

    /**
     * @return array
     */
    public function connectDoList()
    {
        $soapClient = new \SoapClient(self::WSDL_CLIENT, array("trace" => $this->debug, "location" => self::SOAP_CLIENT));

        $params = array(
            "AuthenticationKey" => $this->authenticationKey,
            "AccountID" => $this->accountId
        );
        $result = $soapClient->GetAuthenticationToken(array("authenticationRequest" => $params));

        $this->token = array(
            'AccountID' => $this->accountId,
            'Key' => $result->GetAuthenticationTokenResult->Key
        );

        return $this->token;
    }

    /**
     * Send mail by template
     *
     * @param int $type TemplateID
     * @param array $attachements
     * @param array $datas
     * @param string $recipient
     * @param string $contentType
     * @return string Message ID
     */
    public function sendEmail($type, $attachements, $datas, $recipient, $contentType)
    {
        if ($this->token === null) {
            $this->connectDoList();
        }

        $soapMessage = new \SoapClient(
            self::WSDL_MESSAGE,
            array(
                "trace" => $this->debug,
                "location" => self::SOAP_MESSAGE
            )
        );

        $message = array(
            'Attachments' => $attachements,
            'Data' => $datas,
            'IsTest' => $this->debug,
            'MessageContentType'=> $contentType,
            'Recipient'=> $recipient,
            'TemplateID' => $type
        );

        return $soapMessage->SendMessage(array("token" => $this->token, "message" => $message));
    }

}