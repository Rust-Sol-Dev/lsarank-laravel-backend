<?php

namespace App\Services;

use App\Exceptions\ProcessFailedConnectionException;
use App\Exceptions\ProxyFailedException;
use App\Exceptions\UnsuccessfulResponse;
use App\Models\ProxyData;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AxiosCrawler
{
    /**
     * Scrape using AxiosCrawler
     *
     * @param string $url
     * @param string $proxyIp
     * @param string $proxyPort
     * @param string $username
     * @param string $password
     * @return string
     * @throws ProcessFailedConnectionException
     * @throws ProxyFailedException
     * @throws UnsuccessfulResponse
     */
    public function crawl(string $url, string $proxyIp, string $proxyPort, string $username, string $password)
    {
        $url = $url . "&hl=en&gl=us";

        $scriptPath = base_path('scripts/axiosCrawler.js');

        $command = "/home/andrija/node/bin/node '$scriptPath' '$url' $proxyIp $proxyPort $username $password";

        $process = Process::fromShellCommandline($command)->setTimeout(400);

        $process->run();

        if ($process->isSuccessful()) {
            return rtrim($process->getOutput());
        }

        $exitCode = $process->getExitCode();

        if ($exitCode === 3) {
            throw new UnsuccessfulResponse($process->getErrorOutput());
        }

        $errorOutput = $process->getErrorOutput();

        if (str_contains($errorOutput, 'Your client does not have permission to get URL') || str_contains($errorOutput, 'Sometimes you may be asked to solve the CAPTCHA')) {
            throw new ProxyFailedException($errorOutput);
        }

        if (str_contains($errorOutput, 'ETIMEDOUT') || str_contains($errorOutput, 'ECONNREFUSED') || str_contains($errorOutput, 'ECONNRESET') || str_contains($errorOutput, 'ECONNABORTED') || str_contains($errorOutput, 'Proxy timeout') || str_contains($errorOutput, 'Bad response: 502')) {
            activity()->event('PROXY')->log("$proxyIp:$proxyPort");
            throw new ProcessFailedConnectionException($errorOutput);
        }

        throw new ProcessFailedException($process);
    }
}
