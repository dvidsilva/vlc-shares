<?php
/* Megavideo Downloader 
   luruke.
   
   IMPROVED VERSION BY XIMARX with premium-support
   
   http://forum.codecall.net/classes-code-snippets/14324-php-megavideo-downloader.html
    
    @date = 12/02/08 
      @mail = lurukee@gmail.com 
       
      Usage: 
       
         $obj = new Megavideo( ID or URL );   //http://www.megavideo.com/?v=7G4OHAUW or 7G4OHAUW 
         $obj->get( INFO );               //if is empty return an array with all info else you can select the info 
          
         INFOS: 
          
         URL.............Url with you can download the flv video 
         SIZE............The size (MB) of video 
      TITLE...........Title of video 
      DURATION........Duration of video in minutes 
      SERVER..........Server of video 
      DESCRIPTION.....Description of video 
      ADDED...........Date of added 
      USERNAME........Username of uploader 
      CATEGORY........Category of video 
      VIEWS...........Number of views 
      COMMENTS........Number of comments 
      FAVORITED.......Number of favorites by users 
      RATING..........Rate of video 

greetz evilsocket 4 the idea. 
*/

class X_Megavideo {
	
	public $id = null;
	public $context = null;
	public $userId = null;
	
	function __construct($url, $context = null, $userId = null) {
		$this->context = $context;
		$this->userId = $userId;
		
		preg_match ( '#\?v=(.+?)$#', $url, $id );
		
		$this->id = @$id [1] ? $id [1] : $url;
		
		// Ximarx's fix: reduce id to 8 
		// so megaupload -> megavideo links work
		$this->id = substr ( $this->id, 0, 8 );
		
		$this->getxml ();
		
		//X_Debug::i(print_r($this->xml, true));
		

		$parse = array ('runtimehms' => 'duration',
		 	'size' => 'size',
			's' => 'server', 
			'title' => 'title', 
			'description' => 'description', 
			'added' => 'added', 
			'username' => 'username', 
			'category' => 'category', 
			'views' => 'views', 
			'comments' => 'comments', 
			'favorited' => 'favorited', 
			'rating' => 'rating', 
			'k1' => 'key1', 
			'k2' => 'key2', 
			'un' => 'str' 
		);
		
		foreach ( $parse as $key => $val ) {
			$this->parsexml ( $key, $val );
		}
		
		$this->size = round ( $this->size / (1024 * 1024) );
	
	}
	
	function get($what = false) {
		$all = array ("URL" => "http://www" . $this->server . ".megavideo.com/files/" . $this->decrypt ( $this->str, $this->key1, $this->key2 ) . "/", 
			"SIZE" => $this->size, 
			"TITLE" => $this->title, 
			"DURATION" => $this->duration, 
			"SERVER" => $this->server, 
			"DESCRIPTION" => $this->description, 
			"ADDED" => $this->added, 
			"USERNAME" => $this->username, 
			"CATEGORY" => $this->category, 
			"VIEWS" => $this->views, 
			"COMMENTS" => $this->comments, 
			"FAVORITED" => $this->favorited, 
			"RATING" => $this->rating
		);
		
		return $what && array_key_exists ( strtoupper ( $what ), $all ) ? $all [strtoupper ( $what )] : $all;
	}
	
	function getxml() {
		if ($this->context != null && $this->userId != null) {
			$this->xml = file_get_contents ( "http://www.megavideo.com/xml/videolink.php?v=" . $this->id . "&u=" . $this->userId . "&id=" . time (), false, $this->context ); // or
		} else {
			$this->xml = file_get_contents ( "http://www.megavideo.com/xml/videolink.php?v=" . $this->id . "&id=" . time () ); // or
		}
		//X_Debug::i("http://www.megavideo.com/xml/videolink.php?v=".$this->id."&id=".time());
		//die("Error!\n"); 
		if ($this->xml === false) {
			throw new Exception ( 'Megavideo wrapper error: file_get_contents' );
		}
	}
	
	function parsexml($attribute, $name) {
		preg_match ( "#\s$attribute=\"(.+?)\"#", $this->xml, $tmp );
		@list ( , $this->$name ) = $tmp;
	}
	
