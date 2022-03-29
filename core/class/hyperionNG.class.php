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

require_once __DIR__ . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../php/hyperionNG.inc.php';
if (!class_exists('mDNS')) {
	require_once __DIR__ . '/../../3rdparty/mdns.php';
}

class hyperionNG extends eqLogic
{
	/*     * *************************Attributs****************************** */

	/*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */

	/*     * ***********************Methode static*************************** */

	// Fonction exécutée automatiquement toutes les minutes par Jeedom
	public static function cron()
	{
		foreach (self::byType(__CLASS__) as $eqLogic) {
			if ($eqLogic->getIsEnable() == 1) {
				if (!is_object($eqLogic)) {
					continue;
				}
				$readServerinfo = self::socket($eqLogic->getHumanName(), $eqLogic->getConfiguration('ip'), $eqLogic->getConfiguration('port', 19444), $dataInstance, $dataCommand, array('command' => 'serverinfo'));
				$eqLogic->updateCmd($readServerinfo, $eqLogic->getHumanName());
			}
		}
	}

	/*
	 * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
	  public static function cron5() {
	  }
	 */

	/*
	 * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
	  public static function cron10() {
	  }
	 */

	/*
	 * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
	  public static function cron15() {
	  }
	 */

	/*
	 * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
	  public static function cron30() {
	  }
	 */

	/*
	 * Fonction exécutée automatiquement toutes les heures par Jeedom
	  public static function cronHourly() {
	  }
	 */

	/*
	 * Fonction exécutée automatiquement tous les jours par Jeedom
	  public static function cronDaily() {
	  }
	 */

	public static function scan()
	{
		log::add(__CLASS__, 'info', 'Début fonction scan()');
		$hyperionNG = self::mDNS();
		log::add(__CLASS__, 'debug', '$hyperionNG : ' . print_r($hyperionNG, true));
		if (!empty($hyperionNG)) {
			foreach ($hyperionNG as $hyperionNGKey => $hyperionNGValue) {
				log::add(__CLASS__, 'debug', '$hyperionNGKey : ' . print_r($hyperionNGKey, true));
				log::add(__CLASS__, 'debug', '$hyperionNGValue : ' . print_r($hyperionNGValue, true));
				if (strpos($hyperionNGKey, '_hyperiond-json._tcp.local') !== false) {
					log::add(__CLASS__, 'info', 'Récupération de la liste des instances...');
					$readServerinfo = self::socket($getHumanName, $hyperionNGValue[ip], $hyperionNGValue[port], $dataInstance, $dataCommand, array('command' => 'serverinfo'));
					$decode = json_decode($readServerinfo, true);
					log::add(__CLASS__, 'debug', '$decode[\'info\'][\'instance\'] : ' . print_r($decode['info']['instance'], true));
					log::add(__CLASS__, 'info', 'Liste des instances récupérée');
					if (config::byKey('scanMode', __CLASS__) == 'modeEqLogic' || empty(config::byKey('scanMode', __CLASS__))) {
						$eqLogicIp = self::byTypeAndSearhConfiguration(__CLASS__, '"ip":"' . $hyperionNGValue[ip] . '"');
						if (empty($eqLogicIp)) {
							log::add(__CLASS__, 'info', 'Création d\'un nouvel équipement : Nom : ' . str_replace('.local', '', $hyperionNGValue[target]) . ' : IP : ' . $hyperionNGValue[ip] . '...');
							if (count($decode[info][instance]) > 1) {
								foreach ($decode['info']['instance'] as $decodeValue) {
									log::add(__CLASS__, 'debug', '$decodeValue : ' . print_r($decodeValue, true));
									$instancesList .= $decodeValue['instance'] . '|' . $decodeValue['friendly_name'] . ';';
									log::add(__CLASS__, 'debug', '$instancesList : ' . rtrim($instancesList, ';'));
								}
							} else {
								log::add(__CLASS__, 'info', 'Une seule instance configurée');
							}
							$eqLogic = new hyperionNG();
							$eqLogic->setEqType_name(__CLASS__);
							$eqLogic->setName(str_replace('.local', '', $hyperionNGValue[target]));
							$eqLogic->setCategory('light', 1);
							$eqLogic->setIsEnable(1);
							$eqLogic->setIsVisible(1);
							$eqLogic->setConfiguration('ip', $hyperionNGValue[ip]);
							$eqLogic->setConfiguration('port', $hyperionNGValue[port]);
							$eqLogic->setConfiguration('instancesList', rtrim($instancesList, ';'));
							$eqLogic->save();
							log::add(__CLASS__, 'info', 'Nouvel équipement créé : Nom : ' . str_replace('.local', '', $hyperionNGValue[target]) . ' : IP : ' . $hyperionNGValue[ip]);
							message::add(__CLASS__, 'Nouvel équipement créé : Nom : ' . str_replace('.local', '', $hyperionNGValue[target]) . ' : IP : ' . $hyperionNGValue[ip]);
						} else {
							log::add(__CLASS__, 'debug', '$eqLogicIp : ' . print_r($eqLogicIp, true));
							log::add(__CLASS__, 'info', 'Equipement déjà existant : IP : ' . $hyperionNGValue[ip]);
						}
					} else {
						if (count($decode[info][instance]) > 1) {
							foreach ($decode['info']['instance'] as $decodeValue) {
								$eqLogicIp = self::byTypeAndSearhConfiguration(__CLASS__, '"ip":"' . $hyperionNGValue[ip] . '"');
								$eqLogicInstancesList = self::byTypeAndSearhConfiguration(__CLASS__, '"instancesList":"' . $decodeValue['instance'] . '|' . $decodeValue['friendly_name'] . '"');
								if (empty($eqLogicIp) || empty($eqLogicInstancesList)) {
									log::add(__CLASS__, 'info', 'Création d\'un nouvel équipement : Nom : ' . str_replace('.local', '', $hyperionNGValue[target]) . ' : IP : ' . $hyperionNGValue[ip] . '...');
									$eqLogic = new hyperionNG();
									$eqLogic->setEqType_name(__CLASS__);
									$eqLogic->setName(str_replace('.local', '', $hyperionNGValue[target] . '|' . $decodeValue['friendly_name']));
									$eqLogic->setCategory('light', 1);
									$eqLogic->setIsEnable(1);
									$eqLogic->setIsVisible(1);
									$eqLogic->setConfiguration('ip', $hyperionNGValue[ip]);
									$eqLogic->setConfiguration('port', $hyperionNGValue[port]);
									$eqLogic->setConfiguration('instancesList', $decodeValue['instance'] . '|' . $decodeValue['friendly_name']);
									$eqLogic->save();
									log::add(__CLASS__, 'info', 'Nouvel équipement créé : Nom : ' . str_replace('.local', '', $hyperionNGValue[target] . '|' . $decodeValue['friendly_name']) . ' : IP : ' . $hyperionNGValue[ip]);
									message::add(__CLASS__, 'Nouvel équipement créé : Nom : ' . str_replace('.local', '', $hyperionNGValue[target] . '|' . $decodeValue['friendly_name']) . ' : IP : ' . $hyperionNGValue[ip]);
								} else {
									log::add(__CLASS__, 'debug', '$eqLogicIp : ' . print_r($eqLogicIp, true));
									log::add(__CLASS__, 'debug', '$eqLogicInstancesList : ' . print_r($eqLogicInstancesList, true));
									log::add(__CLASS__, 'info', 'Equipement déjà existant : IP : ' . $hyperionNGValue[ip]);
								}
							}
						} else {
							$eqLogicIp = self::byTypeAndSearhConfiguration(__CLASS__, '"ip":"' . $hyperionNGValue[ip] . '"');
							if (empty($eqLogicIp)) {
								log::add(__CLASS__, 'info', 'Création d\'un nouvel équipement : Nom : ' . str_replace('.local', '', $hyperionNGValue[target]) . ' : IP : ' . $hyperionNGValue[ip] . '...');
								log::add(__CLASS__, 'info', 'Une seule instance configurée');
								$eqLogic = new hyperionNG();
								$eqLogic->setEqType_name(__CLASS__);
								$eqLogic->setName(str_replace('.local', '', $hyperionNGValue[target]));
								$eqLogic->setCategory('light', 1);
								$eqLogic->setIsEnable(1);
								$eqLogic->setIsVisible(1);
								$eqLogic->setConfiguration('ip', $hyperionNGValue[ip]);
								$eqLogic->setConfiguration('port', $hyperionNGValue[port]);
								$eqLogic->save();
								log::add(__CLASS__, 'info', 'Nouvel équipement créé : Nom : ' . str_replace('.local', '', $hyperionNGValue[target]) . ' : IP : ' . $hyperionNGValue[ip]);
								message::add(__CLASS__, 'Nouvel équipement créé : Nom : ' . str_replace('.local', '', $hyperionNGValue[target]) . ' : IP : ' . $hyperionNGValue[ip]);
							} else {
								log::add(__CLASS__, 'debug', '$eqLogicIp : ' . print_r($eqLogicIp, true));
								log::add(__CLASS__, 'info', 'Equipement déjà existant : IP : ' . $hyperionNGValue[ip]);
							}
						}
					}
				} else {
					log::add(__CLASS__, 'info', 'Equipement non compatible');
				}
			}
		} else {
			log::add(__CLASS__, 'info', 'Aucun equipement trouvé');
		}
		log::add(__CLASS__, 'info', 'Fin fonction scan()');
	}

