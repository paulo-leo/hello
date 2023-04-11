<?php

return array(
   'mysql'=>array(
      'host'=>env('DB_HOST'),
      'database'=>env('DB_DATABASE'),
	    'username'=>env('DB_USERNAME'),
      'password'=>env('DB_PASSWORD'),
	    'driver'=>env('DB_DRIVER')
   ),
   'wordpress'=>array(
      'host'=>env('DB_HOST'),
      'database'=>'wordpress',
      'username'=>env('DB_USERNAME'),
      'password'=>env('DB_PASSWORD'),
      'driver'=>env('DB_DRIVER')
    )
);