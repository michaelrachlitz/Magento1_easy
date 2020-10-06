<?php

/**
 * Class Dibs_EasyPayment_Api_Client
 */
class Dibs_EasyPayment_Api_Client {

    CONST LIB_VERSION = '1.0';

    CONST API_VERSION = '1.0';

    CONST API_ENDPOINT = '/v1';

    CONST API_LIVE_SERVER_URL = 'https://api.dibspayment.eu';

    CONST API_TEST_SERVER_URL = 'https://test.api.dibspayment.eu';

    protected $secretKey = '';

    protected $apiServerUrl = '';

    /**
     * Dibs_EasyPayment_Api_Client constructor.
     *
     * @param $secretKey
     * @param bool $isTestEnv
     */
    public function __construct($secretKey, $isTestEnv = false)
    {
        $this->secretKey = $secretKey;

        $this->apiServerUrl = self::API_LIVE_SERVER_URL;

        if ($isTestEnv) {
            $this->apiServerUrl = self::API_TEST_SERVER_URL;
        }
    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
	    'Authorization: '.$this->secretKey,
	    'commercePlatformTag: MG1Easy'

        ];

        return $headers;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiServerUrl . self::API_ENDPOINT;
    }

    /**
     * @param $url
     * @param $method
     * @param array $data
     *
     * @return Dibs_EasyPayment_Api_Response
     */
    public function request($url, $method, $data = [])
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());

        if (('POST' === $method || 'PUT' === $method) && count($data) > 0) {
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $response = $this->prepareResponse($ch);
        return$response;
    }

    protected function prepareResponse($ch)
    {
        $result = curl_exec($ch);
       
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE );
        $response = new Dibs_EasyPayment_Api_Response($code, $result);
        if ($result === false) {
            $response->setResponse(json_encode(['message'=> curl_error($ch)]));
        }

        if (empty($result) && $code != 200) {
            $response->setResponse(json_encode(['message'=> 'CURL Error ', $code]));
        }

        if (in_array($code, [200, 201, 204])) {
            $response->setSuccess(true);
        }

        if ($response->getCode() == 400) {
            $errors = implode(' ', $response->getErrorMessages());
            throw new Dibs_EasyPayment_Api_Exception_Response($errors, 400);
        }

        if (!$response->getSuccess()) {
            $message = $response->getMessage();
            throw new Dibs_EasyPayment_Api_Exception_Response($message, 500);
        }

        return $response;
    }

}