	public static function mDNS()
	{
		log::add(__CLASS__, 'info', 'Début fonction mDNS()');
		$mdns = new mDNS();
		$mdns->query("_hyperiond-json._tcp.local", 1, 12, "");
		$mdns->query("_hyperiond-json._tcp.local", 1, 12, "");
		$mdns->query("_hyperiond-json._tcp.local", 1, 12, "");
		$cc = 15;
		$hyperionNG = array();
		while ($cc > 0) {
			$inpacket = $mdns->readIncoming();
			if ($inpacket->packetheader != NULL) {
				if ($inpacket->packetheader->getAnswerRRs() > 0) {
					for ($x = 0; $x < sizeof($inpacket->answerrrs); $x++) {
						if ($inpacket->answerrrs[$x]->qtype == 12) {
							if ($inpacket->answerrrs[$x]->name == "_hyperiond-json._tcp.local") {
								$name = "";
								for ($y = 0; $y < sizeof($inpacket->answerrrs[$x]->data); $y++) {
									$name .= chr($inpacket->answerrrs[$x]->data[$y]);
								}
								$mdns->query($name, 1, 33, "");
								$cc = 15;
							}
						}
						if ($inpacket->answerrrs[$x]->qtype == 33) {
							$d = $inpacket->answerrrs[$x]->data;
							$port = ($d[4] * 256) + $d[5];
							$offset = 6;
							$size = $d[$offset];
							$offset++;
							$target = "";
							for ($z = 0; $z < $size; $z++) {
								$target .= chr($d[$offset + $z]);
							}
							$target .= ".local";
							$hyperionNG[$inpacket->answerrrs[$x]->name] = array("port" => $port, "ip" => "", "target" => $target);
							$mdns->query($target, 1, 1, "");
							$cc = 15;
						}
						if ($inpacket->answerrrs[$x]->qtype == 1) {
							$d = $inpacket->answerrrs[$x]->data;
							$ip = $d[0] . "." . $d[1] . "." . $d[2] . "." . $d[3];
							foreach ($hyperionNG as $key => $value) {
								if ($value['target'] == $inpacket->answerrrs[$x]->name) {
									$value['ip'] = $ip;
									$hyperionNG[$key] = $value;
								}
							}
						}
					}
				}
			}
			$cc--;
		}
		log::add(__CLASS__, 'info', 'Fin fonction mDNS()');
		return $hyperionNG;
	}

