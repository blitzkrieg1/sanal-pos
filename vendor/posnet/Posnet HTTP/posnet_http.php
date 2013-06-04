<?php
    /*
     * posnet_http.php
     *
     */
     
    /**
     * @package posnet
     */
    if (!defined('POSNET_MODULES_DIR')) define('POSNET_MODULES_DIR', dirname(__FILE__) . '/..');

    // Include the http library
    require_once(POSNET_MODULES_DIR . '/HTTP/http.php');

    class PosnetHTTPConection {
         
        /**
         * Error message for http connection
         * @access private
         */
        var $error;
        /**
         * Used for debugging
         * @access protected
         */
        var $debug = 0;
        /**
         * Used for forcing OpenSSL
         * @access protected
         */
        var $useOpenSSL = false;
        /**
         * Used for indicating debug level
         * 0->No debug (default) <br>
         * 1->Posnet debug <br>
         * 2->Posnet & HTTP debug <br>
         * @access protected
         */
        var $debuglevel = 0;
        /**
         * HTTP method
         *  'POST' (default) <br>
         *  'GET'
         * @access protected
         */
        var $request_method = "POST";

        /**
         * @access protected
         */
        var $url = "";

        /**
         * Constructor
         * @access private
         */
        Function PosnetHTTPConection($url) {
            $this->url = $url;
        }

        /**
         * This function is used to connect to POSNET system via HTTP/HTTPS protocol.
         * $http and $arguments references will be return after a success call.
         * @param string $url
         * @param array $postValues
         * @param HTTP &$http
         * @param array &$arguments
         * @return string
         * @access protected
         */
        Function ConnectToPosnetSystem($url, $postValues, &$http, &$arguments) {

            /* Connection timeout */
            $http->timeout = 30;

            /* Data transfer timeout */
            $http->data_timeout = 60;

            if ($this->debuglevel > 1) {
                /* Output debugging information about the progress of the connection */
                $http->debug = 1;
                /* Format dubug output to display with HTML pages */
                $http->html_debug = 1;
            }

            /*
             *  Need to emulate a certain browser user agent?
             *  Set the user agent this way:
             */
            $http->user_agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";

            $error = $http->GetRequestArguments($url, $arguments);
            if ($error != "")
                return $error;

            //$arguments["ProxyHostName"]="127.0.0.1";
	        //$arguments["ProxyHostPort"]=6060;

            /* Set additional request headers */
            $arguments["Headers"]["Pragma"] = "nocache";
             
            if ($this->request_method == "POST") {
                $arguments["RequestMethod"] = $this->request_method;
                $arguments["PostValues"] = $postValues;
            }
             
            if ($this->debug)
                echo "<H2><LI>Opening connection to:</H2>\n<PRE>", HtmlEntities($arguments["HostName"]), "</PRE>\n";
             
            flush();
             
            return $http->Open($arguments);
        }
         
        /**
         * This function is used to send and receive data via GET/POST methods.
         * @param string $strRequestData
         * @return string
         * @access protected
         */
        Function SendDataAndGetResponse($strRequestData) {
             
            $http = new HTTP();

            if($this->useOpenSSL)
                $http->use_openssl = 1;

            $strResponseData = "";
            $strTempURL = $this->url;
             
            //POST Method
            if ($this->request_method == "POST") {
                $postValues = array(
                "xmldata" => $strRequestData,
                );
            }
            //GET Method
            else
                $strTempURL .= ("?xmldata=".urlencode($strRequestData));
             
            //Connect
            $error = $this->ConnectToPosnetSystem($strTempURL,
                $postValues,
                $http,
                $arguments);
             
            if ($error == "") {
                if ($this->debug) {
                    echo "<H2><LI>Sending request for page:</H2>\n<PRE>";
                    echo HtmlEntities($arguments["RequestURI"]), "\n";
                }
                if ($this->debug)
                    echo "</PRE>\n";
                flush();
                 
                //Send
                $error = $http->SendRequest($arguments);
                if ($error == "") {
                    if ($this->debug)
                        echo "<H2><LI>Request:</LI</H2>\n<PRE>\n".HtmlEntities($http->request)."</PRE>\n";
                     
                    flush();
                     
                    $headers = array();
                    //Read Response Headers
                    $error = $http->ReadReplyHeaders($headers);
                    if ($error == "") {
                        if ($this->debug)
                            echo "<H2><LI>Response status code:</LI</H2>\n<P>".$http->response_status;
                        switch($http->response_status) {
                            case "301":
                            case "302":
                            case "303":
                            case "307":
                            echo " (redirect to <TT>".$headers["location"]."</TT>)<BR>\nSet the <TT>follow_redirect</TT> variable to handle redirect responses automatically.";
                            break;
                        }
                        if ($this->debug)
                            echo "</P>\n";
                        flush();
                        //Read Response Body
                        for(; ; ) {
                            $error = $http->ReadReplyBody($body, 2000);
                            if (strlen($body) == 0)
                                break;
                            $strResponseData .= $body;
                        }
                        flush();
                    }
                }
                $http->Close();
            }
            if (strlen($error)) {
                $this->error = $error;
                if ($this->debug)
                    echo "<CENTER><H2>Error: ", $error, "</H2></CENTER>\n";
                return "";
            }
            return $strResponseData;
        }
         
        /* Public methods */
         
        /**
         * This function is used to set remote URL of POSNET system.
         * @param string $url
         */
        Function SetURL($url) {
            $this->url = $url;
        }
        /**
         * It is used for forcing to use OpenSSL Extension for secure connection
         */
        Function UseOpenssl() {
            $this->useOpenSSL = true;
        }
        /**
         * This function is used to set errors like communication errors.
         * @param string $error
         */
        Function SetError($error) {
            $this->error = $error;
        }

        /**
         * This function is used to set debug level.
         * @param string $debuglevel
         */
        Function SetDebugLevel($debuglevel) {
            $this->debuglevel = $debuglevel;
            if ($this->debuglevel > 0)
                $this->debug = 1;
        }
    };
?>
