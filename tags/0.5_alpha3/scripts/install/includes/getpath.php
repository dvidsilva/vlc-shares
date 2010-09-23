<?

$path = @$_POST ["path"];

$result = array ();

if (false && ! isset ( $path )) {
	$element = array ();
	$element ["name"] = "path should be specified";
	$element ["isFolder"] = false;
	$element ["isError"] = true;
	$result [] = $element;
	return;
} else {
	$path = $path . '/';
	$handle = @opendir ( $path );
	if ($handle != false) {
		while ( false != ($file = @readdir ( $handle )) ) {
			if ($file != "." &&  ($path != '/' || $file != "..") ) {
				$element = array ();
				$element ["name"] = $file;
				$element ["isFolder"] = is_dir ( $path . $file );
				$element ["isError"] = false;
				$result [$file] = $element;
			}
		}
		uasort($result, 'filedirsort');
		//uksort($result, 'pathsort');
	} else {
		$element = array ();
		$element ["name"] = 'Permission denied';
		$element ["isFolder"] = false;
		$element ["isError"] = true;
		$result [] = $element;
		$element = array ();
		$element ["name"] = '..';
		$element ["isFolder"] = true;
		$element ["isError"] = false;
		$result ['..'] = $element;
		
	}
}

function pathsort($key1, $key2) {
	if ( $key1 < $key2 ) {
		return -1;
	} elseif ( $key1 == $key2 ) {
		return 0;
	} else {
		return 1;
	}
}

function filedirsort($value1, $value2) {
	if ($value1['isFolder'] ) {
		if ( $value2['isFolder']) {
			return pathsort($value1['name'], $value2['name']);
		} else {
			return -1;
		}
	} else {
		if ( $value2['isFolder']) {
			return 1;
		} else {
			return pathsort($value1['name'], $value2['name']);
		}
	}
}

echo json_encode ( $result ); 