<?php
    /*
     * posnet_xml.php
     *
     */

    /**
     * @package posnet
     */

    if (!defined('POSNET_MODULES_DIR')) define('POSNET_MODULES_DIR', dirname(__FILE__) . '/..');

    // Include posnet helper library
    require_once('posnet_struct.php');
    // Include the xml library
    require_once(POSNET_MODULES_DIR . '/XML/xml.php');

    class PosnetXML extends XML {
         
        /**
         * Error message for XML parsing
         * @access private
         */
        var $error;
         
        /**
         * Constructor
         * @param string $error
         */
        Function PosnetXML() {
            parent::XML();
            $this->error = "";
        }
         
        /**
         * This function is used to set errors like XML parser errors.
         * @param string $error
         */
        Function SetError($error) {
            $this->error = $error;
        }
         
        /**
         * This function is used to create POSNET XML Header Nodes
         * @param MerchantInfo $merchantInfo
         * @param XMLNode &$node_posnetRequest
         * @access protected
         */
        Function CreateXMLForHeader($merchantInfo, &$node_posnetRequest) {

            $this->xmlDecl = '<?xml version="1.0" encoding="ISO-8859-9"?>';

            $node_posnetRequest = $this->createElement('posnetRequest');
            $this->appendChild($node_posnetRequest);

            $mid = $this->createElement('mid');
            $midTextNode = $this->createTextNode($merchantInfo->mid);
            $mid->appendChild($midTextNode);
            $node_posnetRequest->appendChild($mid);

            $tid = $this->createElement('tid');
            $tidTextNode = $this->createTextNode($merchantInfo->tid);
            $tid->appendChild($tidTextNode);
            $node_posnetRequest->appendChild($tid);

            $username = $this->createElement('username');
            $usernameTextNode = $this->createTextNode($merchantInfo->username);
            $username->appendChild($usernameTextNode);
            $node_posnetRequest->appendChild($username);

            $password = $this->createElement('password');
            $passwordTextNode = $this->createTextNode($merchantInfo->password);
            $password->appendChild($passwordTextNode);
            $node_posnetRequest->appendChild($password);
        }

        /**
         * This function is used to create POSNET XML Transaction Nodes for each transaction type
         * @param MerchantInfo $merchantInfo
         * @param PosnetRequest $posnetRequest
         * @param string $trantype
         * @return string
         * @access protected
         */
        Function CreateXMLForPosnetTransaction($merchantInfo, $posnetRequest, $trantype) {

            //Create Header
            $this->CreateXMLForHeader($merchantInfo, $node_posnetRequest);

            //Create Transaction XML Packet
            switch(strtolower($trantype)) {
                case "auth" :
                case "sale" :
                case "salewp" :                
                {
                    if ($trantype == "auth")
                        $node_tran = $this->createElement('auth');
                    elseif ($trantype == "sale")
                        $node_tran = $this->createElement('sale');    
                    else
                        $node_tran = $this->createElement('saleWP');

                    $node_posnetRequest->appendChild($node_tran);

                    //sale or auth node
                    $node_ccno = $this->createElement('ccno');
                    $node_ccnoTextNode = $this->createTextNode($posnetRequest->ccno);
                    $node_ccno->appendChild($node_ccnoTextNode);
                    $node_tran->appendChild($node_ccno);

                    $node_expDate = $this->createElement('expDate');
                    $node_expDateTextNode = $this->createTextNode($posnetRequest->expdate);
                    $node_expDate->appendChild($node_expDateTextNode);
                    $node_tran->appendChild($node_expDate);

                    $node_cvc = $this->createElement('cvc');
                    $node_cvcTextNode = $this->createTextNode($posnetRequest->cvc);
                    $node_cvc->appendChild($node_cvcTextNode);
                    $node_tran->appendChild($node_cvc);
		    
		    $node_amount = $this->createElement('amount');
                    $node_amountTextNode = $this->createTextNode($posnetRequest->amount);
                    $node_amount->appendChild($node_amountTextNode);
                    $node_tran->appendChild($node_amount);
                    
                    if(strtolower($trantype) == "salewp"){
		    	$node_wpamount = $this->createElement('wpAmount');
                    	$node_wpamountTextNode = $this->createTextNode($posnetRequest->wpamount);
                    	$node_wpamount->appendChild($node_wpamountTextNode);
                    	$node_tran->appendChild($node_wpamount);                    
                    }

                    $node_currency = $this->createElement('currencyCode');
                    $node_currencyTextNode = $this->createTextNode($posnetRequest->currency);
                    $node_currency->appendChild($node_currencyTextNode);
                    $node_tran->appendChild($node_currency);

                    $node_orderid = $this->createElement('orderID');
                    $node_orderidTextNode = $this->createTextNode($posnetRequest->orderid);
                    $node_orderid->appendChild($node_orderidTextNode);
                    $node_tran->appendChild($node_orderid);

                    $node_instnumber = $this->createElement('installment');
                    $node_instnumberTextNode = $this->createTextNode($posnetRequest->instnumber);
                    $node_instnumber->appendChild($node_instnumberTextNode);
                    $node_tran->appendChild($node_instnumber);

                    $node_extrapoint = $this->createElement('extraPoint');
                    $node_extrapointTextNode = $this->createTextNode($posnetRequest->extrapoint);
                    $node_extrapoint->appendChild($node_extrapointTextNode);
                    $node_tran->appendChild($node_extrapoint);

                    $node_multiplepoint = $this->createElement('multiplePoint');
                    $node_multiplepointTextNode = $this->createTextNode($posnetRequest->multiplepoint);
                    $node_multiplepoint->appendChild($node_multiplepointTextNode);
                    $node_tran->appendChild($node_multiplepoint);
                    
                    if(is_numeric($posnetRequest->koicode))
                    {
                        $node_koicode = $this->createElement('koiCode');
                        $node_koicodeTextNode = $this->createTextNode($posnetRequest->koicode);
                        $node_koicode->appendChild($node_koicodeTextNode);
                        $node_tran->appendChild($node_koicode);
                    }
                    break;
                }
                case "capt" :
                {
                    $node_tran = $this->createElement('capt');

                    $node_posnetRequest->appendChild($node_tran);

                    //capt node
                    $node_hostlogkey = $this->createElement('hostLogKey');
                    $node_hostlogkeyTextNode = $this->createTextNode($posnetRequest->hostlogkey);
                    $node_hostlogkey->appendChild($node_hostlogkeyTextNode);
                    $node_tran->appendChild($node_hostlogkey);

                    $node_authcode = $this->createElement('authCode');
                    $node_authcodeTextNode = $this->createTextNode($posnetRequest->authcode);
                    $node_authcode->appendChild($node_authcodeTextNode);
                    $node_tran->appendChild($node_authcode);

                    $node_amount = $this->createElement('amount');
                    $node_amountTextNode = $this->createTextNode($posnetRequest->amount);
                    $node_amount->appendChild($node_amountTextNode);
                    $node_tran->appendChild($node_amount);
                     
                    $node_currency = $this->createElement('currencyCode');
                    $node_currencyTextNode = $this->createTextNode($posnetRequest->currency);
                    $node_currency->appendChild($node_currencyTextNode);
                    $node_tran->appendChild($node_currency);
                     
                    $node_instnumber = $this->createElement('installment');
                    $node_instnumberTextNode = $this->createTextNode($posnetRequest->instnumber);
                    $node_instnumber->appendChild($node_instnumberTextNode);
                    $node_tran->appendChild($node_instnumber);
                     
                    $node_extrapoint = $this->createElement('extraPoint');
                    $node_extrapointTextNode = $this->createTextNode($posnetRequest->extrapoint);
                    $node_extrapoint->appendChild($node_extrapointTextNode);
                    $node_tran->appendChild($node_extrapoint);
                     
                    $node_multiplepoint = $this->createElement('multiplePoint');
                    $node_multiplepointTextNode = $this->createTextNode($posnetRequest->multiplepoint);
                    $node_multiplepoint->appendChild($node_multiplepointTextNode);
                    $node_tran->appendChild($node_multiplepoint);
                     
                    break;
                }
                case "authrev" :
                case "salerev" :
                case "captrev" :
                case "pointusagerev" :
                case "vftsalerev" :
                {
                    $strReverseTranType = "";
                     
                    $node_tran = $this->createElement('reverse');
                    $node_posnetRequest->appendChild($node_tran);
                     
                    switch(strtolower($trantype)) {
                        case "authrev" :
                        $strReverseTranType = "auth";
                        break;
                        case "salerev" :
                        $strReverseTranType = "sale";
                        break;
                        case "captrev" :
                        $strReverseTranType = "capt";
                        break;
                        case "pointusagerev" :
                        $strReverseTranType = "pointUsage";
                        break;
                        case "vftsalerev" :
                        $strReverseTranType = "vftTransaction";
                        break;
                    }
                     
                    $node_trantype = $this->createElement('transaction');
                    $node_trantypeTextNode = $this->createTextNode($strReverseTranType);
                    $node_trantype->appendChild($node_trantypeTextNode);
                    $node_tran->appendChild($node_trantype);
                     
                    $node_hostlogkey = $this->createElement('hostLogKey');
                    $node_hostlogkeyTextNode = $this->createTextNode($posnetRequest->hostlogkey);
                    $node_hostlogkey->appendChild($node_hostlogkeyTextNode);
                    $node_tran->appendChild($node_hostlogkey);
                     
                    $node_authcode = $this->createElement('authCode');
                    $node_authcodeTextNode = $this->createTextNode($posnetRequest->authcode);
                    $node_authcode->appendChild($node_authcodeTextNode);
                    $node_tran->appendChild($node_authcode);
                     
                    break;
                }
                case "return" :
                {
                    $node_tran = $this->createElement('return');

                    $node_posnetRequest->appendChild($node_tran);

                    //return node
                    $node_hostlogkey = $this->createElement('hostLogKey');
                    $node_hostlogkeyTextNode = $this->createTextNode($posnetRequest->hostlogkey);
                    $node_hostlogkey->appendChild($node_hostlogkeyTextNode);
                    $node_tran->appendChild($node_hostlogkey);

                    $node_amount = $this->createElement('amount');
                    $node_amountTextNode = $this->createTextNode($posnetRequest->amount);
                    $node_amount->appendChild($node_amountTextNode);
                    $node_tran->appendChild($node_amount);
                     
                    $node_currency = $this->createElement('currencyCode');
                    $node_currencyTextNode = $this->createTextNode($posnetRequest->currency);
                    $node_currency->appendChild($node_currencyTextNode);
                    $node_tran->appendChild($node_currency);
                     
                    break;
                }
                case "pointusage" :
                {
                    $node_tran = $this->createElement('pointUsage');
                     
                    $node_posnetRequest->appendChild($node_tran);
                     
                    //sale or auth node
                    $node_ccno = $this->createElement('ccno');
                    $node_ccnoTextNode = $this->createTextNode($posnetRequest->ccno);
                    $node_ccno->appendChild($node_ccnoTextNode);
                    $node_tran->appendChild($node_ccno);
                     
                    $node_expDate = $this->createElement('expDate');
                    $node_expDateTextNode = $this->createTextNode($posnetRequest->expdate);
                    $node_expDate->appendChild($node_expDateTextNode);
                    $node_tran->appendChild($node_expDate);
                     
                    $node_amount = $this->createElement('amount');
                    $node_amountTextNode = $this->createTextNode($posnetRequest->amount);
                    $node_amount->appendChild($node_amountTextNode);
                    $node_tran->appendChild($node_amount);
                     
                    $node_currency = $this->createElement('currencyCode');
                    $node_currencyTextNode = $this->createTextNode($posnetRequest->currency);
                    $node_currency->appendChild($node_currencyTextNode);
                    $node_tran->appendChild($node_currency);
                     
                    $node_orderid = $this->createElement('orderID');
                    $node_orderidTextNode = $this->createTextNode($posnetRequest->orderid);
                    $node_orderid->appendChild($node_orderidTextNode);
                    $node_tran->appendChild($node_orderid);
                     
                    break;
                }
                case "pointinquiry" :
                {
                    $node_tran = $this->createElement('pointInquiry');
                     
                    $node_posnetRequest->appendChild($node_tran);
                     
                    //sale or auth node
                    $node_ccno = $this->createElement('ccno');
                    $node_ccnoTextNode = $this->createTextNode($posnetRequest->ccno);
                    $node_ccno->appendChild($node_ccnoTextNode);
                    $node_tran->appendChild($node_ccno);
                     
                    $node_expDate = $this->createElement('expDate');
                    $node_expDateTextNode = $this->createTextNode($posnetRequest->expdate);
                    $node_expDate->appendChild($node_expDateTextNode);
                    $node_tran->appendChild($node_expDate);
                     
                    break;
                }
                case "pointreturn" :
                {
                    $node_tran = $this->createElement('pointReturn');

                    $node_posnetRequest->appendChild($node_tran);

                    //return node
                    $node_hostlogkey = $this->createElement('hostLogKey');
                    $node_hostlogkeyTextNode = $this->createTextNode($posnetRequest->hostlogkey);
                    $node_hostlogkey->appendChild($node_hostlogkeyTextNode);
                    $node_tran->appendChild($node_hostlogkey);

                    $node_wpamount = $this->createElement('wpAmount');
                    $node_wpamountTextNode = $this->createTextNode($posnetRequest->wpamount);
                    $node_wpamount->appendChild($node_wpamountTextNode);
                    $node_tran->appendChild($node_wpamount);
                     
                    $node_currency = $this->createElement('currencyCode');
                    $node_currencyTextNode = $this->createTextNode($posnetRequest->currency);
                    $node_currency->appendChild($node_currencyTextNode);
                    $node_tran->appendChild($node_currency);
                     
                    break;
                }
                case "vftinquiry" :
                {
                    $node_tran = $this->createElement('vftQuery');
                    $node_posnetRequest->appendChild($node_tran);
                     
                    $node_ccno = $this->createElement('ccno');
                    $node_ccnoTextNode = $this->createTextNode($posnetRequest->ccno);
                    $node_ccno->appendChild($node_ccnoTextNode);
                    $node_tran->appendChild($node_ccno);
                     
                    $node_amount = $this->createElement('amount');
                    $node_amountTextNode = $this->createTextNode($posnetRequest->amount);
                    $node_amount->appendChild($node_amountTextNode);
                    $node_tran->appendChild($node_amount);
                     
                    $node_instnumber = $this->createElement('installment');
                    $node_instnumberTextNode = $this->createTextNode($posnetRequest->instnumber);
                    $node_instnumber->appendChild($node_instnumberTextNode);
                    $node_tran->appendChild($node_instnumber);
                     
                    $node_vftcode = $this->createElement('vftCode');
                    $node_vftcodeTextNode = $this->createTextNode($posnetRequest->vftcode);
                    $node_vftcode->appendChild($node_vftcodeTextNode);
                    $node_tran->appendChild($node_vftcode);
                     
                    break;
                }
                case "vftsale" :
                {
                    $node_tran = $this->createElement('vftTransaction');
                    $node_posnetRequest->appendChild($node_tran);
                     
                    $node_ccno = $this->createElement('ccno');
                    $node_ccnoTextNode = $this->createTextNode($posnetRequest->ccno);
                    $node_ccno->appendChild($node_ccnoTextNode);
                    $node_tran->appendChild($node_ccno);
                     
                    $node_expDate = $this->createElement('expDate');
                    $node_expDateTextNode = $this->createTextNode($posnetRequest->expdate);
                    $node_expDate->appendChild($node_expDateTextNode);
                    $node_tran->appendChild($node_expDate);
                     
                    $node_cvc = $this->createElement('cvc');
                    $node_cvcTextNode = $this->createTextNode($posnetRequest->cvc);
                    $node_cvc->appendChild($node_cvcTextNode);
                    $node_tran->appendChild($node_cvc);
                     
                    $node_amount = $this->createElement('amount');
                    $node_amountTextNode = $this->createTextNode($posnetRequest->amount);
                    $node_amount->appendChild($node_amountTextNode);
                    $node_tran->appendChild($node_amount);
                     
                    $node_currency = $this->createElement('currencyCode');
                    $node_currencyTextNode = $this->createTextNode($posnetRequest->currency);
                    $node_currency->appendChild($node_currencyTextNode);
                    $node_tran->appendChild($node_currency);
                     
                    $node_orderid = $this->createElement('orderID');
                    $node_orderidTextNode = $this->createTextNode($posnetRequest->orderid);
                    $node_orderid->appendChild($node_orderidTextNode);
                    $node_tran->appendChild($node_orderid);
                     
                    $node_instnumber = $this->createElement('installment');
                    $node_instnumberTextNode = $this->createTextNode($posnetRequest->instnumber);
                    $node_instnumber->appendChild($node_instnumberTextNode);
                    $node_tran->appendChild($node_instnumber);
                     
                    $node_vftcode = $this->createElement('vftCode');
                    $node_vftcodeTextNode = $this->createTextNode($posnetRequest->vftcode);
                    $node_vftcode->appendChild($node_vftcodeTextNode);
                    $node_tran->appendChild($node_vftcode);
                    
                    if(is_numeric($posnetRequest->koicode))
                    {
                        $node_koicode = $this->createElement('koiCode');
                        $node_koicodeTextNode = $this->createTextNode($posnetRequest->koicode);
                        $node_koicode->appendChild($node_koicodeTextNode);
                        $node_tran->appendChild($node_koicode);
                    }
                    
                    break;
                }
                case "koiinquiry" :
                {
                    $node_tran = $this->createElement('koiCampaignQuery');
                    $node_posnetRequest->appendChild($node_tran);
                     
                    $node_ccno = $this->createElement('ccno');
                    $node_ccnoTextNode = $this->createTextNode($posnetRequest->ccno);
                    $node_ccno->appendChild($node_ccnoTextNode);
                    $node_tran->appendChild($node_ccno);
                     
                    break;
                }
                default:
                $this->SetError("Invalid trantype");
                return "";
            }
             
            return $this->toString();
        }
         
        /**
         * This function is used to parse POSNET XML Response
         * @param string $strXMLData
         * @return string
         */
        Function ParseXMLForPosnetTransaction($strXMLData) {
            return $this->parseXML($strXMLData);
        }
    };
?>
