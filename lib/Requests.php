<?php
namespace NetworkLeads;

/**
 * Class Stripe.
 */
class Requests
{
    private $debug = false;
    private $SK = NULL;
    private $ST = NULL;

    public function __construct($SK,$ST)
    {
        $this->SK = $SK;
        $this->ST = $ST;
    }

    public function a(){
        var_dump($this->SK);
        var_dump($this->ST);
    }
    public function send($url, $method, $data, $expectedHttpCode, $isArray = false){


        if($this->debug){
            echo "URL: $url\n";
            echo "Data: " . print_r($data, true) . "\n";
        }

        $c = curl_init();

        curl_setopt($c, CURLOPT_VERBOSE, $this->debug);

        if($this->SK && $this->ST)
        {
            curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($c, CURLOPT_USERPWD, "$this->username:$this->password");
        }else{
            return false;
        }

        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_USERAGENT, 'api');
        curl_setopt($c, CURLOPT_TIMEOUT, 3600);
        curl_setopt($c, CURLOPT_HEADER, true);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);

        if(is_array($data))
        {
            foreach($data as $key => $value)
            {
                if(is_bool($value))
                    $data[$key] = $value ? 'true' : 'false';
            }
        }

        switch($method)
        {
            case 'GET':
                curl_setopt($c, CURLOPT_HTTPGET, true);
                if(@count($data))
                    $url .= '?' . http_build_query($data);
                break;

            case 'POST':
                curl_setopt($c, CURLOPT_POST, true);
                if(@count($data))
                    curl_setopt($c, CURLOPT_POSTFIELDS, $data);
                break;

            case 'PUT':
                curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($c, CURLOPT_PUT, true);

                $headers = array(
                    'X-HTTP-Method-Override: PUT',
                    'Content-type: application/x-www-form-urlencoded'
                );

                if(@count($data))
                {
                    $content = json_encode($data, JSON_FORCE_OBJECT);

                    $fileName = tempnam(sys_get_temp_dir(), 'gitPut');
                    file_put_contents($fileName, $content);

                    $f = fopen($fileName, 'rb');
                    curl_setopt($c, CURLOPT_INFILE, $f);
                    curl_setopt($c, CURLOPT_INFILESIZE, strlen($content));
                }
                curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
                break;
        }

        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($c);

        curl_close($c);

        if($this->debug)
            echo "Response:\n$response\n";


        return $this->parseResponse($url, $response, $expectedHttpCode, $isArray);
    }

    public function parseResponse($url, $response, $expectedHttpCode, $isArray = false)
    {
        // parse response
        $header = false;
        $content = array();
        $status = 200;

        foreach(explode("\r\n", $response) as $line)
        {
            if (strpos($line, 'HTTP/1.1') === 0)
            {
                $lineParts = explode(' ', $line);
                $status = intval($lineParts[1]);
                $header = true;
            }
            else if ($line == '')
            {
                $header = false;
            }
            else if ($header)
            {
                $line = explode(': ', $line);
                switch($line[0])
                {
                    case 'Status':
                        $status = intval(substr($line[1], 0, 3));
                        break;

                    case 'X-RateLimit-Limit':
                        $this->rateLimit = intval($line[1]);
                        break;

                    case 'X-RateLimit-Remaining':
                        $this->rateLimitRemaining = intval($line[1]);
                        break;

                    case 'Link':
                        $matches = null;
                        if(preg_match_all('/<https:\/\/api\.github\.com\/[^?]+\?([^>]+)>; rel="([^"]+)"/', $line[1], $matches))
                        {
                            foreach($matches[2] as $index => $page)
                            {
                                $this->pageData[$page] = array();
                                $requestParts = explode('&', $matches[1][$index]);
                                foreach($requestParts as $requestPart)
                                {
                                    list($field, $value) = explode('=', $requestPart, 2);
                                    $this->pageData[$page][$field] = $value;
                                }
                            }
                        }
                        break;
                }
            }
            else
            {
                $content[] = $line;
            }
        }

        if($status !== $expectedHttpCode)
            throw new Exception("Expected status [$expectedHttpCode], actual status [$status], URL [$url]");

        return json_decode(implode("\n", $content));
    }

}