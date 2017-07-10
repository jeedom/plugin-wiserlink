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

	public static function cron($_eqlogic_id = null) {
		$eqLogics = ($_eqlogic_id !== null) ? array(eqLogic::byId($_eqlogic_id)) : eqLogic::byType('wiserlink', true);
		foreach ($eqLogics as $wiserlink) {
			try {
				$wiserlink->getwiserlinkInfo();
			} catch (Exception $e) {

			}
		}
	}

	/*     * *********************Méthodes d'instance************************* */

	public function getwiserlinkInfo() {
		if ($this->getConfiguration('pwd') != '' && $this->getConfiguration('user') != '' && $this->getConfiguration('addr') != ''){
			$url = 'http://' .$this->getConfiguration('user') .':'. $this->getConfiguration('pwd') . '@' . $this->getConfiguration('addr') . '/vesta/UsageMeter';
			log::add('wiserlink','debug','Refresh on : ' .$url);
			$request_http = new com_http($url);
			$results = json_decode(trim($request_http->exec($_timeout = 5)), true);
			log::add('wiserlink','debug',print_r($results,true));
			foreach ($results['UsageMeterList'] as $measure) {
				if (in_array($measure['Type'], array('Load1','Heating'))){
					$this->checkAndUpdateCmd('ct1', $measure['Power']);
					$this->checkAndUpdateCmd('ct1_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Load2','Hot water'))){
					$this->checkAndUpdateCmd('ct2', $measure['Power']);
					$this->checkAndUpdateCmd('ct2_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Load3','Cooling'))){
					$this->checkAndUpdateCmd('ct3', $measure['Power']);
					$this->checkAndUpdateCmd('ct3_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Load4','Sockets'))){
					$this->checkAndUpdateCmd('ct4', $measure['Power']);
					$this->checkAndUpdateCmd('ct4_energy', $measure['EnergyConsumed']);
				}
				else if (in_array($measure['Type'], array('Load5','Others'))){
					$this->checkAndUpdateCmd('ct5', $measure['Power']);
					$this->checkAndUpdateCmd('ct5_energy', $measure['EnergyConsumed']);
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
			$cmd->setUnite('W');
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
			$cmd->setUnite('kWh');
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
			$cmd->setName(__('Eau', __FILE__));
			$cmd->setUnite('W');
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
			$cmd->setName(__('Eau Energie', __FILE__));
			$cmd->setUnite('kWh');
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setOrder(16);
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
