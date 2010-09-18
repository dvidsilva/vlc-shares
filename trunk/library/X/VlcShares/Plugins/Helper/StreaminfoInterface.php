<?php

interface X_VlcShares_Plugins_Helper_StreaminfoInterface {
	
	// type unknown
	const AVCODEC_UNKNOWN = 0;
	
	// === audio codecs
	const ACODEC_AAC = 1;
	 
	// === video codecs
	const VCODEC_H264 = 1;
	
	
	/**
	 * Set the source $location
	 * Use fluent interface
	 * @param $location
	 * @return X_VlcShares_Plugins_Helper_StreaminfoInterface
	 */
	function setLocation($location);

	/**
	 * Return all infos about the source setted 
	 * with setLocation() in an associative array
	 * The array has this format:
	 * array(
	 * 	'source'	=> $source
	 * 	'videos'	=> array from getVideosInfo()
	 * 	'audios'	=> array from getAudiosInfo()
	 * 	'subs'		=> array from getSubsInfo()
	 * )
	 * 
	 * @return array
	 */
	function getInfos();
	
	/**
	 * Return info abount video streams
	 * as an associative array
	 * The array has this format:
	 * array(
	 * 	0	=>	array(
	 * 		'codecType'		=> result from getVideoCodecType(0),
	 * 		'codecName'		=> result from getVideoCodecName(0),
	 * 		'HELPER:custom'	=> result from a custom operation of the helper 
	 * 	),
	 * 	...
	 * )
	 * 
	 * @return array
	 */
	function getVideosInfo();
	
	/**
	 * Return the human-readable name
	 * of the codec
	 * @param $index stream index
	 * @return string
	 */
	function getVideoCodecName($index = 0);
	
	/**
	 * Return the code of the
	 * codec. The code is one of
	 * VCODEC_XXX constant
	 * or AVCODEC_UNKNOWN
	 * 
	 * @param $index
	 * @return int
	 */
	function getVideoCodecType($index = 0);
	
	/**
	 * Return the number of video streams in source
	 * @return int
	 */
	function getVideoStreamsNumber();
	
	/**
	 * Return info abount audio streams
	 * as an associative array
	 * The array has this format:
	 * array(
	 * 	0	=>	array(
	 * 		'codecType'		=> result from getAudioCodecType(0),
	 * 		'codecName'		=> result from getAudioCodecName(0),
	 * 		'HELPER:custom'	=> result from a custom operation of the helper 
	 * 	),
	 * 	...
	 * )
	 * 
	 * @return array
	 */
	function getAudiosInfo();
	
	/**
	 * Return the human-readable name
	 * of the codec
	 * @param $index stream index
	 * @return string
	 */
	function getAudioCodecName($index = 0);
	
	/**
	 * Return the code of the
	 * codec. The code is one of
	 * ACODEC_XXX constant
	 * or AVCODEC_UNKNOWN
	 * 
	 * @param $index
	 * @return int
	 */
	function getAudioCodecType($index = 0);
	
	/**
	 * Return the number of audio streams in source
	 * @return int
	 */
	function getAudioStreamsNumber();
	
	/**
	 * Return info abount video streams
	 * as an associative array
	 * The array has this format:
	 * array(
	 * 	0	=>	array(
	 * 		'format'		=> result from getSubFormat(0),
	 * 		'language'		=> result from getSubLanguage(0),
	 * 		'HELPER:custom'	=> result from a custom operation of the helper 
	 * 	),
	 * 	...
	 * )
	 * 
	 * @return array
	 */
	function getSubsInfo();
	
	/**
	 * Return the number of sub streams in source
	 * @return int
	 */
	function getSubsNumber();
	
	/**
	 * Return the name of sub format
	 * @param $index
	 * @return string
	 */
	function getSubFormat($index = 0);
	
	/**
	 * Return the language of the sub
	 * @param $index
	 * @return string
	 */
	function getSubLanguage($index = 0);
	
}
