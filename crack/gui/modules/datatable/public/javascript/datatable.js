(function($) {
	var _datatable_count = 0;

	function T3DataTable($obj, opts) {
		var self;
		var data;         //2-dimensional array of data. Row major.
		var header;       //Array of header labels
		var $table;       //Actual jQuery table object
		var opts;         //Options for the table
		var sorting;      //Key (header label) value (current sorting method) pair of current sorting technique
		var filtering;    //Current filters on header
		var filter;       //Filter values for each header
		var empty;        //Indicates the field is empty
		var options;      //Table options lambda functions

		self = this;
		this.data      = [];
		this.header    = [];
		this.$table    = undefined;
		this.opts      = opts;
		this.sorting   = {};
		this.filtering = {};
		this.filter    = {};

		this.options = {
			'e': function(idx) { return '<a href="edit?id=' + idx + '" name="options_edit_' + idx + '" id="options_edit_' + idx + '"><img src="/modules/datatable/public/images/edit_16x16.png" alt="Edit" title="Edit" /></a>'; },
			'r': function(idx) { return '<a href="#" name="options_remove_' + idx + '" id="options_remove_' + idx + ' onclick="return controller.bulkDelete(this);"><img src="/modules/datatable/public/images/delete_16x16.png" alt="Delete" title="Delete" /></a>'; }
		};

		this.initialize = function($obj) {
			
			//Wrap datatable
			$obj.wrap("<div id=\"t3_datatable_" + _datatable_count + "\" name=\"t3_datatable_" + _datatable_count + "\" style=\"border: 0px; clear: both;\" class=\"t3_datatable\" />");
			self.$table = $obj;

			//populate data and header
			self._populateHeader();
			self._populateData();

			//Attach self
			$('#t3_datatable_' + _datatable_count).data('t3_datatable', self);
			self.$table.data('t3_datatable', self);

			//Render
			self._renderHeader();
			self._renderData();

			//Increment count
			_datatable_count++;

			//Execute onLoad event
			if(self.opts['onLoad'] != undefined)
				self.opts['onLoad'](self);
		}

		this._getFilterData = function(key, idx) {
			var ret={};

			if(idx == -1)
			{
				alert('header index is -1!!');
				return null;
			}

			//Get header index
			self.$table.find('tbody').find('tr').not('tr#' + self.$table.attr('id') + "_bulk_options").each(function() {
				var $th = $($(this).find('td')[idx]);
				var val = self.getCellValue($th);

				//Add to filter
				if(ret[val] == undefined)
					ret[val] = 0;

				ret[val]++;
			});

			return ret;
		}

		this._populateFilterCheck = function(filters, header, $th)
		{
			if(!$.isArray(filters) && filters == "all")
			{
				var id = self.$table.attr('id') + "_head_" + header + "_filter__all";

				//Check all field
				$th.find("#" + id).attr('checked', true);
				
				filters = self.filter[header];
				for(key in filters)
				{
					id = self.$table.attr('id') + "_head_" + header + "_filter_" + key;
					$th.find('input[id="' + id + '"]').attr('checked', true);
					self.filtering[header].push(key);
				}
			}
			else  //Array of elements that should be checked marked
			{
				for(var i=0; i<filters.length; i++)
				{
					var filter = filters[i];
					var id     = self.$table.attr('id') + "_head_" + header + "_filter_" + filter;
					$th.find('input[id="' + id + '"]').attr('checked', true);
					self.filtering[header].push(filter);
				}
			}
		}

		this._populateHeader = function() {
			self.filter = {};

			self.$table.find("thead tr th").each(function() {
				var html;
				var $th = $(this).clone(true);
				var id = $th.attr('id').replace(/^.*_head_(.*)$/, '$1');

				//Sorting capabilities
				if(!$.isEmptyObject(self.opts['sorting_fields']) && self.opts['sorting_fields'][id] != undefined)
				{
					var $img;
					$img = $('<img src="/images/sort.png" style="position: relative; top: .2em; cursor: pointer; padding-left: 5px;" />');

					//append
					$th.append($img);

					//on click event
					var clicked = false;
					$img.click(function() {
						if(!clicked)
						{
							clicked = true;
							self._sort(id);
							clicked = false;
						}
					});
				}

				//Filtering
				if(!$.isEmptyObject(self.opts['filtering_fields']) && self.opts['filtering_fields'][id] != undefined)
				{
					//Grab filter data
					var data = self._getFilterData(id, self.header.length);
					var $img;
					var popid;
					var html;

					//Set filter data
					self.filter[id] = data;

					//Add filter image
					$img   = $('<img src="/images/filter.png" style="position: relative; top: .2em; cursor: pointer; padding-left: 5px;" />');

					//Add popup
					popid  = $th.attr('id') + "_filter";
					html  = '<div name="' + popid + '_wrapper" id="' + popid + '_wrapper" class="filter-wrapper">';
					html += '<div name="' + popid + '_popup" id="' + popid + '_popup" style="" class="filter-popup">';
					
					//Get checkboxes for filters
					html += '<input type="checkbox" name="' + popid + '__all" id="' + popid + '__all" />(All)<br />';
					for(var key in data)
					{
						var label  = key + " (" + data[key] + ")";
						var name   = popid + "_" + key;
						html += '<input type="checkbox" name="' + name + '" id="' + name + '" /> ' + label + '<br />';
					}

					html += '</div>';
					html += '</div>';
					$popup = $(html);

					//append
					$th.append($img);
					$img.after($popup);
					
					//Add filtering
					if(self.filtering[id] == undefined)
						self.filtering[id] = [];

					//Assign on click events to the check boxes
					$popup.find(':checkbox').click(function() {
						var	$chk   = $(this);
						var header = id;
						var filter = $chk.attr('id').replace(/^.*_head_.*_(.*)$/, '$1');
						var val    = $chk.is(':checked');

						//Make sure we arent using _all_
						if(filter == "all")
						{
							var name = self.$table.attr('id') + '_head_' + header + "_filter";
						
							if(val)
							{
								var filters;
								$('input[name^="' + name + '"]').not($chk).attr('checked', val);
								filters = $('input[name^="' + name + '"]').not($chk).map(function() { return $(this).attr('id').replace(/^.*head_.*_filter__?([a-zA-Z0-9\s]*)*$/, '$1'); });

								for(var i=0; i<filters.length; i++)
									self.filtering[header].push(filters[i]);
							}
							else
							{
								$('input[name^="' + name + '"]').removeAttr('checked');
								self.filtering[header] = [];
							}
						}
						else
						{
							//Check to se if we are on or off
							if(val)
								self.filtering[header].push(filter);
							else
							{
								self.filtering[header] = $.grep(self.filtering[header], function(value) { return value != filter; });
							}
						}
						
						//Render data
						self._renderData();
					});

					$img.click(function() {
						var $filter = $('#' + popid + '_wrapper');
						$filter.toggle();
					});

					//Check mark the appropriate items
					self._populateFilterCheck(self.opts['filtering_fields'][id], id, $th);
				}

				//Push header
				self.header.push($th);
			});
		}

		this._populateData = function() {
			var bulk = self.$table.attr('id') + "_bulk_options";

			$obj.find("tbody tr").not('#' + bulk).each(function() {
				var entry = [];
				var $tr   = $(this).clone(true);

				self.data.push($tr);
			});

			//Check if we are empty
			if(self.data.length == 1 && self.data[0].find('td').attr('colspan') == self.header.length && self.data[0].find('td').html().search(/No Data/) != -1)
				self.empty = true;
		}

		this._renderHeader = function() {
			var idx=0;
			
			//Clear the header
			self.$table.find('thead tr').find('th').remove();

			for(var i=0; i<self.header.length; i++)
				self.$table.find('thead tr').append(self.header[i]);
		}

		this._renderData = function() {
			var name = self.$table.attr('id');
			var id   = self.$table.attr('id') + "_bulk_options";
			var $tbody;

			//Clear tbody
			$tbody = self.$table.find('tbody');
			$tbody.find('tr').not('tr#' + id).remove();

			//Render all data points
			for(var i=0; i<self.data.length; i++)
			{
				var entry = self.data[i].clone(true);

				//Do filtering
				self._checkFilter(entry);

				//add to tbody
				$tbody.append(entry);
			}

			if(self.opts['onRender'] != undefined)
				self.opts['onRender'](self);
		}

		this._getIndexHeader = function(header) {
			//Find the index of the header
			for(var idx=0; idx<self.header.length; idx++)
			{
				var name = self.header[idx].attr('id').replace(/^.*_head_(.*)$/, '$1');
				if(name == header)
					return idx;
			}

			return -1;
		}

		this._checkFilter = function($tr) {
			var visible = true;

			//go through filtering
			for(var header in self.filtering)
			{
				var filters = self.filtering[header];
				var idx     = self._getIndexHeader(header);
				var $td     = $($tr.find('td')[idx]);
				var val     = self.getCellValue($td);

				if($.inArray(val, filters) == -1)
				{
					visible = false
					break;
				}
			}

			//Check if we visibly show or not
			if(visible)
				$tr.show();
			else
				$tr.hide();
		}

		this._sort = function(header) {
			var new_data = [];
			var list     = [];
			var sort;
			var idx;


			//Find sorting technique
			if(self.sorting[header] == undefined)
				sort = 'asc';
			else
				sort = ((self.sorting[header] == 'asc')? 'desc' : 'asc');

			//Get index of header
			idx = self._getIndexHeader(header);
			if(idx == -1)
			{
				alert('couldnt find \'' + header + '\'.');
				return false;
			}

			//Get values
			self.$table.find('tbody').find('tr').not('tr#' + self.$table.attr('id') + "_bulk_options").each(function() {
				var $tr = $(this);
				var $td = $($tr.find('td')[idx]);
				var val = self.getCellValue($td);
				var ridx;

				ridx  = $tr.attr('id').replace(/datatable_[0-9]*_row_/, '');
				val  += " r" + ridx;
				list.push(val);
			});

			//Custom sort function
			function customSort(a, b) {
				var aidx;
				var bidx;

				//Take out row identifier
				aidx = a.replace(/^.*r([0-9]*)/, '$1');
				bidx = b.replace(/^.*r([0-9]*)/, '$1');
				a    = a.replace(/^(.*) r[0-9]*/, '$1');
				b    = b.replace(/^(.*) r[0-9]*/, '$1');

				//Check if we have a %, if so rm it
				if(a.match(/^.*%\s*?$/) || a.match(/^.*\$.*$/)) //percentage or currency match
				{
					a = $.trim(a.match(/[0-9.]+/g)[0]);
					b = $.trim(b.match(/[0-9.]+/g)[0]);
				}

				//Return
				if(!isNaN(a))
				{
					//If == do row comparison
					if(a == b)
						return aidx - bidx;

					return a - b;
				}

				//Alphebetically sort
				a = a.toLowerCase();
				b = b.toLowerCase();

				return ((a < b)? -1 : ((a > b)? 1 : 0));
			}

			//Sort
			list.sort(customSort);

			//Check if we reverse
			if(sort == 'desc')
				list.reverse();

			//After sort create new entries to add
			for(var i=0; i<list.length; i++)
			{
				var id  = self.$table.attr('id') + "_row_" + list[i].replace(/^.*r([0-9]*)$/, '$1');
				var $tr = self.$table.find('tbody').find('tr#' + id);
				var $td = $($tr.find('td')[idx]);
				var $ntr = $tr.clone();
				var name = self.$table.attr('id') + "_row_";

				//Push to new data
				new_data.push($ntr);
			}

			//Set sort
			self.sorting = {};
			self.sorting[header] = sort;

			//Set new data
			self.data = new_data;
			self._renderData();
		}

		//Get value of cell
		this.getCellValue = function($td) {
			var checkTextNode = function() { return this.nodeType == Node.TEXT_NODE };
			var val;

			//Check if we have images
			if($td.find('img').size() > 0)
				val = $td.find('img').attr('title');
			else
			{
				val = $td.contents().filter(checkTextNode).add($td.find('*').contents().filter(checkTextNode));
				val = $(val).text();
			}

			return val;
		}

		this.removeRow = function(idx) {
			self.data.splice(idx, 1);
			self._renderData();
		}

		this.updateRow = function(idx, row) {
			//TODO: Check for bulk entries --EMJ
			var $tr = self.data[idx];
			var i=0;

			//Update entries
			$tr.find('td').each(function() {
				var $td = $(this);
				$td.html(row[i]);
				i++;
			});

			//Render the table
			self._renderData();
		}

		this.appendRow = function(row) {
			//TODO: Refresh filters/sorting repopulate header --EMJ
			//TODO: Check for bulk entries --EMJ
			var $tbody = $(self.$table.find('tbody'));
			var idx    = self.data.length;
			var $tr;

			if(self.empty)
			{
				self.data  = [];
				self.empty = false;
			}

			//Add row
			var id = self.$table.attr('id') + "_row_" + idx;
			$tr = $('<tr id="' + id + '" name="' + id + '"></tr>');
			for(var i=0; i<row.length; i++)
			{
				var $td = $('<td>' + row[i] + '</td>');
				$tr.append($td);
			}

			if(self.opts['options'])
			{
				var html = '';
				var $td;

				for(var i=0; i<self.opts['options'].length; i++)
				{
					var c = self.opts['options'][i];

					if(self.options[c] != undefined)
						html += self.options[c](idx) + " ";
				}

				//Add to options field
				$td = $('<td>' + html + '</td>')
				$tr.append($td);
			}

			//Add to data
			self.data.push($tr);
			self._renderData();
		}

		//Call constructor
		self.initialize($obj);
	}
	
	//Datatable jquery
	$.fn.datatable = function(options) {

		var opts = $.extend({}, $.fn.datatable.defaults, options);

		return this.each(function() {
			var $this = $(this);
			var o;
			var d;

			//Get options
			o = (($.meta)? $.extend({}, opts, $this.data()) : opts);
			d = new T3DataTable($this, o);
		});
	}

	/*$.fn.datatable.defaults = {
		sorting: true,

		sorting_fields: {
			'Website': 'none',
			'Category': 'none',
			'Type': 'none',
			'Enabled': 'none'
		},

		sorting_funcs: {
		},

		filtering: false,
		filtering_fields: {
			'Website': '',
			'Category': '',
			'Type': '',
			'Enabled': ''
		},
		
		onRender: tableHilight
	};*/

	/* Sorting options
	 *  - asc: ascending
	 *  - desc: descending
	 *  - none: no sorting, as is */

	$.fn.datatable.defaults = {
		sorting: false,
		sorting_fields : {},
		sorting_funcs: {},
		filter: false,
		fitler_header: {},
		onRender: undefined,
		options: false,                   //Options column
		onLoad: undefined                 //Onload event for the table
	};

})(jQuery);

function tableHilight()
{
	$('table.list tr').hover(
		function() {
			$('td', this).addClass('over');
		},
		function() {
			$('td', this).removeClass('over');
		}
	);

	$('table.list tr').click(function() {
		if($('td', this).classExists('clicked'))
			$('td', this).removeClass('clicked');
		else
			$('td', this).addClass('clicked');
	});
}

$(document).ready(function() {
	tableHilight();
});