	public static function socket($getHumanName, $ip, $port, $dataInstance, $dataCommand, $dataServerinfo)
	{
		log::add(__CLASS__, 'info', 'Début fonction socket()');
		if (!empty($getHumanName)) {
			$getHumanName = 'Equipement : ' . $getHumanName . ' : ';
		}
		$create = socket_create(AF_INET, SOCK_STREAM, 0);
		if ($create == false) {
			log::add(__CLASS__, 'warning', $getHumanName . 'Erreur socket_create() : ' . socket_strerror(socket_last_error()));
		}
		$connect = socket_connect($create, $ip, $port);
		if ($connect == false) {
			log::add(__CLASS__, 'warning', $getHumanName . 'Erreur socket_connect() : ' . socket_strerror(socket_last_error()));
		}
		if (!empty($dataInstance)) {
			$encodeInstance = json_encode($dataInstance) . "\n";
			$writeInstance = socket_write($create, $encodeInstance, strlen($encodeInstance));
			if ($writeInstance === false) {
				log::add(__CLASS__, 'warning', $getHumanName . 'Erreur socket_write() : ' . socket_strerror(socket_last_error()));
			} else {
				log::add(__CLASS__, 'debug', $getHumanName . '$encodeInstance : ' . $encodeInstance);
				$readInstance = socket_read($create, 128, PHP_NORMAL_READ);
				if ($readInstance == false) {
					log::add(__CLASS__, 'warning', $getHumanName . 'Erreur socket_read() : ' . socket_strerror(socket_last_error()));
				} else {
					log::add(__CLASS__, 'debug', $getHumanName . '$readInstance : ' . $readInstance);
				}
			}
		}
		if (!empty($dataCommand)) {
			$encodeCommand = json_encode($dataCommand) . "\n";
			$writeCommand = socket_write($create, $encodeCommand, strlen($encodeCommand));
			if ($writeCommand === false) {
				log::add(__CLASS__, 'error', $getHumanName . 'Erreur socket_write() : ' . socket_strerror(socket_last_error()));
			} else {
				log::add(__CLASS__, 'debug', $getHumanName . '$encodeCommand : ' . $encodeCommand);
				$readCommand = socket_read($create, 128, PHP_NORMAL_READ);
				if ($readCommand == false) {
					log::add(__CLASS__, 'error', $getHumanName . 'Erreur socket_read() : ' . socket_strerror(socket_last_error()));
				} else {
					log::add(__CLASS__, 'debug', $getHumanName . '$readCommand : ' . $readCommand);
				}
			}
		}
		if (!empty($dataServerinfo)) {
			$encodeServerinfo = json_encode($dataServerinfo) . "\n";
			$writeServerinfo = socket_write($create, $encodeServerinfo, strlen($encodeServerinfo));
			if ($writeServerinfo === false) {
				log::add(__CLASS__, 'warning', $getHumanName . 'Erreur socket_write() : ' . socket_strerror(socket_last_error()));
			} else {
				log::add(__CLASS__, 'debug', $getHumanName . '$encodeServerinfo : ' . $encodeServerinfo);
				$readServerinfo = socket_read($create, 32768, PHP_NORMAL_READ);
				if ($readServerinfo == false) {
					log::add(__CLASS__, 'warning', $getHumanName . 'Erreur socket_read() : ' . socket_strerror(socket_last_error()));
				} else {
					log::add(__CLASS__, 'debug', $getHumanName . '$readServerinfo : ' . $readServerinfo);
				}
			}
		}
		socket_close($create);
		log::add(__CLASS__, 'info', 'Fin fonction socket()');
		return $readServerinfo;
	}

	/*     * *********************Méthodes d'instance************************* */

	// Fonction exécutée automatiquement avant la création de l'équipement 
	public function preInsert()
	{
		$this->setCategory('light', 1);
	}

	// Fonction exécutée automatiquement après la création de l'équipement 
	public function postInsert()
	{
	}

	// Fonction exécutée automatiquement avant la mise à jour de l'équipement 
	public function preUpdate()
	{
		if (empty($this->getConfiguration('ip'))) {
			throw new Exception(__('L\'adresse IP ne peut être vide', __FILE__));
		}
	}

	// Fonction exécutée automatiquement après la mise à jour de l'équipement 
	public function postUpdate()
	{
	}

	// Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement 
	public function preSave()
	{
		if (empty($this->getConfiguration('port'))) {
			$this->setConfiguration('port', 19444);
		}
	}

	// Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement 
	public function postSave()
	{
		log::add(__CLASS__, 'info', 'Equipement : ' . $this->getHumanName() . ' : Création/mise à jour des commandes...');
		$order = 0;

		$connectionState = $this->getCmd(null, 'connectionState');
		if (!is_object($connectionState)) {
			$connectionState = new hyperionNGCmd();
			$connectionState->setLogicalId('connectionState');
			$connectionState->setIsVisible(1);
			$connectionState->setName(__('Etat connexion', __FILE__));
			$connectionState->setOrder($order++);
			$connectionState->setTemplate('dashboard', 'hyperionNG::connectionState');
		}
		$connectionState->setType('info');
		$connectionState->setSubType('binary');
		$connectionState->setEqLogic_id($this->getId());
		$connectionState->save();

		$instancesList = $this->getConfiguration('instancesList');
		if (!empty($instancesList)) {
			$instanceState = $this->getCmd(null, 'instanceState');
			if (!is_object($instanceState)) {
				$instanceState = new hyperionNGCmd();
				$instanceState->setLogicalId('instanceState');
				$instanceState->setIsVisible(1);
				$instanceState->setName(__('Etat instance', __FILE__));
				$instanceState->setOrder($order++);
			}
			$instanceState->setType('info');
			$instanceState->setSubType('numeric');
			$instanceState->setEqLogic_id($this->getId());
			$instanceState->save();
		}

		$colorState = $this->getCmd(null, 'colorState');
		if (!is_object($colorState)) {
			$colorState = new hyperionNGCmd();
			$colorState->setLogicalId('colorState');
			$colorState->setIsVisible(1);
			$colorState->setName(__('Etat couleur', __FILE__));
			$colorState->setOrder($order++);
		}
		$colorState->setType('info');
		$colorState->setSubType('string');
		$colorState->setEqLogic_id($this->getId());
		$colorState->save();

		$colorDurationState = $this->getCmd(null, 'colorDurationState');
		if (!is_object($colorDurationState)) {
			$colorDurationState = new hyperionNGCmd();
			$colorDurationState->setLogicalId('colorDurationState');
			$colorDurationState->setIsVisible(1);
			$colorDurationState->setName(__('Etat durée couleur', __FILE__));
			$colorDurationState->setOrder($order++);
			$colorDurationState->setUnite('s');
		}
		$colorDurationState->setType('info');
		$colorDurationState->setSubType('numeric');
		$colorDurationState->setEqLogic_id($this->getId());
		$colorDurationState->save();

		$brightnessState = $this->getCmd(null, 'brightnessState');
		if (!is_object($brightnessState)) {
			$brightnessState = new hyperionNGCmd();
			$brightnessState->setLogicalId('brightnessState');
			$brightnessState->setIsVisible(1);
			$brightnessState->setName(__('Etat luminosité', __FILE__));
			$brightnessState->setOrder($order++);
			$brightnessState->setUnite('%');
		}
		$brightnessState->setType('info');
		$brightnessState->setSubType('numeric');
		$brightnessState->setEqLogic_id($this->getId());
		$brightnessState->save();

		$backlightThresholdState = $this->getCmd(null, 'backlightThresholdState');
		if (!is_object($backlightThresholdState)) {
			$backlightThresholdState = new hyperionNGCmd();
			$backlightThresholdState->setLogicalId('backlightThresholdState');
			$backlightThresholdState->setIsVisible(1);
			$backlightThresholdState->setName(__('Etat rétroéclairage', __FILE__));
			$backlightThresholdState->setOrder($order++);
			$backlightThresholdState->setUnite('%');
		}
		$backlightThresholdState->setType('info');
		$backlightThresholdState->setSubType('numeric');
		$backlightThresholdState->setEqLogic_id($this->getId());
		$backlightThresholdState->save();

		$effectState = $this->getCmd(null, 'effectState');
		if (!is_object($effectState)) {
			$effectState = new hyperionNGCmd();
			$effectState->setLogicalId('effectState');
			$effectState->setIsVisible(1);
			$effectState->setName(__('Etat effet', __FILE__));
			$effectState->setOrder($order++);
		}
		$effectState->setType('info');
		$effectState->setSubType('string');
		$effectState->setEqLogic_id($this->getId());
		$effectState->save();

		$effectDurationState = $this->getCmd(null, 'effectDurationState');
		if (!is_object($effectDurationState)) {
			$effectDurationState = new hyperionNGCmd();
			$effectDurationState->setLogicalId('effectDurationState');
			$effectDurationState->setIsVisible(1);
			$effectDurationState->setName(__('Etat durée effet', __FILE__));
			$effectDurationState->setOrder($order++);
			$effectDurationState->setUnite('s');
		}
		$effectDurationState->setType('info');
		$effectDurationState->setSubType('numeric');
		$effectDurationState->setEqLogic_id($this->getId());
		$effectDurationState->save();

		$hyperionState = $this->getCmd(null, 'hyperionState');
		if (!is_object($hyperionState)) {
			$hyperionState = new hyperionNGCmd();
			$hyperionState->setLogicalId('hyperionState');
			$hyperionState->setIsVisible(1);
			$hyperionState->setName(__('Etat Hyperion', __FILE__));
			$hyperionState->setOrder($order++);
			$hyperionState->setTemplate('dashboard', 'hyperionNG::hyperionNG');
		}
		$hyperionState->setType('info');
		$hyperionState->setSubType('binary');
		$hyperionState->setEqLogic_id($this->getId());
		$hyperionState->save();

		$smoothingState = $this->getCmd(null, 'smoothingState');
		if (!is_object($smoothingState)) {
			$smoothingState = new hyperionNGCmd();
			$smoothingState->setLogicalId('smoothingState');
			$smoothingState->setIsVisible(1);
			$smoothingState->setName(__('Etat fondu', __FILE__));
			$smoothingState->setOrder($order++);
		}
		$smoothingState->setType('info');
		$smoothingState->setSubType('binary');
		$smoothingState->setEqLogic_id($this->getId());
		$smoothingState->save();

		$blackBorderState = $this->getCmd(null, 'blackBorderState');
		if (!is_object($blackBorderState)) {
			$blackBorderState = new hyperionNGCmd();
			$blackBorderState->setLogicalId('blackBorderState');
			$blackBorderState->setIsVisible(1);
			$blackBorderState->setName(__('Etat détection des bordures noir', __FILE__));
			$blackBorderState->setOrder($order++);
		}
		$blackBorderState->setType('info');
		$blackBorderState->setSubType('binary');
		$blackBorderState->setEqLogic_id($this->getId());
		$blackBorderState->save();

		$forwarderState = $this->getCmd(null, 'forwarderState');
		if (!is_object($forwarderState)) {
			$forwarderState = new hyperionNGCmd();
			$forwarderState->setLogicalId('forwarderState');
			$forwarderState->setIsVisible(1);
			$forwarderState->setName(__('Etat transfert', __FILE__));
			$forwarderState->setOrder($order++);
		}
		$forwarderState->setType('info');
		$forwarderState->setSubType('binary');
		$forwarderState->setEqLogic_id($this->getId());
		$forwarderState->save();

		$boblightServerState = $this->getCmd(null, 'boblightServerState');
		if (!is_object($boblightServerState)) {
			$boblightServerState = new hyperionNGCmd();
			$boblightServerState->setLogicalId('boblightServerState');
			$boblightServerState->setIsVisible(1);
			$boblightServerState->setName(__('Etat serveur Boblight', __FILE__));
			$boblightServerState->setOrder($order++);
		}
		$boblightServerState->setType('info');
		$boblightServerState->setSubType('binary');
		$boblightServerState->setEqLogic_id($this->getId());
		$boblightServerState->save();

		$grabberState = $this->getCmd(null, 'grabberState');
		if (!is_object($grabberState)) {
			$grabberState = new hyperionNGCmd();
			$grabberState->setLogicalId('grabberState');
			$grabberState->setIsVisible(1);
			$grabberState->setName(__('Etat platforme de capture', __FILE__));
			$grabberState->setOrder($order++);
		}
		$grabberState->setType('info');
		$grabberState->setSubType('binary');
		$grabberState->setEqLogic_id($this->getId());
		$grabberState->save();

		$v4lState = $this->getCmd(null, 'v4lState');
		if (!is_object($v4lState)) {
			$v4lState = new hyperionNGCmd();
			$v4lState->setLogicalId('v4lState');
			$v4lState->setIsVisible(1);
			$v4lState->setName(__('Etat capture par USB', __FILE__));
			$v4lState->setOrder($order++);
		}
		$v4lState->setType('info');
		$v4lState->setSubType('binary');
		$v4lState->setEqLogic_id($this->getId());
		$v4lState->save();

		$ledDeviceState = $this->getCmd(null, 'ledDeviceState');
		if (!is_object($ledDeviceState)) {
			$ledDeviceState = new hyperionNGCmd();
			$ledDeviceState->setLogicalId('ledDeviceState');
			$ledDeviceState->setIsVisible(1);
			$ledDeviceState->setName(__('Etat équipement LED', __FILE__));
			$ledDeviceState->setOrder($order++);
		}
		$ledDeviceState->setType('info');
		$ledDeviceState->setSubType('binary');
		$ledDeviceState->setEqLogic_id($this->getId());
		$ledDeviceState->save();

		$refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = new hyperionNGCmd();
			$refresh->setLogicalId('refresh');
			$refresh->setIsVisible(1);
			$refresh->setName(__('Rafraîchir', __FILE__));
			$refresh->setOrder($order++);
		}
		$refresh->setType('action');
		$refresh->setSubType('other');
		$refresh->setEqLogic_id($this->getId());
		$refresh->save();

		$instancesList = $this->getConfiguration('instancesList');
		if (!empty($instancesList)) {
			$instances = $this->getCmd(null, 'instances');
			if (!is_object($instances)) {
				$instances = new hyperionNGCmd();
				$instances->setLogicalId('instances');
				$instances->setIsVisible(1);
				$instances->setName(__('Instances', __FILE__));
				$instances->setOrder($order++);
				$instances->setConfiguration('listValue', $instancesList);
				$instances->setValue($instanceState->getId());
			}
			$instances->setType('action');
			$instances->setSubType('select');
			$instances->setEqLogic_id($this->getId());
			$instances->save();
		}

		$color = $this->getCmd(null, 'color');
		if (!is_object($color)) {
			$color = new hyperionNGCmd();
			$color->setLogicalId('color');
			$color->setIsVisible(1);
			$color->setName(__('Couleur', __FILE__));
			$color->setOrder($order++);
			$color->setValue($colorState->getId());
		}
		$color->setType('action');
		$color->setSubType('color');
		$color->setEqLogic_id($this->getId());
		$color->save();

