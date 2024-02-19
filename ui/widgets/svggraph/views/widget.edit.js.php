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


use Widgets\SvgGraph\Includes\CWidgetFieldDataSet;

?>

window.widget_svggraph_form = new class {

	/**
	 * @type {Map<HTMLLIElement, CSortable>}
	 */
	#single_items_sortable = new Map();

	init({form_tabs_id, color_palette, templateid}) {
		colorPalette.setThemeColors(color_palette);

		this._$overlay_body = jQuery('.overlay-dialogue-body');
		this._form = document.getElementById('widget-dialogue-form');
		this._templateid = templateid;
		this._dataset_wrapper = document.getElementById('data_sets');
		this._any_ds_aggregation_function_enabled = false;

		this._$overlay_body.on('scroll', () => {
			const $preview = jQuery('.<?= ZBX_STYLE_SVG_GRAPH_PREVIEW ?>', this._$overlay_body);

			if (!$preview.length) {
				this._$overlay_body.off('scroll');
				return;
			}

			if ($preview.offset().top < this._$overlay_body.offset().top && this._$overlay_body.height() > 400) {
				jQuery('#svg-graph-preview').css('top',
					this._$overlay_body.offset().top - $preview.offset().top
				);
				jQuery('.graph-widget-config-tabs .ui-tabs-nav').css('top', $preview.height());
			}
			else {
				jQuery('#svg-graph-preview').css('top', 0);
				jQuery('.graph-widget-config-tabs .ui-tabs-nav').css('top', 0);
			}
		});

		jQuery(`#${form_tabs_id}`)
			.on('tabsactivate', () => jQuery.colorpicker('hide'))
			.on('change', 'input, z-select, .multiselect', (e) => this.onGraphConfigChange(e));

		this._datasetTabInit();
		this._legendTabInit();
		this._problemsTabInit();

		this.onGraphConfigChange();
	}

	onGraphConfigChange() {
		this._updateForm();
		this._updatePreview();
	}

	updateVariableOrder(obj, row_selector, var_prefix) {
		for (const k of [10000, 0]) {
			jQuery(row_selector, obj).each(function(i) {
				if (var_prefix === 'ds') {
					jQuery(this).attr('data-set', i);
					jQuery('.single-item-table', this).attr('data-set', i);
				}

				jQuery('.multiselect[data-params]', this).each(function() {
					const name = jQuery(this).multiSelect('getOption', 'name');

					if (name !== null) {
						jQuery(this).multiSelect('modify', {
							name: name.replace(/([a-z]+\[)\d+(]\[[a-z_]+])/, `$1${k + i}$2`)
						});
					}
				});

				jQuery(`[name^="${var_prefix}["]`, this)
					.filter(function () {
						return jQuery(this).attr('name').match(/[a-z]+\[\d+]\[[a-z_]+]/);
					})
					.each(function () {
						jQuery(this).attr('name',
							jQuery(this).attr('name').replace(/([a-z]+\[)\d+(]\[[a-z_]+])/, `$1${k + i}$2`)
						);
					});

				jQuery(`[name^="${var_prefix}["]`, this)
					.filter(function () {
						return jQuery(this).attr('name').match(/[a-z]+\[\d+]\[[a-z_]+]/);
					})
					.each(function () {
						jQuery(this).attr('name',
							jQuery(this).attr('name').replace(/([a-z]+\[)\d+(]\[[a-z_]+])/, `$1${k + i}$2`)
						);
					});

				jQuery(`[id^="${var_prefix}_"]`, this)
					.filter(function () {
						return jQuery(this).attr('id').match(/[a-z]+_\d+_[a-z_]+/);
					})
					.each(function () {
						jQuery(this).attr('id',
							jQuery(this).attr('id').replace(/([a-z]+_)\d+(_[a-z_]+)/, `$1${k + i}$2`)
						);
					});

				jQuery(`[id^="lbl_${var_prefix}_"]`, this)
					.filter(function () {
						return jQuery(this).attr('id').match(/lbl_[a-z]+_\d+_[a-z_]+/);
					})
					.each(function () {
						jQuery(this).attr('id',
							jQuery(this).attr('id').replace(/(lbl_[a-z]+_)\d+(_[a-z_]+)/, `$1${k + i}$2`)
						);
					});
			});
		}
	}

	_datasetTabInit() {
		this._updateDatasetsLabel();

		// Initialize vertical accordion.
		jQuery(this._dataset_wrapper)
			.on('focus', '.<?= CMultiSelect::ZBX_STYLE_CLASS ?> input.input', function() {
				jQuery('#data_sets').zbx_vertical_accordion('expandNth',
					jQuery(this).closest('.<?= ZBX_STYLE_LIST_ACCORDION_ITEM ?>').index()
				);
			})
			.on('click', function(e) {
				if (!e.target.classList.contains('color-picker-preview')) {
					jQuery.colorpicker('hide');
				}

				if (e.target.classList.contains('js-click-expend')
						|| e.target.classList.contains('color-picker-preview')
						|| e.target.classList.contains('<?= ZBX_STYLE_BTN_GREY ?>')) {
					jQuery('#data_sets').zbx_vertical_accordion('expandNth',
						jQuery(e.target).closest('.<?= ZBX_STYLE_LIST_ACCORDION_ITEM ?>').index()
					);
				}
			})
			.on('collapse', function(event, data) {
				jQuery('textarea, .multiselect', data.section).scrollTop(0);
				jQuery(window).trigger('resize');
				const dataset = data.section[0];

				if (dataset.dataset.type == '<?= CWidgetFieldDataSet::DATASET_TYPE_SINGLE_ITEM ?>') {
					const message_block = dataset.querySelector('.no-items-message');

					if (dataset.querySelectorAll('.single-item-table-row').length == 0) {
						message_block.style.display = 'block';
					}
				}
			})
			.on('expand', function(event, data) {
				jQuery(window).trigger('resize');
				const dataset = data.section[0];

				if (dataset.dataset.type == '<?= CWidgetFieldDataSet::DATASET_TYPE_SINGLE_ITEM ?>') {
					const message_block = dataset.querySelector('.no-items-message');

					if (dataset.querySelectorAll('.single-item-table-row').length == 0) {
						message_block.style.display = 'none';
					}

					widget_svggraph_form._initSingleItemSortable(dataset);
				}
			})
			.zbx_vertical_accordion({handler: '.<?= ZBX_STYLE_LIST_ACCORDION_ITEM_TOGGLE ?>'});

		// Initialize rangeControl UI elements.
		jQuery('.<?= CRangeControl::ZBX_STYLE_CLASS ?>', jQuery(this._dataset_wrapper)).rangeControl();

		// Initialize pattern fields.
		jQuery('.multiselect', jQuery(this._dataset_wrapper)).multiSelect();

		for (const colorpicker of jQuery('.<?= ZBX_STYLE_COLOR_PICKER ?> input')) {
			jQuery(colorpicker).colorpicker({
				onUpdate: function(color) {
					jQuery('.<?= ZBX_STYLE_COLOR_PREVIEW_BOX ?>',
						jQuery(this).closest('.<?= ZBX_STYLE_LIST_ACCORDION_ITEM ?>')
					).css('background-color', `#${color}`);
				},
				appendTo: '.overlay-dialogue-body'
			});
		}

		this._dataset_wrapper.addEventListener('click', (e) => {
			if (e.target.classList.contains('js-add')) {
				this._selectItems();
			}

			if (e.target.classList.contains('element-table-remove')) {
				this._removeSingleItem(e.target);
			}

			if (e.target.classList.contains('js-remove')) {
				this._removeDataSet(e.target);
			}
		});

		document
			.getElementById('dataset-add')
			.addEventListener('click', () => {
				this._addDataset(<?= CWidgetFieldDataSet::DATASET_TYPE_PATTERN_ITEM ?>);
			});

		document
			.getElementById('dataset-menu')
			.addEventListener('click', (e) => this._addDatasetMenu(e));

		window.addPopupValues = (list) => {
			if (!isset('object', list) || list.object !== 'itemid') {
				return false;
			}

			for (let i = 0; i < list.values.length; i++) {
				this._addSingleItem(list.values[i].itemid, list.values[i].name);
			}


			this._updateSingleItemsLinks();
			this._initSingleItemSortable(this._getOpenedDataset());

			this._updatePreview();
		}

		this._updateSingleItemsLinks();
		this._initDataSetSortable();

		this._initSingleItemSortable(this._getOpenedDataset());
	}

	_legendTabInit() {
		document.getElementById('legend')
			.addEventListener('click', (e) => {
				jQuery('#legend_lines').rangeControl(
					e.target.checked ? 'enable' : 'disable'
				);
				if (!e.target.checked) {
					jQuery('#legend_columns').rangeControl('disable');
				}
				else if (!document.getElementById('legend_statistic').checked) {
					jQuery('#legend_columns').rangeControl('enable');
				}
				document.getElementById('legend_statistic').disabled = !e.target.checked;
				document.getElementById('legend_aggregation').disabled = (!e.target.checked
					|| !this._any_ds_aggregation_function_enabled
				);
			});

		document.getElementById('legend_statistic')
			.addEventListener('click', (e) => {
				jQuery('#legend_columns').rangeControl(
					!e.target.checked ? 'enable' : 'disable'
				);
			});
	}

	_problemsTabInit() {
		const widget = document.getElementById('problems');

		document.getElementById('show_problems')
			.addEventListener('click', (e) => {
				jQuery('#graph_item_problems, #problem_name, #problemhosts_select').prop('disabled', !e.target.checked);
				jQuery('#problemhosts_').multiSelect(e.target.checked ? 'enable' : 'disable');
				jQuery('[name^="severities["]', jQuery(widget)).prop('disabled', !e.target.checked);
				jQuery('[name="evaltype"]', jQuery(widget)).prop('disabled', !e.target.checked);
				jQuery('input, button, z-select', jQuery('#tags_table_tags', jQuery(widget))).prop('disabled',
					!e.target.checked
				);
			});
	}

	_updateDatasetsLabel() {
		for (const dataset of this._dataset_wrapper.querySelectorAll('.<?= ZBX_STYLE_LIST_ACCORDION_ITEM ?>')) {
			this._updateDatasetLabel(dataset);
		}
	}

	_updateDatasetLabel(dataset) {
		const placeholder_text = <?= json_encode(_('Data set')) ?> + ` #${parseInt(dataset.dataset.set) + 1}`;

		const data_set_label = dataset.querySelector('.js-dataset-label');
		const data_set_label_input = dataset.querySelector(`[name="ds[${dataset.dataset.set}][data_set_label]"]`);

		data_set_label.textContent = data_set_label_input.value !== '' ? data_set_label_input.value : placeholder_text;
		data_set_label_input.placeholder = placeholder_text;
	}

	_addDatasetMenu(e) {
		const menu = [
			{
				items: [
					{
						label: <?= json_encode(_('Item pattern')) ?>,
						clickCallback: () => {
							this._addDataset(<?= CWidgetFieldDataSet::DATASET_TYPE_PATTERN_ITEM ?>);
						}
					},
					{
						label: <?= json_encode(_('Item list')) ?>,
						clickCallback: () => {
							this._addDataset(<?= CWidgetFieldDataSet::DATASET_TYPE_SINGLE_ITEM ?>);
						}
					}
				]
			},
			{
				items: [
					{
						label: <?= json_encode(_('Clone')) ?>,
						disabled: this._getOpenedDataset() === null,
						clickCallback: () => {
							this._cloneDataset();
						}
					}
				]
			}
		];

		jQuery(e.target).menuPopup(menu, new jQuery.Event(e), {
			position: {
				of: e.target,
				my: 'left top',
				at: 'left bottom',
				within: '.wrapper'
			}
		});
	}

	_addDataset(type) {
		jQuery(this._dataset_wrapper).zbx_vertical_accordion('collapseAll');

		const template = type == <?= CWidgetFieldDataSet::DATASET_TYPE_SINGLE_ITEM ?>
			? new Template(jQuery('#dataset-single-item-tmpl').html())
			: new Template(jQuery('#dataset-pattern-item-tmpl').html());

		const used_colors = [];

		for (const color of this._form.querySelectorAll('.<?= ZBX_STYLE_COLOR_PICKER ?> input')) {
			if (color.value !== '') {
				used_colors.push(color.value);
			}
		}

		this._dataset_wrapper.insertAdjacentHTML('beforeend', template.evaluate({
			rowNum: this._dataset_wrapper.querySelectorAll('.<?= ZBX_STYLE_LIST_ACCORDION_ITEM ?>').length,
			color: type == <?= CWidgetFieldDataSet::DATASET_TYPE_SINGLE_ITEM ?>
				? ''
				: colorPalette.getNextColor(used_colors)
		}));

		const dataset = this._getOpenedDataset();

		for (const colorpicker of dataset.querySelectorAll('.<?= ZBX_STYLE_COLOR_PICKER ?> input')) {
			jQuery(colorpicker).colorpicker({appendTo: '.overlay-dialogue-body'});
		}

		for (const multiselect of dataset.querySelectorAll('.multiselect')) {
			jQuery(multiselect).multiSelect();
		}

		for (const range_control of dataset.querySelectorAll('.<?= CRangeControl::ZBX_STYLE_CLASS ?>')) {
			jQuery(range_control).rangeControl();
		}

		this._$overlay_body.scrollTop(Math.max(this._$overlay_body.scrollTop(),
			this._form.scrollHeight - this._$overlay_body.height()
		));

		this._initDataSetSortable();
		this._updateForm();
	}

	_cloneDataset() {
		const dataset = this._getOpenedDataset();

		this._addDataset(dataset.dataset.type);

		const cloned_dataset = this._getOpenedDataset();

		if (dataset.dataset.type == <?= CWidgetFieldDataSet::DATASET_TYPE_SINGLE_ITEM ?>) {
			for (const row of dataset.querySelectorAll('.single-item-table-row')) {
				this._addSingleItem(
					row.querySelector(`[name^='ds[${dataset.getAttribute('data-set')}][itemids]`).value,
					row.querySelector('.table-col-name a').textContent
				);
			}

			this._updateSingleItemsLinks();
			this._initSingleItemSortable(cloned_dataset);
		}
		else {
			if (this._templateid === null) {
				jQuery('.js-hosts-multiselect', cloned_dataset).multiSelect('addData',
					jQuery('.js-hosts-multiselect', dataset).multiSelect('getData')
				);
			}

			jQuery('.js-items-multiselect', cloned_dataset).multiSelect('addData',
				jQuery('.js-items-multiselect', dataset).multiSelect('getData')
			);
		}

		for (const input of dataset.querySelectorAll('[name^=ds]')) {
			const cloned_name = input.name.replace(/([a-z]+\[)\d+(]\[[a-z_]+])/,
				`$1${cloned_dataset.getAttribute('data-set')}$2`
			);

			if (input.tagName.toLowerCase() === 'z-select') {
				cloned_dataset.querySelector(`[name="${cloned_name}"]`).value = input.value;
			}
			else if (input.type === 'text') {
				cloned_dataset.querySelector(`[name="${cloned_name}"]`).value = input.value;

				if (input.classList.contains('<?= CRangeControl::ZBX_STYLE_CLASS ?>')) {
					// Fire change event to redraw range input.
					cloned_dataset.querySelector(`[name="${cloned_name}"]`).dispatchEvent(new Event('change'));
				}
			}
			else if (input.type === 'checkbox' || input.type === 'radio') {
				// Click to fire events.
				cloned_dataset.querySelector(`[name="${cloned_name}"][value="${input.value}"]`).checked = input.checked;
			}
		}

		this._updateDatasetLabel(cloned_dataset);
		this._updatePreview();
	}

	_removeDataSet(obj) {
		const dataset_remove = obj.closest('.list-accordion-item');

		dataset_remove.remove();

		if (this.#single_items_sortable.has(dataset_remove)) {
			this.#single_items_sortable.get(dataset_remove).enable(false);
			this.#single_items_sortable.delete(dataset_remove);
		}

		this.updateVariableOrder(jQuery(this._dataset_wrapper), '.<?= ZBX_STYLE_LIST_ACCORDION_ITEM ?>', 'ds');
		this._updateDatasetsLabel();

		const dataset = this._getOpenedDataset();

		if (dataset !== null) {
			this._updateSingleItemsOrder(dataset);
			this._initSingleItemSortable(dataset);
		}

		this._initDataSetSortable();
		this._updateSingleItemsLinks();
		this.onGraphConfigChange();
	}

	_getOpenedDataset() {
		return this._dataset_wrapper.querySelector('.<?= ZBX_STYLE_LIST_ACCORDION_ITEM_OPENED ?>[data-set]');
	}

	_initDataSetSortable() {
		if (this._sortable_data_set === undefined) {
			this._sortable_data_set = new CSortable(document.querySelector('#data_sets'), {
				selector_handle: '.js-main-drag-icon, .js-dataset-label'
			});

			this._sortable_data_set.on(CSortable.EVENT_SORT, () => {
				this.updateVariableOrder(this._dataset_wrapper, '.<?= ZBX_STYLE_LIST_ACCORDION_ITEM ?>', 'ds');
				this._updateDatasetsLabel();
				this._updatePreview();
			});
		}
	}

	_selectItems() {
		if (this._templateid === null) {
			PopUp('popup.generic', {
				srctbl: 'items',
				srcfld1: 'itemid',
				srcfld2: 'name',
				dstfrm: this._form.id,
				numeric: 1,
				writeonly: 1,
				multiselect: 1,
				with_webitems: 1,
				real_hosts: 1,
				resolve_macros: 1
			});
		}
		else {
			PopUp('popup.generic', {
				srctbl: 'items',
				srcfld1: 'itemid',
				srcfld2: 'name',
				dstfrm: this._form.id,
				numeric: 1,
				writeonly: 1,
				multiselect: 1,
				with_webitems: 1,
				hostid: this._templateid,
				hide_host_filter: 1
			});
		}
	}

	_addSingleItem(itemid, name) {
		const dataset = this._getOpenedDataset();
		const items_table = dataset.querySelector('.single-item-table');

		if (items_table.querySelector(`input[value="${itemid}"]`) !== null) {
			return;
		}

		const dataset_index = dataset.getAttribute('data-set');
		const template = new Template(jQuery('#dataset-item-row-tmpl').html());
		const item_next_index = items_table.querySelectorAll('.single-item-table-row').length + 1;

		items_table.querySelector('tbody').insertAdjacentHTML('beforeend', template.evaluate({
			dsNum: dataset_index,
			rowNum: item_next_index,
			name: name,
			itemid: itemid
		}));

		const used_colors = [];

		for (const color of this._form.querySelectorAll('.<?= ZBX_STYLE_COLOR_PICKER ?> input')) {
			if (color.value !== '') {
				used_colors.push(color.value);
			}
		}

		jQuery(`#items_${dataset_index}_${item_next_index}_color`)
			.val(colorPalette.getNextColor(used_colors))
			.colorpicker();
	}

	_removeSingleItem(element) {
		element.closest('.single-item-table-row').remove();

		const dataset = this._getOpenedDataset();

		this._updateSingleItemsOrder(dataset);
		this._updateSingleItemsLinks();
		this._initSingleItemSortable(dataset);
		this._updatePreview();
	}

	_initSingleItemSortable(dataset) {
		const rows_container = dataset.querySelector('.single-item-table tbody');

		if (rows_container === null) {
			return;
		}

		if (this.#single_items_sortable.has(dataset)) {
			return;
		}

		const sortable = new CSortable(rows_container, {
			selector_handle: '.table-col-handle'
		});

		sortable.on(CSortable.EVENT_SORT, () => {
			this._updateSingleItemsOrder(dataset);
			this._updateSingleItemsLinks();
		});

		this.#single_items_sortable.set(dataset, sortable);
	}

	_updateSingleItemsLinks() {
		for (const dataset of this._dataset_wrapper.querySelectorAll('.<?= ZBX_STYLE_LIST_ACCORDION_ITEM ?>')) {
			const dataset_index = dataset.getAttribute('data-set');
			const size = dataset.querySelectorAll('.single-item-table-row').length + 1;

			for (let i = 0; i < size; i++) {
				jQuery(`#items_${dataset_index}_${i}_name`).off('click').on('click', () => {
					let ids = [];
					for (let i = 0; i < size; i++) {
						ids.push(jQuery(`#items_${dataset_index}_${i}_input`).val());
					}

					if (this._templateid === null) {
						PopUp('popup.generic', {
							srctbl: 'items',
							srcfld1: 'itemid',
							srcfld2: 'name',
							dstfrm: widget_svggraph_form._form.id,
							dstfld1: `items_${dataset_index}_${i}_input`,
							dstfld2: `items_${dataset_index}_${i}_name`,
							numeric: 1,
							writeonly: 1,
							with_webitems: 1,
							real_hosts: 1,
							resolve_macros: 1,
							dialogue_class: 'modal-popup-generic',
							excludeids: ids
						});
					}
					else {
						PopUp('popup.generic', {
							srctbl: 'items',
							srcfld1: 'itemid',
							srcfld2: 'name',
							dstfrm: widget_svggraph_form._form.id,
							dstfld1: `items_${dataset_index}_${i}_input`,
							dstfld2: `items_${dataset_index}_${i}_name`,
							numeric: 1,
							writeonly: 1,
							with_webitems: 1,
							hostid: this._templateid,
							hide_host_filter: 1,
							dialogue_class: 'modal-popup-generic',
							excludeids: ids
						});
					}
				});
			}
		}
	}

	_updateSingleItemsOrder(dataset) {
		jQuery.colorpicker('destroy', jQuery('.single-item-table .<?= ZBX_STYLE_COLOR_PICKER ?> input', dataset));

		const dataset_index = dataset.getAttribute('data-set');

		for (const row of dataset.querySelectorAll('.single-item-table-row')) {
			const prefix = `items_${dataset_index}_${row.rowIndex}`;

			row.querySelector('.table-col-no span').textContent = `${row.rowIndex}:`;
			row.querySelector('.table-col-name a').id = `${prefix}_name`;
			row.querySelector('.table-col-action input').id = `${prefix}_input`;

			const colorpicker = row.querySelector('.single-item-table .<?= ZBX_STYLE_COLOR_PICKER ?> input');

			colorpicker.id = `${prefix}_color`;
			jQuery(colorpicker).colorpicker({appendTo: '.overlay-dialogue-body'});
		}
	}

	_updateForm() {
		const axes_used = {<?= GRAPH_YAXIS_SIDE_LEFT ?>: 0, <?= GRAPH_YAXIS_SIDE_RIGHT ?>: 0};

		for (const element of this._form.querySelectorAll('[type=radio], [type=hidden]')) {
			if (element.name.match(/ds\[\d+]\[axisy]/) && element.checked) {
				axes_used[element.value]++;
			}
		}

		for (const element of this._form.querySelectorAll('[type=hidden]')) {
			if (element.name.match(/or\[\d+]\[axisy]/)) {
				axes_used[element.value]++;
			}
		}

		const dataset = this._getOpenedDataset();

		if (dataset !== null) {
			this._updateDatasetLabel(dataset);

			const dataset_index = dataset.getAttribute('data-set');

			const draw_type = dataset.querySelector(`[name="ds[${dataset_index}][type]"]:checked`);
			const is_stacked = dataset.querySelector(`[type=checkbox][name="ds[${dataset_index}][stacked]"]`).checked;

			// Data set tab.
			const aggregate_function_select = dataset.querySelector(`[name="ds[${dataset_index}][aggregate_function]"]`);
			const approximation_select = dataset.querySelector(`[name="ds[${dataset_index}][approximation]"]`);

			let stacked_enabled = true;
			let width_enabled = true;
			let pointsize_enabled = true;
			let fill_enabled = true;
			let missingdata_enabled = true;
			let aggregate_none_enabled = true;
			let approximation_all_enabled = true;

			switch (draw_type.value) {
				case '<?= SVG_GRAPH_TYPE_LINE ?>':
					pointsize_enabled = false;
					if (is_stacked) {
						approximation_all_enabled = false;
					}
					break;

				case '<?= SVG_GRAPH_TYPE_POINTS ?>':
					stacked_enabled = false;
					width_enabled = false;
					fill_enabled = false;
					missingdata_enabled = false;
					approximation_all_enabled = false;
					break;

				case '<?= SVG_GRAPH_TYPE_STAIRCASE ?>':
					pointsize_enabled = false;
					approximation_all_enabled = false;
					break;

				case '<?= SVG_GRAPH_TYPE_BAR ?>':
					width_enabled = false;
					pointsize_enabled = false;
					fill_enabled = false;
					missingdata_enabled = false;

					if (is_stacked) {
						aggregate_none_enabled = false;
					}

					approximation_all_enabled = false;
					break;
			}

			dataset.querySelector(`[type=checkbox][name="ds[${dataset_index}][stacked]"]`).disabled = !stacked_enabled;
			jQuery(`[name="ds[${dataset_index}][width]"]`, dataset).rangeControl(width_enabled ? 'enable' : 'disable');
			jQuery(`[name="ds[${dataset_index}][pointsize]"]`, dataset).rangeControl(
				pointsize_enabled ? 'enable' : 'disable'
			);
			jQuery(`[name="ds[${dataset_index}][fill]"]`, dataset).rangeControl(fill_enabled ? 'enable' : 'disable');

			for (const element of dataset.querySelectorAll(`[name="ds[${dataset_index}][missingdatafunc]"]`)) {
				element.disabled = !missingdata_enabled;
			}

			aggregate_function_select.getOptionByValue(<?= AGGREGATE_NONE ?>).disabled = !aggregate_none_enabled;
			if (!aggregate_none_enabled && aggregate_function_select.value == <?= AGGREGATE_NONE ?>) {
				aggregate_function_select.value = <?= AGGREGATE_AVG ?>;
			}

			const aggregation_enabled = aggregate_function_select.value != <?= AGGREGATE_NONE ?>;

			dataset.querySelector(`[name="ds[${dataset_index}][aggregate_interval]"]`).disabled = !aggregation_enabled;

			for (const element of dataset.querySelectorAll(`[name="ds[${dataset_index}][aggregate_grouping]"]`)) {
				element.disabled = !aggregation_enabled;
			}

			approximation_select.getOptionByValue(<?= APPROXIMATION_ALL ?>).disabled = !approximation_all_enabled;
			if (!approximation_all_enabled && approximation_select.value == <?= APPROXIMATION_ALL ?>) {
				approximation_select.value = <?= APPROXIMATION_AVG ?>;
			}
		}

		const all_datasets = this._dataset_wrapper.querySelectorAll('.<?= ZBX_STYLE_LIST_ACCORDION_ITEM ?>');

		this._any_ds_aggregation_function_enabled = false;

		for (const ds of all_datasets) {
			const ds_index = ds.getAttribute('data-set');
			const aggregate_function_select = ds.querySelector(`[name="ds[${ds_index}][aggregate_function]"]`);

			if (aggregate_function_select.value != <?= AGGREGATE_NONE ?>) {
				this._any_ds_aggregation_function_enabled = true;
				break;
			}
		}

		document.getElementById('legend_aggregation').disabled = !this._any_ds_aggregation_function_enabled;

		// Displaying options tab.
		const percentile_left_checkbox = document.getElementById('percentile_left');
		percentile_left_checkbox.disabled = !axes_used[<?= GRAPH_YAXIS_SIDE_LEFT ?>];

		document.getElementById('percentile_left_value').disabled = !percentile_left_checkbox.checked
			|| !axes_used[<?= GRAPH_YAXIS_SIDE_LEFT ?>];

		const percentile_right_checkbox = document.getElementById('percentile_right');
		percentile_right_checkbox.disabled = !axes_used[<?= GRAPH_YAXIS_SIDE_RIGHT ?>];

		document.getElementById('percentile_right_value').disabled = !percentile_right_checkbox.checked
			|| !axes_used[<?= GRAPH_YAXIS_SIDE_RIGHT ?>];

		// Axes tab.
		const lefty_checkbox = document.getElementById('lefty');
		lefty_checkbox.disabled = !axes_used[<?= GRAPH_YAXIS_SIDE_LEFT ?>];

		const lefty_on = !lefty_checkbox.disabled && lefty_checkbox.checked;

		if (lefty_checkbox.disabled) {
			lefty_checkbox.checked = true;
		}

		for (const element of document.querySelectorAll('#lefty_min, #lefty_max, #lefty_units')) {
			element.disabled = !lefty_on;
		}

		document.getElementById('lefty_static_units').disabled = !lefty_on
			|| document.getElementById('lefty_units').value != <?= SVG_GRAPH_AXIS_UNITS_STATIC ?>;

		const righty_checkbox = document.getElementById('righty');
		righty_checkbox.disabled = !axes_used[<?= GRAPH_YAXIS_SIDE_RIGHT ?>];

		const righty_on = !righty_checkbox.disabled && righty_checkbox.checked;

		if (righty_checkbox.disabled) {
			righty_checkbox.checked = true;
		}

		for (const element of document.querySelectorAll('#righty_min, #righty_max, #righty_units')) {
			element.disabled = !righty_on;
		}

		document.getElementById('righty_static_units').disabled = !righty_on
			|| document.getElementById('righty_units').value != <?= SVG_GRAPH_AXIS_UNITS_STATIC ?>;

		// Trigger event to update tab indicators.
		document.getElementById('tabs').dispatchEvent(new Event(TAB_INDICATOR_UPDATE_EVENT));
	}

	#update_preview_abort_controller = null;
	#update_preview_loading_timeout = null;

	_updatePreview() {
		if (this.#update_preview_abort_controller !== null) {
			this.#update_preview_abort_controller.abort();
		}

		if (this.#update_preview_loading_timeout !== null) {
			clearTimeout(this.#update_preview_loading_timeout);
		}

		const preview = document.getElementById('svg-graph-preview');
		const preview_container = preview.parentElement;
		const preview_computed_style = getComputedStyle(preview);
		const contents_width = Math.floor(parseFloat(preview_computed_style.width));
		const contents_height = Math.floor(parseFloat(preview_computed_style.height)) - 10;

		const fields = getFormFields(this._form);

		fields.override_hostid = this.#resolveOverrideHostId();
		fields.time_period = this.#resolveTimePeriod(fields.time_period);

		const data = {
			templateid: this._templateid ?? undefined,
			fields,
			preview: 1,
			contents_width,
			contents_height
		};

		const curl = new Curl('zabbix.php');

		curl.setArgument('action', 'widget.svggraph.view');

		this.#update_preview_loading_timeout = setTimeout(() => {
			this.#update_preview_loading_timeout = null;
			preview_container.classList.add('is-loading');
		}, 1000);

		const abort_controller = new AbortController();

		this.#update_preview_abort_controller = abort_controller;

		fetch(curl.getUrl(), {
			method: 'POST',
			headers: {'Content-Type': 'application/json'},
			body: JSON.stringify(data),
			signal: abort_controller.signal
		})
			.then((response) => response.json())
			.then((response) => {
				if ('error' in response) {
					throw {error: response.error};
				}

				for (const element of this._form.parentNode.children) {
					if (element.matches('.msg-good, .msg-bad, .msg-warning')) {
						element.parentNode.removeChild(element);
					}
				}

				if (this.#update_preview_loading_timeout !== null) {
					clearTimeout(this.#update_preview_loading_timeout);
					this.#update_preview_loading_timeout = null;
				}

				preview_container.classList.remove('is-loading');

				if ('body' in response) {
					preview.innerHTML = response.body;
					preview.setAttribute('unselectable', 'on');
					preview.style.userSelect = 'none';
				}
			})
			.catch((exception) => {
				if (abort_controller.signal.aborted) {
					return;
				}

				for (const element of this._form.parentNode.children) {
					if (element.matches('.msg-good, .msg-bad, .msg-warning')) {
						element.parentNode.removeChild(element);
					}
				}

				if (this.#update_preview_loading_timeout !== null) {
					clearTimeout(this.#update_preview_loading_timeout);
					this.#update_preview_loading_timeout = null;
				}

				preview_container.classList.remove('is-loading');

				let title;
				let messages = [];

				if (typeof exception === 'object' && 'error' in exception) {
					title = exception.error.title;
					messages = exception.error.messages;
				}
				else {
					title = <?= json_encode(_('Unexpected server error.')) ?>;
				}

				const message_box = makeMessageBox('bad', messages, title)[0];

				this._form.parentNode.insertBefore(message_box, this._form);
			});
	}

	#resolveTimePeriod(time_period_field) {
		if ('from' in time_period_field && 'to' in time_period_field) {
			return time_period_field;
		}

		let time_period;

		if (CWidgetBase.FOREIGN_REFERENCE_KEY in time_period_field) {
			const {reference} = CWidgetBase.parseTypedReference(
				time_period_field[CWidgetBase.FOREIGN_REFERENCE_KEY]
			);

			time_period = ZABBIX.EventHub.getData({
				context: 'dashboard',
				event_type: 'broadcast',
				reference,
				type: '_timeperiod'
			});
		}

		if (time_period === undefined) {
			time_period = ZABBIX.EventHub.getData({
				context: 'dashboard',
				event_type: 'broadcast',
				reference: CDashboard.REFERENCE_DASHBOARD,
				type: '_timeperiod'
			});
		}

		return {
			from: time_period.from,
			to: time_period.to
		};
	}

	#resolveOverrideHostId() {
		return ZABBIX.EventHub.getData({
			context: 'dashboard',
			event_type: 'broadcast',
			reference: CDashboard.REFERENCE_DASHBOARD,
			type: '_hostid'
		});
	}
};
