<?php

/**
 * OrdersController
 * Manage the customers table
 * @version 1.0
 */

class Admin_OrdersController extends Zend_Controller_Action {
	
	protected $orders;
	protected $translator;
	protected $datagrid;
	protected $session;
	
	/**
	 * preDispatch
	 * Starting of the module
	 * (non-PHPdoc)
	 * @see library/Zend/Controller/Zend_Controller_Action#preDispatch()
	 */
	
	public function preDispatch() {
		$this->session = new Zend_Session_Namespace ( 'Admin' );
		$this->orders = new Orders ();
		$registry = Zend_Registry::getInstance ();
		$this->translator = $registry->Zend_Translate;
		$this->datagrid = $this->_helper->ajaxgrid;
		$this->datagrid->setModule ( "orders" )->setModel ( $this->orders );
	}
	
	/**
	 * indexAction
	 * Create the User object and get all the records.
	 * @return unknown_type
	 */
	public function indexAction() {
		$this->_helper->redirector ( 'list', 'orders', 'admin' );
	}
	
	/**
	 * indexAction
	 * Create the User object and get all the records.
	 * @return unknown_type
	 */
	public function listAction() {
		$this->view->title = "Orders list";
		$this->view->description = "Here you can see all the orders.";
		$this->view->buttons = array(array("url" => "/admin/orders/new/", "label" => $this->translator->translate('New order'), "params" => array('css' => array('button', 'float_right'))));
		$this->datagrid->setConfig ( Orders::grid () )->datagrid ();
	}

	/**
	 * Load Json Records
	 *
	 * @return string Json records
	 */
	public function loadrecordsAction() {
		$this->_helper->ajaxgrid->setConfig ( Orders::grid() )->loadRecords ($this->getRequest ()->getParams());
	}
	
	/**
	 * searchProcessAction
	 * Search the record 
	 * @return unknown_type
	 */
	public function searchprocessAction() {
		$this->_helper->ajaxgrid->setConfig ( Orders::grid () )->search ();
	}
	
	/*
	 *  bulkAction
	 *  Execute a custom function for each item selected in the list
	 *  this method will be call from a jQuery script 
	 *  @return string
	 */
	public function bulkAction() {
		$this->_helper->ajaxgrid->massActions ();
	}
	
	/**
	 * recordsperpage
	 * Set the number of the records per page
	 * @return unknown_type
	 */
	public function recordsperpageAction() {
		$this->_helper->ajaxgrid->setRowNum ();
	}
	
