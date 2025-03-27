<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FtpService
{
    public function uploadBuildFiles()
    {
        $host = "w3.sampa.br";
        $conn = ftp_connect($host);

        if (!$conn) {
            throw new \Exception("Failed to connect.");
        }

        ftp_login($conn, "w3", "t3wftpt3w");
        
        $paths = [
            "/agendamento_web/homologacao/",
            "/agendamento_web/homologacao/static/css/",
            "/agendamento_web/homologacao/static/js/",
            "/agendamento_web/homologacao/static/media/"
        ];

        foreach ($paths as $build) {
            $full_path = Storage::disk('public')->path($build);
            $glob = glob($full_path . "*.*");
            $public_path = Storage::disk('public')->path('');
            //$pathname = str_replace($public_path . "agendamento_web", "", $full_path);

            foreach ($glob as $filename) {
                // Use diretórios relativos para FTP, sem o uso de `path()`
                $ftp_directory = str_replace("agendamento_web", "www", $build);
                
                // Crie o diretório remoto, se necessário
                if (!ftp_chdir($conn, $ftp_directory)) {
                    ftp_mkdir($conn, $ftp_directory);
                }

                // Suba o arquivo usando FTP
                ftp_chdir($conn, $ftp_directory);
                ftp_put($conn, basename($filename), $filename, FTP_BINARY);
            }
        }

        ftp_close($conn);
    }
}
