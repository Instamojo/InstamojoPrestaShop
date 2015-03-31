<?php

class InstamojoAPI {
    const version = '1.1';

    protected $curl;
    protected $endpoint = 'https://www.instamojo.com/api/1.1/';
    protected $api_key = null;
    protected $auth_token = null;

    /**
    * @param string $api_key
    * @param string $auth_token is available on the d
    * @param string $endpoint can be set if you are working on an alternative server.
    * @return array AuthToken object.
    */
    public function __construct($api_key, $auth_token=null, $endpoint=null) 
    {
        $this->api_key = (string) $api_key;
        $this->auth_token = (string) $auth_token;
        if(!is_null($endpoint)){
            $this->endpoint = (string) $endpoint;   
        }
    }

    public function __destruct() 
    {
        if(!is_null($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
    * @return array headers with Authentication tokens added 
    */
    private function build_curl_headers() 
    {
        $headers = array("X-Api-key: $this->api_key");
        if($this->auth_token) {
            $headers[] = "X-Auth-Token: $this->auth_token";
        }
        return $headers;        
    }

    /**
    * @param string $path
    * @return string adds the path to endpoint with.
    */
    private function build_api_call_url($path) 
    {
        return $this->endpoint . $path . '/';

    }

    /**
    * @param string $method ('GET', 'POST', 'DELETE', 'PATCH')
    * @param string $path whichever API path you want to target.
    * @param array $data contains the POST data to be sent to the API.
    * @return array decoded json returned by API.
    */
    private function api_call($method, $path, array $data=null) 
    {
        $path = (string) $path;
        $method = (string) $method;
        $data = (array) $data;
        $headers = $this->build_curl_headers();
        $request_url = $this-> build_api_call_url($path);

        $options = array();
        $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_RETURNTRANSFER] = true;
        
        if($method == 'POST') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        } else if($method == 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        } else if($method == 'PATCH') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);         
            $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
        } else if ($method == 'GET' or $method == 'HEAD') {
            if (!empty($data)) {
                /* Update URL to container Query String of Paramaters */
                $request_url .= '?' . http_build_query($data);
            }
        }
        // $options[CURLOPT_VERBOSE] = true;
        $options[CURLOPT_URL] = $request_url;

        $this->curl = curl_init();
        $setopt = curl_setopt_array($this->curl, $options);
        $response = curl_exec($this->curl);
        $headers = curl_getinfo($this->curl);

        $error_number = curl_errno($this->curl);
        $error_message = curl_error($this->curl);
        $response_obj = json_decode($response, true);

        if($response_obj['success'] == false) {
            $message = json_encode($response_obj['message']);
            throw new Exception($message . PHP_EOL);
        }
        return $response_obj;
    }

    /**
    * @param string payment_id as provided by paymentsList() or Instamojo's webhook or redirect functions.
    * @return array single Payment object.
    */  
    public function paymentDetail($payment_id) 
    {
        $response = $this->api_call('GET', 'payments/' . $payment_id, array()); 
        return $response['payment'];
    }
}
?>
