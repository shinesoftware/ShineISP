<?php
class Shineisp_Controller_Common extends Zend_Controller_Action {
	/*
	 * Common for the whole admin controllers
	*/
	
	public function init() {
		// Get all settings
		Zend_Registry::set('Settings', Settings::getAll());
		
		// Statuses are used everywhere in system, so we need to make just one query
		Zend_Registry::set('Statuses', Statuses::getAll());
    }	
}