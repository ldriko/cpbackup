<?php

//NOTE : UNTUK MENGUBAH LOKASI FILE BACKUP ADA DI FILE cpbackup.php LINE 143, ARAHKAN KE LOKASI GOOGLE DRIVE UNTUK TERSYNC DENGAN GOOGLE DRIVE
//JADWALKAN BACKUP SECARA OTOMATIS MENGGUNAKAN APLIKASI TASK SCHEDULER DARI WINDOWS

return [
	[
		'hostname'           => 'localhost',
		'cpuser'             => 'root',
		'cppasswd'           => 'admin',
		'port'               => 2083,
		'ssl'                => true,
		'proxy'              => false,
		'max_number_of_file' => 15, //total maksimal file databse yang disimpan, yg lama akan terhapus tergantikan 15 file terbaru
		'database'           => [
			'database_a',
		],
	],
	[
		'hostname'           => 'localhost',
		'cpuser'             => 'root',
		'cppasswd'           => 'admin',
		'port'               => 2083,
		'ssl'                => true,
		'proxy'              => false,
		'max_number_of_file' => 15,
		'database'           => [
			'database_a',
			'database_b',
		],
	],
];
