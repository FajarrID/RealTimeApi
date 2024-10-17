<?php

namespace Kyouka\RealTimeAPI;

use pocketmine\scheduler\Task;

class UpdateTimeTask extends Task {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        $this->plugin->updateTime();
    }
}
