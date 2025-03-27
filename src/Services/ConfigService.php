<?php

namespace App\Services;

use App\Models\Config;
use Illuminate\Database\Capsule\Manager as DB;

class ConfigService
{
    private $processService;
    private $ftpService;

    public function __construct(ProcessService $processService, FtpService $ftpService)
    {
        $this->processService = $processService;
        $this->ftpService = $ftpService;
    }

    public function createConfig(array $data)
    {
        DB::transaction(function () use ($data) {
            Config::where('tipo', $data['tipo'])->update(['selected' => false]);
            Config::create($data);
        });

        return Config::latest()->first();
    }

    public function getAllConfigs()
    {
        return Config::all();
    }

    public function getLastConfig($tipo)
    {
        return Config::where('tipo', $tipo)->latest()->first();
    }

    public function updateConfig(array $data)
    {
        $siteData = $this->generateEnv(json_decode($data['site'], true));
        $businessData = $this->generateEnv(json_decode($data['business'], true));
        $allData = array_merge($siteData, $businessData);

        $this->processService->executeBuildProcess($allData);
        $this->ftpService->uploadBuildFiles();
    }

    private function generateEnv($array)
    {
        $env = config('app.agendamento_web');
        $values = [];

        foreach ($array as $item => $v) {
            if (is_array($env[$item])) {
                foreach ($env[$item] as $key => $value) {
                    $values[$value] = $v[$key];
                }
            } else {
                if (!is_array($env[$item]) && isset($env[$item])) {
                    $values[$env[$item]] = $v;
                }
            }
        }

        return $values;
    }
}