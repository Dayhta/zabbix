<?php
/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


require_once dirname(__FILE__).'/../common/testTimeoutsDisplay.php';

/**
 * @onBefore prepareTimeoutsData
 *
 * @backup config, hosts, proxy
 */
class testHostsTimeouts extends testTimeoutsDisplay {

	protected static $hostid;
	protected static $hostid_druleids;

	public static function prepareTimeoutsData() {
		CDataHelper::call('proxy.create',
			[
				[
					'name' => 'Proxy assigned to host',
					'operating_mode' => 0
				]
			]
		);
		$proxyid = CDataHelper::getIds('name');

		$host_result = CDataHelper::createHosts([
			[
				'host' => 'Host for timeouts check',
				'groups' => [
					[
						'groupid' => 4 // Zabbix servers
					]
				],
				'interfaces' => [
					[
						'type'=> INTERFACE_TYPE_AGENT,
						'main' => INTERFACE_PRIMARY,
						'useip' => INTERFACE_USE_DNS,
						'ip' => '',
						'dns' => 'zabbix_agent',
						'port' => '1'
					],
					[
						'type'=> INTERFACE_TYPE_SNMP,
						'main' => INTERFACE_PRIMARY,
						'useip' => INTERFACE_USE_DNS,
						'ip' => '',
						'dns' => 'snmp',
						'port' => '2',
						'details' => [
							'version' => 1,
							'community' => '{$SNMP_COMMUNITY}'
						]
					]
				],
				'discoveryrules' => [
					[
						'name' => 'Zabbix agent',
						'key_' => 'zabbix_agent_drule',
						'type' => ITEM_TYPE_ZABBIX,
						'delay' => 5
					]
				]
			],
			[
				'host' => 'Host for timeouts check with proxy',
				'groups' => [
					[
						'groupid' => 4 // Zabbix servers
					]
				],
				'interfaces' => [
					[
						'type'=> INTERFACE_TYPE_AGENT,
						'main' => INTERFACE_PRIMARY,
						'useip' => INTERFACE_USE_DNS,
						'ip' => '',
						'dns' => 'zabbix_agent',
						'port' => '1'
					],
					[
						'type'=> INTERFACE_TYPE_SNMP,
						'main' => INTERFACE_PRIMARY,
						'useip' => INTERFACE_USE_DNS,
						'ip' => '',
						'dns' => 'snmp',
						'port' => '2',
						'details' => [
							'version' => 1,
							'community' => '{$SNMP_COMMUNITY}'
						]
					]
				],
				'discoveryrules' => [
					[
						'name' => 'Zabbix agent',
						'key_' => 'zabbix_agent_drule',
						'type' => ITEM_TYPE_ZABBIX,
						'delay' => 5
					]
				]
			]
		]);
		self::$hostid = $host_result['hostids'];
		self::$hostid_druleids = $host_result['discoveryruleids'];

		CDataHelper::call('host.update',
			[
				[
					'hostid' => self::$hostid['Host for timeouts check with proxy'],
					'proxyid' => $proxyid['Proxy assigned to host']
				]
			]
		);
	}

