# cPanel Multihost Database Backup Downloader

How to:

1. Configure the settings
```php
   <?php

    //NOTE : UNTUK MENGUBAH LOKASI FILE BACKUP ADA DI FILE cpbackup.php LINE 143, ARAHKAN KE LOKASI GOOGLE DRIVE UNTUK TERSYNC DENGAN GOOGLE DRIVE
    //JADWALKAN BACKUP SECARA OTOMATIS MENGGUNAKAN APLIKASI TASK SCHEDULER DARI WINDOWS
    
    return [
        'backup_path' => 'C:/Database Backups',
        'hosts' => [
            [
                'override_name' => 'result_folder_name',
                'hostname' => 'hostname',
                'cpuser' => 'cpanel_user',
                'cppasswd' => 'cpanel_password',
                'port' => 2083,
                'ssl' => true,
                'proxy' => false,
                'max_number_of_file' => 15,
                'database' => [
                    'database_name',
                ]
            ]
        ]
    ];
```
2. Just run and take a sip of coffee
```shell
php index.php
```
