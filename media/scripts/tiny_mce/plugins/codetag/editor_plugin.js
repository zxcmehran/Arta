/**
 * Codetag Plugin for tinyMCE inside Arta.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('codetag');

	tinymce.create('tinymce.plugins.CodeTagPlugin', {
		
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mceCodeTag', function() {
				ed.windowManager.open({
					file : url + '/dialog.htm',
					width : 500 + parseInt(ed.getLang('codetag.delta_width', 0)),
					height : 450 + parseInt(ed.getLang('codetag.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url // Plugin absolute URL
				});

			});
			

			// Register example button
			ed.addButton('codetag', {
				title : 'codetag.desc',
				cmd : 'mceCodeTag',
				image : url + '/img/code.png'
			});
			

			// Add a node change handler, selects the button in the UI when a image is selected
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('codetag', n.nodeName == 'CODE');
				//cm.setDisabled('codetag', n.nodeName !== 'CODE' && ed.selection.getNode().innerHTML=='');
			});
		},
		
		_setCode2Img: function(){
			
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'Code Tag plugin',
				author : 'Mehran Ahadi',
				authorurl : 'http://artaproject.com/',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('codetag', tinymce.plugins.CodeTagPlugin);
})();