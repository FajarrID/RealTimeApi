<?php
namespace Kyouka\RealTimeAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\utils\Config;
class Main extends PluginBase {
    private $timezone;
    public function onEnable(): void {
        $this->saveResource("config.yml");
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->timezone = $config->get("timezone", "Asia/Makassar");
        $this->getScheduler()->scheduleRepeatingTask(new UpdateTimeTask($this), 1200);
    }
    public function updateTime(): void {
        $worldManager = $this->getServer()->getWorldManager();
        foreach ($worldManager->getWorlds() as $world) {
            if (!$this->isDisableWorld($world)) {
                $timeData = $this->getRealTimeFromAPI($this->timezone);

                if ($timeData !== null) {
                    $realTime = $timeData['datetime'];
                    $formattedTime = strtotime($realTime) % 86400; 
                    $world->setTime(($formattedTime / 86400) * World::TIME_FULL); 
                }
            }
        }
    }

    private function getRealTimeFromAPI(string $timezone): ?array {
        $url = "https://worldtimeapi.org/api/timezone/{$timezone}";
        $json = @file_get_contents($url);
        
        if ($json === false) {
            $this->getLogger()->error("Gagal mengambil data dari API.");
            return null;
        }

        return json_decode($json, true);
    }

    private function isDisableWorld(World $world): bool {
        $worldname = $world->getFolderName();
        $disableWorld = $this->getConfig()->get("disableWorld");

        if ($disableWorld === null) {
            return false;
        }

        foreach ($disableWorld as $entry) {
            if (strpos($worldname, $entry) !== false) {
                return true;
            }
        }

        return false;
    }
}
