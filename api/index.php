<?php 

	header('Content-Type: application/json;charset=utf-8');
	echo json_encode( array('version' => '1.4132.2' , 'api' => 'br.com.ctracker.api') );
	die;