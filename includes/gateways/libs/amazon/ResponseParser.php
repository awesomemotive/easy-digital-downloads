<?php
namespace PayWithAmazon;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* ResponseParser
 * Methods provided to convert the Response from the POST to XML, Array or JSON
 */

require_once 'Interface.php';

class ResponseParser implements ResponseInterface
{
    public $response = null;

    public function __construct($response=null)
    {
        $this->response = $response;
    }

    /* Returns the XML portion of the response */

    public function toXml()
    {
        return $this->response['ResponseBody'];
    }

    /* toJson - converts XML into Json
     * @param $response [XML]
     */

    public function toJson()
    {
        $response = $this->simpleXmlObject();

        return (json_encode($response));
    }

    /* toArray - converts XML into associative array
     * @param $this->response [XML]
     */

    public function toArray()
    {
        $response = $this->simpleXmlObject();

        // Converting the SimpleXMLElement Object to array()
        $response = json_encode($response);

        return (json_decode($response, true));
    }

    private function simpleXmlObject()
    {
        $response = $this->response;

        // Getting the HttpResponse Status code to the output as a string
        $status = strval($response['Status']);

        // Getting the Simple XML element object of the XML Response Body
        $response = simplexml_load_string((string) $response['ResponseBody']);

        // Adding the HttpResponse Status code to the output as a string
        $response->addChild('ResponseStatus', $status);

        return $response;
    }

    /* Get the status of the BillingAgreement */

    public function getBillingAgreementDetailsStatus($response)
    {
       $data= new \SimpleXMLElement($response);
        $namespaces = $data->getNamespaces(true);
        foreach($namespaces as $key=>$value){
            $namespace = $value;
        }
        $data->registerXPathNamespace('GetBA', $namespace);
        foreach ($data->xpath('//GetBA:BillingAgreementStatus') as $value) {
            $baStatus = json_decode(json_encode((array)$value), TRUE);
        }

        return $baStatus ;
    }
}