		$colorDuration = $this->getCmd(null, 'colorDuration');
		if (!is_object($colorDuration)) {
			$colorDuration = new hyperionNGCmd();
			$colorDuration->setLogicalId('colorDuration');
			$colorDuration->setIsVisible(1);
			$colorDuration->setName(__('Durée couleur', __FILE__));
			$colorDuration->setOrder($order++);
			$colorDuration->setValue($colorDurationState->getId());
		}
		$colorDuration->setType('action');
		$colorDuration->setSubType('slider');
		$colorDuration->setEqLogic_id($this->getId());
		$colorDuration->save();

		$brightness = $this->getCmd(null, 'brightness');
		if (!is_object($brightness)) {
			$brightness = new hyperionNGCmd();
			$brightness->setLogicalId('brightness');
			$brightness->setIsVisible(1);
			$brightness->setName(__('Luminosité', __FILE__));
			$brightness->setOrder($order++);
			$brightness->setValue($brightnessState->getId());
		}
		$brightness->setType('action');
		$brightness->setSubType('slider');
		$brightness->setEqLogic_id($this->getId());
		$brightness->save();

		$backlightThreshold = $this->getCmd(null, 'backlightThreshold');
		if (!is_object($backlightThreshold)) {
			$backlightThreshold = new hyperionNGCmd();
			$backlightThreshold->setLogicalId('backlightThreshold');
			$backlightThreshold->setIsVisible(1);
			$backlightThreshold->setName(__('Rétroéclairage', __FILE__));
			$backlightThreshold->setOrder($order++);
			$backlightThreshold->setValue($backlightThresholdState->getId());
		}
		$backlightThreshold->setType('action');
		$backlightThreshold->setSubType('slider');
		$backlightThreshold->setEqLogic_id($this->getId());
		$backlightThreshold->save();

		$providedEffects = $this->getCmd(null, 'providedEffects');
		if (!is_object($providedEffects)) {
			$providedEffects = new hyperionNGCmd();
			$providedEffects->setLogicalId('providedEffects');
			$providedEffects->setIsVisible(1);
			$providedEffects->setName(__('Effets fournis', __FILE__));
			$providedEffects->setOrder($order++);
			$providedEffects->setConfiguration('listValue', 'Aucun|Aucun;Atomic swirl|Atomic swirl;Blue mood blobs|Blue mood blobs;Breath|Breath;Candle|Candle;Cinema brighten lights|Cinema brighten lights;Cinema dim lights|Cinema dim lights;Cold mood blobs|Cold mood blobs;Collision|Collision;Color traces|Color traces;Double swirl|Double swirl;Fire|Fire;Flags Germany/Sweden|Flags Germany/Sweden;Full color mood blobs|Full color mood blobs;Green mood blobs|Green mood blobs;Knight rider|Knight rider;Led Test|Led Test;Light clock|Light clock;Lights|Lights;Notify blue|Notify blue;Pac-Man|Pac-Man;Plasma|Plasma;Police Lights Single|Police Lights Single;Police Lights Solid|Police Lights Solid;Rainbow mood|Rainbow mood;Rainbow swirl|Rainbow swirl;Rainbow swirl fast|Rainbow swirl fast;Random|Random;Red mood blobs|Red mood blobs;Sea waves|Sea waves;Snake|Snake;Sparks|Sparks;Strobe red|Strobe red;Strobe white|Strobe white;System Shutdown|System Shutdown;Trails|Trails;Trails color|Trails color;Warm mood blobs|Warm mood blobs;Waves with Color|Waves with Color;X-Mas|X-Mas');
			$providedEffects->setValue($effectState->getId());
		}
		$providedEffects->setType('action');
		$providedEffects->setSubType('select');
		$providedEffects->setEqLogic_id($this->getId());
		$providedEffects->save();

		$effectDuration = $this->getCmd(null, 'effectDuration');
		if (!is_object($effectDuration)) {
			$effectDuration = new hyperionNGCmd();
			$effectDuration->setLogicalId('effectDuration');
			$effectDuration->setIsVisible(1);
			$effectDuration->setName(__('Durée effet', __FILE__));
			$effectDuration->setOrder($order++);
			$effectDuration->setValue($effectDurationState->getId());
		}
		$effectDuration->setType('action');
		$effectDuration->setSubType('slider');
		$effectDuration->setEqLogic_id($this->getId());
		$effectDuration->save();

		$hyperionOn = $this->getCmd(null, 'hyperionOn');
		if (!is_object($hyperionOn)) {
			$hyperionOn = new hyperionNGCmd();
			$hyperionOn->setLogicalId('hyperionOn');
			$hyperionOn->setIsVisible(1);
			$hyperionOn->setName(__('Hyperion On', __FILE__));
			$hyperionOn->setOrder($order++);
			$hyperionOn->setValue($hyperionState->getId());
		}
		$hyperionOn->setType('action');
		$hyperionOn->setSubType('other');
		$hyperionOn->setEqLogic_id($this->getId());
		$hyperionOn->save();

		$hyperionOff = $this->getCmd(null, 'hyperionOff');
		if (!is_object($hyperionOff)) {
			$hyperionOff = new hyperionNGCmd();
			$hyperionOff->setLogicalId('hyperionOff');
			$hyperionOff->setIsVisible(1);
			$hyperionOff->setName(__('Hyperion Off', __FILE__));
			$hyperionOff->setOrder($order++);
			$hyperionOff->setValue($hyperionState->getId());
		}
		$hyperionOff->setType('action');
		$hyperionOff->setSubType('other');
		$hyperionOff->setEqLogic_id($this->getId());
		$hyperionOff->save();

		$smoothingOn = $this->getCmd(null, 'smoothingOn');
		if (!is_object($smoothingOn)) {
			$smoothingOn = new hyperionNGCmd();
			$smoothingOn->setLogicalId('smoothingOn');
			$smoothingOn->setIsVisible(1);
			$smoothingOn->setName(__('Fondu On', __FILE__));
			$smoothingOn->setOrder($order++);
			$smoothingOn->setValue($smoothingState->getId());
		}
		$smoothingOn->setType('action');
		$smoothingOn->setSubType('other');
		$smoothingOn->setEqLogic_id($this->getId());
		$smoothingOn->save();

