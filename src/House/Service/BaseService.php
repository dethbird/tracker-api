<?php
require_once("House/Service/Response/ServiceResponse.php");

class BaseService
{
	/**
	*
	*/
	protected $response;

    public function __construct()
    {
    	$this->response = new ServiceResponse();
    }

    public function prepareResponse(ServiceResponse $response = null){
    	return is_null($response) ? json_encode($this->response) : json_encode($response);
    }

    public function resultsToArray($items){
    	$response = array();
    	foreach($items as $item){
            $obj = $item->to_array();
            if(isset($item->date_added)){
                $obj['date_added'] = $item->date_added;
            }
            if(isset($item->date_updated)){
                $obj['date_updated'] = $item->date_updated;
            }
            if(isset($item->json)){
                $obj['json_decoded'] = json_decode($item->json);
                unset($obj['json']);
            }
    		$response[] = $obj;
    	}
    	return $response;
    }

    public function gen_uuid($len=12)
    {
        $hex = md5("P1zza P4rty!!!" . uniqid("", true));

        $pack = pack('H*', $hex);

        $uid = base64_encode($pack);        // max 22 chars

        $uid = preg_replace("/[^A-Za-z0-9]/", "", $uid);    // mixed case
        //$uid = ereg_replace("[^A-Z0-9]", "", strtoupper($uid));    // uppercase only

        if ($len<4)
            $len=4;
        if ($len>128)
            $len=128;                       // prevent silliness, can remove

        while (strlen($uid)<$len)
            $uid = $uid . gen_uuid(22);     // append until length achieved

        return substr($uid, 0, $len);
    }
}
