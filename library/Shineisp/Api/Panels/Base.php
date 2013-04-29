<?php
/**
 * 
 * @author Shine Software
 *
 */
class Shineisp_Api_Panels_Base {
	
	protected $isLive;
	protected $name;
	protected $path;
	protected $session;
	protected $actions = array();

						
	/**
	 * @return the $session
	 */
	public function getSession() {
		return $this->session;
	}

	/**
	 * @param field_type $session
	 */
	public function setSession($session) {
		$this->session = $session;
	}

	/**
	 * @return the $path
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @param field_type $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param field_type $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return the $isLive
	 */
	public function getIsLive() {
		return $this->isLive;
	}

	/**
	 * @param field_type $isLive
	 */
	public function setIsLive($isLive) {
		$this->isLive = $isLive;
	}
	/**
	 * @return the $actions
	 */
	public function getActions() {
		return $this->actions;
	}

	/**
	 * @param field_type $actions
	 */
	public function setActions($actions) {
		$this->actions = $actions;
	}

	/**
	 * create a readable username
	 *
	 * @param array $customer
	 * @return multitype:string NULL
	 */
	public static function generateUsernames($customer) {
		$arrUsernames = array();
	
		if ( isset($customer ['company']) && !empty($customer ['company']) && !empty($customer ['vat']) && !empty($customer ['vat']) ) {
			// Microsoft Corp => microsoftcorp
			$arrUsernames[] = strtolower(preg_replace("#[^a-zA-Z0-9]*#", "", $customer ['company']));
		}
	
		// Jon Doe => jdoe
		$arrUsernames[]  = strtolower(preg_replace("#[^a-zA-Z0-9]*#", "", substr($customer ['firstname'], 0, 1).$customer ['lastname']));
	
		// Jon Doe => doej
		$arrUsernames[] = strtolower(preg_replace("#[^a-zA-Z0-9]*#", "", $customer['lastname'].substr($customer ['firstname'], 0, 1)));
	
		// Jon Doe => jond
		$arrUsernames[] = strtolower(preg_replace("#[^a-zA-Z0-9]*#", "", $customer['firstname'].substr($customer ['lastname'], 0, 1)));
	
		// Jon Doe => djon
		$arrUsernames[] = strtolower(preg_replace("#[^a-zA-Z0-9]*#", "", substr($customer ['lastname'], 0, 1).$customer ['firstname']));
	
		// fallback to each generated username followed by customer_id
		foreach ( $arrUsernames as $tmpUser ) {
			$arrUsernames[] = $tmpUser.$customer ['customer_id'];
		}
		// fallback to each generated username followed by timestamp
		foreach ( $arrUsernames as $tmpUser ) {
			$arrUsernames[] = $tmpUser.time();
		}
	
		return $arrUsernames;
	}
	
	/**
	 * 
	 * Send the email profile to the user
	 */
	public function sendMail($task){
		$isp = Isp::getActiveISP();
		
		// Get the service details
		$service = OrdersItems::getAllInfo($task['orderitem_id']);
		
		// If the setup has been written by the task action then ...
		if(!empty($service['setup'])){
			$setup = json_decode($service['setup'], true);
				
			// Get the service/product name
			$productname  = !empty($service['Products']['ProductsData'][0]['name']) ? $service['Products']['ProductsData'][0]['name'] : "";
			$welcome_mail = (!empty($service['Products']['welcome_mail_id']) && intval($service['Products']['welcome_mail_id']) > 0) ? intval($service['Products']['welcome_mail_id']) : 'new_hosting'; // new_hosting = fallback to old method if no template is set

			// Check if the customer is present in the service and if there is a welcome_mail set for the bought product 
			if( !empty($service['Orders']['Customers']) ){
				
				// Getting the customer
				$customer = $service['Orders']['Customers'];
				
				$strSetup = "";
				foreach ($setup as $section => $details) {
					$strSetup .= strtoupper($section) . "\n===============\n";
					foreach ($details as $label => $detail){
						$strSetup .= "$label: " . $detail . "\n"; 
					}
					$strSetup .= "\n";
				}
				
				Shineisp_Commons_Utilities::sendEmailTemplate($isp ['email'], $welcome_mail, array(
					'setup'        => $strSetup
				   ,'fullname'     => $customer ['fullname']
                   ,'hostingplan'  => $productname
				   ,'controlpanel' => $isp ['website'].":8080"
				   ,'signature'    => $isp ['company']
				));
									
				
			}
		}
	
	}
	
}