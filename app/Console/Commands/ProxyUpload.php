<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProxyData;

class ProxyUpload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy:upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $file = fopen(storage_path("app/proxy500.txt"), "r");

        while(!feof($file)) {
            $line = fgets($file);
            $cleanedLine = str_replace("\r\n","", $line);

            $explodedLine = explode(':', $cleanedLine);

            $ip = $explodedLine[0];
            $port = $explodedLine[1];
            $username = $explodedLine[2];
            $password = $explodedLine[3];

//            $response = file_get_contents("https://proxycheck.io/v2/$ip?vpn=1&asn=1");

            $result = ProxyData::where('ip_address', $ip)->where('port', $port)->count();

            if ($result) {
                continue;
            }

            $this->info("AAAA");

            try {
                ProxyData::create([
                    'ip_address' => $explodedLine[0],
                    'port' => $explodedLine[1],
                    'username' => $username,
                    'password' => $password,
                ]);
            } catch (\Exception $exception) {

            }
        }

        fclose($file);
    }
}
