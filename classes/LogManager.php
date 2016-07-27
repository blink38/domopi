<?php

/**
 * Classe de gestion des journaux d'événement
 */
 
 class LogManager{
 
	const INFO = "INFO";
	const NOTICE = "NOTICE";
	const DEBUG = "DEBUG";
	const WARNING = "WARNING";
	const ERROR = "ERROR";
	const NONE = "NONE";
	
	private $a_level = array(
		LogManager::NONE => 0,
		LogManager::INFO =>  1,
		LogManager::ERROR => 1,
		LogManager::WARNING => 1,
		LogManager::DEBUG => 5,
		LogManager::NOTICE => 2
	);
	
	private $level = LogManager::INFO;
	
	private $calledClass = "";
	
	public function setLevel($level){
		$this->level = strtoupper($level);
	}
	
	public function setCalledClass($cl){
		$this->calledClass = $cl;
	}
	
	/**
	 * Ajout d'un message d'information
	 *
	 * @param $message message à ajouter au journal d'événement
	 */
	public function info($message){
		$this->log(LogManager::INFO,$message);
	}
	
	/**
	 * Ajout d'un message de notice (plus verbeux que information)
	 *
	 * @param $message message à ajouter au journal d'événement
	 */
	public function notice($message){
		$this->log(LogManager::NOTICE,$message);
	}
 
	/**
	 * Ajout d'un message de warning
	 *
	 * @param $message message à ajouter au journal d'événement
	 */
	public function warning($message){
		$this->log(LogManager::WARNING,$message);
	}

	/**
	 * Ajout d'un message de debug
	 *
	 * @param $message message à ajouter au journal d'événement
	 */
	public function debug($message){
		$this->log(LogManager::DEBUG,$message);
	}
	
	/**
	 * Ajout d'un message d'erreur
	 *
	 * @param $message message à ajouter au journal d'événement
	 */
	public function error($message){
		$this->log(LogManager::ERROR,$message);
	}


	/**
	 * Ajout d'un message de log
	 *
	 * @param $level niveau du message
	 * @param $message message à ajouter
	 */
	 public function log($level, $message){
	
		global $OPTS;

		if (isset($OPTS->quiet)){
			return;
		}	 
		// error_log("1. a_level[".$level."] = ".$this->a_level[$level]);
		// error_log("2. a_level[".$this->level."] = ".$this->a_level[$this->level]);
		
		if ($this->a_level[$level] <= $this->a_level[$this->level]){
			$d = date("d/m/Y H:i:s");
			error_log($d." [" . $level . "] ".$this->calledClass . " - ".$message);
		}
	 }
 
 
 
 }
 
 ?>