	public function testHostsTimeouts_checkItemsMacros() {
		$link = 'zabbix.php?action=item.list&context=host&filter_set=1&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check'];
		$this->checkGlobal('global_macros', $link, 'Create item');
	}

	public function testHostsTimeouts_checkDiscoveryMacros() {
		$link = 'host_discovery.php?filter_set=1&context=host&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check'];
		$this->checkGlobal('global_macros', $link, 'Create discovery rule');
	}

	public function testHostsTimeouts_checkPrototypeMacros() {
		$link = 'zabbix.php?action=item.prototype.list&context=host&parent_discoveryid='.
				self::$hostid_druleids['Host for timeouts check:zabbix_agent_drule'];
		$this->checkGlobal('global_macros', $link, 'Create item prototype');
	}

	public function testHostsTimeouts_checkItemsCustom() {
		$link = 'zabbix.php?action=item.list&context=host&filter_set=1&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check'];
		$this->checkGlobal('global_custom', $link, 'Create item');
	}

	public function testHostsTimeouts_checkDiscoveryCustom() {
		$link = 'host_discovery.php?filter_set=1&context=host&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check'];
		$this->checkGlobal('global_custom', $link, 'Create discovery rule');
	}

	public function testHostsTimeouts_checkPrototypeCustom() {
		$link = 'zabbix.php?action=item.prototype.list&context=host&parent_discoveryid='.
				self::$hostid_druleids['Host for timeouts check:zabbix_agent_drule'];
		$this->checkGlobal('global_custom', $link, 'Create item prototype');
	}

	public function testHostsTimeouts_checkItemsDefault() {
		$link = 'zabbix.php?action=item.list&context=host&filter_set=1&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check'];
		$this->checkGlobal('global_default', $link, 'Create item');
	}

	public function testHostsTimeouts_checkDiscoveryDefault() {
		$link = 'host_discovery.php?filter_set=1&context=host&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check'];
		$this->checkGlobal('global_default', $link, 'Create discovery rule');
	}

	public function testHostsTimeouts_checkPrototypeDefault() {
		$link = 'zabbix.php?action=item.prototype.list&context=host&parent_discoveryid='.
				self::$hostid_druleids['Host for timeouts check:zabbix_agent_drule'];
		$this->checkGlobal('global_default', $link, 'Create item prototype');
	}

	public function testHostsTimeouts_checkItemsProxyDefault() {
		$link = 'zabbix.php?action=item.list&context=host&filter_set=1&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check with proxy'];
		$this->checkGlobal('global_default', $link, 'Create item', true);
	}

	public function testHostsTimeouts_checkDiscoveryProxyDefault() {
		$link = 'host_discovery.php?filter_set=1&context=host&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check with proxy'];
		$this->checkGlobal('global_default', $link, 'Create discovery rule', true);
	}

	public function testHostsTimeouts_checkPrototypeProxyDefault() {
		$link = 'zabbix.php?action=item.prototype.list&context=host&parent_discoveryid='.
				self::$hostid_druleids['Host for timeouts check with proxy:zabbix_agent_drule'];
		$this->checkGlobal('global_default', $link, 'Create item prototype', true);
	}

	public function testHostsTimeouts_checkItemsProxyMacros() {
		$link = 'zabbix.php?action=item.list&context=host&filter_set=1&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check with proxy'];
		$this->checkGlobal('proxy_macros', $link, 'Create item', true);
	}

	public function testHostsTimeouts_checkDiscoveryProxyMacros() {
		$link = 'host_discovery.php?filter_set=1&context=host&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check with proxy'];
		$this->checkGlobal('proxy_macros', $link, 'Create discovery rule', true);
	}

	public function testHostsTimeouts_checkPrototypeProxyMacros() {
		$link = 'zabbix.php?action=item.prototype.list&context=host&parent_discoveryid='.
				self::$hostid_druleids['Host for timeouts check with proxy:zabbix_agent_drule'];
		$this->checkGlobal('proxy_macros', $link, 'Create item prototype', true);
	}

	public function testHostsTimeouts_checkItemsProxyCustom() {
		$link = 'zabbix.php?action=item.list&context=host&filter_set=1&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check with proxy'];
		$this->checkGlobal('proxy_custom', $link, 'Create item', true);
	}

	public function testHostsTimeouts_checkDiscoveryProxyCustom() {
		$link = 'host_discovery.php?filter_set=1&context=host&filter_hostids%5B0%5D='.
				self::$hostid['Host for timeouts check with proxy'];
		$this->checkGlobal('proxy_custom', $link, 'Create discovery rule', true);
	}

	public function testHostsTimeouts_checkPrototypeProxyCustom() {
		$link = 'zabbix.php?action=item.prototype.list&context=host&parent_discoveryid='.
				self::$hostid_druleids['Host for timeouts check with proxy:zabbix_agent_drule'];
		$this->checkGlobal('proxy_custom', $link, 'Create item prototype', true);
	}
}
