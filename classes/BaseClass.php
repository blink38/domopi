<?php

/**
 * Classe générique pour les managers
 *
 * Contient le logger
 *
 */
class BaseClass{


	private $logger;
	
	function __construct(){
	
		global $DEBUG;
		
		$this->logger = new LogManager();
		$this->logger->setCalledClass(get_called_class());
		
		
		$className = get_class($this);
		global $OPTS;
		if (!isset($OPTS->quiet)){
			if ($DEBUG != null && array_key_exists($className, $DEBUG)){
				$this->logger->setLevel($DEBUG[$className]->level);
			} else {
				$this->logger->setLevel(LogManager::INFO);
			}
		} else {
			$this->logger->setLevel(LogManager::NONE);
		}
		
	}
	
	
	function getLogger(){
		return $this->logger;
	}
}
?>
