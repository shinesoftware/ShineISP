<?php

/**
 * BanksController
 * Manage the product category table
 * @version 1.0
 */

class Admin_BanksController extends Shineisp_Controller_Admin {
	
	protected $banks;
	protected $datagrid;
	protected $session;
	protected $translator;
	
	/**
	 * preDispatch
	 * Starting of the module
	 * (non-PHPdoc)
	 * @see library/Zend/Controller/Zend_Controller_Action#preDispatch()
	 */
	
	public function preDispatch() {
		$this->session = new Zend_Session_Namespace ( 'Admin' );
		$this->banks = new Banks ();
		$this->translator = Shineisp_Registry::getInstance ()->Zend_Translate;
		$this->datagrid = $this->_helper->ajaxgrid;
		$this->datagrid->setModule ( "banks" )->setModel ( $this->banks );		
	}
	
	/**
	 * indexAction
	 * Create the User object and get all the records.
	 * @return unknown_type
	 */
	public function indexAction() {
		$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'redirector' );
		$redirector->gotoUrl ( '/admin/banks/list' );
	}
	
	/**
	 * indexAction
	 * Create the User object and get all the records.
	 * @return datagrid
	 */
	public function listAction() {
		$this->view->title = $this->translator->translate("Banks list");
		$this->view->description = $this->translator->translate("Here you can see all the banks.");
		$this->view->buttons = array(array("url" => "/admin/banks/new/", "label" => $this->translator->translate('New'), "params" => array('css' => array('button', 'float_right'))));
		$this->datagrid->setConfig ( Banks::grid() )->datagrid ();
	}
	
	/**
	 * Load Json Records
	 *
	 * @return string Json records
	 */
	public function loadrecordsAction() {
		$this->_helper->ajaxgrid->setConfig ( Banks::grid() )->loadRecords ($this->getRequest ()->getParams());
	}
	
	
	/**
	 * searchProcessAction
	 * Search the record 
	 * @return unknown_type
	 */
	public function searchprocessAction() {
		$this->_helper->ajaxgrid->setConfig ( Banks::grid() )->search ();
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
		$this->view->form = $this->getForm ( "/admin/banks/process" );
		$this->view->title = $this->translator->translate("Bank Details");
		$this->view->description = $this->translator->translate("Here you can handle the bank parameters");
		$this->view->buttons = array(array("url" => "#", "label" => $this->translator->translate('Save'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
									 array("url" => "/admin/banks/list", "label" => $this->translator->translate('List'), "params" => array('css' => array('button', 'float_right'))));
		$this->render ( 'applicantform' );
	}
	
	/**
	 * resetAction
	 * Reset the filter previously set
	 */
	public function resetAction() {
		$NS = new Zend_Session_Namespace ( 'Admin' );
		unset ( $NS->search_banks );
		$this->_helper->redirector ( 'index', 'banks' );
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
				$this->view->title = $this->translator->translate ( 'Are you sure to delete the record selected?' );
				$this->view->description = $this->translator->translate ( 'If you delete the bank information parameters the customers cannot pay you anymore with this method of payment' );
				
				$record = $this->banks->find ( $id );
				$this->view->recordselected = $record [0] ['name'];
			} else {
				$this->_helper->redirector ( 'list', $controller, 'admin', array ('mex' => $this->translator->translate ( 'Unable to process request at this time.' ), 'status' => 'error' ) );
			}
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}
	
	/**
	 * deleteAction
	 * Delete a record previously selected by the category
	 * @return unknown_type
	 */
	public function deleteAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		try {
			$this->banks->find ( $id )->delete ();
		} catch ( Exception $e ) {
			$this->_helper->redirector ( 'list', 'banks', 'admin', array ('mex' => $this->translator->translate ( 'Unable to process request at this time.' ) . ": " . $e->getMessage (), 'status' => 'error' ) );
		}
		return $this->_helper->redirector ( 'list', 'banks', 'admin' );
	}
	
	/**
	 * editAction
	 * Get a record and populate the application form 
	 * @return unknown_type
	 */
	public function editAction() {
		$form = $this->getForm ( '/admin/banks/process' );
		$id = $this->getRequest ()->getParam ( 'id' );
		
		// Create the buttons in the edit form
		$this->view->buttons = array(
				array("url" => "#", "label" => $this->translator->translate('Save'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/banks/list", "label" => $this->translator->translate('List'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/banks/new/", "label" => $this->translator->translate('New'), "params" => array('css' => array('button', 'float_right'))),
		);
		
		if (! empty ( $id ) && is_numeric ( $id )) {
			$rs = $this->banks->getAllInfo ( $id, null, true );
			
			if (! empty ( $rs [0] )) {
				$form->populate ( $rs [0] );
			}
			
			$this->view->buttons[] = array("url" => "/admin/banks/confirm/id/$id", "label" => $this->translator->translate('Delete'), "params" => array('css' => array('button', 'float_right')));
				
		}
		
		$this->view->title = $this->translator->translate("Bank Details");
        $this->view->description = $this->translator->translate("Here you can edit the main bank information parameters. Be careful, if you change something the module could be damaged.");
		
		$this->view->mex = $this->getRequest ()->getParam ( 'mex' );
		$this->view->mexstatus = $this->getRequest ()->getParam ( 'status' );
		
		$this->view->form = $form;
		$this->render ( 'applicantform' );
	}
	
	/**
	 * processAction
	 * Update the record previously selected
	 * @return unknown_type
	 */
	public function processAction() {
		$i = 0;
		$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'redirector' );
		$form = $this->getForm ( "/admin/banks/process" );
		$request = $this->getRequest ();
		
		// Create the buttons in the edit form
		$this->view->buttons = array(
				array("url" => "#", "label" => $this->translator->translate('Save'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/banks/list", "label" => $this->translator->translate('List'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/banks/new/", "label" => $this->translator->translate('New'), "params" => array('css' => array('button', 'float_right'))),
		);
		
		// Check if we have a POST request
		if (! $request->isPost ()) {
			return $this->_helper->redirector ( 'list', 'banks', 'admin' );
		}
		
		if ($form->isValid ( $request->getPost () )) {
			// Get the id 
			$id = $this->getRequest ()->getParam ( 'bank_id' );
			
			// Set the new values
			if (is_numeric ( $id )) {
				$this->banks = Doctrine::getTable ( 'Banks' )->find ( $id );
			}
			
			// Get the values posted
			$params = $form->getValues ();
			try {
				
				$this->banks->name = $params ['name'];
				$this->banks->account = $params ['account'];
				$this->banks->method_id = $params ['method_id'];
				$this->banks->url_test = $params ['url_test'];
				$this->banks->url_official = $params ['url_official'];
				$this->banks->enabled = $params ['enabled'];
				$this->banks->classname = $params ['classname'];
				$this->banks->test_mode = $params ['test_mode'];
				$this->banks->description = $params ['description'];
				
				// Save the data
				$this->banks->save ();
				$id = is_numeric ( $id ) ? $id : $this->banks->getIncremented ();
				
				$this->_helper->redirector ( 'edit', 'banks', 'admin', array ('id' => $id, 'mex' => 'The task requested has been executed successfully.', 'status' => 'success' ) );
			
			} catch ( Exception $e ) {
				$this->_helper->redirector ( 'edit', 'banks', 'admin', array ('id' => $id, 'mex' => $this->translator->translate ( 'Unable to process request at this time.' ) . ": " . $e->getMessage (), 'status' => 'error' ) );
			}
			
			$redirector->gotoUrl ( "/admin/banks/edit/id/$id" );
		} else {
			$this->view->form = $form;
			$this->view->title = $this->translator->translate("Bank Edit");
			$this->view->description = $this->translator->translate("Edit the bank information");
			return $this->render ( 'applicantform' );
		}
	}
	
	/**
	 * getForm
	 * Get the customized application form 
	 * @return unknown_type
	 */
	private function getForm($action) {
		$form = new Admin_Form_BanksForm ( array ('action' => $action, 'method' => 'post' ) );
		return $form;
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
			foreach ( $items as $categoryid ) {
				if (is_numeric ( $categoryid )) {
					$this->banks->set_status ( $categoryid, $status );
				}
			}
			return true;
		}
		return false;
	}
	
}
