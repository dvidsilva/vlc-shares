<?php

require_once 'pclzip.php';
require_once 'Zend/Dom/Query.php';

/** 
 * @author ximarx
 * 
 * 
 */
class X_Egg {
	//TODO - Insert your code here

	private $destinationPath;
	private $basePath;
	private $manifestFile;
	
	private $m_label;
	private $m_description;
	private $m_file;
	private $m_class;
	private $m_version;
	private $m_key;
	private $m_c_from;
	private $m_c_to;
	
	private $_cleanFlag = false;
	
	private $files = array();
	
	private $installSql = null;
	private $uninstallSql = null;
	
	private function __construct() {}
	
	
	protected function parse($manifestFile) {
		$this->manifestFile = $manifestFile;
		
		if ( !file_exists($manifestFile) ) {
			throw new Exception('Manifest file not found');
		}
		
		/* @var $dom Zend_Dom_Query */
		$dom = new Zend_Dom_Query(@file_get_contents($manifestFile));
		
		$this->m_label = $dom->queryXPath('/vs-manifest/metadata/label')->current()->nodeValue;
		$this->m_description = $dom->queryXPath('/vs-manifest/metadata/description')->current()->nodeValue;
		$this->m_file = $dom->queryXPath('/vs-manifest/metadata/file')->current()->nodeValue;
		$this->m_class = $dom->queryXPath('/vs-manifest/metadata/class')->current()->nodeValue;
		$this->m_version = $dom->queryXPath('/vs-manifest/metadata/version')->current()->nodeValue;
		$this->m_key = $dom->queryXPath('/vs-manifest/metadata/key')->current()->nodeValue;
		$this->m_c_from = $dom->queryXPath('/vs-manifest/metadata/compatibility/from')->current()->nodeValue;
		try {
			$this->m_c_to = $dom->queryXPath('/vs-manifest/metadata/compatibility/to')->current()->nodeValue;
		} catch (Exception $e) { $this->m_c_to = null; } // if there is no TO, it isn't a problem
		
		$filesXPath = $dom->queryXPath('/vs-manifest/files//file');
		
		while ( $filesXPath->valid() ) {
			$node = $filesXPath->current();
			
			$properties = array();
			for ( $i = 0; $i < $node->attributes->length; $i++ ) {
				$properties[$node->attributes->item($i)->nodeName] = $node->attributes->item($i)->nodeValue;
			}
			
			$filename = $node->nodeValue;
			
			$parentStack = array();
			$parent = $node->parentNode;
			
			while ( $parent != null && !( $parent->nodeName == 'files' && $parent->parentNode->nodeName == 'vs-manifest' ) ) {
				// we are at top
				$parentStack[] = $parent->nodeName;
				$parent = $parent->parentNode;
			}
			
			$path = implode('/', array_reverse($parentStack, false));

			if ( $properties ) {
				X_Debug::i("Properties for {{$path}/{$filename}}: ".print_r($properties, true));
			}
			
			$this->files[] = new X_Egg_File("$path/$filename", $this->basePath, $this->destinationPath, $properties);
			
			$filesXPath->next();
		}
		
		try {
			$this->installSql = @$dom->queryXPath('/vs-manifest/database/install')->current()->nodeValue;
		} catch (Exception $e) { $this->installSql = null; } // if there is no TO, it isn't a problem

		try {
			$this->uninstallSql = @$dom->queryXPath('/vs-manifest/database/uninstall')->current()->nodeValue;
		} catch (Exception $e) { $this->uninstallSql = null; } // if there is no TO, it isn't a problem
		
		
	}
	
	public function getFiles() {
		return $this->files;
	}
	
	public function getLabel() {
		return $this->m_label;
	}
	
	public function getDescription() {
		return $this->m_description;
	}
	
	public function getFile() {
		return $this->m_file;
	}
	
	public function getClass() {
		return $this->m_class;
	}
	
	public function getVersion() {
		return $this->m_version;
	}
	
	public function getKey() {
		return $this->m_key;
	}
	
	public function getCompatibilityFrom() {
		return $this->m_c_from;
	}
	
	public function getCompatibilityTo() {
		return $this->m_c_to;
	}
	
