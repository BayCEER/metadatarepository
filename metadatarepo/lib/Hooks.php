<?php 

namespace OCA\MDRepo;


use OC\Files\Filesystem;

class Hooks {
	public static function connectHooks() {
		// Listen to write signals
	    \OCP\Util::connectHook('OC_Filesystem', 'post_write', 'OCA\MDRepo\Hooks', 'write_hook');
	    \OCP\Util::connectHook('OC_Filesystem', 'delete', 'OCA\MDRepo\Hooks', 'remove_hook');
		\OCP\Util::connectHook('OC_Filesystem', 'post_rename', 'OCA\MDRepo\Hooks', 'rename_hook');
//		\OCP\Util::connectHook('OC_Filesystem', 'post_copy', 'OCA\MDRepo\Hooks', 'copy_hook');

//	   $eventDispatcher = \OC::$server->getEventDispatcher();
//	   $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', ['OCA\MDRepo\Hooks']);
	}

	/**
	 * listen to write event.
	 */
	public static function write_hook($params) {
		if (\OCP\App::isEnabled('metadatarepo')) {
		    $path = $params[Filesystem::signal_param_path];
		    if(preg_match('/ReadmeDC\\.(private\\.|)(txt|jpe?g|png|gif|svg)$/i', $path)){
		        Backend::write($path);
		    }
		}
	}


	public static function remove_hook($params) {
	    if (\OCP\App::isEnabled('metadatarepo')) {
	        $path = $params[Filesystem::signal_param_path];
	        if(preg_match('/ReadmeDC\\.(private\\.|)(txt|jpe?g|png|gif|svg)$/i', $path)){
	            Backend::delete($path);
	        }
	        
	    }
	}
	
	
	public static function rename_hook($params) {
	    if (\OCP\App::isEnabled('metadatarepo')) {
	        $oldpath = $params[Filesystem::signal_param_oldpath];
	        $newpath = $params[Filesystem::signal_param_newpath];
	        if(preg_match('/ReadmeDC\\.(private\\.|)(txt|jpe?g|png|gif|svg)$/i', $oldpath) || 
	            preg_match('/ReadmeDC\\.(private\\.|)(txt|jpe?g|png|gif|svg)$/i', $newpath)){
	           if(preg_match('/ReadmeDC\\.(private\\.|)(txt|jpe?g|png|gif|svg)$/i', $oldpath) && 
	               ! preg_match('/ReadmeDC\\.(private\\.|)(txt|jpe?g|png|gif|svg)$/i', $newpath)){
	               Backend::delete($newpath,$oldpath);
	           } elseif(! preg_match('/ReadmeDC\\.(private\\.|)(txt|jpe?g|png|gif|svg)$/i', $oldpath) && 
	               preg_match('/ReadmeDC\\.(private\\.|)(txt|jpe?g|png|gif|svg)$/i', $newpath)){
	               Backend::write($newpath);
	           }
	        }
	        
	    }
	}
/*	
	public static function copy_hook($params) {
	    if (\OCP\App::isEnabled('metadatarepo')) {
	        $path = $params[Filesystem::signal_param_path];
	        if(preg_match('/ReadmeDC\\.txt$/i', $path)){
	            Backend::write($path);
	        }
	        
	    }
	}
	*/
}
