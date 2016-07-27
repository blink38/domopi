<?php

/**
 * Classe de gestion de la configuration
 */
 
 class Configuration  extends BaseClass{
 
	private $configfile;
	
	private $cfg_modes = null;
	private $cfg_progs = null;
	private $cfg_sensors = null;
	private $cfg_debug = null;
	private $cfg_gpio = null;
	private $cfg_actions = null;
	private $cfg_stats = null;

	
	private $cfg = null;
	private $cfg_loaded = false;

	function __construct($cfgfile){
	

		$this->configfile = $cfgfile;

		parent::__construct();
	
		global $CFG;
		if (empty($CFG)){
			$CFG = new StdClass();
		}
		
		$this->cfg_sensors = new ConfigurationSensors();
		$this->cfg_modes = new ConfigurationModes();
		$this->cfg_progs = new ConfigurationPrograms();
		$this->cfg_debug = new ConfigurationDebug();
		$this->cfg_gpio = new ConfigurationGpio();
		$this->cfg_actions = new ConfigurationActions();
		$this->cfg_stats = new ConfigurationStats();
		
		global $OPTS;
		if (!isset($OPTS->quiet)){
			if (isset($OPTS->verbose)){
				$_level = LogManager::DEBUG;
			} else {
				$_level = LogManager::INFO;
			}

			$this->cfg_sensors->getLogger()->setLevel($_level);
			$this->cfg_modes->getLogger()->setLevel($_level);
			$this->cfg_progs->getLogger()->setLevel($_level);
			$this->cfg_debug->getLogger()->setLevel($_level);
			$this->cfg_gpio->getLogger()->setLevel($_level);
			$this->cfg_actions->getLogger()->setLevel($_level);
			$this->cfg_stats->getLogger()->setLevel($_level);
		}
	}
	
	public function setLogLevel($level){
	
		$this->getLogger()->setLevel($level);
		$this->cfg_sensors->getLogger()->setLevel($level);
		$this->cfg_modes->getLogger()->setLevel($level);
		$this->cfg_progs->getLogger()->setLevel($level);
		$this->cfg_debug->getLogger()->setLevel($level);
		$this->cfg_gpio->getLogger()->setLevel($level);
		$this->cfg_actions->getLogger()->setLevel($level);
		$this->cfg_stats->getLogger()->setLevel($level);
	}
	
	/**
	 * Load the XML configuration file
	 *
	 * @return false if configuration file was not found
	 *         true if configuration is open and XML content is valid
	 */
	public function load(){

		$this->cfg = new DOMDocument();
		
		if (file_exists($this->configfile)){
			$this->getLogger()->info("Opening configuration file ".$this->configfile);
			$this->cfg_loaded = $this->cfg->load($this->configfile);
		} else {
			$this->getLogger()->error("Configuration file ".$this->configfile." was not found :-(");
			$this->cfg_loaded = false;
			return false;
		}
		return $this->cfg_loaded;
	}
 
	/**
	 * Read the configuration file
	 *
	 * @return : true if configuration is read, false otherwise
	 */
	public function read(){
	
		if (!$this->cfg_loaded){
			$this->load();
		}
		
		if ($this->cfg_loaded){
			
			$this->getLogger()->debug("Starting reading XML configuration");
			$config = $this->cfg->getElementsByTagName("configuration");
				
			if ($config->length == 1){
			
				// first read debug configuration
				$this->cfg_debug->read_debug($config->item(0)->getElementsByTagName("debug")->item(0));
				
				// $this->read_sensors($config->item(0));
				$this->cfg_sensors->read_sensors($config->item(0));
				
				$this->cfg_actions->read_actions($config->item(0)->getElementsByTagName("actions")->item(0));
				$this->cfg_gpio->read_gpios($config->item(0)->getElementsByTagName("gpios")->item(0));
				$this->cfg_stats->read_stats($config->item(0)->getElementsByTagName("statistics")->item(0));
				
				$this->cfg_modes->read_modes($config->item(0)->getElementsByTagName("modes")->item(0));
				$this->cfg_progs->read_programs($config->item(0)->getElementsByTagName("programs")->item(0));
			} else {
				$this->getLogger()->error("section <configuration> not found. Can't read configuration.");
				return false;
			}
		}
		
		return true;
	}
	
	
	/**
	 * Read a boolean value
	 * 
	 * if "true" ou "TRUE" then return true (boolean type)
	 * if "false" or "FALSE" then return false (boolean type)
	 * otherwise return $defaultValue (or false if no default value)
	 *
	 * @param str string value to read
	 * @param default default value (default = false)
	 * @return a boolean
	 */
	 public static function get_boolean_from_string($str, $default = false){
	 
		if ($default !== false or $default !== true){
			$default = false;
		}
		
		if (strcasecmp(trim($str),"true") == 0){
			return true;
		}
		
		if (strcasecmp(trim($str),"false") == 0){
			return false;
		}
		
		return $default;
	}
	
	/**
	 * Read a hour value (timestamp - int)
	 * 
	 * @param str string value to read
	 * @return a boolean
	 */
	 public static function get_hour_from_string($str){
	 
		// hour can be : *:10 (at 10mn past each hour)
		if (strpos($str,"*:") === 0){
			if (is_numeric(substr($str,2))){
				return $str;
			}
		}
		
		if (($timestamp = strtotime($str)) === false) {
			return false;
		}
		
		// error_log(date("H:i",$timestamp));
		return $timestamp;
	}
	
	/**
	 * Read a int value
	 *
	 * @param str string value to read
	 * @return a numeric (or null)
	 */
	public static function get_numeric_from_string($str){
	
		if (is_numeric(trim($str))){
			return intval(trim($str));
		}
		
		return null;
	}
	
 }

 
?>
