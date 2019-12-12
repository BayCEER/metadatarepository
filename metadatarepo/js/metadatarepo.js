
var MDRepo = {
	/**
	 * Setup on page load
	 */
	initialize: function(container) {
		// Don't load if not in the files app TODO: Fix for sharing
		if(!$('#content.app-files').length) { return; }
	},

};


MDRepo.NewFileMenuPlugin = {

	attach: function(menu) {
		var fileList = menu.fileList;
		
		// only attach to main file list, public view is not supported yet
		if (fileList.id !== 'files') {
			return;
		}

		// register the new menu entry
		menu.addMenuEntry({
			id: 'readmedc',
			displayName: t('metadatarepo', 'New ReadmeDC.txt'),
			templateName: t('metadatarepo', '__ReadmeDC.TXT'),
			iconClass: 'icon-filetype-text',
			fileType: 'readmedc.txt',
			actionHandler: function(name) {
				var dir = fileList.getCurrentDirectory();
				// first create the file
				fileList.createFile(name).then(function() {
					
					// Ajax to fill the new file
					$.ajax({
						type: 'post',
						url: OC.generateUrl('/apps/metadatarepo/fill'),
						data: {
							dir: dir,
							filename: name
						},
						success: function(){
							OCA.Files_Texteditor.loadFile(dir,name,function(){},function(){});
							// once the file got successfully created,
							// open the editor
							OCA.Files_Texteditor._onEditorTrigger(
									name,
									{
										fileList: fileList,
										dir: dir
									}
							);
						}
					});
								
					
				});
			}
		});
	}
};

OCA.MDRepo = MDRepo;

OC.Plugins.register('OCA.Files.NewFileMenu', MDRepo.NewFileMenuPlugin);

