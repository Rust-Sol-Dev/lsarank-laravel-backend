<?php

namespace Database\Seeders;

use App\Models\ProxyData;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProxySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $file = fopen(storage_path("app/Webshare 100 proxies.txt"), "r");

        while(!feof($file)) {
            $line = fgets($file);
            $cleanedLine = str_replace("\r\n","", $line);

            $explodedLine = explode(':', $cleanedLine);

            try {
                ProxyData::create([
                    'ip_address' => $explodedLine[0],
                    'port' => $explodedLine[1],
                    'username' => $explodedLine[2],
                    'password' => $explodedLine[3],
                ]);
            } catch (\Exception $exception) {

            }
        }

        fclose($file);

    }
}
