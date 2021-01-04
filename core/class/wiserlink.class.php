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
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class wiserlink extends eqLogic {
	/*     * *************************Attributs****************************** */
	public static $_widgetPossibility = array('custom' => true);
	private static $_wiserlinks = null;

	/*     * ***********************Methode static*************************** */
	
	public static function daemon() {
		$starttime = microtime (true);
		log::add('wes','debug','cron start');
		foreach (self::byType('wes') as $eqLogic) {
			$eqLogic->getwiserlinkInfo();
		}
		log::add('wes','debug','cron stop');
		$endtime = microtime (true);
		if ($endtime - $starttime < config::byKey('temporisation_lecture', 'wes', 60, true)) {
			usleep(floor((config::byKey('temporisation_lecture', 'wes') + $starttime - $endtime)*1000000));
		}
	}

	public static function deamon_info() {
		$return = array();
		$return['log'] = '';
		$return['state'] = 'nok';
		$cron = cron::byClassAndFunction('wiserlink', 'daemon');
		if (is_object($cron) && $cron->running()) {
			$return['state'] = 'ok';
		}
		$return['launchable'] = 'ok';
		return $return;
	}

	public static function deamon_start($_debug = false) {
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$cron = cron::byClassAndFunction('wiserlink', 'daemon');
		if (!is_object($cron)) {
			throw new Exception(__('Tâche cron introuvable', __FILE__));
		}
		$cron->run();
	}

	public static function deamon_stop() {
		$cron = cron::byClassAndFunction('wiserlink', 'daemon');
		if (!is_object($cron)) {
			throw new Exception(__('Tâche cron introuvable', __FILE__));
		}
		$cron->halt();
	}

	public static function deamon_changeAutoMode($_mode) {
		$cron = cron::byClassAndFunction('wiserlink', 'daemon');
		if (!is_object($cron)) {
			throw new Exception(__('Tâche cron introuvable', __FILE__));
		}
		$cron->setEnable($_mode);
		$cron->save();
	}

	/*     * *********************Méthodes d'instance************************* */

	public function getwiserlinkInfo() {
		if ($this->getConfiguration('pwd') != '' && $this->getConfiguration('user') != '' && $this->getConfiguration('addr') != ''){
			$url = 'http://' .$this->getConfiguration('user') .':'. $this->getConfiguration('pwd') . '@' . $this->getConfiguration('addr') . '/vesta/UsageMeter';
			log::add('wiserlink','debug','Refresh on : ' .$url);
			$request_http = new com_http($url);
			$results = json_decode(trim($request_http->exec($_timeout = 5)), true);
			log::add('wiserlink','debug',json_encode($results,true));
			foreach ($results['UsageMeterList'] as $measure) {
				if (in_array($measure['Type'], array('Heating'))){
					$this->checkAndUpdateCmd('ct1', $measure['Power']);
					$this->checkAndUpdateCmd('ct1_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Hot water'))){
					$this->checkAndUpdateCmd('ct2', $measure['Power']);
					$this->checkAndUpdateCmd('ct2_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Cooling'))){
					$this->checkAndUpdateCmd('ct3', $measure['Power']);
					$this->checkAndUpdateCmd('ct3_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Sockets'))){
					$this->checkAndUpdateCmd('ct4', $measure['Power']);
					$this->checkAndUpdateCmd('ct4_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Others'))){
					$this->checkAndUpdateCmd('ct5', $measure['Power']);
					$this->checkAndUpdateCmd('ct5_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Load1'))){
					$this->checkAndUpdateCmd('load1', $measure['Power']);
					$this->checkAndUpdateCmd('load1_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Load2'))){
					$this->checkAndUpdateCmd('load2', $measure['Power']);
					$this->checkAndUpdateCmd('load2_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Load3'))){
					$this->checkAndUpdateCmd('load3', $measure['Power']);
					$this->checkAndUpdateCmd('load3_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Load4'))){
					$this->checkAndUpdateCmd('load4', $measure['Power']);
					$this->checkAndUpdateCmd('load4_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Load5'))){
					$this->checkAndUpdateCmd('load5', $measure['Power']);
					$this->checkAndUpdateCmd('load5_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Electricity Meter'))){
					$this->checkAndUpdateCmd('teleinfo', $measure['Power']);
					$this->checkAndUpdateCmd('teleinfo_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Hot Water Meter'))){
					$this->checkAndUpdateCmd('eau', $measure['Power']);
					$this->checkAndUpdateCmd('eau_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Calorimeter'))){
					$this->checkAndUpdateCmd('gaz', $measure['Power']);
					$this->checkAndUpdateCmd('gaz_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Cold Water Meter'))){
					$this->checkAndUpdateCmd('eau2', $measure['Power']);
					$this->checkAndUpdateCmd('eau2_energy', $measure['EnergyConsumed']);
				}
			}
		}
	}

	public function preInsert() {
		$this->setCategory('energy', 1);
	}
	
	public function postAjax() {
		$this->getwiserlinkInfo();
	}
	
	public function getImage() {
        return 'plugins/wiserlink/core/config/wiser.png';
    }
	
	public function postSave() {
		$cmd = $this->getCmd(null, 'ct1');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('ct1');
			$cmd->setIsVisible(1);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Puissance-1', __FILE__));
			$cmd->setUnite('W');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setOrder(1);
			$cmd->setDisplay('icon','<i class="icon techno-heating3"></i>');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'ct1_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('ct1_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Energie-1', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setOrder(2);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'ct2');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('ct2');
			$cmd->setIsVisible(1);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Puissance-2', __FILE__));
			$cmd->setUnite('W');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setDisplay('icon','<i class="icon maison-shower2"></i>');
			$cmd->setOrder(3);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'ct2_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('ct2_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Energie-2', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setOrder(4);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'ct3');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('ct3');
			$cmd->setIsVisible(1);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Puissance-3', __FILE__));
			$cmd->setUnite('W');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setDisplay('icon','<i class="icon nature-snowflake"></i>');
			$cmd->setOrder(5);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'ct3_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('ct3_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Energie-3', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setOrder(6);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'ct4');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('ct4');
			$cmd->setIsVisible(1);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Puissance-4', __FILE__));
			$cmd->setUnite('W');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setDisplay('icon','<i class="icon jeedom-prise"></i>');
			$cmd->setOrder(7);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'ct4_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('ct4_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Energie-4', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setOrder(8);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'ct5');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('ct5');
			$cmd->setIsVisible(1);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Puissance-5', __FILE__));
			$cmd->setUnite('W');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setDisplay('icon','<i class="icon maison-house109"></i>');
			$cmd->setOrder(9);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'ct5_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('ct5_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Energie-5', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setOrder(10);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'teleinfo');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('teleinfo');
			$cmd->setIsVisible(1);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Teleinfo', __FILE__));
			$cmd->setUnite('W');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setDisplay('icon','<i class="fa fa-bolt"></i>');
			$cmd->setOrder(11);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'teleinfo_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('teleinfo_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Teleinfo Energie', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setOrder(12);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'gaz');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('gaz');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Gaz', __FILE__));
			$cmd->setUnite('m3');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setDisplay('icon','<i class="icon nature-fire14"></i>');
			$cmd->setOrder(13);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'gaz_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('gaz_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Gaz Energie', __FILE__));
			$cmd->setUnite('m3');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setOrder(14);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'eau');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('eau');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Eau chaude', __FILE__));
			$cmd->setUnite('m3');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setDisplay('icon','<i class="icon nature-watering1"></i>');
			$cmd->setOrder(15);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'eau_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('eau_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Eau chaude Total', __FILE__));
			$cmd->setUnite('m3');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setOrder(16);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'eau2');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('eau2');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Eau froide', __FILE__));
			$cmd->setUnite('m3');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setDisplay('icon','<i class="icon nature-watering1"></i>');
			$cmd->setOrder(17);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'eau2_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('eau2_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Eau froide Total', __FILE__));
			$cmd->setUnite('m3');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setOrder(18);
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'load1');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('load1');
			$cmd->setIsVisible(1);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Charge-1', __FILE__));
			$cmd->setUnite('W');
			$cmd->setTemplate('dashboard', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'load1_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('load1_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Energie-Charge-1', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'loa21');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('load2');
			$cmd->setIsVisible(1);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Charge-2', __FILE__));
			$cmd->setUnite('W');
			$cmd->setTemplate('dashboard', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'load2_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('load2_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Energie-Charge-2', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'load3');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('load3');
			$cmd->setIsVisible(1);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Charge-3', __FILE__));
			$cmd->setUnite('W');
			$cmd->setTemplate('dashboard', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'load3_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('load3_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Energie-Charge-3', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'load4');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('load4');
			$cmd->setIsVisible(1);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Charge-4', __FILE__));
			$cmd->setUnite('W');
			$cmd->setTemplate('dashboard', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'load4_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('load4_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Energie-Charge-4', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'load5');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('load5');
			$cmd->setIsVisible(1);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Charge-5', __FILE__));
			$cmd->setUnite('W');
			$cmd->setTemplate('dashboard', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
		
		$cmd = $this->getCmd(null, 'load5_energy');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('load5_energy');
			$cmd->setIsVisible(0);
			$cmd->setIsHistorized(1);
			$cmd->setName(__('Energie-Charge-5', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
		}
		$cmd->setType('info');
		$cmd->setSubType('numeric');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();

		$cmd = $this->getCmd(null, 'refresh');
		if (!is_object($cmd)) {
			$cmd = new wiserlinkCmd();
			$cmd->setLogicalId('refresh');
			$cmd->setIsVisible(1);
			$cmd->setName(__('Rafraichir', __FILE__));
		}
		$cmd->setType('action');
		$cmd->setSubType('other');
		$cmd->setEqLogic_id($this->getId());
		$cmd->save();
	}
}

class wiserlinkCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = null) {
		$eqLogic = $this->getEqlogic();
		if ($this->getLogicalId() == 'refresh') {
			return wiserlink::cron($eqLogic->getId());
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}
?>