	public function getInstallSQL() {
		if ( $this->installSql === null) {
			return null;
		} else {
			return $this->basePath.$this->installSql;
		}
	}
	
	public function getUninstallSQL() {
		if ( $this->uninstallSql === null) {
			return null;
		} else {
			return $this->basePath.$this->uninstallSql;
		}
	}
	
	public function getManifestFile() {
		return $this->manifestFile;
	}
	
	public function getDestinationPath() {
		return $this->destinationPath;
	}
	
	public function getBasePath() {
		return $this->basePath;
	}
	
	
	/**
	 * Set the destination base path where file will be placed
	 * @return X_Egg
	 */
	protected function setDestinationPath($path) {
		$this->destinationPath = rtrim($path, '\\/').'/';
		return $this;
	}
	
	/**
	 * Set egg base path
	 * @return X_Egg
	 */
	protected function setBasePath($path) {
		$this->basePath = rtrim($path, '\\/').'/';
		return $this;
	}
	
	function cleanTmp() {
		// remove temp file
		if ( $this->_cleanFlag && $this->getBasePath() !== null ) {
			try {
				//$this->_cleanDir(new DirectoryIterator($this->getBasePath()));
				$this->_rrmdir($this->getBasePath());
			} catch (Exception $e) {
				X_Debug::e('Directory cleanup error: '.$e->getMessage());
			}
		}
	}
	
	private function _rrmdir($dir) { 
		$dir = rtrim($dir, '\\/');
		X_Debug::i('Cleaning dir '.$dir);
		if (is_dir($dir)) { 
			$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (filetype($dir."/".$object) == "dir") {
						$this->_rrmdir($dir."/".$object);
						rmdir($dir."/".$object);
					} else {
						unlink($dir."/".$object);
					} 
				} 
			}
     		reset($objects); 
     		//rmdir($dir); 
		}
	} 	
	
	/**
	 * Try to clean directory using recorsion.
	 * No ensurance for wrong permissions
	 */
	private function _cleanDir(DirectoryIterator $dir) {
		X_Debug::i('Cleaning dir '.$dir->getRealPath());
		foreach ($dir as $entry) {
			/* @var $entry DirectoryIterator */
			if ( $entry->isDot() ) continue;
			
			if ( $entry->isFile() ) {
				@unlink($entry->getRealPath());
			} elseif ( $entry->isDir() ) {
				$this->_cleanDir($entry);
				@rmdir($entry->getRealPath());
			}
		}
	} 
	
	/**
	 * Factory of X_Egg: accepts a manifest file or a xegg (zip) archive
	 * @param string $eggfile path to manifest file or xegg file
	 * @param string $dest_basepath destination root path
	 * @param string $tmp_path temp dir for xegg unpack (ignored if $eggfile is a manifest)
	 * @param bool $disableExtCheck allow to disable zip xegg file type check
	 * @return X_Egg
	 */
	public static function factory($eggfile, $dest_basepath, $tmp_path = false, $disableExtCheck = false) {
		
		$egg = new X_Egg();
		
		// i have to unzip egg file if it is not a xml
		$extension = strtolower(pathinfo($eggfile, PATHINFO_EXTENSION ));
		if ( $extension == 'xml' ) {
			// i only have to read it
			$manifestFile = $eggfile;
			$basepath = dirname($eggfile);
		} elseif ( $disableExtCheck || $extension == 'zip' || $extension == 'xegg' ) {
			// i have to unpack the zip file
			
			$pclzip = new PclZip($eggfile);
			
			if ( $tmp_path === false ) {
				$tmp_path = sys_get_temp_dir() . '/x_egg_unzip/';
			} else {
				$tmp_path = rtrim($tmp_path, '\\/').'/';
			}
			$egg->_cleanFlag = true;
			$pclzip->extract(PCLZIP_OPT_PATH, $tmp_path);

			$manifestFile = $tmp_path.'manifest.xml';
			$basepath = $tmp_path;
			
		} else {
			throw new Exception("Invalid X_Egg file extensions. Valid extension are 'xegg' or 'zip'");
		}
		
		$egg->setDestinationPath($dest_basepath)
			->setBasePath($basepath)
			->parse($manifestFile);
			
		return $egg;
		
	}

}

