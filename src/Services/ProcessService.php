<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ProcessService
{
    public function executeBuildProcess(array $envVars)
    {
        $process = Process::fromShellCommandline(
            'cd storage/ & git clone https://github.com/erlancarreira/agendamento_web.git & cd agendamento_web/ & npm install --silent & npm run --silent build & xcopy /S /I homologacao ..\homologacao & cd .. & rmdir /s /q agendamento_web',
            null,
            $envVars
        );

        $process->setTimeout(null);
        $process->setIdleTimeout(null);
        $process->start();

        while ($process->isRunning()) {
            if (connection_aborted()) {
                break;
            }

            echo "id: 2\n", "event: ping\n", "data: carregando...\n", "\n\n";
            ob_flush();
            flush();
            sleep(5);
        }

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}