	function decrypt($str, $key1, $key2) {
		$reg1 = array ();
		
		for($reg3 = 0; $reg3 < strlen ( $str ); $reg3 ++) {
			$reg0 = $str [$reg3];
			
			switch ($reg0) {
				case '0' :
					$reg1 [] = '0000';
					break;
				case '1' :
					$reg1 [] = '0001';
					break;
				case '2' :
					$reg1 [] = '0010';
					break;
				case '3' :
					$reg1 [] = '0011';
					break;
				case '4' :
					$reg1 [] = '0100';
					break;
				case '5' :
					$reg1 [] = '0101';
					break;
				case '6' :
					$reg1 [] = '0110';
					break;
				case '7' :
					$reg1 [] = '0111';
					break;
				case '8' :
					$reg1 [] = '1000';
					break;
				case '9' :
					$reg1 [] = '1001';
					break;
				case 'a' :
					$reg1 [] = '1010';
					break;
				case 'b' :
					$reg1 [] = '1011';
					break;
				case 'c' :
					$reg1 [] = '1100';
					break;
				case 'd' :
					$reg1 [] = '1101';
					break;
				case 'e' :
					$reg1 [] = '1110';
					break;
				case 'f' :
					$reg1 [] = '1111';
					break;
			}
		}
		
		$reg1 = join ( $reg1 );
		$reg6 = array ();
		
		for($reg3 = 0; $reg3 < 384; $reg3 ++) {
			$key1 = ($key1 * 11 + 77213) % 81371;
			$key2 = ($key2 * 17 + 92717) % 192811;
			$reg6 [] = ($key1 + $key2) % 128;
		}
		
		for($reg3 = 256; $reg3 >= 0; $reg3 --) {
			$reg5 = @$reg6 [$reg3];
			$reg4 = @$reg3 % 128;
			$reg8 = @$reg1 [$reg5];
			$reg1 [$reg5] = @$reg1 [$reg4];
			$reg1 [$reg4] = $reg8;
		}
		
		for($reg3 = 0; $reg3 < 128; $reg3 ++) {
			$reg1 [$reg3] = @$reg1 [$reg3] ^ (@$reg6 [$reg3 + 256] & 1);
		}
		
		$reg12 = $reg1;
		$reg7 = array ();
		
		for($reg3 = 0; $reg3 < @strlen ( $reg12 ); $reg3 += 4) {
			$reg9 = substr ( $reg12, $reg3, 4 );
			$reg7 [] = $reg9;
		}
		
		$reg2 = array ();
		
		for($reg3 = 0; $reg3 < count ( $reg7 ); $reg3 ++) {
			$reg0 = @$reg7 [$reg3];
			
			switch ($reg0) {
				case '0000' :
					$reg2 [] = '0';
					break;
				case '0001' :
					$reg2 [] = '1';
					break;
				case '0010' :
					$reg2 [] = '2';
					break;
				case '0011' :
					$reg2 [] = '3';
					break;
				case '0100' :
					$reg2 [] = '4';
					break;
				case '0101' :
					$reg2 [] = '5';
					break;
				case '0110' :
					$reg2 [] = '6';
					break;
				case '0111' :
					$reg2 [] = '7';
					break;
				case '1000' :
					$reg2 [] = '8';
					break;
				case '1001' :
					$reg2 [] = '9';
					break;
				case '1010' :
					$reg2 [] = 'a';
					break;
				case '1011' :
					$reg2 [] = 'b';
					break;
				case '1100' :
					$reg2 [] = 'c';
					break;
				case '1101' :
					$reg2 [] = 'd';
					break;
				case '1110' :
					$reg2 [] = 'e';
					break;
				case '1111' :
					$reg2 [] = 'f';
					break;
			}
		}
		
		return join ( $reg2 );
	
	}

} 

/*
$argv[1] = $_GET['url'];

$obj = new Megavideo($argv[1]); 
   print "-- Megavideo Downloader by luruke --\n"; 
   print "URL download:..........{$obj->get(url)}\n"; 
   print "Title:.................{$obj->get(title)}\n"; 
   print "Duration:..............{$obj->get(duration)}m\n"; 
   print "Size:..................{$obj->get(size)}Mb\n"; 
*/    

