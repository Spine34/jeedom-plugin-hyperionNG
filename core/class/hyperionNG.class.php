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

class hyperionNG extends eqLogic
{
	/*     * *************************Attributs****************************** */

	/*
	* Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
	* Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	*/

	// public static $_widgetPossibility = array('custom' => true);
	public static $_widgetPossibility = array(
		'custom' => true,
		'parameters' => array(
			'colorWidgetName' => array(
				'name' => 'Couleur de la police du bandeau',
				'type' => 'color',
				'default' => '',
				'allow_transparent' => true,
				'allow_displayType' => true
			),
			'bgWidgetName' => array(
				'name' => 'Couleur de fond du bandeau',
				'type' => 'color',
				'default' => '',
				'allow_transparent' => true,
				'allow_displayType' => true
			),
			'colorEqLogic' => array(
				'name' => 'Couleur de la police',
				'type' => 'color',
				'default' => '',
				'allow_transparent' => true,
				'allow_displayType' => true
			),
			'bgEqLogic' => array(
				'name' => 'Couleur de fond',
				'type' => 'color',
				'default' => '',
				'allow_transparent' => true,
				'allow_displayType' => true
			),
			'cmdName' => array(
				'name' => 'Nom des commandes',
				'type' => '',
				'default' => '',
				'allow_transparent' => false,
				'allow_displayType' => true
			),
			'timeWidget' => array(
				'name' => 'Widgets time',
				'type' => '',
				'default' => '',
				'allow_transparent' => false,
				'allow_displayType' => true
			)
		)
	);


	/*
	* Permet de crypter/décrypter automatiquement des champs de configuration du plugin
	* Exemple : "param1" & "param2" seront cryptés mais pas "param3"
	public static $_encryptConfigKey = array('param1', 'param2');
	*/

	/*     * ***********************Methode static*************************** */

	public static function dependancy_install()
	{
		log::remove(__CLASS__ . '_update');
		return array('script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder(__CLASS__) . '/dependency', 'log' => log::getPathToLog(__CLASS__ . '_update'));
	}

	public static function dependancy_info()
	{
		$return = array();
		$return['log'] = log::getPathToLog(__CLASS__ . '_update');
		$return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '/dependency';
		if (file_exists(jeedom::getTmpFolder(__CLASS__) . '/dependency')) {
			$return['state'] = 'in_progress';
		} else {
			if (exec(system::getCmdSudo() . system::get('cmd_check') . '-Ec "speedtest"') < 1) {
				$return['state'] = 'nok';
			} else {
				$return['state'] = 'ok';
			}
		}
		return $return;
	}

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
		$this->setConfiguration('template', 'coreWidget');
		$this->setDisplay('advanceWidgetParametercolorWidgetNamedashboard-default', 1);
		$this->setDisplay('advanceWidgetParametercolorWidgetNamemobile-default', 1);
		$this->setDisplay('advanceWidgetParameterbgWidgetNamedashboard-default', 0);
		$this->setDisplay('advanceWidgetParameterbgWidgetNamedashboard', '#26273b');
		$this->setDisplay('advanceWidgetParameterbgWidgetNamemobile-default', 0);
		$this->setDisplay('advanceWidgetParameterbgWidgetNamemobile', '#26273b');
		$this->setDisplay('advanceWidgetParametercolorEqLogicdashboard-default', 1);
		$this->setDisplay('advanceWidgetParametercolorEqLogicmobile-default', 1);
		$this->setDisplay('advanceWidgetParameterbgEqLogicdashboard-default', 0);
		$this->setDisplay('advanceWidgetParameterbgEqLogicdashboard', '#141526');
		$this->setDisplay('advanceWidgetParameterbgEqLogicmobile-default', 0);
		$this->setDisplay('advanceWidgetParameterbgEqLogicmobile', '#141526');
		$this->setDisplay('advanceWidgetParametercmdNamedashboard-default', 1);
		$this->setDisplay('advanceWidgetParametercmdNamemobile-default', 1);
		$this->setDisplay('advanceWidgetParametertimeWidgetdashboard-default', 1);
		$this->setDisplay('advanceWidgetParametertimeWidgetmobile-default', 1);
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
				if (isset($command['isHistorized'])) {
					$cmd->setIsHistorized($command['isHistorized']);
				}
				if (isset($command['configuration'])) {
					foreach ($command['configuration'] as $key => $value) {
						$cmd->setConfiguration($key, $value);
					}
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

	// Permet de modifier l'affichage du widget (également utilisable par les commandes)
	public function toHtml($_version = 'dashboard')
	{
		if ($this->getConfiguration('template') == 'coreWidget') {
			return parent::toHtml($_version);
		}
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		foreach (($this->getCmd('info')) as $cmd) {
			$logical = $cmd->getLogicalId();
			$replace['#' . $logical . '_Id#'] = $cmd->getId();
			if ($logical == 'server') {
				$server = str_replace(' - ', '<br>', $cmd->execCmd());
				$servers = explode('(', $server);
				$server = rtrim($servers[0]);
				$replace['#' . $logical . '_Value#'] = $server;
			} else {
				$replace['#' . $logical . '_Value#'] = $cmd->execCmd();
			}
			$replace['#' . $logical . '_ValueDate#'] = $cmd->getValueDate();
			$replace['#' . $logical . '_CollectDate#'] = $cmd->getCollectDate();
			$replace['#' . $logical . '_Unite#'] = $cmd->getUnite();
			$replace['#' . $logical . '_Name#'] = $cmd->getName();
		}
		if ($version == 'dashboard') {
			$replace['#cmdName#'] = $this->getDisplay('advanceWidgetParametercmdNamedashboard-default');
			$replace['#timeWidget#'] = $this->getDisplay('advanceWidgetParametertimeWidgetdashboard-default');
		} else {
			$replace['#cmdName#'] = $this->getDisplay('advanceWidgetParametercmdNamemobile-default');
			$replace['#timeWidget#'] = $this->getDisplay('advanceWidgetParametertimeWidgetmobile-default');
		}
		$html = template_replace($replace, getTemplate('core', $version, 'speedtestByOoklaWithoutGauges', __CLASS__));
		$html = translate::exec($html, 'plugins/speedtestByOokla/core/template/' . $version . '/speedtestByOoklaWithoutGauges.html');
		$html = $this->postToHtml($_version, $html);
		return $html;
		// return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'speedtestByOoklaWithoutGauges', __CLASS__)));
	}

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

	public function refreshData()
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
