<?php

if (! defined( 'ABSPATH' )) exit;

class WebsmsCountryList
{
	public static function getCountryCodeList()
    {
        $countries=array();
		$datas = (array)json_decode(WebsmscURLOTP::country_list(),true);
		if(array_key_exists('description',$datas)){
			$countries = $datas['description'];
		}
        return $countries;
    }

	public static function getCountryPattern($countryCode=NULL)
    {
		$c = self::getCountryCodeList();
		$pattern ='';
		
		foreach($c as $list)
		{
			if($list['Country']['c_code']==$countryCode){
				
				if(array_key_exists('pattern',$list['Country'])){
					$pattern = $list['Country']['pattern'];
					break;
				}
			}
		}			
		return $pattern;
    }
	
	
	
}
new WebsmsCountryList;
