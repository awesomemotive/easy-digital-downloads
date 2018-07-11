<?php
namespace PayWithAmazon;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/* Class IPN_Handler
 * Takes headers and body of the IPN message as input in the constructor
 * verifies that the IPN is from the right resource and has the valid data
 */

require_once 'HttpCurl.php';
require_once 'Interface.php';
class IpnHandler implements IpnHandlerInterface
{

    private $headers = null;
    private $body = null;
    private $snsMessage = null;
    private $fields = array();
    private $signatureFields = array();
    private $certificate = null;
    private $expectedCnName = 'sns.amazonaws.com';

    private $ipnConfig = array('cabundle_file'  => null,
			       'proxy_host' 	=> null,
                               'proxy_port' 	=> -1,
                               'proxy_username' => null,
			       'proxy_password' => null);


    public function __construct($headers, $body, $ipnConfig = null)
    {
        $this->headers = array_change_key_case($headers, CASE_LOWER);
        $this->body = $body;

        if ($ipnConfig != null) {
            $this->checkConfigKeys($ipnConfig);
        }

        // Get the list of fields that we are interested in
        $this->fields = array(
            "Timestamp" => true,
            "Message" => true,
            "MessageId" => true,
            "Subject" => false,
            "TopicArn" => true,
            "Type" => true
        );

        // Validate the IPN message header [x-amz-sns-message-type]
        $this->validateHeaders();

        // Converts the IPN [Message] to Notification object
        $this->getMessage();

        // Checks if the notification [Type] is Notification and constructs the signature fields
        $this->checkForCorrectMessageType();

        // Verifies the signature against the provided pem file in the IPN
        $this->constructAndVerifySignature();
    }

