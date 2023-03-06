<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';
function rgb2hex($r, $g, $b)
{
	$r = dechex($r);
	if (strlen($r) == 1) {
		$r = '0' . $r;
	}
	$g = dechex($g);
	if (strlen($g) == 1) {
		$g = '0' . $g;
	}
	$b = dechex($b);
	if (strlen($b) == 1) {
		$b = '0' . $b;
	}
	$hex = '#' . $r . $g . $b;
	return $hex;
}

class hyperionNG extends eqLogic
{
	/*     * *************************Attributs****************************** */

	/*
	* Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
	* Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	*/

	public static $_widgetPossibility = array('custom' => true);

	/*
	* Permet de crypter/décrypter automatiquement des champs de configuration du plugin
	* Exemple : "param1" & "param2" seront cryptés mais pas "param3"
	public static $_encryptConfigKey = array('param1', 'param2');
	*/

	/*     * ***********************Methode static*************************** */

	public static function update()
	{
		foreach (eqLogic::byType(__CLASS__, true) as $eqLogic) {
			$autorefresh = $eqLogic->getConfiguration('autorefresh');
			if ($autorefresh != '') {
				try {
					$c = new Cron\CronExpression(checkAndFixCron($autorefresh), new Cron\FieldFactory);
					if ($c->isDue()) {
						$eqLogic->refreshData();
					}
				} catch (Exception $exc) {
					log::add(__CLASS__, 'error', $eqLogic->getHumanName() . ' : Invalid cron expression : ' . $autorefresh);
				}
			}
		}
	}

	/*
	* Fonction exécutée automatiquement toutes les minutes par Jeedom
	public static function cron() {}
	*/

	/*
	* Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
	public static function cron5() {}
	*/

	/*
	* Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
	public static function cron10() {}
	*/

	/*
	* Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
	public static function cron15() {}
	*/

	/*
	* Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
	public static function cron30() {}
	*/

	/*
	* Fonction exécutée automatiquement toutes les heures par Jeedom
	public static function cronHourly() {}
	*/

	/*
	* Fonction exécutée automatiquement tous les jours par Jeedom
	public static function cronDaily() {}
	*/

	/*     * *********************Méthodes d'instance************************* */

	// Fonction exécutée automatiquement avant la création de l'équipement
	public function preInsert()
	{
		$this->setIsEnable(1);
		$this->setIsVisible(1);
	}

	// Fonction exécutée automatiquement après la création de l'équipement
	public function postInsert()
	{
	}

	// Fonction exécutée automatiquement avant la mise à jour de l'équipement
	public function preUpdate()
	{
	}

	// Fonction exécutée automatiquement après la mise à jour de l'équipement
	public function postUpdate()
	{
	}

	// Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
	public function preSave()
	{
	}

	// Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
	public function postSave()
	{
		$order = 0;
		if (!is_file(dirname(__FILE__) . '/../config/cmds/commands.json')) {
			log::add(__CLASS__, 'error', $this->getHumanName() . ' : Command creation file not found');
		}
		$commands = json_decode(file_get_contents(dirname(__FILE__) . '/../config/cmds/commands.json'), true);
		// log::add(__CLASS__, 'debug', $this->getHumanName() . ' : $commands : ' . print_r($commands, true));
		foreach ($commands as $command) {
			// log::add(__CLASS__, 'debug', $this->getHumanName() . ' : $command : ' . print_r($command, true));
			$cmd = $this->getCmd(null, $command['logicalId']);
			if (!is_object($cmd)) {
				log::add(__CLASS__, 'info', $this->getHumanName() . ' : Command [' . $command['name'] . '] created');
				$cmd = (new hyperionNGCmd);
				$cmd->setEqLogic_id($this->getId());
				$cmd->setName($command['name']);
				$cmd->setLogicalId($command['logicalId']);
				$cmd->setType($command['type']);
				$cmd->setSubType($command['subType']);
				$cmd->setOrder($order++);
				if (isset($command['unite'])) {
					$cmd->setUnite($command['unite']);
				}
				// if (isset($command['isHistorized'])) {
				// 	$cmd->setIsHistorized($command['isHistorized']);
				// }
				if (isset($command['configuration'])) {
					foreach ($command['configuration'] as $key => $value) {
						$cmd->setConfiguration($key, $value);
					}
				}
				if (isset($command['value'])) {
					$cmd->setValue($this->getCmd(null, $command['value'])->getId());
				}
				if (isset($command['generic_type'])) {
					$cmd->setGeneric_type($command['generic_type']);
				}
				if (isset($command['template'])) {
					foreach ($command['template'] as $key => $value) {
						$cmd->setTemplate($key, $value);
					}
				}
				if (isset($command['isVisible'])) {
					$cmd->setIsVisible($command['isVisible']);
				}
				$cmd->save();
			}
		}
	}

	// Fonction exécutée automatiquement avant la suppression de l'équipement
	public function preRemove()
	{
	}

