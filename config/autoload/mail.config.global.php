<?php
return array(
	'mail' => array(
		'transport' => array(
			'options' => array(
				'host'              => 'smtp.gmail.com',
				'connection_class'  => 'plain',
				'connection_config' => array(
					'username' => '<email-id>',
					'password' => '<password>',
					'ssl' => 'tls'
				),
			),  
		),
	),
);