    private function checkConfigKeys($ipnConfig)
    {
        $ipnConfig = array_change_key_case($ipnConfig, CASE_LOWER);
	$ipnConfig = trimArray($ipnConfig);

        foreach ($ipnConfig as $key => $value) {
            if (array_key_exists($key, $this->ipnConfig)) {
                $this->ipnConfig[$key] = $value;
            } else {
                throw new \Exception('Key ' . $key . ' is either not part of the configuration or has incorrect Key name.
				check the ipnConfig array key names to match your key names of your config array ', 1);
            }
        }
    }

    /* Setter function
     * Sets the value for the key if the key exists in ipnConfig
     */

    public function __set($name, $value)
    {
        if (array_key_exists(strtolower($name), $this->ipnConfig)) {
            $this->ipnConfig[$name] = $value;
        } else {
            throw new \Exception("Key " . $name . " is not part of the configuration", 1);
        }
    }

    /* Getter function
     * Returns the value for the key if the key exists in ipnConfig
     */

    public function __get($name)
    {
        if (array_key_exists(strtolower($name), $this->ipnConfig)) {
            return $this->ipnConfig[$name];
        } else {
            throw new \Exception("Key " . $name . " was not found in the configuration", 1);
        }
    }

    /* Trim the input Array key values */

    private function trimArray($array)
    {
	foreach ($array as $key => $value)
	{
	    $array[$key] = trim($value);
	}
	return $array;
    }

    private function validateHeaders()
    {
        // Quickly check that this is a sns message
        if (!array_key_exists('x-amz-sns-message-type', $this->headers)) {
            throw new \Exception("Error with message - header " . "does not contain x-amz-sns-message-type header");
        }

        if ($this->headers['x-amz-sns-message-type'] !== 'Notification') {
            throw new \Exception("Error with message - header x-amz-sns-message-type is not " . "Notification, is " . $this->headers['x-amz-sns-message-type']);
        }
    }

    private function getMessage()
    {
        $this->snsMessage = json_decode($this->body, true);

        $json_error = json_last_error();

        if ($json_error != 0) {
            $errorMsg = "Error with message - content is not in json format" . $this->getErrorMessageForJsonError($json_error) . " " . $this->snsMessage;
            throw new \Exception($errorMsg);
        }
    }

    /* Convert a json error code to a descriptive error message
     *
     * @param int $json_error message code
     *
     * @return string error message
     */

    private function getErrorMessageForJsonError($json_error)
    {
        switch ($json_error) {
            case JSON_ERROR_DEPTH:
                return " - maximum stack depth exceeded.";
                break;
            case JSON_ERROR_STATE_MISMATCH:
                return " - invalid or malformed JSON.";
                break;
            case JSON_ERROR_CTRL_CHAR:
                return " - control character error.";
                break;
            case JSON_ERROR_SYNTAX:
                return " - syntax error.";
                break;
            default:
                return ".";
                break;
        }
    }

    /* checkForCorrectMessageType()
     *
     * Checks if the Field [Type] is set to ['Notification']
     * Gets the value for the fields marked true in the fields array
     * Constructs the signature string
     */

    private function checkForCorrectMessageType()
    {
        $type = $this->getMandatoryField("Type");
        if (strcasecmp($type, "Notification") != 0) {
            throw new \Exception("Error with SNS Notification - unexpected message with Type of " . $type);
        }

        if (strcmp($this->getMandatoryField("Type"), "Notification") != 0) {
            throw new \Exception("Error with signature verification - unable to verify " . $this->getMandatoryField("Type") . " message");
        } else {

            // Sort the fields into byte order based on the key name(A-Za-z)
            ksort($this->fields);

            // Extract the key value pairs and sort in byte order
            $signatureFields = array();
            foreach ($this->fields as $fieldName => $mandatoryField) {
                if ($mandatoryField) {
                    $value = $this->getMandatoryField($fieldName);
                } else {
                    $value = $this->getField($fieldName);
                }

                if (!is_null($value)) {
                    array_push($signatureFields, $fieldName);
                    array_push($signatureFields, $value);
                }
            }

            /* Create the signature string - key / value in byte order
             * delimited by newline character + ending with a new line character
             */
            $this->signatureFields = implode("\n", $signatureFields) . "\n";

        }
    }

    /* Verify that the signature is correct for the given data and
     * public key
     *
     * @param string $data            data to validate
     * @param string $signature       decoded signature to compare against
     * @param string $certificatePath path to certificate, can be file or url
     *
     * @throws Exception if there is an error with the call
     *
     * @return bool true if valid
     */

    private function constructAndVerifySignature()
    {
	$signature       = base64_decode($this->getMandatoryField("Signature"));
        $certificatePath = $this->getMandatoryField("SigningCertURL");

        $this->certificate = $this->getCertificate($certificatePath);

        $result = $this->verifySignatureIsCorrectFromCertificate($signature);
        if (!$result) {
            throw new \Exception("Unable to match signature from remote server: signature of " . $this->getCertificate($certificatePath) . " , SigningCertURL of " . $this->getMandatoryField("SigningCertURL") . " , SignatureOf " . $this->getMandatoryField("Signature"));
        }
    }

    /* getCertificate($certificatePath)
     *
     * gets the certificate from the $certificatePath using Curl
     */

    private function getCertificate($certificatePath)
    {
        $httpCurlRequest  = new HttpCurl($this->ipnConfig);

	$response = $httpCurlRequest->httpGet($certificatePath);

        return $response;
    }

    /* Verify that the signature is correct for the given data and public key
     *
     * @param string $data            data to validate
     * @param string $signature       decoded signature to compare against
     * @param string $certificate     certificate object defined in Certificate.php
     */

    public function verifySignatureIsCorrectFromCertificate($signature)
    {
        $certKey = openssl_get_publickey($this->certificate);

        if ($certKey === False) {
            throw new \Exception("Unable to extract public key from cert");
        }

        try {
            $certInfo    = openssl_x509_parse($this->certificate, true);
            $certSubject = $certInfo["subject"];

            if (is_null($certSubject)) {
                throw new \Exception("Error with certificate - subject cannot be found");
            }
        } catch (\Exception $ex) {
            throw new \Exception("Unable to verify certificate - error with the certificate subject", null, $ex);
        }

        if (strcmp($certSubject["CN"], $this->expectedCnName)) {
            throw new \Exception("Unable to verify certificate issued by Amazon - error with certificate subject");
        }

        $result = -1;
        try {
            $result = openssl_verify($this->signatureFields, $signature, $certKey, OPENSSL_ALGO_SHA1);
        } catch (\Exception $ex) {
            throw new \Exception("Unable to verify signature - error with the verification algorithm", null, $ex);
        }

        return ($result > 0);
    }


    /* Extract the mandatory field from the message and return the contents
     *
     * @param string $fieldName name of the field to extract
     *
     * @throws Exception if not found
     *
     * @return string field contents if found
     */

    private function getMandatoryField($fieldName)
    {
        $value = $this->getField($fieldName);
        if (is_null($value)) {
            throw new \Exception("Error with json message - mandatory field " . $fieldName . " cannot be found");
        }
        return $value;
    }

    /* Extract the field if present, return null if not defined
     *
     * @param string $fieldName name of the field to extract
     *
     * @return string field contents if found, null otherwise
     */

    private function getField($fieldName)
    {
        if (array_key_exists($fieldName, $this->snsMessage)) {
            return $this->snsMessage[$fieldName];
        } else {
            return null;
        }
    }

    /* returnMessage() - JSON decode the raw [Message] portion of the IPN */

    public function returnMessage()
    {
        return json_decode($this->snsMessage['Message'], true);
    }

    /* toJson() - Converts IPN [Message] field to JSON
     *
     * Has child elements
     * ['NotificationData'] [XML] - API call XML notification data
     * @param remainingFields - consists of remaining IPN array fields that are merged
     * Type - Notification
     * MessageId -  ID of the Notification
     * Topic ARN - Topic of the IPN
     * @return response in JSON format
     */

    public function toJson()
    {
        $response = $this->simpleXmlObject();

        // Merging the remaining fields with the response
        $remainingFields = $this->getRemainingIpnFields();
        $responseArray = array_merge($remainingFields,(array)$response);

        // Converting to JSON format
        $response = json_encode($responseArray);

        return $response;
    }

    /* toArray() - Converts IPN [Message] field to associative array
     * @return response in array format
     */

    public function toArray()
    {
        $response = $this->simpleXmlObject();

        // Converting the SimpleXMLElement Object to array()
        $response = json_encode($response);
        $response = json_decode($response, true);

        // Merging the remaining fields with the response array
        $remainingFields = $this->getRemainingIpnFields();
        $response = array_merge($remainingFields,$response);

        return $response;
    }

    /* addRemainingFields() - Add remaining fields to the datatype
     *
     * Has child elements
     * ['NotificationData'] [XML] - API call XML response data
     * Convert to SimpleXML element object
     * Type - Notification
     * MessageId -  ID of the Notification
     * Topic ARN - Topic of the IPN
     * @return response in array format
     */

    private function simpleXmlObject()
    {
        $ipnMessage = $this->returnMessage();

        // Getting the Simple XML element object of the IPN XML Response Body
        $response = simplexml_load_string((string) $ipnMessage['NotificationData']);

        // Adding the Type, MessageId, TopicArn details of the IPN to the Simple XML element Object
        $response->addChild('Type', $this->snsMessage['Type']);
        $response->addChild('MessageId', $this->snsMessage['MessageId']);
        $response->addChild('TopicArn', $this->snsMessage['TopicArn']);

        return $response;
    }

    /* getRemainingIpnFields()
     * Gets the remaining fields of the IPN to be later appended to the return message
     */

    private function getRemainingIpnFields()
    {
        $ipnMessage = $this->returnMessage();

        $remainingFields = array(
                            'NotificationReferenceId' =>$ipnMessage['NotificationReferenceId'],
                            'NotificationType' =>$ipnMessage['NotificationType'],
                            'IsSample' =>$ipnMessage['IsSample'],
                            'SellerId' =>$ipnMessage['SellerId'],
                            'ReleaseEnvironment' =>$ipnMessage['ReleaseEnvironment'],
                            'Version' =>$ipnMessage['Version']);

        return $remainingFields;
    }
}
