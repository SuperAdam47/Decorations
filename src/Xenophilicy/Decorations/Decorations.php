<?php
# MADE BY:
#  __    __                                          __        __  __  __
# /  |  /  |                                        /  |      /  |/  |/  |
# $$ |  $$ |  ______   _______    ______    ______  $$ |____  $$/ $$ |$$/   _______  __    __
# $$  \/$$/  /      \ /       \  /      \  /      \ $$      \ /  |$$ |/  | /       |/  |  /  |
#  $$  $$<  /$$$$$$  |$$$$$$$  |/$$$$$$  |/$$$$$$  |$$$$$$$  |$$ |$$ |$$ |/$$$$$$$/ $$ |  $$ |
#   $$$$  \ $$    $$ |$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |  $$ |$$ |$$ |$$ |$$ |      $$ |  $$ |
#  $$ /$$  |$$$$$$$$/ $$ |  $$ |$$ \__$$ |$$ |__$$ |$$ |  $$ |$$ |$$ |$$ |$$ \_____ $$ \__$$ |
# $$ |  $$ |$$       |$$ |  $$ |$$    $$/ $$    $$/ $$ |  $$ |$$ |$$ |$$ |$$       |$$    $$ |
# $$/   $$/  $$$$$$$/ $$/   $$/  $$$$$$/  $$$$$$$/  $$/   $$/ $$/ $$/ $$/  $$$$$$$/  $$$$$$$ |
#                                         $$ |                                      /  \__$$ |
#                                         $$ |                                      $$    $$/
#                                         $$/                                        $$$$$$/

namespace Xenophilicy\Decorations;

use onebone\economyapi\EconomyAPI;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use Xenophilicy\Decorations\commands\DecorationCommand;
use Xenophilicy\Decorations\decoration\DecorationManager;
use Xenophilicy\Decorations\entity\DecorationEntity;

/**
 * Class Decorations
 * @package Xenophilicy\Decorations
 */
class Decorations extends PluginBase {
    
    const CONFIG_VERSION = "1.0.0";
    
    /** @var array */
    public static $settings;
    /** @var Decorations */
    private static $instance;
    /** @var EconomyAPI */
    private $economy;
    /** @var DecorationManager */
    private $decorationManager;
    
    /**
     * @param string $setting
     * @return int|string|bool
     */
    public static function getSetting(string $setting){
        return self::$settings[$setting] ?? false;
    }
    
    public static function getInstance(): self{
        return self::$instance;
    }
    
    public function getEconomy(): EconomyAPI{
        return $this->economy;
    }
    
    public function getDecorationManager(): DecorationManager{
        return $this->decorationManager;
    }
    
    public function onEnable(){
        $this->saveDefaultConfig();
        self::$instance = $this;
        self::$settings = $this->getConfig()->getAll();
        if(version_compare(self::CONFIG_VERSION, self::$settings["VERSION"], "gt")){
            $this->getLogger()->critical("You've updated Decorations but have an outdated config verion, delete your old config to prevent unwanted errors");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        $this->saveResource("decorations.json");
        $path = $this->getDecorationDirectory(false);
        if(!is_dir($path)){
            mkdir($path);
            $path = $this->getDecorationDirectory(true);
            foreach(["mug", "chair", "television"] as $model){
                $this->saveResource($path . "$model.geo.json");
                $this->saveResource($path . "$model.png");
            }
        }
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getInstance()->getCommandMap()->register("Decorations", new DecorationCommand());
        Entity::registerEntity(DecorationEntity::class);
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        $this->decorationManager = new DecorationManager();
    }
    
    public function getDecorationDirectory(bool $internal): string{
        return ($internal ? "decorations" . DIRECTORY_SEPARATOR : $this->getDataFolder() . "decorations" . DIRECTORY_SEPARATOR);
    }
}