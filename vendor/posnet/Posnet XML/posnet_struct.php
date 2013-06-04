<?php
    /**
     * @package posnet
     */
     
    /**
     * It is used for a template class for merchant info
     */
    class MerchantInfo {
        /**
         * Merchant ID (10 char)
         * @var string
         */
        var $mid = "";
        /**
         * Terminal ID (8 char)
         * @var string
         */
        var $tid = "";
        /**
         * Username (8 char) for being used to login Posnet XML Service
         * @var string
         */
        var $username = "";
        /**
         * Password (8 char) for being used to login Posnet XML Service
         * @var string
         */
        var $password = "";
    };
     
     
    /**
     * It is used for a template class for transaction requests
     */
    class PosnetRequest {
        /**
         * Creditcard number (16-19 char)
         * @var string
         */
        var $ccno; // min 16 char, max 21 char
        /**
         * Expire date of the credit card (4 chars). 2 Digits year, 2 digits month (YYMM).
         * e.g. 0712
         * @var string
         */
        var $expdate;
        /**
         * CVC(3 chars) of the credit card. "XXX" can be specified in test environment.
         * @var string
         */
        var $cvc;
        /**
         * Order ID(24 chars). Distinguishes this transaction from the others.
         * Should be different in each authorization, sale or bonus usage transaction.
         * Can be alpha-numeric. To specify a distinct one in each authorization,
         * you can combine your mid, date, time an incrementing 2 digit number.
         * e.g. 670000006701040316155901
         * @var string
         */
        var $orderid;
        /**
         * Transaction amount (1-12 chars) in YKr (100 YTLs) (12 chars). Last 2 digits are always assumed to be YKr.
         * Should contain no thousands or decimal separator.
         * e.g. 10 YTL : 1000
         * e.g. 1.015,16 YTL : 101516
         * @var string
         */
        var $amount;
        /**
         * Transaction wpamount (1-12 chars) in YKr (100 YTLs) (12 chars). Last 2 digits are always assumed to be YKr.
         * Should contain no thousands or decimal separator.
         * e.g. 10 YTL : 1000
         * e.g. 1.015,16 YTL : 101516
         * @var string
         */
        var $wpamount;
        /**
         * Curency Code (2 chars) for a transaction request
         * e.g. : YT
         * @var string
         */
        var $currency;
        /**
         * Instalment number (2 chars). Specifies number of instalments.
         * If no instalment will be made, 0 should be specified.
         * e.g. : 03
         * @var string
         */
        var $instnumber;
        /**
         * Hostlogkey (18 chars) returned from authorization or sale transaction.
         * This value will be used in the reversal or capture of this transaction.
         * @var string
         */
        var $hostlogkey;
        /**
         * Authorization code (6 chars) returned from authorization or sale transaction.
         * This value will be used in the reversal or capture of this transaction.
         * @var string
         */
        var $authcode;
        /**
         * Campaign Name (4 chars) is used for Forward Sale Transaction
         * e.g. : K001
         * @var string
         */
        var $vftcode;
        /**
         * Specifies how many extra (6 chars) WorldPoints card holder will get.
         * Normally, card holders get no extra WorldPoints, that is when they pay 1 YTL,
         * they get 1 WorldPoint. When you, for instance, specify 10 in this parameter for
         * a shopping of 100 YTL, a card holder will get 110 WorldPoints instead of 100.
         * e.g. : 000010
         * @var string
         */
        var $extrapoint; // fix 6 char, Ex : 000000
        /**
         * Specifies how many multiple (2 chars) WorldPoints card holder will get.
         * Normally, card holders get 1 multiple WorldPoints, that is when they pay 1 YTL,
         * they get 1 WorldPoint. When you, for instance, specify 2 in this parameter for
         * a shopping of 100 YTL, a card holder will get 200 WorldPoints instead of 100.
         * e.g. : 02
         * @var string
         */
        var $multiplepoint;
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
         */
        var $koicode;
    };
     
    /**
     * It is used for a template class for transaction responses
     */
    class PosnetResponse {
        /**
         * Result of transaction (1 char). Shows if the transaction was approved.
         *  '0’: Approved
         *  ’1’: Not approved
         *  ’2’: Approved just before a time
         * @var string
         */
        var $approved;
        /**
         * Response Code (1 - 4 chars). Error code if transaction is not approved.
         * It is recommended to check this parameter rather than Responsetext.
         * Because error codes don't change, but error descriptions do.
         * You can display your own error message for common errors
         * (such as wrong expire date) by checking this parameter.
         * @var string
         */
        var $errorcode;
        /**
         * Response Text (1 - 50 chars). Short description of the error if transaction is not approved.
         * @var string
         */
        var $errormessage;
        /**
         * Hostlogkey (18 chars) returned from authorization or sale transaction.
         * This value will be used in the reversal or capture of this transaction. * @var string
         * @var string
         */
        var $hostlogkey;
        /**
         * Authorization code (6 chars) returned from authorization or sale transaction.
         * This value will be used in the reversal or capture of this transaction.
         * @var string
         */
        var $authcode;
         
        /**
         * Instalment number (2 chars).
         * @var string
         */
        var $instcount;
        /**
         * Amount (1-12 chars) of each instalment.
         * @var string
         */
        var $instamount;
         
        /**
         * WorldPoints (8 chars) gained from this transaction.
         * @var string
         */
        var $point;
        /**
         * YTL equivalent (12 chars) of  WorldPoints gained from this transaction.
         * @var string
         */
        var $pointAmount;
        /**
         * WorldPoints (8 chars) the card holder has.
         * @var string
         */
        var $totalPoint;
        /**
         * YTL equivalent (12 chars) of  WorldPoints the card holder has.
         * @var string
         */
        var $totalPointAmount;
         
        /**
         * Amount (1-12 chars) of vade applied to vft transaction.
         * @var string
         */
        var $vft_amount;
        /**
         * Interest Rate (6 chars) for vft transaction.
         * @var string
         */
        var $vft_rate;
        /**
         * VFT Day Count (4 chars) is the difference between first instalment payment date
         * and transaction date.
         * @var string
         */
        var $vft_daycount;
         
        /**
         * @var array
         */
        var $koiInfo;
         
        function PosnetResponse() {
            $this->Init();
        }
         
        /**
         * Initialize parameters
         * @access protected
         */
        function Init() {
            $this->approved = "0";
            $this->errorcode = "";
            $this->errormessage = "";
             
            $this->hostlogkey = "";
            $this->authcode = "";
             
            $this->instcount = "";
            $this->instamount = "";
             
            $this->point = "";
            $this->pointAmount = "";
            $this->totalPoint = "";
            $this->totalPointAmount = "";
             
            $this->vft_amount = "";
            $this->vft_rate = "";
            $this->vft_daycount = "";
            
            $this->koiInfo = null;
        }
    };
?>
