<?php 
include_once 'vtwitter.class.php';

	$properties = array(
				'title' => '',
				'screen_name' => 'campuserasnet',
				'count' =>  5,
				'published_when' => 1, 
	);

	$twitter = new vtwitter( $properties );
	$twitter->vtwitter_printer();

	print_r($twitter_answer);
	
?>