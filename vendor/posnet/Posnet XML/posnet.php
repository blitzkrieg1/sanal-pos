<?php
    /*
     * posnet.php
     *
     */

    /**
     * @package posnet
     */
    if (!defined('POSNET_MODULES_DIR')) define('POSNET_MODULES_DIR', dirname(__FILE__) . '/..');

    // Include posnet xml library
    require_once('posnet_xml.php');

    // Include posnet http library
    require_once(POSNET_MODULES_DIR . '/Posnet HTTP/posnet_http.php');

    class Posnet extends PosnetHTTPConection {

        /**
         * reference for MerchantInfo Class
         * @access private
         */
        var $merchantInfo;

        /**
         * reference for PosnetResponse Class
         * @access private
         */
        var $posnetResponse;

        /**
         * temporary reference for request XML data
         * @access private
         */
        var $strRequestXMLData;
         
        /**
         * temporary reference for response XML data
         * @access private
         */
        var $strResponseXMLData;
         
        /**
         * reference for PosnetResponseXML Array
         * @access private
         */
        var $arrayPosnetResponseXML;
         
        /**
         * temporary reference for KOI Code
         * @access private
         */
        var $koicode;
         
        /**
         * Constructor
         * @access private
         */
        Function Posnet() {
            $this->merchantInfo = new MerchantInfo;
            $this->strRequestXMLData = "";
            $this->strResponseXMLData = "";
        }
         
        /**
         * Get & Set Response parameters from XML array
         * @access protected
         */
        Function SetResponseParameters() {
             
            if (array_key_exists("posnetResponse", $this->arrayPosnetResponseXML)) {
                if (array_key_exists("approved", $this->arrayPosnetResponseXML['posnetResponse']))
                    $this->posnetResponse->approved = $this->arrayPosnetResponseXML['posnetResponse']['approved'];
                if (array_key_exists("respCode", $this->arrayPosnetResponseXML['posnetResponse']))
                    $this->posnetResponse->errorcode = $this->arrayPosnetResponseXML['posnetResponse']['respCode'];
                if (array_key_exists("respText", $this->arrayPosnetResponseXML['posnetResponse']))
                    $this->posnetResponse->errormessage = $this->arrayPosnetResponseXML['posnetResponse']['respText'];

                if (array_key_exists("authCode", $this->arrayPosnetResponseXML['posnetResponse']))
                    $this->posnetResponse->authcode = $this->arrayPosnetResponseXML['posnetResponse']['authCode'];
                if (array_key_exists("hostlogkey", $this->arrayPosnetResponseXML['posnetResponse']))
                    $this->posnetResponse->hostlogkey = $this->arrayPosnetResponseXML['posnetResponse']['hostlogkey'];

                //Point Info
                if (array_key_exists("pointInfo", $this->arrayPosnetResponseXML['posnetResponse'])) {
                    if (array_key_exists("point", $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']))
                        $this->posnetResponse->point = $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['point'];
                    if (array_key_exists("pointAmount", $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']))
                        $this->posnetResponse->pointAmount = $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['pointAmount'];
                    if (array_key_exists("totalPoint", $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']))
                        $this->posnetResponse->totalPoint = $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['totalPoint'];
                    if (array_key_exists("totalPointAmount", $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']))
                        $this->posnetResponse->totalPointAmount = $this->arrayPosnetResponseXML['posnetResponse']['pointInfo']['totalPointAmount'];
                }
                //Instalment Info
                if (array_key_exists("instInfo", $this->arrayPosnetResponseXML['posnetResponse'])) {
                    $this->posnetResponse->instcount = $this->arrayPosnetResponseXML['posnetResponse']['instInfo']['inst1'];
                    $this->posnetResponse->instamount = $this->arrayPosnetResponseXML['posnetResponse']['instInfo']['amnt1'];
                }
                //VFT Info
                if (array_key_exists("vftInfo", $this->arrayPosnetResponseXML['posnetResponse'])) {
                    $this->posnetResponse->vft_amount = $this->arrayPosnetResponseXML['posnetResponse']['vftInfo']['vftAmount'];
                    $this->posnetResponse->vft_rate = $this->arrayPosnetResponseXML['posnetResponse']['vftInfo']['vftRate'];
                    $this->posnetResponse->vft_daycount = $this->arrayPosnetResponseXML['posnetResponse']['vftInfo']['vftDayCount'];
                }
                //KOI Info
                if (array_key_exists("koiInfo", $this->arrayPosnetResponseXML['posnetResponse'])) {
                    foreach ($this->arrayPosnetResponseXML['posnetResponse']['koiInfo'] as $vars => $value) {
                        if(is_string($vars) && $vars == 'code') {
                            $this->posnetResponse->koiInfo[1]['code'] = $value;
                        }
                        else if(is_string($vars) && $vars == 'message') {
                            $this->posnetResponse->koiInfo[1]['message'] = $value;
                        }
                        else if(is_long($vars) && is_array($value)) {
                            if (array_key_exists("code", $value) && array_key_exists("message", $value)) {
                                $this->posnetResponse->koiInfo[$vars] = $value;
                            }
                        }
                    }
                }
            }
        }

        /**
         * Main function for Posnet Transactions. Create XML, connect with HTTP(S),receive and parse XML response
         * @access protected
         */
        Function DoTran($posnetRequest, $trantype) {

            //set_time_limit(0);

            //Create Posnet Response Class
            $this->posnetResponse = new PosnetResponse();

            //Create Request XML
            $posnetXML = new PosnetXML();
            $this->strRequestXMLData = $posnetXML->CreateXMLForPosnetTransaction($this->merchantInfo, $posnetRequest, $trantype);

            if ($this->strRequestXMLData == "") {
                $this->posnetResponse->errorcode = "900";
                $this->posnetResponse->errormessage = "XML Create Error : ".$posnetXML->error;
                return false;
            }

            // Show XML result
            if ($this->debug) {
                echo "<H2><LI>XML creation:</LI</H2>\n<PRE>\n";
                echo HtmlSpecialChars($this->strRequestXMLData);
                echo "</PRE>\n";
            }

            // Send and Receive Data with HTTP. In case of connection error, Retry 2 times
            for($i = 1; $i < 3; $i++)
            {
                $this->strResponseXMLData = urldecode($this->SendDataAndGetResponse($this->strRequestXMLData));
                if ($this->strResponseXMLData == "") {
                    if($i >= 2)
                    {
                        $this->posnetResponse->errorcode = "901";
                        $this->posnetResponse->errormessage = "HTTP Connection Error : ".$this->error;
                        return false;
                    }
                }
                else
                    break;
            }

            if ($this->debug) {
                echo "<H2><LI>Response body:</LI</H2>\n<PRE>\n";
                echo HtmlSpecialChars($this->strResponseXMLData);
                echo "</PRE>\n";
            }
             
            //Parse Response XML
            $this->arrayPosnetResponseXML = $posnetXML->ParseXMLForPosnetTransaction($this->strResponseXMLData);
             
            if ($this->debug) {
                echo "<H2><LI>Response XML Array :</LI</H2>\n<PRE>\n";
                print_r ($this->arrayPosnetResponseXML);
                echo "</pre>";
            }
             
            if (count($this->arrayPosnetResponseXML) == 0) {
                if ($this->debug)
                    echo "<H2><LI>Unable to parse XML !</LI</H2>\n<PRE>\n";
                $this->posnetResponse->errorcode = "902";
                $this->posnetResponse->errormessage = "XML Parse Error : ".$posnetXML->error;
                return false;
            } else
            {
                $this->SetResponseParameters();
                if($this->GetApprovedCode() == "0")
                    return false;
            }
            return true;
        }
         
        /* Public methods */
         
        //CreditCard Transactions
         
        /**
         * It is used for Authorization Transaction
         * @param string $ccno
         * @param string $expdate
         * @param string $cvc
         * @param string $orderid
         * @param string $amount
         * @param string $currency
         * @param string $instnumber
         * @param string $multpoint
         * @param string $extpoint
         * @return bool
         */
        Function DoAuthTran($ccno,
            $expdate,
            $cvc,
            $orderid,
            $amount,
            $currency,
            $instnumber,
            $multpoint = "00",
            $extpoint = "000000") {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->ccno = $ccno;
            $posnetRequest->expdate = $expdate;
            $posnetRequest->cvc = $cvc;
            $posnetRequest->orderid = $orderid;
            $posnetRequest->amount = $amount;
            $posnetRequest->currency = $currency;
            $posnetRequest->instnumber = $instnumber;
            $posnetRequest->multiplepoint = $multpoint;
            $posnetRequest->extrapoint = $extpoint;
            $posnetRequest->koicode = $this->koicode;
            
            return $this->DoTran($posnetRequest, "auth");
        }

        /**
         * It is used for Sale Transaction
         * @param string $ccno
         * @param string $expdate
         * @param string $cvc
         * @param string $orderid
         * @param string $amount
         * @param string $currency
         * @param string $instnumber
         * @param string $multpoint
         * @param string $extpoint
         * @return bool
         */
        Function DoSaleTran($ccno,
            $expdate,
            $cvc,
            $orderid,
            $amount,
            $currency,
            $instnumber,
            $multpoint = "00",
            $extpoint = "000000") {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->ccno = $ccno;
            $posnetRequest->expdate = $expdate;
            $posnetRequest->cvc = $cvc;
            $posnetRequest->orderid = $orderid;
            $posnetRequest->amount = $amount;
            $posnetRequest->currency = $currency;
            $posnetRequest->instnumber = $instnumber;
            $posnetRequest->multiplepoint = $multpoint;
            $posnetRequest->extrapoint = $extpoint;
            $posnetRequest->koicode = $this->koicode;
            
            return $this->DoTran($posnetRequest, "sale");
        }

        /**
         * It is used for Sale + Point Usage Transaction
         * @param string $ccno
         * @param string $expdate
         * @param string $cvc
         * @param string $orderid
         * @param string $amount
         * @param string $wpamount         
         * @param string $currency
         * @param string $instnumber
         * @param string $multpoint
         * @param string $extpoint
         * @return bool
         */
        Function DoSaleWPTran($ccno,
            $expdate,
            $cvc,
            $orderid,
            $amount,
            $wpamount,            
            $currency,
            $instnumber,
            $multpoint = "00",
            $extpoint = "000000") {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->ccno = $ccno;
            $posnetRequest->expdate = $expdate;
            $posnetRequest->cvc = $cvc;
            $posnetRequest->orderid = $orderid;
            $posnetRequest->amount = $amount;
            $posnetRequest->wpamount = $wpamount;            
            $posnetRequest->currency = $currency;
            $posnetRequest->instnumber = $instnumber;
            $posnetRequest->multiplepoint = $multpoint;
            $posnetRequest->extrapoint = $extpoint;
            $posnetRequest->koicode = $this->koicode;
            
            return $this->DoTran($posnetRequest, "saleWP");
        }
        
        /**
         * It is used for Capture Transaction
         * @param string $hostlogkey
         * @param string $authcode
         * @param string $amount
         * @param string $currency
         * @param string $instnumber
         * @param string $multpoint
         * @param string $extpoint
         * @return bool
         */
        Function DoCaptTran($hostlogkey,
            $authcode,
            $amount,
            $currency,
            $instnumber,
            $multpoint = "00",
            $extpoint = "000000") {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->hostlogkey = $hostlogkey;
            $posnetRequest->authcode = $authcode;
            $posnetRequest->amount = $amount;
            $posnetRequest->currency = $currency;
            $posnetRequest->instnumber = $instnumber;
            $posnetRequest->multiplepoint = $multpoint;
            $posnetRequest->extrapoint = $extpoint;

            return $this->DoTran($posnetRequest, "capt");
        }

        /**
         * It is used for Authorization Reverse Transaction
         * @param string $hostlogkey
         * @param string $authcode
         * @return bool
         */
        Function DoAuthReverseTran($hostlogkey,
            $authcode) {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->hostlogkey = $hostlogkey;
            $posnetRequest->authcode = $authcode;

            return $this->DoTran($posnetRequest, "authrev");
        }

        /**
         * It is used for Sale Reverse Transaction
         * @param string $hostlogkey
         * @param string $authcode
         * @return bool
         */
        Function DoSaleReverseTran($hostlogkey,
            $authcode) {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->hostlogkey = $hostlogkey;
            $posnetRequest->authcode = $authcode;

            return $this->DoTran($posnetRequest, "salerev");
        }

        /**
         * It is used for Capture Reverse Transaction
         * @param string $hostlogkey
         * @param string $authcode
         * @return bool
         */
        Function DoCaptReverseTran($hostlogkey,
            $authcode) {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->hostlogkey = $hostlogkey;
            $posnetRequest->authcode = $authcode;

            return $this->DoTran($posnetRequest, "captrev");
        }

        /**
         * It is used for Return Transaction
         * @param string $hostlogkey
         * @param string $amount
         * @param string $currency
         * @return bool
         */
        Function DoReturnTran($hostlogkey,
            $amount,
            $currency) {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->hostlogkey = $hostlogkey;
            $posnetRequest->amount = $amount;
            $posnetRequest->currency = $currency;

            return $this->DoTran($posnetRequest, "return");
        }

        //Point Transactions

        /**
         * It is used for Point Usage Transaction
         * @param string $ccno
         * @param string $expdate
         * @param string $orderid
         * @param string $amount
         * @param string $currency
         * @return bool
         */
        Function DoPointUsageTran($ccno,
            $expdate,
            $orderid,
            $amount,
            $currency) {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->ccno = $ccno;
            $posnetRequest->expdate = $expdate;
            $posnetRequest->orderid = $orderid;
            $posnetRequest->amount = $amount;
            $posnetRequest->currency = $currency;

            return $this->DoTran($posnetRequest, "pointusage");
        }

        /**
         * It is used for Point Reverse Transaction
         * @param string $hostlogkey
         * @return bool
         */
        Function DoPointReverseTran($hostlogkey) {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->hostlogkey = $hostlogkey;

            return $this->DoTran($posnetRequest, "pointusagerev");
        }


        /**
         * It is used for Return Transaction
         * @param string $hostlogkey
         * @param string $amount
         * @param string $currency
         * @return bool
         */
        Function DoPointReturnTran($hostlogkey,
            $wpamount,
            $currency) {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->hostlogkey = $hostlogkey;
            $posnetRequest->wpamount = $wpamount;
            $posnetRequest->currency = $currency;

            return $this->DoTran($posnetRequest, "pointReturn");
        }
        
        /**
         * It is used for Point Inquiry Transaction
         * @param string $ccno
         * @param string $expdate
         * @return bool
         */
        Function DoPointInquiryTran($ccno,
            $expdate ) {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->ccno = $ccno;
            $posnetRequest->expdate = $expdate;

            return $this->DoTran($posnetRequest, "pointinquiry");
        }

        //VFT Transactions

        /**
         * It is used for VFT Inquiry Transaction
         * @param string $ccno
         * @param string $amount
         * @param string $instnumber
         * @param string $vftcode
         * @return bool
         */
        Function DoVFTInquiry($ccno,
            $amount,
            $instnumber,
            $vftcode) {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->ccno = $ccno;
            $posnetRequest->amount = $amount;
            $posnetRequest->instnumber = $instnumber;
            $posnetRequest->vftcode = $vftcode;

            return $this->DoTran($posnetRequest, "vftinquiry");
        }
         
        /**
         * It is used for VFT Sale Transaction
         * @param string $ccno
         * @param string $expdate
         * @param string $cvc
         * @param string $orderid
         * @param string $amount
         * @param string $currency
         * @param string $instnumber
         * @param string $vftcode
         * @return bool
         */
        Function DoVFTSale($ccno,
            $expdate,
            $cvc,
            $orderid,
            $amount,
            $currency,
            $instnumber,
            $vftcode) {
             
            $posnetRequest = new PosnetRequest();
             
            $posnetRequest->ccno = $ccno;
            $posnetRequest->expdate = $expdate;
            $posnetRequest->cvc = $cvc;
            $posnetRequest->orderid = $orderid;
            $posnetRequest->amount = $amount;
            $posnetRequest->currency = $currency;
            $posnetRequest->instnumber = $instnumber;
            $posnetRequest->vftcode = $vftcode;
            $posnetRequest->koicode = $this->koicode;
             
            return $this->DoTran($posnetRequest, "vftsale");
        }
         
        /**
         * It is used for VFT Sale Reverse Transaction
         * @param string $hostlogkey
         * @param string $authcode
         * @return bool
         */
        Function DoVFTSaleReverse($hostlogkey,
            $authcode) {
             
            $posnetRequest = new PosnetRequest();
             
            $posnetRequest->hostlogkey = $hostlogkey;
            $posnetRequest->authcode = $authcode;
             
            return $this->DoTran($posnetRequest, "vftsalerev");
        }
        
        //KOI Transactions

        /**
         * It is used for KOI Inquiry Transaction
         * @param string $ccno
         * @return bool
         */
        Function DoKOIInquiry($ccno) {

            $posnetRequest = new PosnetRequest();

            $posnetRequest->ccno = $ccno;
         
            return $this->DoTran($posnetRequest, "koiinquiry");
        }
         
        /**
         * It is used for setting merchant ID
         * @param string $strMid
         */
        Function SetMid($strMid) {
            $this->merchantInfo->mid = $strMid;
        }

        /**
         * It is used for setting terminal ID
         * @param string $strMid
         */
        Function SetTid($strTid) {
            $this->merchantInfo->tid = $strTid;
        }

        /**
         * It is used for setting username for login to posnet web service
         * @param string $strUsername
         */
        Function SetUsername($strUsername) {
            $this->merchantInfo->username = $strUsername;
        }

        /**
         * It is used for setting password for login to posnet web service
         * @param string $strPassword
         */
        Function SetPassword($strPassword) {
            $this->merchantInfo->password = $strPassword;
        }
        
        /**
         * It is used for setting koicode for Joker Vadaa. 
         * Available koicodes can be inqueried by DoKOIInquiryTran function.
         *
         *  1	Ek Taksit
         *  2	Taksit Atlatma
         *  3	Ekstra Puan
         *  4	Kontur Kazaným
         *  5	Ekstre Erteleme
         *  6	Özel Vade Farký
         * @param string $strPassword
         */
        Function SetKoiCode($strKoiCode) {
            $this->koicode = $strKoiCode;
        }
        
        //Get Methods

        /**
         * It is used for getting XML data for response
         * @return string
         */
        Function GetResponseXMLData() {
            return $this->strResponseXMLData;
        }

        /**
         * It is used for getting XML data for request
         * @return string
         */
        Function GetRequestXMLData() {
            return $this->strRequestXMLData;
        }
         
        //Response XML Parameters

        /**
         * It is used for getting Approved Code
         * @return string
         */
        Function GetApprovedCode() {
            return $this->posnetResponse->approved;
        }

        /**
         * It is used for getting Response Code
         * @return string
         */
        Function GetResponseCode() {
            return $this->posnetResponse->errorcode;
        }

        /**
         * It is used for getting Response Message
         * @return string
         */
        Function GetResponseText() {
            return $this->posnetResponse->errormessage;
        }

        /**
         * It is used for getting Authorization Code
         * @return string
         */
        Function GetAuthcode() {
            return $this->posnetResponse->authcode;
        }
         
        /**
         * It is used for getting Hostlogkey
         * @return string
         */
        Function GetHostlogkey() {
            return $this->posnetResponse->hostlogkey;
        }

        //Point Info

        /**
         * It is used for getting Point for a success transaction
         * @return string
         */
        Function GetPoint() {
            return $this->posnetResponse->point;
        }

        /**
         * It is used for getting Point Amount for a success transaction
         * @return string
         */
        Function GetPointAmount() {
            return $this->posnetResponse->pointAmount;
        }
         
        /**
         * It is used for getting cardholder available Total Point
         * @return string
         */
        Function GetTotalPoint() {
            return $this->posnetResponse->totalPoint;
        }

        /**
         * It is used for getting cardholder available Total Point Amount
         * @return string
         */
        Function GetTotalPointAmount() {
            return $this->posnetResponse->totalPointAmount;
        }
        
        //Instalment Info

        /**
         * It is used for getting instalment number
         * @return string
         */
        Function GetInstalmentNumber() {
            return $this->posnetResponse->instcount;
        }

        /**
         * It is used for getting each instalment amount
         * @return string
         */
        Function GetInstalmentAmount() {
            return $this->posnetResponse->instamount;
        }
         
        //VFT Info

        /**
         * It is used for getting vft rate
         * @return string
         */
        Function GetVFTRate() {
            return $this->posnetResponse->vft_rate;
        }

        /**
         * It is used for getting due-date amount
         * @return string
         */
        Function GetVFTAmount() {
            return $this->posnetResponse->vft_amount;
        }

        /**
         * It is used for getting vft day count
         * @return string
         */
        Function GetVFTDayCount() {
            return $this->posnetResponse->vft_daycount;
        }
        
        //KOI Info

        /**
         * It is used for getting koi message count
         * @return string
         */
        Function GetCampMessageCount() {
            if($this->posnetResponse->koiInfo == null)
                return 0;
                
            return count($this->posnetResponse->koiInfo);
        }
        
        /**
         * It is used for getting koi message by specified index
         * @param string $strMessageIndex
         * @return string
         */
        Function GetCampMessage($strMessageIndex) {
            if($this->posnetResponse->koiInfo == null)
                return "";
            
            if(array_key_exists($strMessageIndex, $this->posnetResponse->koiInfo))
                return $this->posnetResponse->koiInfo[$strMessageIndex]['message'];
            else
                return "";
        }
        
        /**
         * It is used for getting koi code by specified index
         * @param string $strMessageIndex
         * @return string
         */
        Function GetCampCode($strMessageIndex) {
            if($this->posnetResponse->koiInfo == null)
                return "";
            
            if(array_key_exists($strMessageIndex, $this->posnetResponse->koiInfo))
                return $this->posnetResponse->koiInfo[$strMessageIndex]['code'];
            else
                return "";
        }
    };
?>
