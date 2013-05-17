<?php

/**
 * Roles
 * Manage the roles items table
 * @version 1.0
 */

class Admin_RolesController extends Zend_Controller_Action {
	
	protected $roles;
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
		$this->roles = new AdminRoles();
		$this->translator = Zend_Registry::getInstance ()->Zend_Translate;
		$this->datagrid = $this->_helper->ajaxgrid;
		$this->datagrid->setModule ( "roles" )->setModel ( $this->roles );				
	}
	
	/**
	 * indexAction
	 * Create the User object and get all the records.
	 * @return unknown_type
	 */
	public function indexAction() {
		$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'redirector' );
		$redirector->gotoUrl ( '/admin/roles/list' );
	}
	
	/**
	 * indexAction
	 * Create the User object and get all the records.
	 * @return datagrid
	 */
	public function listAction() {
		$this->view->title = $this->translator->translate("Admin Roles list");
		$this->view->description = $this->translator->translate("Here you can see all the roles.");
		$this->view->buttons = array(array("url" => "/admin/roles/new/", "label" => $this->translator->translate('New'), "params" => array('css' => array('button', 'float_right'))));
		
		$this->datagrid->setConfig ( AdminRoles::grid() )->datagrid ();
	}
	
	/**
	 * Load Json Records
	 *
	 * @return string Json records
	 */
	public function loadrecordsAction() {
		$this->_helper->ajaxgrid->setConfig ( AdminRoles::grid() )->loadRecords ($this->getRequest ()->getParams());
	}
	
	
	/**
	 * searchProcessAction
	 * Search the record 
	 * @return unknown_type
	 */
	public function searchprocessAction() {
		$this->_helper->ajaxgrid->setConfig ( AdminRoles::grid() )->search ();
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
		$this->view->form = $this->getForm ( "/admin/roles/process" );
		$this->view->title = $this->translator->translate("New Role");
		$this->view->description = $this->translator->translate("Here you can create a new roles.");
		$this->view->buttons = array(array("url" => "#", "label" => $this->translator->translate('Save'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
								array("url" => "/admin/roles/list", "label" => $this->translator->translate('List'), "params" => array('css' => array('button', 'float_right'))));
		
		$this->render ( 'applicantform' );
	}

	/**
	 * deleteAction
	 * Delete a record previously selected by the reviews
	 * @return unknown_type
	 */
	public function deleteAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		if (is_numeric ( $id )) {
			AdminRoles::deleteItem( $id );
			$this->_helper->redirector ( 'list', 'roles', 'admin', array ('mex' => $this->translator->translate ( "The task requested has been executed successfully." ), 'status' => 'success' ) );
		}
		return $this->_helper->redirector ( 'index', 'roles' );
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
				$this->view->title = $this->translator->translate ( 'Are you sure to delete this role?' );
				$this->view->description = $this->translator->translate ( 'The permision will be no more longer available and the users attached cannot use the resource.' );
				
				$record = $this->roles->find ( $id, null, true );
				$this->view->recordselected = $record [0] ['AdminResources']['name'] . " (" . $record [0] ['AdminRoles']['name'] . " profile) " . $record [0]['AdminResources']['module'] . ":" . $record [0]['AdminResources']['controller'] . " = " . $record [0]['role'];
			} else {
				$this->_helper->redirector ( 'list', $controller, 'admin', array ('mex' => $this->translator->translate ( 'Unable to process request at this time.' ), 'status' => 'error' ) );
			}
		} catch ( Exception $e ) {
			echo $e->getMessage ();
		}
	}

	/**
	 * editAction
	 * Get a record and populate the application form 
	 * @return unknown_type
	 */
	public function editAction() {
		$auth = Zend_Auth::getInstance ();
		
		$form = $this->getForm ( '/admin/roles/process' );
		$form->getElement ( 'save' )->setLabel ( 'Update' );
		$id = $this->getRequest ()->getParam ( 'id' );
		
		// Create the buttons in the edit form
		$this->view->buttons = array(
				array("url" => "#", "label" => $this->translator->translate('Save'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/roles/list", "label" => $this->translator->translate('List'), "params" => array('css' => array('button', 'float_right'))),
				array("url" => "/admin/roles/new/", "label" => $this->translator->translate('New'), "params" => array('css' => array('button', 'float_right'))),
		);
		
		if (! empty ( $id ) && is_numeric ( $id )) {
			$rs = AdminRoles::find ( $id, null, true );
			if (! empty ( $rs[0] )) {
				
				// Load the users connected to this role
				$users = AdminUser::getUserbyRoleID($id);
				
				// Load the roles of each resource
				$roles = AdminPermissions::getPermissionByRoleID($id);
				
				// Load the resources
				$this->view->resources = json_encode ( AdminResources::createTree ( 0, $roles ) );
				
				// Join the roles and the users
				$rs[0]['users'] = $users;
				
				$form->populate ( $rs[0] );
				$this->view->buttons[] = array("url" => "/admin/roles/confirm/id/$id", "label" => $this->translator->translate('Delete'), "params" => array('css' => array('button', 'float_right')));
			}
		}
		
		$this->view->mex = $this->getRequest ()->getParam ( 'mex' );
		$this->view->mexstatus = $this->getRequest ()->getParam ( 'status' );
		$this->view->title = $this->translator->translate("Role edit");
		$this->view->description = $this->translator->translate("Here you can edit the role permissions.");
		
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
		$form = $this->getForm ( "/admin/roles/process" );
		$request = $this->getRequest ();
		
		// Create the buttons in the edit form
		$this->view->buttons = array(
				array("url" => "#", "label" => $this->translator->translate('Save'), "params" => array('css' => array('button', 'float_right'), 'id' => 'submit')),
				array("url" => "/admin/roles/list", "label" => $this->translator->translate('List'), "params" => array('css' => array('button', 'float_right'))),
				array("url" => "/admin/roles/new/", "label" => $this->translator->translate('New'), "params" => array('css' => array('button', 'float_right'))),
		);
		
		// Check if we have a POST request
		if (! $request->isPost ()) {
			return $this->_helper->redirector ( 'list', 'roles', 'admin' );
		}
		
		if ($form->isValid ( $request->getPost () )) {
			
			$params = $request->getPost ();
			
			if(AdminRoles::SaveAll($params, $params ['role_id'])){
				$this->_helper->redirector ( 'list', 'roles', 'admin', array ('mex' => $this->translator->translate ( "The task requested has been executed successfully." ), 'status' => 'success' ) );
			}else{
				$this->_helper->redirector ( 'list', 'roles', 'admin', array ('mex' => $this->translator->translate ( "There was an error on save the data." ), 'status' => 'error' ) );
			}
		} else {
			$this->view->form = $form;
			$this->view->title = "Role edit";
			$this->view->description = "Here you can edit the role data.";
			return $this->render ( 'applicantform' );
		}
	}
	
	/**
	 * getForm
	 * Get the customized application form 
	 * @return form
	 */
	private function getForm($action) {
		$form = new Admin_Form_RolesForm ( array ('action' => $action, 'method' => 'post' ) );
		return $form;
	}

}
