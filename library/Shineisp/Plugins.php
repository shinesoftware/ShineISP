<?php
/**
 * ShineISP new Plugin Architecture
 * @author GUEST.it s.r.l. <assistenza@guest.it>
 *
 */
class Shineisp_Plugins {
	/*
	 * Initialize all plugins available and register subscriptions
	 */
	public function initAll() {
		$path = PROJECT_PATH . "/library/Shineisp/Plugins";
		$iterator = new DirectoryIterator($path);
		foreach ($iterator as $fileinfo) {
    		if ($fileinfo->isDir() && !$fileinfo->isDot()) {
        		$pluginDir      = $fileinfo->getFilename();
				$pluginMainFile = $path.'/'.$pluginDir.'/Main.php';
				$pluginName     = 'Shineisp_Plugins_'.$pluginDir.'_Main';
				
				// Check if plugins looks good
				$reflectionClass = new ReflectionClass($pluginName);
				if ( ! ($reflectionClass->isInstantiable() && $reflectionClass->implementsInterface('Shineisp_Plugins_Interface') && is_callable(array($pluginName,'onInit')) ) ) {
					Shineisp_Commons_Utilities::logs("Skipping not instantiable plugin '".$pluginName."'", "plugins.log" );
					continue;
				}

				// Initialize
				$plugin = new $pluginName;
				$plugin->onInit();				 
				
				// Check if the Main exists
				if ( file_exists($pluginMainFile) && is_readable($pluginMainFile) ) {
					// TODO: parse XML config file
				} 
    		}
		}
	}
}
