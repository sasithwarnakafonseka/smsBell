<?php
/**
 * smsBell Client 
 * Version : PHP - V1.0.2(WordPress)
 * Copyright @ 2017 - 2020 wsw.lk
 * Customer Service : +94-(0)11-4348585
 * Email : support@wsw.lk
 */

class Newsletterslk {
    private $user_token; // USER API TOKEN
    private $user_key; //USER API KEY
    private $sender_id="WebSMS"; //USER SENDER KEY AND DEFAULT WebSMS
    private $country_code="94";//Default Country Code Sri Lanka //94 with out +
    protected $url='http://smsm.lankabell.com:4090/Sms.svc/SendSms?';// ALWAYS USE THIS LINK TO CALL API SERVICE
    
    public $msgType="sms";// Message type sms/voice/unicode/flash/music/mms/whatsapp
    public $route=0;// Your Routing Path Default 0
    public $file=false;// File URL for voice or whatsapp. Default not set
    public $scheduledate=false;//Date and Time to send message (YYYY-MM-DD HH:mm:ss) Default not use
    public $duration=false;//Duration of your voice message in seconds (required for voice)
    public $language=false;//Language of voice message (required for text-to-speach)

  
    /**
     * Call to site
     */
    private function Call($params){ 
        
        // print_r($mUrl);
        // exit();
        // $params=urlencode($params);
        $mUrl=$this->url.$params;
        if($params){ 
            $curl_handle=curl_init();
            curl_setopt($curl_handle,CURLOPT_URL,$mUrl);
            curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
            curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
            $buffer = curl_exec($curl_handle);
            curl_close($curl_handle);
            if($buffer){ 
                return $buffer;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Set user Credentials
     * @return boolen
     */
    public function setUser($key,$token){
        if($key && $token){
            $this->user_key=$key;
            $this->user_token=$token;
            return true;
        }else{
            return false;
        }
    }

    /**
     * Set Sender ID
     * @return boolen
     */
    public function setSenderID($sender_id){
        if($sender_id){
            $this->sender_id=$sender_id;
            return true;
        }else{
            return false;
        }
    }

    /**
     * Set Default Routing
     * @return boolen
     */
    public function RouteNumber($number,$if_wp=TRUE){
        if($if_wp==TRUE){
            return $number;
        }else{
            if($number){
                $explode=str_split($number);
                if($explode[0]=="+"){
                    unset($explode[0]);
                    $number=implode("",$explode);
                }else{
                    if($explode[0]==0){
                        unset($explode[0]);
                        $number=implode("",$explode);
                    }
                    $number=$this->country_code.$number;
                }
                return $number;
            }else{
                return false;
            }
        }
       
    }

    /**
     * Check avalible credit balance
     * @return array
     */
    public function CheckBalance($json=FALSE){
        $param='balance&apikey='.$this->user_key.'&apitoken='.$this->user_token;
        if($result=$this->Call($param)){
            if($json===FALSE){
                $c=json_decode($result);
                if($c['status']=="error"){
                    return false;
                }else{
                    return $c;
                }
            }else{
                return $result;
            }
        }else{
            return false;
        }
    }

    /**
     * Check SMS status
     * group_id = The group_id returned by send sms request
     * @return array
     */
    public function CheckStatus($group_id,$json=FALSE){
        if($group_id){
            $param="&groupstatus&apikey=".$this->user_key."&apitoken=".$this->user_token."&groupid=".$group_id;
            if($res=$this->Call($param)){
                if($json===FALSE){
                    $c=json_decode($res);//You can also use direct json by call json as true
                    if($c['status']=="error"){
                        return false;
                    }else{
                        return $c;
                    }
                }else{
                    return $res;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }



    /**
     * Send Message
     * @return boolen
     */
    // phoneNumber=07xxxxxx&smsMessage=test&companyId=xxxxx&pword=xxxxx
    public function SendMessage($Mobile,$TEXT,$json=FALSE){
            if($Mobile){
                if($TEXT){
                    $Mobile=$this->RouteNumber($Mobile);
                    if($this->msgType=="sms"){
                        //SMS
                        //using user_key as CompanyId and user_token as password
                        $param='phoneNumber='.$Mobile.'&smsMessage='.urlencode($TEXT).'&companyId='.$this->user_key.'&pword='.$this->user_token;
                      
                        if($res=$this->Call($param)){ 
                            if($json !=FALSE){
                                return $res;
                            }else{
                                $c=json_decode($res);
                                return $c;
                            }
                        }
                    }
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
    }


?>