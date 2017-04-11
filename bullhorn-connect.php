<?php

include_once('setting.php');
$settings = get_option('bullhorn_option');
//var_dump($settings);
define('CLIENT_ID', $settings['client_id']);
define('CLIENT_SECRET', $settings['client_secret']);
define('USER', $settings['bullhorn_user']);
define('PASS', $settings['bullhorn_pass']);
define('THANK_PAGE', $settings['bullhorn_thank']);

class BullhornConnect
{
	private function getAuthCode()
	{
		$url = 'https://auth.bullhornstaffing.com/oauth/authorize?client_id='.CLIENT_ID.'&response_type=code&action=Login&username='.USER.'&password='.PASS;
		$curl = curl_init( $url ); 
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		//curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_AUTOREFERER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);

		$content = curl_exec( $curl );
		curl_close( $curl );//die($content); 

		if(preg_match('#Location: (.*)#', $content, $r)) {
			$l = trim($r[1]);
			$temp = preg_split("/code=/", $l);
			$authcode = $temp[1];
		}

		return $authcode;
	}

	private function doBullhornAuth($authCode)
	{
		$url = 'https://auth.bullhornstaffing.com/oauth/token?grant_type=authorization_code&code='.$authCode.'&client_id='.CLIENT_ID.'&client_secret='.CLIENT_SECRET;

		$options = array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => array()
		); 

		$ch = curl_init( $url ); 
		curl_setopt_array( $ch, $options ); 
		$content = curl_exec( $ch ); 

		curl_close( $ch ); //die($content);

		return $content;

	}

	private function doBullhornLogin($accessToken)
	{
		$url = 'https://rest.bullhornstaffing.com/rest-services/login?version=*&access_token='.$accessToken;
		$curl = curl_init( $url ); 
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$content = curl_exec( $curl );
		curl_close( $curl );
		return $content;
	}
	public function bullhorn_login(){
		try {
			if(get_transient('refresh_token') === false){
				$authCode = self::getAuthCode();//echo $authCode;die;
				$auth = self::doBullhornAuth($authCode);//echo $auth;die;
				$tokens = json_decode($auth);//print '<pre>';print_r($tokens);die;
				$session = json_decode(self::doBullhornLogin($tokens->access_token), true);
				set_transient('refresh_token', $session, 8 * 60);
				return $session;
			}
			return get_transient('refresh_token');
			die;
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}
	public function httpGETRequest($url){
	//	var_dump($url);
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,  
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			return json_decode($response, true);
		}
	}
	public function httpPUTRequest($url, $CandidateData){
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($CandidateData));
	//	var_dump(json_encode($CandidateData));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			return json_decode($response, true);
		}
	}
	public function httpPOSTRequest($url, $CandidateData){
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $url);
//		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($CandidateData));
//		var_dump(count($CandidateData));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			echo "cURL Error #:" . $err;
		} else {
			return json_decode($response, true);
		}
	}
	public function attachResume($url){
//		var_dump($url);
		$fileName  = $_FILES['fileToUpload']['name'];
		$filePath  = $_FILES['fileToUpload']['tmp_name'];
		$post = file_get_contents(''.$filePath.'');
		$eol = "\r\n";
		$separator = ''.md5(microtime()).'';
		$requestBody = '';
		$requestBody .= '--'.$separator. $eol;
		$requestBody .= 'Content-Disposition: form-data; name="resume"; filename="'.$fileName.'"'. $eol;
		$requestBody .= 'Content-Length: "'.strlen($post).'"'. $eol;
		$requestBody .= 'Content-Type: text/html'.$eol;
		$requestBody .= 'Content-Transfer-Encoding: binary'. $eol. $eol;
		$requestBody .= ''.$post.''. $eol;
		$requestBody .= '--'.$separator.'--'. $eol . $eol;

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: multipart/form-data; boundary='.$separator.''));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);
		$response = curl_exec($curl);
		curl_close($curl);
		//var_dump($response);
	}
	public function isCandidateSubmitted($jobID, $candidateID, $token, $url){		
		$response = self::httpGETRequest($url.'/search/JobSubmission?BhRestToken='.$token.'&query=jobOrder.id%3A'.$jobID.'&fields=candidate');
		foreach($response['data'] as $candidate){
			if($candidate['candidate']['id'] == $candidateID)
				return true;
		}
		return false;
	}
	public function isCandidateHasCV($candidateID, $token, $url){		
//		https://rest31.bullhornstaffing.com/rest-services/2cium1/entityFiles/Candidate/1446?BhRestToken=4e7ba018-f98b-4ad1-8d07-29f9605214d6
		$response = self::httpGETRequest($url.'/entityFiles/Candidate/'.$candidateID.'?BhRestToken='.$token);
		return (isset($response['EntityFiles']) ? count($response['EntityFiles']) > 0 : false);
	}
	public function urlBullhornEncode($string){
		$replacements = array('%3A','%20','%22','%2C');
		$entities = array(':',' ','"',',');
		return str_replace($entities, $replacements, $string);
	}
	
}