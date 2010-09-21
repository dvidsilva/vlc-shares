<?php

class X_VlcShares {
	const VERSION = '0.5_alpha1';
	private $CONFIG_PATH;
	
	// durante la creazione della playlist delle collection:
	//		output: X_Plx_Entry
	const TRG_COLLECTIONS_INDEX = 'collectionsIndex';
	// corrisponde alla playlist della pagina del plugin
	//		input: Zend_Request; output: X_Plx
	const TRG_PLUGIN_PAGE = 'pluginPage';
	// genera i link da visualizzare nella pagina di gestione nella sezione plugins
	//		output: array('pluginName','pluginDesc','pluginLink')
	const TRG_MANAGE_PLUGINS_CONFS = 'managePageConfs';
	// genera i link da visualizzare nella pagina di gestione nella sezione azioni
	//		output: array('actionName','actionDesc','actionLink')
	const TRG_MANAGE_PLUGINS_LINKS = 'managePageLinks';
	// elementi da inserire prima del traversal di una directory
	// 		output: X_Plx_Entry|array(X_Plx_Entry)
	const TRG_DIR_TRAVERSAL_PRE = 'preDirTraversal';
	// indicare se elemento valido
	//		input: path al file; output: boolean
	const TRG_DIR_TRAVERSAL = 'whileDirTraversal';
	// elementi da inserire prima del traversal di una directory
	// 		output: X_Plx_Entry|array(X_Plx_Entry)
	const TRG_DIR_TRAVERSAL_POST = 'postDirTraversal';
	// elementi da inserire prima del traversal per i subs
	// 		output: array(completeFilename, dirname, filename) |array(modename => array('argname' => 'argvalue/')
	const TRG_MODE_ADDITIONALS = 'modeAdditionals';
	// elementi da inserire prima del traversal di una directory
	// 		output: X_Plx_Entry|array(X_Plx_Entry)
	const TRG_PROFILES_TRAVERSAL_PRE = 'preProfilesTraversal';
	// indicare se elemento valido
	//		input: path al file, profilesName; output: boolean
	const TRG_PROFILES_TRAVERSAL = 'whileProfilesTraversal';
	// indicare se elemento valido
	//		input: path al file; output: array('Profilename' => 'ProfileArgs')
	const TRG_PROFILES_ADDITIONALS = 'whileProfilesAdditional';
	// elementi da inserire prima del traversal di una directory
	// 		output: X_Plx_Entry|array(X_Plx_Entry)
	const TRG_PROFILES_TRAVERSAL_POST = 'postProfilesTraversal';
	// lista di parametri da sostituire
	//		input: Zend_Request, output: array('placeholder'=>'substitute')
	const TRG_VLC_ARGS_SUBTITUTE = 'vlcArgsSubstitute';
	// consente di eseguire operazioni su X_Vlc prima e dopo
	// lo spawn
	//		input: X_Vlc
	const TRG_VLC_SPAWN_PRE = 'preVlcSpawn';
	const TRG_VLC_SPAWN_POST = 'postVlcSpawn';
	// elementi da inserire all'inizio del menu di stream
	//		input: array(Request, completePath, dirPath, filename), output: X_Plx_Item|array(X_Plx_Item)
	const TRG_STREAM_MENU_PRE = 'preStreamMenuItems';
	// elementi da inserire alla fine del menu di stream
	//		input: array(Request, completePath, dirPath, filename), output: X_Plx_Item|array(X_Plx_Item)
	const TRG_STREAM_MENU_POST = 'postStreamMenuItems';
	// elementi da inserire all'inizio del menu di controllo
	//		input: X_Vlc, output: X_Plx_Item|array(X_Plx_Item)
	const TRG_CONTROLS_MENU_PRE = 'preControlsMenuItems';
	// elementi da inserire all'inizio del menu di controllo
	//		input: X_Vlc, output: X_Plx_Item|array(X_Plx_Item)
	const TRG_CONTROLS_MENU_POST = 'postControlsMenuItems';
	// elementi da inserire all'inizio del menu di controllo
	//		input: X_Plx, output qualsiasi cosa su cui si possa fare echo
	const TRG_ENDPAGES_OUTPUT_FILTER_PLX = 'plxEndpageOutputFilter';
	// elementi da inserire all'inizio del menu di controllo
	//		input: string, output qualsiasi cosa su cui si possa fare echo
	const TRG_ENDPAGES_OUTPUT_FILTER_HTML = 'htmlEndpageOutputFilter';
	
	
	static private $instance = null; 
	private function __construct() {
		$this->CONFIG_PATH = APPLICATION_PATH . '/configs/vlc-shares.newconfig.ini';
	}
	
	static private function i() {
		if ( is_null(self::$instance ) ) {
			self::$instance = new X_VlcShares();
		}
		return self::$instance;
	}
	static public function config() {
		return self::i()->CONFIG_PATH;
	}
	static public function version() {
		return self::VERSION;
	}
}