		$smoothingOff = $this->getCmd(null, 'smoothingOff');
		if (!is_object($smoothingOff)) {
			$smoothingOff = new hyperionNGCmd();
			$smoothingOff->setLogicalId('smoothingOff');
			$smoothingOff->setIsVisible(1);
			$smoothingOff->setName(__('Fondu Off', __FILE__));
			$smoothingOff->setOrder($order++);
			$smoothingOff->setValue($smoothingState->getId());
		}
		$smoothingOff->setType('action');
		$smoothingOff->setSubType('other');
		$smoothingOff->setEqLogic_id($this->getId());
		$smoothingOff->save();

		$blackBorderOn = $this->getCmd(null, 'blackBorderOn');
		if (!is_object($blackBorderOn)) {
			$blackBorderOn = new hyperionNGCmd();
			$blackBorderOn->setLogicalId('blackBorderOn');
			$blackBorderOn->setIsVisible(1);
			$blackBorderOn->setName(__('Détection des bordures noir On', __FILE__));
			$blackBorderOn->setOrder($order++);
			$blackBorderOn->setValue($blackBorderState->getId());
		}
		$blackBorderOn->setType('action');
		$blackBorderOn->setSubType('other');
		$blackBorderOn->setEqLogic_id($this->getId());
		$blackBorderOn->save();

		$blackBorderOff = $this->getCmd(null, 'blackBorderOff');
		if (!is_object($blackBorderOff)) {
			$blackBorderOff = new hyperionNGCmd();
			$blackBorderOff->setLogicalId('blackBorderOff');
			$blackBorderOff->setIsVisible(1);
			$blackBorderOff->setName(__('Détection des bordures noir Off', __FILE__));
			$blackBorderOff->setOrder($order++);
			$blackBorderOff->setValue($blackBorderState->getId());
		}
		$blackBorderOff->setType('action');
		$blackBorderOff->setSubType('other');
		$blackBorderOff->setEqLogic_id($this->getId());
		$blackBorderOff->save();

		$forwarderOn = $this->getCmd(null, 'forwarderOn');
		if (!is_object($forwarderOn)) {
			$forwarderOn = new hyperionNGCmd();
			$forwarderOn->setLogicalId('forwarderOn');
			$forwarderOn->setIsVisible(1);
			$forwarderOn->setName(__('Transfert On', __FILE__));
			$forwarderOn->setOrder($order++);
			$forwarderOn->setValue($forwarderState->getId());
		}
		$forwarderOn->setType('action');
		$forwarderOn->setSubType('other');
		$forwarderOn->setEqLogic_id($this->getId());
		$forwarderOn->save();

		$forwarderOff = $this->getCmd(null, 'forwarderOff');
		if (!is_object($forwarderOff)) {
			$forwarderOff = new hyperionNGCmd();
			$forwarderOff->setLogicalId('forwarderOff');
			$forwarderOff->setIsVisible(1);
			$forwarderOff->setName(__('Transfert Off', __FILE__));
			$forwarderOff->setOrder($order++);
			$forwarderOff->setValue($forwarderState->getId());
		}
		$forwarderOff->setType('action');
		$forwarderOff->setSubType('other');
		$forwarderOff->setEqLogic_id($this->getId());
		$forwarderOff->save();

		$boblightServerOn = $this->getCmd(null, 'boblightServerOn');
		if (!is_object($boblightServerOn)) {
			$boblightServerOn = new hyperionNGCmd();
			$boblightServerOn->setLogicalId('boblightServerOn');
			$boblightServerOn->setIsVisible(1);
			$boblightServerOn->setName(__('Serveur Boblight On', __FILE__));
			$boblightServerOn->setOrder($order++);
			$boblightServerOn->setValue($boblightServerState->getId());
		}
		$boblightServerOn->setType('action');
		$boblightServerOn->setSubType('other');
		$boblightServerOn->setEqLogic_id($this->getId());
		$boblightServerOn->save();

		$boblightServerOff = $this->getCmd(null, 'boblightServerOff');
		if (!is_object($boblightServerOff)) {
			$boblightServerOff = new hyperionNGCmd();
			$boblightServerOff->setLogicalId('boblightServerOff');
			$boblightServerOff->setIsVisible(1);
			$boblightServerOff->setName(__('Serveur Boblight Off', __FILE__));
			$boblightServerOff->setOrder($order++);
			$boblightServerOff->setValue($boblightServerState->getId());
		}
		$boblightServerOff->setType('action');
		$boblightServerOff->setSubType('other');
		$boblightServerOff->setEqLogic_id($this->getId());
		$boblightServerOff->save();

		$grabberOn = $this->getCmd(null, 'grabberOn');
		if (!is_object($grabberOn)) {
			$grabberOn = new hyperionNGCmd();
			$grabberOn->setLogicalId('grabberOn');
			$grabberOn->setIsVisible(1);
			$grabberOn->setName(__('Platforme de capture On', __FILE__));
			$grabberOn->setOrder($order++);
			$grabberOn->setValue($grabberState->getId());
		}
		$grabberOn->setType('action');
		$grabberOn->setSubType('other');
		$grabberOn->setEqLogic_id($this->getId());
		$grabberOn->save();

		$grabberOff = $this->getCmd(null, 'grabberOff');
		if (!is_object($grabberOff)) {
			$grabberOff = new hyperionNGCmd();
			$grabberOff->setLogicalId('grabberOff');
			$grabberOff->setIsVisible(1);
			$grabberOff->setName(__('Platforme de capture Off', __FILE__));
			$grabberOff->setOrder($order++);
			$grabberOff->setValue($grabberState->getId());
		}
		$grabberOff->setType('action');
		$grabberOff->setSubType('other');
		$grabberOff->setEqLogic_id($this->getId());
		$grabberOff->save();

		$v4lOn = $this->getCmd(null, 'v4lOn');
		if (!is_object($v4lOn)) {
			$v4lOn = new hyperionNGCmd();
			$v4lOn->setLogicalId('v4lOn');
			$v4lOn->setIsVisible(1);
			$v4lOn->setName(__('Capture par USB On', __FILE__));
			$v4lOn->setOrder($order++);
			$v4lOn->setValue($v4lState->getId());
		}
		$v4lOn->setType('action');
		$v4lOn->setSubType('other');
		$v4lOn->setEqLogic_id($this->getId());
		$v4lOn->save();

		$v4lOff = $this->getCmd(null, 'v4lOff');
		if (!is_object($v4lOff)) {
			$v4lOff = new hyperionNGCmd();
			$v4lOff->setLogicalId('v4lOff');
			$v4lOff->setIsVisible(1);
			$v4lOff->setName(__('Capture par USB Off', __FILE__));
			$v4lOff->setOrder($order++);
			$v4lOff->setValue($v4lState->getId());
		}
		$v4lOff->setType('action');
		$v4lOff->setSubType('other');
		$v4lOff->setEqLogic_id($this->getId());
		$v4lOff->save();