	/**
	 * newAction
	 * Create the form module in order to create a record
	 * @return unknown_type
	 */
	public function newAction() {
		
		$this->view->form = $this->getForm ( "/admin/orders/process" );
		$this->view->title = "New Order";
		$this->view->description = "Create a new order using this form.";
		$this->view->buttons = array(array("url" => "#", "label" => $this->translator->translate('Save order'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')));
		$this->render ( 'applicantform' );
	}
	
	/**
	 * confirmAction
	 * Ask to the user a confirmation before to execute the task
	 * @return null
	 */
	public function confirmAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$controller = Zend_Controller_Front::getInstance ()->getRequest ()->getControllerName ();
		try {
			if (is_numeric ( $id )) {
				$this->view->back = "/admin/$controller/edit/id/$id";
				$this->view->goto = "/admin/$controller/delete/id/$id";
				$this->view->title = $this->translator->translate ( 'Are you sure to delete this order?' );
				$this->view->description = $this->translator->translate ( 'If you delete this order all the data will be no more longer available.' );
				
				$record = $this->orders->find ( $id );
				$this->view->recordselected = $record ['order_id'] . " - " . Shineisp_Commons_Utilities::formatDateOut ( $record ['order_date'] );
			} else {
				$this->_helper->redirector ( 'list', $controller, 'admin', array ('mex' => $this->translator->translate ( 'Unable to process request at this time.' ), 'status' => 'error' ) );
			}
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	
	}
	
	/**
	 * deleteAction
	 * Delete a record previously selected by the order
	 * @return unknown_type
	 */
	public function deleteAction() {
		$files = new Files ();
		$id = $this->getRequest ()->getParam ( 'id' );
		try {
			if (is_numeric ( $id )) {
				
				// Delete all the files attached
				Shineisp_Commons_Utilities::delTree ( PUBLIC_PATH . "/documents/orders/$id/" );
				Orders::DeleteByID($id);
				
			}
		} catch ( Exception $e ) {
			die ( $e->getMessage () . " " . $e->getTraceAsString () );
		}
		
		return $this->_helper->redirector ( 'index', 'orders' );
	}
	
	/**
	 * getproducts
	 * Get products using the categories
	 * @return Json
	 */
	public function getproductsAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		if($id == "domains"){
			$products = DomainsTlds::getList();
		}elseif(is_numeric($id)){
			$products = ProductsCategories::getProductListbyCatID ( $id, "p.product_id, pd.name as name" );	
		}
		
		die ( json_encode ( $products ) );
	}
	
	/**
	 * Get product info
	 * @return Json
	 */
	public function getproductinfoAction() {
		$ns = new Zend_Session_Namespace ( 'Admin' );
		$id = $this->getRequest ()->getParam ( 'id' );
		$product = Products::getAllInfo( $id, $ns->langid);
		die ( json_encode ( $product ) );
	}
	
	/**
	 * Get billing cycles information
	 * @return Json
	 */
	public function getbillingsAction() {
		$ns = new Zend_Session_Namespace ( 'Admin' );
		$id = $this->getRequest ()->getParam ( 'id' );
		$billid = $this->getRequest ()->getParam ( 'billid' );
		$billings = ProductsTranches::getTranchesBy_ProductId_BillingId( $id, $billid );
		die ( json_encode ( $billings ) );
	}
	
	/**
	 * editAction
	 * Get a record and populate the application form 
	 * @return unknown_type
	 */
	public function editAction() {
		$form = $this->getForm ( '/admin/orders/process' );
		$currency = new Zend_Currency();
		
		$form->getElement ( 'categories' )->addMultiOptions(array('domains' => $this->translator->translate('Domains')));
		$id = intval($this->getRequest ()->getParam ( 'id' ));
		
		$this->view->description = "Here you can edit the selected order.";
		
		if (! empty ( $id ) && is_numeric ( $id )) {
			$rs = $this->orders->find ( $id );
			if (! empty ( $rs )) {
				$rs = $rs->toArray ();
				
				$rs ['setupfee']        = Orders::getSetupfee ( $id );
				$rs ['order_date']      = Shineisp_Commons_Utilities::formatDateOut ( $rs ['order_date'] );
				$rs ['expiring_date']   = Shineisp_Commons_Utilities::formatDateOut ( $rs ['expiring_date'] );
				$rs ['received_income'] = 0;
				$rs ['missing_income']  = $rs['grandtotal'];
				
				//* GUEST - ALE - 20130325: Calculate missing income and received income based on total payments for this order
				$payments = Payments::findbyorderid ( $id, 'income', true );
				if (isset ( $payments )) {
					foreach ( $payments as $payment ) {
						$rs ['received_income'] += (isset($payment['income'])) ? $payment['income'] : 0;
						$rs ['missing_income']  -= (isset($payment['income'])) ? $payment['income'] : 0;
					}
				}

				// set the default income to prepare the payment task
				$rs ['income'] = $rs ['missing_income'];
				$rs ['missing_income'] = sprintf('%.2f',$rs ['missing_income']);
				unset($payments);
				
				$parent = Customers::find ( $rs ['customer_id'] );
				
				//if customer comes from reseller
				if ($parent ['parent_id']) {
					$rs ['customer_parent_id'] = $parent ['parent_id'];
				} else {
					$rs ['customer_parent_id'] = $rs ['customer_id'];
				}
				
				$link = Fastlinks::findlinks ( $id, $rs ['customer_id'], 'Orders' );
				if (isset ( $link [0] )) {
					$rs ['fastlink'] = $link [0] ['code'];
					$rs ['visits'] = $link [0] ['visits'];
				}
				
				$form->populate ( $rs );
				$this->view->id = $id;
				$this->view->customerid = $rs ['customer_id'];
				
				if(!empty($rs['fastlink'])){
					$this->view->titlelink = "/index/link/id/" . $rs['fastlink'];
				}
				
				$this->view->title = $this->translator->_( "Order nr. %s - %s", Orders::formatOrderId($id), $rs ['order_date']);
				$this->view->messages = Messages::find ( 'order_id', $id, true );
			} else {
				$this->_helper->redirector ( 'list', 'orders', 'admin' );
			}
		}
		
		$customer = Customers::get_by_customerid ( $rs ['customer_id'], 'company, firstname, lastname, email' );
		
		$this->view->mex = urldecode ( $this->getRequest ()->getParam ( 'mex' ) );
		$this->view->mexstatus = $this->getRequest ()->getParam ( 'status' );
		
		$createInvoiceConfirmText = ( $rs ['missing_income'] > 0 ) ? $this->translator->translate('Are you sure to create or overwrite invoice for this order? The order is not paid.') : $this->translator->translate('Are you sure to create or overwrite invoice for this order?');
		
		// Create the buttons in the edit form
		$this->view->buttons = array(
									array("url" => "#", "label" => $this->translator->translate('Save'), "params" => array('id'=>'submit', 'css' => array('button', 'float_right', 'submit'))),
									array("url" => "/admin/orders/createinvoice/id/$id", "label" => $this->translator->translate('Invoice'), "params" => array('css' => array('button', 'float_right')), 'onclick' => "return confirm('".$createInvoiceConfirmText."')"),
									array("url" => "/admin/orders/print/id/$id", "label" => $this->translator->translate('Print'), "params" => array('css' => array('button', 'float_right'))),
									array("url" => "/admin/orders/dropboxit/id/$id", "label" => $this->translator->translate('Dropbox It'), "params" => array('css' => array('button', 'float_right'))),
									array("url" => "/admin/orders/renew/id/$id", "label" => $this->translator->translate('Renew'), "params" => array('css' => array('button', 'float_right'))),
									array("url" => "/admin/orders/sendorder/id/$id", "label" => $this->translator->translate('Email'), "params" => array('css' => array('button', 'float_right'))),
									array("url" => "/admin/orders/confirm/id/$id", "label" => $this->translator->translate('Delete'), "params" => array('css' => array('button', 'float_right'))),
									array("url" => "/admin/orders/new/", "label" => $this->translator->translate('New'), "params" => array('css' => array('button', 'float_right'))),
									array("url" => "/admin/customers/edit/id/".$rs ['customer_id'], "label" => $this->translator->translate('Customer'), "params" => array('css' => array('button', 'float_right'))),
								);
		
		// Check if the order has been invoiced
		$invoice_id = Orders::isInvoiced($id);
		if($invoice_id){
			$this->view->buttons[] = array("url" => "/admin/orders/sendinvoice/id/$invoice_id", "label" => $this->translator->translate('Email invoice'), "params" => array('css' => array('button', 'float_right')));
			$this->view->buttons[] = array("url" => "/admin/invoices/print/id/$invoice_id", "label" => $this->translator->translate('Print invoice'), "params" => array('css' => array('button', 'float_right')));
			$this->view->buttons[] = array("url" => "/admin/invoices/dropboxit/id/$invoice_id", "label" => $this->translator->translate('Dropbox invoice'), "params" => array('css' => array('button', 'float_right')));
		}
		
		$this->view->customer = array ('records' => $customer, 'editpage' => 'customers' );
		$this->view->ordersdatagrid = $this->orderdetailGrid ();
		$this->view->paymentsdatagrid = $this->paymentsGrid ();
		$this->view->filesgrid = $this->filesGrid ();
		$this->view->form = $form;
		$this->render ( 'applicantform' );
	}
	
	private function orderdetailGrid() {
		$request = Zend_Controller_Front::getInstance ()->getRequest ();
		if (isset ( $request->id ) && is_numeric ( $request->id )) {
			$rs = Orders::getDetails ( $request->id );
			if (isset ( $rs )) {
				
				// In this section I will delete the empty OrdersItemsDomains subarray created by Doctrine because the simplegrid works only with a flat array
				// where the array keys are the fields. So, if the OrdersItemsDomains is empty means that if the order item doesn't has 
				// a domain attached it this empty array will be deleted in all the recordset.
				// TODO: improve this section when doctrine improve the engine. 
				$myrec = array ();
				foreach ( $rs as $record ) {
					$amount = $record ['quantity'] * $record ['price'] + $record ['setupfee'];
					
					// Add the taxes if the product need them
					if ($record ['taxpercentage'] > 0) {
						$record ['vat'] = number_format ( ($amount * $record ['taxpercentage'] / 100), 2 );
						$record ['grandtotal'] = number_format ( ($amount * (100 + $record ['taxpercentage']) / 100), 2 );
					} else {
						$record ['vat'] = 0;
						$record ['grandtotal'] = $amount;
					}
					
					if (count ( $record ['OrdersItemsDomains'] ) == 0) {
						unset ( $record ['OrdersItemsDomains'] );
					}
					unset ( $record ['taxpercentage'] );
					$myrec [] = $record;
				}
				
				return array ('records' => $myrec, 'delete' => array ('controller' => 'ordersitems', 'action' => 'confirm' ), 'edit' => array ('controller' => 'ordersitems', 'action' => 'edit' ), 'pager' => true );
			}
		}
	}
	
	/**
	 * Creation of the payment transaction grid 
	 * @return multitype:boolean multitype:string
	 */
	private function paymentsGrid() {
		$currency = new Zend_Currency();
		$myrec = array ();
		$requestId = $this->getParam('id');
				
		if (!empty($requestId) && is_numeric ( $requestId )) {
			$rs = Payments::findbyorderid ( $requestId, 'payment_id, paymentdate, b.name as bank, description, reference, confirmed, income, outcome', true );
			
			if (isset ( $rs )) {
				$i = 0;
				
				// Format some data
				foreach ( $rs as $record ) {
					$myrec[$i]['id'] = $record['payment_id'];
					
					// Set the date format
					$myrec[$i]['payment_date'] = Shineisp_Commons_Utilities::formatDateOut ($record['paymentdate']);
					
					$myrec[$i]['description'] = $record['description'];
					$myrec[$i]['reference'] = $record['reference'];
					$myrec[$i]['confirmed'] = $record['confirmed'] ? $this->translator->translate ( 'Yes' ) : $this->translator->translate ( 'No' );
					$myrec[$i]['type'] = $record['bank'];
					
					// Checking the currency set in the configuration
					$myrec[$i]['income'] = $currency->toCurrency($record['income'], array('currency' => Settings::findbyParam('currency')));
					$myrec[$i]['outcome'] = $currency->toCurrency($record['outcome'], array('currency' => Settings::findbyParam('currency')));
						
					$i++;
				}
				
				return array (
					'records' => $myrec, 
					'pager'  => true,
					'edit' => array ('controller' => 'payments', 'action' => 'edit' ),
					'delete' => array ('controller' => 'orders', 'action' => 'deletepayment' ),
				);
			}
		}
	}
	
	
	/*
	 * Renew Action
	 * Renew a order 
	 */
	public function renewAction() {
		$request = Zend_Controller_Front::getInstance ()->getRequest ();
		if (isset ( $request->id ) && is_numeric ( $request->id )) {
			$newOrderId = Orders::cloneOrder ( $request->id );
			if (is_numeric ( $newOrderId )) {
				$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $newOrderId, 'mex' => 'The task requested has been executed successfully.', 'status' => 'success' ) );
			} else {
				$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $request->id, 'mex' => $this->translator->translate ( 'renew_failed' ), 'status' => 'error' ) );
			}
		}
		$this->_helper->redirector ( 'list', 'orders', 'admin' );
	}
	
	/*
	 * Delete payment transaction
	 */
	public function deletepaymentAction() {
		$request = Zend_Controller_Front::getInstance ()->getRequest ();
		if (isset ( $request->id ) && is_numeric ( $request->id )) {

			// Get the order id attached to the payment transaction
			$orderId = Payments::getOrderId($request->id);
			if(is_numeric($orderId)){
				
				// Delete the payment transaction
				if (Payments::deleteByID($request->id)) {
					$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $orderId, 'mex' => 'The task requested has been executed successfully.', 'status' => 'success' ) );
				} else {
					$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $orderId, 'mex' => $this->translator->translate ( 'There was a problem' ), 'status' => 'error' ) );
				}
			}
		}
		$this->_helper->redirector ( 'list', 'orders', 'admin' );
	}
	
	/*
	 * 
	 */
	private function filesGrid() {
		$request = Zend_Controller_Front::getInstance ()->getRequest ();
		if (isset ( $request->id ) && is_numeric ( $request->id )) {
			$rs = Files::findbyExternalId ( $request->id, "orders", "file, Date_Format(date, '%d/%m/%Y') as date" );
			if (isset ( $rs [0] )) {
				return array ('records' => $rs, 'delete' => array ('controller' => 'files', 'action' => 'confirm' ) );
			}
		}
	}
	
	/**
	 * createinvoiceAction
	 * Create an invoice reference
	 * @return void
	 */
	public function createinvoiceAction() {
		$request = Zend_Controller_Front::getInstance ()->getRequest ();
		if (is_numeric ( $request->id )) {
			Orders::Complete($request->id);
			$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $request->id, 'mex' => $this->translator->translate ( 'The task requested has been executed successfully.' ), 'status' => 'success' ) );
		}
	}
	
	/**
	 * processAction
	 * Update the record previously selected
	 * @return unknown_type
	 */
	public function processAction() {
		$form = $this->getForm ( "/admin/orders/process" );
		$request = $this->getRequest ();
		
		// Create the buttons in the edit form
		$this->view->buttons = array(
				array("url" => "#", "label" => $this->translator->translate('Save'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/orders/list", "label" => $this->translator->translate('List'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/orders/new/", "label" => $this->translator->translate('New'), "params" => array('css' => array('button', 'float_right'))),
		);
		
		// Get the id 
		$id = $this->getRequest ()->getParam ( 'order_id' );
		
		// Check if we have a POST request
		if (! $request->isPost ()) {
			return $this->_helper->redirector ( 'list', 'orders', 'admin' );
		}
		
		if ($form->isValid ( $request->getPost () )) {
			
			$params = $form->getValues ();
			
			// Save the data
			$id = Orders::saveAll ( $params, $id );
			
			// Save the upload file
			Orders::UploadDocument($id, $params ['customer_id']);
			
			$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $id, 'mex' => $this->translator->translate ( 'The task requested has been executed successfully.' ), 'status' => 'success' ) );
		} else {
			$this->view->form = $form;
			$this->view->title = "Order Edit";
			$this->view->description = "Here you can edit the selected order.";
			return $this->render ( 'applicantform' );
		}
	}
	
	/**
	 * getForm
	 * Get the customized application form 
	 * @return unknown_type
	 */
	private function getForm($action) {
		$form = new Admin_Form_OrdersForm ( array ('action' => $action, 'method' => 'post' ) );
		return $form;
	}
	
	/**
	 * SortingData
	 * Manage the request of sorting of the order 
	 * @return string
	 */
	private function sortingData($sort) {
		$strSort = "";
		if (! empty ( $sort )) {
			$sort = addslashes ( htmlspecialchars ( $sort ) );
			$sorts = explode ( "-", $sort );
			
			foreach ( $sorts as $sort ) {
				$sort = explode ( ",", $sort );
				$strSort .= $sort [0] . " " . $sort [1] . ",";
			}
			
			if (! empty ( $strSort )) {
				$strSort = substr ( $strSort, 0, - 1 );
			}
		}
		
		return $strSort;
	}
	
	/**
	 * set_status
	 * Set the status of all items passed
	 * @param $items
	 * @return void
	 */
	private function set_status($items) {
		$request = $this->getRequest ();
		$status = $request->getParams ( 'params' );
		$params = parse_str ( $status ['params'], $output );
		$status = $output ['status'];
		if (is_array ( $items ) && is_numeric ( $status )) {
			foreach ( $items as $orderid ) {
				if (is_numeric ( $orderid )) {
					$this->orders->set_status ( $orderid, $status ); // set it as deleted
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * sendorder
	 * Send the order email
	 * @return url link
	 */
	public function sendorderAction() {
		$request = $this->getRequest ();
		$orderid = $request->getParam ( 'id' );
		
		if (is_numeric ( $orderid )) {
			if (Orders::sendOrder ( $orderid )) {
				$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $orderid, 'mex' => $this->translator->translate ( 'The order has been sent successfully.' ), 'status' => 'success' ) );
			}
		}
		$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $orderid, 'mex' => $this->translator->translate ( 'The order has not been found.' ), 'status' => 'error' ) );
	}
	
	/**
	 * sendinvoice
	 * Send the invoice email
	 * @return url link
	 */
	public function sendinvoiceAction() {
		$request = $this->getRequest ();
		$invoiceid = $request->getParam ( 'id' );
		$order = Invoices::getOrderbyInvoiceId ( $invoiceid );
		if ($order) {
			$orderid = $order [0] ['order_id'];
			if (is_numeric ( $invoiceid )) {
				if (Invoices::sendInvoice ( $invoiceid )) {
					$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $order [0] ['order_id'], 'mex' => $this->translator->translate ( 'The invoice has been sent successfully.' ), 'status' => 'success' ) );
				}
			}
			$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $order [0] ['order_id'], 'mex' => $this->translator->translate ( 'The invoice has not been found.' ), 'status' => 'error' ) );
		}
		$this->_helper->redirector ( 'list', 'orders', 'admin', array ('mex' => $this->translator->translate ( 'The invoice has not been found.' ), 'status' => 'error' ) );
	}
	
	
	/**
	 * printAction
	 * Create a pdf invoice document
	 * @return void
	 */
	public function printAction() {
		$request = Zend_Controller_Front::getInstance ()->getRequest ();
		try {
			if (is_numeric ( $request->id )) {
				Orders::pdf ( $request->id, true, true );
			}
		} catch ( Exception $e ) {
			die ( $e->getMessage () );
		}
		die ();
	}
	
	/**
	 * Upload an order to the dropbox account
	 *
	 * @return void
	 */
	public function dropboxitAction() {
		$request = Zend_Controller_Front::getInstance ()->getRequest ();
		if (is_numeric ( $request->id )) {
			$sent = Orders::DropboxIt( $request->id );
			if ($sent) {
				$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $request->id, 'mex' => $this->translator->translate ( 'The order has been uploaded in dropbox.' ), 'status' => 'success' ) );
			} else {
				$this->_helper->redirector ( 'edit', 'orders', 'admin', array ('id' => $request->id, 'mex' => $this->translator->translate ( 'There was a problem during the process.' ), 'status' => 'error' ) );
			}
		}
	}
}