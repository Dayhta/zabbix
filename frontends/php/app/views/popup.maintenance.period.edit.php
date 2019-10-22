<?php
/*
** Zabbix
** Copyright (C) 2001-2019 Zabbix SIA
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


$form = (new CFormList())
	->cleanItems()
	->setId('maintenance_period_form')
	->addVar('action', 'popup.maintenance.period.edit')
	->addVar('refresh', 1)
	->addVar('update', $data['update'])
	->addVar('index', $data['index']);

if ($data['timeperiodid']) {
	$form->addVar('timeperiodid', $data['timeperiodid']);
}

$days = [];

foreach (range(1, 7) as $day) {
	$value = 1 << ($day - 1);
	$days[] = [
		'name' => getDayOfWeekCaption($day),
		'value' => $value,
		'checked' => (bool) ($value & $data['dayofweek'])
	];
}

$months = [];

foreach (range(1, 12) as $month) {
	$value = 1 << ($month - 1);
	$months[] = [
		'name' => getMonthCaption($month),
		'value' => $value,
		'checked' => (bool) ($value & $data['month'])
	];
}

$form
	->addRow((new CLabel(_('Period type'), 'timeperiod_type')),
		(new CComboBox('timeperiod_type', $data['timeperiod_type'], null, [
			TIMEPERIOD_TYPE_ONETIME => _('One time only'),
			TIMEPERIOD_TYPE_DAILY	=> _('Daily'),
			TIMEPERIOD_TYPE_WEEKLY	=> _('Weekly'),
			TIMEPERIOD_TYPE_MONTHLY	=> _('Monthly')
		]))
	)
	->addRow((new CLabel(_('Every day(s)'), 'every'))->setAsteriskMark(),
		(new CNumericBox('every', $data['every'], 3))
			->setWidth(ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH)
			->setAriaRequired(),
		'row_timeperiod_every_day'
	)
	->addRow((new CLabel(_('Every week(s)'), 'every'))->setAsteriskMark(),
		(new CNumericBox('every', $data['every'], 2))
			->setWidth(ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH)
			->setAriaRequired(),
		'row_timeperiod_every_week'
	)
	->addRow((new CLabel(_('Day of week'), 'days'))->setAsteriskMark(),
		(new CCheckBoxList('days'))
			->addClass('col-3')
			->setOptions($days),
		'row_timeperiod_dayofweek'
	)
	->addRow((new CLabel(_('Month'), 'months'))->setAsteriskMark(),
		(new CCheckBoxList('months'))
			->addClass('col-4')
			->setOptions($months),
		'row_timeperiod_months'
	)
	->addRow(new CLabel(_('Date'), 'month_date_type'),
		(new CRadioButtonList('month_date_type', (int) $data['month_date_type']))
			->addValue(_('Day of month'), 0)
			->addValue(_('Day of week'), 1)
			->setModern(true),
		'row_timeperiod_date'
	)
	->addRow((new CLabel(_('Day of week'), 'every'))->setAsteriskMark(),
		new CComboBox('every', $data['every'], null, [
			1 => _('first'),
			2 => _x('second', 'adjective'),
			3 => _('third'),
			4 => _('fourth'),
			5 => _('last')
		]),
		'row_timeperiod_week'
	)
	->addRow('',
		(new CCheckBoxList('monthly_days'))
			->addClass('col-3')
			->setOptions($days),
		'row_timeperiod_week_days'
	)
	->addRow((new CLabel(_('Day of month'), 'day'))->setAsteriskMark(),
		(new CNumericBox('day', $data['day'], 2))
		->setWidth(ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH)
		->setAriaRequired(),
		'row_timeperiod_day'
	)
	->addRow((new CLabel(_('Date'), 'start_date'))->setAsteriskMark(),
		(new CDateSelector('start_date', $data['start_date']))
			->setDateFormat(ZBX_DATE_TIME)
			->setPlaceholder(_('YYYY-MM-DD hh:mm'))
			->setAriaRequired(),
		'row_timepreiod_start_date'
	)
	->addRow(new CLabel(_('At (hour:minute)'), 'hour'),
		[
			(new CNumericBox('hour', $data['hour'], 2))
				->setWidth(ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH),
			':',
			(new CNumericBox('minute', $data['minute'], 2))
				->setWidth(ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH)
		],
		'row_timeperiod_period_at_hours_minutes'
	)
	->addRow((new CLabel(_('Maintenance period length'), 'period_days'))->setAsteriskMark(),
		[
			(new CNumericBox('period_days', $data['period_days'], 3))
				->setWidth(ZBX_TEXTAREA_NUMERIC_STANDARD_WIDTH),
			_('Days'),
			new CComboBox('period_hours', $data['period_hours'], null, range(0, 23)),
			_('Hours'),
			new CComboBox('period_minutes', $data['period_minutes'], null, range(0, 59)),
			_('Minutes')
		],
		'row_timeperiod_period_length'
	);

$output = [
	'header' => $data['title'],
	'body' => (new CDiv([$data['errors'], $form]))->toString(),
	'buttons' => [
		[
			'title' => $data['update'] ? _('Apply') : _('Add'),
			'class' => 'dialogue-widget-save',
			'keepOpen' => true,
			'isSubmit' => true,
			'action' => 'submitMaintenancePeriod("#'.$form->getId().'")'
		]
	],
	'params' => $data['params'],
	'script_inline' => require 'app/views/popup.maintenance.period.edit.js.php'
];

if ($data['user']['debug_mode'] == GROUP_DEBUG_MODE_ENABLED) {
	CProfiler::getInstance()->stop();
	$output['debug'] = CProfiler::getInstance()->make()->toString();
}

echo (new CJson())->encode($output);