		$ledDeviceOn = $this->getCmd(null, 'ledDeviceOn');
		if (!is_object($ledDeviceOn)) {
			$ledDeviceOn = new hyperionNGCmd();
			$ledDeviceOn->setLogicalId('ledDeviceOn');
			$ledDeviceOn->setIsVisible(1);
			$ledDeviceOn->setName(__('Equipement LED On', __FILE__));
			$ledDeviceOn->setOrder($order++);
			$ledDeviceOn->setValue($ledDeviceState->getId());
		}
		$ledDeviceOn->setType('action');
		$ledDeviceOn->setSubType('other');
		$ledDeviceOn->setEqLogic_id($this->getId());
		$ledDeviceOn->save();

		$ledDeviceOff = $this->getCmd(null, 'ledDeviceOff');
		if (!is_object($ledDeviceOff)) {
			$ledDeviceOff = new hyperionNGCmd();
			$ledDeviceOff->setLogicalId('ledDeviceOff');
			$ledDeviceOff->setIsVisible(1);
			$ledDeviceOff->setName(__('Equipement LED Off', __FILE__));
			$ledDeviceOff->setOrder($order++);
			$ledDeviceOff->setValue($ledDeviceState->getId());
		}
		$ledDeviceOff->setType('action');
		$ledDeviceOff->setSubType('other');
		$ledDeviceOff->setEqLogic_id($this->getId());
		$ledDeviceOff->save();

		$randomColor = $this->getCmd(null, 'randomColor');
		if (!is_object($randomColor)) {
			$randomColor = new hyperionNGCmd();
			$randomColor->setLogicalId('randomColor');
			$randomColor->setIsVisible(1);
			$randomColor->setName(__('Couleur aléatoire', __FILE__));
			$randomColor->setOrder($order++);
		}
		$randomColor->setType('action');
		$randomColor->setSubType('other');
		$randomColor->setEqLogic_id($this->getId());
		$randomColor->save();

		$randomEffect = $this->getCmd(null, 'randomEffect');
		if (!is_object($randomEffect)) {
			$randomEffect = new hyperionNGCmd();
			$randomEffect->setLogicalId('randomEffect');
			$randomEffect->setIsVisible(1);
			$randomEffect->setName(__('Effet aléatoires', __FILE__));
			$randomEffect->setOrder($order++);
		}
		$randomEffect->setType('action');
		$randomEffect->setSubType('other');
		$randomEffect->setEqLogic_id($this->getId());
		$randomEffect->save();

		$reset = $this->getCmd(null, 'reset');
		if (!is_object($reset)) {
			$reset = new hyperionNGCmd();
			$reset->setLogicalId('reset');
			$reset->setIsVisible(1);
			$reset->setName(__('Remise à zéro', __FILE__));
			$reset->setOrder($order++);
		}
		$reset->setType('action');
		$reset->setSubType('other');
		$reset->setEqLogic_id($this->getId());
		$reset->save();

		$userEffect = $this->getCmd(null, 'userEffect');
		if (!is_object($userEffect)) {
			$userEffect = new hyperionNGCmd();
			$userEffect->setLogicalId('userEffect');
			$userEffect->setIsVisible(1);
			$userEffect->setName(__('Effet utilisateur', __FILE__));
			$userEffect->setOrder($order++);
		}
		$userEffect->setType('action');
		$userEffect->setSubType('message');
		$userEffect->setEqLogic_id($this->getId());
		$userEffect->save();

		log::add(__CLASS__, 'info', 'Equipement : ' . $this->getHumanName() . ' : Commandes créées/mises à jour');
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
	 * Non obligatoire : permet de modifier l'affichage du widget (également utilisable par les commandes)
	  public function toHtml($_version = 'dashboard') {
	  }
	 */

	/*
	 * Non obligatoire : permet de déclencher une action après modification de variable de configuration
	public static function postConfig_<Variable>() {
	}
	 */

	/*
	 * Non obligatoire : permet de déclencher une action avant modification de variable de configuration
	public static function preConfig_<Variable>() {
	}
	 */

	/*     * **********************Getteur Setteur*************************** */

	public function updateCmd($readServerinfo, $getHumanName)
	{
		if (!empty($readServerinfo)) {
			log::add(__CLASS__, 'info', 'Equipement : ' . $getHumanName . ' : Mise à jour des commandes info...');
			$decode = json_decode($readServerinfo, true);
			$this->checkAndUpdateCmd('connectionState', 1);
			$hex = rgb2hex($decode['info']['activeLedColor'][0]['RGB Value'][0], $decode['info']['activeLedColor'][0]['RGB Value'][1], $decode['info']['activeLedColor'][0]['RGB Value'][2]);
			if ($hex != '#000000') {
				$this->checkAndUpdateCmd('colorState', $hex);
			} else {
				$this->checkAndUpdateCmd('colorState', 'Aucune');
			}
			$this->checkAndUpdateCmd('brightnessState', $decode['info']['adjustment'][0]['brightness']);
			$this->checkAndUpdateCmd('backlightThresholdState', $decode['info']['adjustment'][0]['backlightThreshold']);
			$effectState = $decode['info']['activeEffects'][0]['name'];
			if (!empty($effectState)) {
				$this->checkAndUpdateCmd('effectState', $effectState);
			} else {
				$this->checkAndUpdateCmd('effectState', 'Aucun');
			}
			$this->checkAndUpdateCmd('hyperionState', $decode['info']['components'][0]['enabled']);
			$this->checkAndUpdateCmd('smoothingState', $decode['info']['components'][1]['enabled']);
			$this->checkAndUpdateCmd('blackBorderState', $decode['info']['components'][2]['enabled']);
			$this->checkAndUpdateCmd('forwarderState', $decode['info']['components'][3]['enabled']);
			$this->checkAndUpdateCmd('boblightServerState', $decode['info']['components'][4]['enabled']);
			$this->checkAndUpdateCmd('grabberState', $decode['info']['components'][5]['enabled']);
			$this->checkAndUpdateCmd('v4lState', $decode['info']['components'][6]['enabled']);
			$this->checkAndUpdateCmd('ledDeviceState', $decode['info']['components'][7]['enabled']);
			log::add(__CLASS__, 'info', 'Equipement : ' . $getHumanName . ' : Commandes info mises à jour');
		} else {
			log::add(__CLASS__, 'warning', 'Equipement : ' . $getHumanName . ' : Echec de la connexion');
			$this->checkAndUpdateCmd('connectionState', 0);
		}
	}
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
	 * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
	  public function dontRemoveCmd() {
	  return true;
	  }
	 */

