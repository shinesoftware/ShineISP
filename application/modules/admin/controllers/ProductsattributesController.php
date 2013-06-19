<?php
/**
 * Products Attributes Class
 * Handle the product attributes
 * @version 1.0
 */

class Admin_ProductsattributesController extends Shineisp_Controller_Admin {
	
	protected $productsattributes;
	protected $datagrid;
	protected $session;
	protected $translator;
	
	public function preDispatch() {	
		$this->session = new Zend_Session_Namespace ( 'Admin' );
		$this->productsattributes = new ProductsAttributes();
		$this->translator = Shineisp_Registry::getInstance ()->Zend_Translate;
		$this->datagrid = $this->_helper->ajaxgrid;
		$this->datagrid->setModule ( "productsattributes" )->setModel ( $this->productsattributes );		
	}
	
	/**
	 * indexAction
	 * Create the User object and get all the records.
	 * @return 
	 */
	public function indexAction() {
		$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'redirector' );
		$redirector->gotoUrl ( '/admin/productsattributes/list' );
	}

	/**
	 * indexAction
	 * Create the User object and get all the records.
	 * @return datagrid
	 */
	public function listAction() {
		$this->view->title = $this->translator->translate("Product Attributes");
		$this->view->description = $this->translator->translate("Here you can see all the attributes.");
		$this->view->buttons = array(array("url" => "/admin/productsattributes/new/", "label" => $this->translator->translate('New'), "params" => array('css' => array('button', 'float_right'))));
		$this->datagrid->setConfig ( ProductsAttributes::grid() )->datagrid ();
	}

	/**
	 * Load Json Records
	 *
	 * @return string Json records
	 */
	public function loadrecordsAction() {
		$this->_helper->ajaxgrid->setConfig ( ProductsAttributes::grid() )->loadRecords ($this->getRequest ()->getParams());
	}
	
	/**
	 * searchProcessAction
	 * Search the record 
	 * @return unknown_type
	 */
	public function searchprocessAction() {
		$this->_helper->ajaxgrid->setConfig ( ProductsAttributes::grid() )->search ();
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
		$Session = new Zend_Session_Namespace ( 'Admin' );
		$this->view->form = $this->getForm ( "/admin/productsattributes/process" );

		// I have to add the language id into the hidden field in order to save the record with the language selected 
		$this->view->form->populate ( array('language_id' => $Session->langid) );
		
		$this->view->buttons = array(array("url" => "#", "label" => $this->translator->translate('Save'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
									 array("url" => "/admin/productsattributes/list", "label" => $this->translator->translate('List'), "params" => array('css' => array('button', 'float_right'))));
		
		$this->view->title = $this->translator->translate("Attributes");
		$this->view->description = $this->translator->translate("Here you can edit the attribute details.");
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
				$this->view->title = $this->translator->translate ( 'Are you sure to delete the record selected?' );
				$this->view->description = $this->translator->translate ( 'If you delete the bank information parameters the customers cannot pay you anymore with this method of payment' );
	
				$record = $this->productsattributes->find ( $id )->toArray();
				$this->view->recordselected = $record ['code'];
			} else {
				$this->_helper->redirector ( 'list', $controller, 'admin', array ('mex' => $this->translator->translate ( 'Unable to process request at this time.' ), 'status' => 'error' ) );
			}
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}	
	
	/**
	 * getForm
	 * Get the customized application form 
	 * @return unknown_type
	 */
	private function getForm($action) {
		$form = new Admin_Form_ProductsAttributesForm ( array ('action' => $action, 'method' => 'post' ) );
		return $form;
	}
	
	/**
	 * deleteAction
	 * Delete a record previously selected by the customer
	 * @return unknown_type
	 */
	public function deleteAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$controller = Zend_Controller_Front::getInstance ()->getRequest ()->getControllerName ();
		
		if (! empty ( $id ) && is_numeric ( $id )) {
			if(ProductsAttributes::deleteAttribute($id)){
				$this->_helper->redirector ( 'list', $controller, 'admin', array ('mex' => $this->translator->translate ( 'Attribute deleted' ), 'status' => 'information' ) );	
			}
		}
		$this->_helper->redirector ( 'list', $controller, 'admin', array ('mex' => $this->translator->translate ( 'Unable to process request at this time.' ), 'status' => 'error' ) );
	}
	
	/**
	 * editAction
	 * Get a record and populate the application form 
	 * @return unknown_type
	 */
	public function editAction() {
		$Session = new Zend_Session_Namespace ( 'Admin' );
		$form = $this->getForm ( '/admin/productsattributes/process' );
		
		// Create the buttons in the edit form
		$this->view->buttons = array(
				array("url" => "#", "label" => $this->translator->translate('Save'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/productsattributes/list", "label" => $this->translator->translate('List'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/productsattributes/new/", "label" => $this->translator->translate('New'), "params" => array('css' => array('button', 'float_right'))),
		);
		
		// Set the system field attribute title
		$panel = Isp::getPanel(); 
		if(!empty($panel)){
			$form->getElement ( 'system_var' )->setLabel ( $panel );
		}
		
		$id = $this->getRequest ()->getParam ( 'id' );
		
		if (! empty ( $id ) && is_numeric ( $id )) {
			$rs = $this->productsattributes->getAllInfo ( $id, "*, pad.label as label, pad.prefix as prefix, pad.suffix as suffix, pad.description as description, pad.language_id as language_id", $Session->langid );
			
			if (! empty ( $rs )) {
				$this->view->id = $id;
				$rs['language_id'] = $Session->langid; // added to the form the language id selected 
				$form->populate ( $rs );
			}
			
			$this->view->buttons[] = array("url" => "/admin/productsattributes/confirm/id/$id", "label" => $this->translator->translate('Delete'), "params" => array('css' => array('button', 'float_right')));
				
		}
		$this->view->title = $this->translator->translate("Attribute Group");
		$this->view->description = $this->translator->translate("Here you can edit the attribute group details.");
		
		$this->view->form = $form;
		$this->render ( 'applicantform' );
	}
	
	/**
	 * processAction
	 * Update the record previously selected
	 * @return unknown_type
	 */
	public function processAction() {
		$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'redirector' );
		$form = $this->getForm ( "/admin/productsattributes/process" );
		$request = $this->getRequest ();
		
		// Create the buttons in the edit form
		$this->view->buttons = array(
				array("url" => "#", "label" => $this->translator->translate('Save'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/productsattributes/list", "label" => $this->translator->translate('List'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/productsattributes/new/", "label" => $this->translator->translate('New'), "params" => array('css' => array('button', 'float_right'))),
		);
		
		try {
			
			// Check if we have a POST request
			if (! $request->isPost ()) {
				return $this->_helper->redirector ( 'list', 'productsattributes', 'admin' );
			}
			
			if ($form->isValid ( $request->getPost () )) {
				$params = $request->getParams();
				
				$id = $params['attribute_id'];
				$code = $params['code'];
				$label = $params['label'];
				$type = $params['type'];
				$prefix = $params['prefix'];
				$suffix = $params['suffix'];
				$is_system_var = $params['system'];
				$description = $params['description'];
				$position = $params['position'];
				$is_visible_on_front = $params['is_visible_on_front'];
				$active = $params['active'];
				$system = $params['system'];
				$system_var = $params['system_var'];
				$defaultvalue = $params['defaultvalue'];
				$is_required = $params['is_required'];
				$is_comparable = $params['is_comparable'];
				$on_product_listing = !empty($params['on_product_listing']) && $params['on_product_listing'] == 1 ? true : false;
				$language_id = $params['language_id'];
				
				$id = ProductsAttributes::addNew($id, $code, $label, $type, $language_id, $position, $active, $prefix, $suffix, $description, $is_visible_on_front, $is_system_var, $system_var, $defaultvalue, $is_required, $is_comparable, $on_product_listing);
				
				if($id === false){
					$this->_helper->redirector ( 'list', 'productsattributes', 'admin', array ('mex' => "There was an error during the saving process. Check all the parameters.", 'status' => 'error' ) );
				}				
				$this->_helper->redirector ( 'edit', 'productsattributes', 'admin', array ('id' => $id, 'mex' => $this->translator->translate ( 'The task requested has been executed successfully.' ), 'status' => 'success' ) );
			
			} else {
				$this->view->form = $form;
				$this->view->title = $this->translator->translate("Hosting Plan Feature details");
				$this->view->description = $this->translator->translate("Here you can fix the hosting plan feature details.");
				return $this->render ( 'applicantform' );
			}
		} catch ( Exception $e ) {
			$this->_helper->redirector ( 'edit', 'productsattributes', 'admin', array ('id' => $id, 'mex' => $e->getMessage (), 'status' => 'error' ) );
		}
	}
}