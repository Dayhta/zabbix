<?php declare(strict_types = 0);
/*
** Zabbix
** Copyright (C) 2001-2024 Zabbix SIA
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


namespace Widgets\HostNavigator\Includes;

use Zabbix\Widgets\{
	CWidgetField,
	CWidgetForm
};

use Zabbix\Widgets\Fields\{
	CWidgetFieldCheckBox,
	CWidgetFieldHostPatternSelect,
	CWidgetFieldIntegerBox,
	CWidgetFieldMultiSelectGroup,
	CWidgetFieldMultiSelectOverrideHost,
	CWidgetFieldRadioButtonList,
	CWidgetFieldSeverities,
	CWidgetFieldTags
};

/**
 * Host navigator widget form.
 */
class WidgetForm extends CWidgetForm {

	public const HOST_STATUS_ANY = -1;
	public const HOST_STATUS_ENABLED = 0;
	public const HOST_STATUS_DISABLED = 1;

	public const PROBLEMS_ALL = 0;
	public const PROBLEMS_UNSUPPRESSED = 1;
	public const PROBLEMS_NONE = 2;

	public const GROUP_BY_HOST_GROUP = 0;
	public const GROUP_BY_TAG_VALUE = 1;
	public const GROUP_BY_SEVERITY = 2;

	private const LINES_MIN = 1;
	private const LINES_MAX = 9999;
	private const LINES_DEFAULT = 100;

	public function addFields(): self {
		return $this
			->addField($this->isTemplateDashboard()
				? null
				: new CWidgetFieldMultiSelectGroup('groupids', _('Host groups'))
			)
			->addField($this->isTemplateDashboard()
				? null
				: new CWidgetFieldHostPatternSelect('hosts', _('Hosts'))
			)
			->addField($this->isTemplateDashboard()
				? null
				: (new CWidgetFieldRadioButtonList('status', _('Host status'), [
					self::HOST_STATUS_ANY => _('Any'),
					self::HOST_STATUS_ENABLED => _('Enabled'),
					self::HOST_STATUS_DISABLED => _('Disabled')
				]))->setDefault(self::HOST_STATUS_ANY)
			)
			->addField($this->isTemplateDashboard()
				? null
				: (new CWidgetFieldRadioButtonList('host_tags_evaltype', _('Host tags'), [
					TAG_EVAL_TYPE_AND_OR => _('And/Or'),
					TAG_EVAL_TYPE_OR => _('Or')
				]))->setDefault(TAG_EVAL_TYPE_AND_OR)
			)
			->addField($this->isTemplateDashboard()
				? null
				: new CWidgetFieldTags('host_tags')
			)
			->addField(
				new CWidgetFieldSeverities('severities', _('Severity'))
			)
			->addField(
				new CWidgetFieldCheckBox('maintenance',
					$this->isTemplateDashboard() ? _('Show data in maintenance') : _('Show hosts in maintenance')
				)
			)
			->addField(
				(new CWidgetFieldRadioButtonList('problems', _('Show problems'), [
					self::PROBLEMS_ALL => _('All'),
					self::PROBLEMS_UNSUPPRESSED => _('Unsuppressed'),
					self::PROBLEMS_NONE => _('None')
				]))->setDefault(self::PROBLEMS_UNSUPPRESSED)
			)
			->addField(
				new CWidgetFieldHostGrouping('group_by', _('Group by'))
			)
			->addField($this->isTemplateDashboard()
				? null
				: (new CWidgetFieldIntegerBox('show_lines', _('Host limit'), self::LINES_MIN, self::LINES_MAX))
					->setDefault(self::LINES_DEFAULT)
					->setFlags(CWidgetField::FLAG_NOT_EMPTY | CWidgetField::FLAG_LABEL_ASTERISK)
			)
			->addField(
				new CWidgetFieldMultiSelectOverrideHost()
			);
	}
}