	// Exécution d'une commande  
	public function execute($_options = array())
	{
		$eqLogic = $this->getEqLogic();
		log::add('hyperionNG', 'info', 'Equipement : ' . $eqLogic->getHumanName() . ' : Commande exécutée : ' . $this->getLogicalId());
		log::add('hyperionNG', 'debug', 'Equipement : ' . $eqLogic->getHumanName() . ' : Options commande exécutée : ' . print_r($_options, true));
		$instanceState = $eqLogic->getCmd(null, 'instanceState');
		$colorDurationState = $eqLogic->getCmd(null, 'colorDurationState');
		$effectDurationState = $eqLogic->getCmd(null, 'effectDurationState');
		$dataInstance = array();
		$dataCommand = array();
		$dataServerinfo = array();
		if ($this->getLogicalId() == 'instances') {
			$eqLogic->checkAndUpdateCmd('instanceState', $_options['select']);
		}
		if (is_object($instanceState)) {
			$dataInstance['command'] = 'instance';
			$dataInstance['subcommand'] = 'switchTo';
			$dataInstance['instance'] = intval($instanceState->execCmd());
		}
		if ($this->getLogicalId() == 'color') {
			$dataCommand['command'] = 'color';
			$dataCommand['color'] = hex2rgb($_options['color']);
			$dataCommand['duration'] = intval($colorDurationState->execCmd() * 1000);
			$dataCommand['priority'] = 50;
			$dataCommand['origin'] = 'Jeedom';
		} else if ($this->getLogicalId() == 'colorDuration') {
			$eqLogic->checkAndUpdateCmd('colorDurationState', intval($_options['slider']));
		} else if ($this->getLogicalId() == 'brightness') {
			$dataCommand['command'] = 'adjustment';
			$dataCommand['adjustment'] = array('brightness' => intval($_options['slider']));
		} else if ($this->getLogicalId() == 'backlightThreshold') {
			$dataCommand['command'] = 'adjustment';
			$dataCommand['adjustment'] = array('backlightThreshold' => intval($_options['slider']));
		} else if ($this->getLogicalId() == 'providedEffects') {
			$dataCommand['command'] = 'effect';
			$dataCommand['effect'] = array('name' => $_options['select']);
			$dataCommand['duration'] = intval($effectDurationState->execCmd() * 1000);
			$dataCommand['priority'] = 50;
			$dataCommand['origin'] = 'Jeedom';
		} else if ($this->getLogicalId() == 'effectDuration') {
			$eqLogic->checkAndUpdateCmd('effectDurationState', intval($_options['slider']));
		} else if ($this->getLogicalId() == 'hyperionOn') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'ALL', 'state' => true);
		} else if ($this->getLogicalId() == 'hyperionOff') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'ALL', 'state' => false);
		} else if ($this->getLogicalId() == 'smoothingOn') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'SMOOTHING', 'state' => true);
		} else if ($this->getLogicalId() == 'smoothingOff') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'SMOOTHING', 'state' => false);
		} else if ($this->getLogicalId() == 'blackBorderOn') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'BLACKBORDER', 'state' => true);
		} else if ($this->getLogicalId() == 'blackBorderOff') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'BLACKBORDER', 'state' => false);
		} else if ($this->getLogicalId() == 'forwarderOn') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'FORWARDER', 'state' => true);
		} else if ($this->getLogicalId() == 'forwarderOff') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'FORWARDER', 'state' => false);
		} else if ($this->getLogicalId() == 'boblightServerOn') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'BOBLIGHTSERVER', 'state' => true);
		} else if ($this->getLogicalId() == 'boblightServerOff') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'BOBLIGHTSERVER', 'state' => false);
		} else if ($this->getLogicalId() == 'grabberOn') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'GRABBER', 'state' => true);
		} else if ($this->getLogicalId() == 'grabberOff') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'GRABBER', 'state' => false);
		} else if ($this->getLogicalId() == 'v4lOn') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'V4L', 'state' => true);
		} else if ($this->getLogicalId() == 'v4lOff') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'V4L', 'state' => false);
		} else if ($this->getLogicalId() == 'ledDeviceOn') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'LEDDEVICE', 'state' => true);
		} else if ($this->getLogicalId() == 'ledDeviceOff') {
			$dataCommand['command'] = 'componentstate';
			$dataCommand['componentstate'] = array('component' => 'LEDDEVICE', 'state' => false);
		} else if ($this->getLogicalId() == 'randomColor') {
			$dataCommand['command'] = 'color';
			$dataCommand['color'] = array(rand(0, 255), rand(0, 255), rand(0, 255));
			$dataCommand['duration'] = intval($colorDurationState->execCmd() * 1000);
			$dataCommand['priority'] = 50;
			$dataCommand['origin'] = 'Jeedom';
		} else if ($this->getLogicalId() == 'randomEffect') {
			$randomEffect = array('Aucun', 'Atomic swirl', 'Blue mood blobs', 'Breath', 'Candle', 'Cinema brighten lights', 'Cinema dim lights', 'Cold mood blobs', 'Collision', 'Color traces', 'Double swirl', 'Fire', 'Flags Germany/Sweden', 'Full color mood blobs', 'Green mood blobs', 'Knight rider', 'Led Test', 'Light clock', 'Lights', 'Notify blue', 'Pac-Man', 'Plasma', 'Police Lights Single', 'Police Lights Solid', 'Rainbow mood', 'Rainbow swirl', 'Rainbow swirl fast', 'Random', 'Red mood blobs', 'Sea waves', 'Snake', 'Sparks', 'Strobe red', 'Strobe white', 'System Shutdown', 'Trails', 'Trails color', 'Warm mood blobs', 'Waves with Color', 'X-Mas');
			$dataCommand['command'] = 'effect';
			$dataCommand['effect'] = array('name' => $randomEffect[array_rand($randomEffect)]);
			$dataCommand['duration'] = intval($effectDurationState->execCmd() * 1000);
			$dataCommand['priority'] = 50;
			$dataCommand['origin'] = 'Jeedom';
		} else if ($this->getLogicalId() == 'reset') {
			$dataCommand['command'] = 'clear';
			$dataCommand['priority'] = -1;
		} else if ($this->getLogicalId() == 'userEffect') {
			$dataCommand['command'] = 'effect';
			$dataCommand['effect'] = array('name' => $_options['message']);
			$dataCommand['duration'] = intval($effectDurationState->execCmd() * 1000);
			$dataCommand['priority'] = 50;
			$dataCommand['origin'] = 'Jeedom';
		}
		$dataServerinfo['command'] = 'serverinfo';
		$readServerinfo = hyperionNG::socket($eqLogic->getHumanName(), $eqLogic->getConfiguration('ip'), $eqLogic->getConfiguration('port', 19444), $dataInstance, $dataCommand, $dataServerinfo);
		$eqLogic->updateCmd($readServerinfo, $eqLogic->getHumanName());
	}

	/*     * **********************Getteur Setteur*************************** */
}