	// Fonction exécutée automatiquement après la suppression de l'équipement
	public function postRemove()
	{
	}

	/*
	* Permet de crypter/décrypter automatiquement des champs de configuration des équipements
	* Exemple avec le champ "Mot de passe" (password)
	public function decrypt() {
		$this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
	}
	public function encrypt() {
		$this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
	}
	*/

	/*
	* Permet de modifier l'affichage du widget (également utilisable par les commandes)
	public function toHtml($_version = 'dashboard') {}
	*/

	/*
	* Permet de déclencher une action avant modification d'une variable de configuration du plugin
	* Exemple avec la variable "param3"
	public static function preConfig_param3( $value ) {
		// do some checks or modify on $value
		return $value;
	}
	*/

	/*
	* Permet de déclencher une action après modification d'une variable de configuration du plugin
	* Exemple avec la variable "param3"
	public static function postConfig_param3($value) {
		// no return value
	}
	*/

	public function refreshDataOld()
	{
		if ($this->getIsEnable() == 1) {
			if ($this->getConfiguration('serverId') == '') {
				$cmd = 'sudo /usr/bin/speedtest --accept-license --accept-gdpr --format=json';
			} else {
				$cmd = 'sudo /usr/bin/speedtest --accept-license --accept-gdpr --format=json --server-id=' . $this->getConfiguration('serverId');
			}
			log::add(__CLASS__, 'debug', $this->getHumanName() . ' : $cmd : ' . $cmd);
			$speedtest = shell_exec($cmd);
			if ($speedtest == false || $speedtest == null) {
				$speedtest = shell_exec($cmd . ' 2>&1');
				log::add(__CLASS__, 'debug', $this->getHumanName() . ' : $speedtest : ' . $speedtest);
				$speedtests = explode("\n", rtrim($speedtest));
				log::add(__CLASS__, 'debug', $this->getHumanName() . ' : $speedtests : ' . print_r($speedtests, true));
				foreach ($speedtests as $speedtest) {
					if ($this->getConfiguration('disableError') != 1) {
						log::add(__CLASS__, 'error', $this->getHumanName() . ' : Error shell_exec() : ' . $speedtest);
					} else {
						log::add(__CLASS__, 'warning', $this->getHumanName() . ' : Error shell_exec() : ' . $speedtest);
					}
				}
			} else {
				log::add(__CLASS__, 'debug', $this->getHumanName() . ' : $speedtest : ' . $speedtest);
				$speedtest = json_decode($speedtest, true);
				log::add(__CLASS__, 'debug', $this->getHumanName() . ' : $speedtest : ' . print_r($speedtest, true));
				$this->checkAndUpdateCmd('download', $speedtest['download']['bandwidth']);
				$this->checkAndUpdateCmd('upload', $speedtest['upload']['bandwidth']);
				$this->checkAndUpdateCmd('ping', $speedtest['ping']['latency']);
				$this->checkAndUpdateCmd('isp', $speedtest['isp']);
				$this->checkAndUpdateCmd('internalIp', $speedtest['interface']['internalIp']);
				$this->checkAndUpdateCmd('externalIp', $speedtest['interface']['externalIp']);
				$this->checkAndUpdateCmd('server', $speedtest['server']['name'] . ' - ' . $speedtest['server']['location'] . ' (id: ' . $speedtest['server']['id'] . ')');
				$this->checkAndUpdateCmd('timestamp', date('Y-m-d H:i:s', strtotime($speedtest['timestamp'])));
				log::add(__CLASS__, 'info', $this->getHumanName() . ' : Updated commands');
				$serverList = shell_exec('sudo /usr/bin/speedtest --servers');
				log::add(__CLASS__, 'debug', $this->getHumanName() . ' : $serverList : ' . $serverList);
				$serverList = str_replace('Closest servers:' . "\n" . "\n", '', $serverList);
				$serverLists = explode("\n", rtrim($serverList));
				log::add(__CLASS__, 'debug', $this->getHumanName() . ' : $serverLists : ' . print_r($serverLists, true));
				foreach ($serverLists as $server) {
					log::add(__CLASS__, 'debug', $this->getHumanName() . ' : $server : ' . $server);
				}
				$this->setConfiguration('serverList', $serverList);
				$this->save();
			}
		}
	}

	public function refreshData()
	{
		if ($this->getIsEnable() == 1) {
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}

class hyperionNGCmd extends cmd
{
	/*     * *************************Attributs****************************** */

	/*
	public static $_widgetPossibility = array();
	*/

	/*     * ***********************Methode static*************************** */


	/*     * *********************Methode d'instance************************* */

	/*
	* Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
	public function dontRemoveCmd() {
		return true;
	}
	*/

	// Exécution d'une commande
	public function execute($_options = array())
	{
		if ($this->getLogicalId() == 'refresh') {
			$this->getEqLogic()->refreshData();
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}
