<?php
/**
 *  ____  _            _______ _          _____
 * |  _ \| |          |__   __| |        |  __ \
 * | |_) | | __ _ _______| |  | |__   ___| |  | | _____   __
 * |  _ <| |/ _` |_  / _ \ |  | '_ \ / _ \ |  | |/ _ \ \ / /
 * | |_) | | (_| |/ /  __/ |  | | | |  __/ |__| |  __/\ V /
 * |____/|_|\__,_/___\___|_|  |_| |_|\___|_____/ \___| \_/
 *
 * Copyright (C) 2018 iiFlamiinBlaze
 *
 * iiFlamiinBlaze's plugins are licensed under MIT license!
 * Made by iiFlamiinBlaze for the PocketMine-MP Community!
 *
 * @author iiFlamiinBlaze
 * Twitter: https://twitter.com/iiFlamiinBlaze
 * GitHub: https://github.com/iiFlamiinBlaze
 * Discord: https://discord.gg/znEsFsG
 */
declare(strict_types=1);

namespace iiFlamiinBlaze\BlazinFly;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\TextFormat;

class BlazinFly extends PluginBase implements Listener{

	const PREFIX = TextFormat::AQUA . "BlazinFly" . TextFormat::GOLD . " > ";
	const VERSION = "v1.10.0";

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
	}

	private function multiWorldCheck(Entity $entity) : bool{
		if(!$entity instanceof Player) return false;
		if($this->getConfig()->get("multi-world") === "on"){
			if(!in_array($entity->getWorld()->getDisplayName(), $this->getConfig()->get("worlds"))){
				$entity->sendMessage(self::PREFIX . TextFormat::RED . "This world does not allow flight");
				if(!$entity->isCreative()){
					$entity->setFlying(false);
					$entity->setAllowFlight(false);
				}
				return false;
			}
		}elseif($this->getConfig()->get("multi-world") === "off") return true;
		return true;
	}

	public function onJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		if($this->getConfig()->get("onJoin-FlyReset") === true){
			if($player->isCreative()) return;
			$player->setAllowFlight(false);
			$player->sendMessage($this->getConfig()->get("fly-disabled"));
		}
	}

	public function onLevelChange(EntityTeleportEvent $event) : void{
		$entity = $event->getEntity();
		if($entity instanceof Player) $this->multiWorldCheck($entity);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($command->getName() === "fly"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX . TextFormat::RED . "Use this command in-game");
				return false;
			}
			if(!$sender->hasPermission("blazinfly.command")){
				$sender->sendMessage(self::PREFIX . TextFormat::RED . "You do not have permission to use this command");
				return false;
			}
			if(empty($args[0])){
				if(!$sender->isCreative()){
					if($this->multiWorldCheck($sender) === false) return false;
					$sender->sendMessage($sender->getAllowFlight() === false ? $this->getConfig()->get("fly-enabled") : $this->getConfig()->get("fly-disabled"));
					$sender->setAllowFlight($sender->getAllowFlight() === false ? true : false);
					if($sender->getAllowFlight() === false && $sender->isFlying()) $sender->setFlying(false);
				}else{
					$sender->sendMessage(self::PREFIX . TextFormat::RED . "You can only use this command in survival mode");
					return false;
				}
				return false;
			}
			if(!$sender->hasPermission("blazinfly.other")){
				$sender->sendMessage(self::PREFIX . TextFormat::RED . "You do not have permission to enable flight for others");
				return false;
			}
			if($this->getServer()->getPlayerByPrefix($args[0])){
				$player = $this->getServer()->getPlayerByPrefix($args[0]);
				if(!$player->isCreative()){
					if($this->multiWorldCheck($player) === false) return false;
					$player->sendMessage($player->getAllowFlight() === false ? $this->getConfig()->get("fly-enabled") : $this->getConfig()->get("fly-disabled"));
					$sender->sendMessage($player->getAllowFlight() === false ? self::PREFIX . TextFormat::GREEN . "You have enabled fly for " . $player->getName() : self::PREFIX . TextFormat::RED . "You have disabled fly for " . $player->getName());
					$player->setAllowFlight($player->getAllowFlight() === false ? true : false);
					if($sender->getAllowFlight() === false && $sender->isFlying()) $sender->setFlying(false);
				}else{
					$sender->sendMessage(self::PREFIX . TextFormat::RED . $player->getName() . " is in creative mode");
					return false;
				}
			}else{
				$sender->sendMessage(self::PREFIX . TextFormat::RED . "Player not found");
				return false;
			}
		}
		return true;
	}

	public function onDamage(EntityDamageEvent $event) : void{
		$entity = $event->getEntity();
		if($this->getConfig()->get("onDamage-FlyReset") === true){
			if($event instanceof EntityDamageByEntityEvent){
				if($entity instanceof Player){
					$damager = $event->getDamager();
					if(!$damager instanceof Player) return;
					if($damager->isCreative()) return;
					if($damager->getAllowFlight() === true){
						$damager->sendMessage(self::PREFIX . TextFormat::DARK_RED . "Flight mode disabled due to combat");
						$damager->setAllowFlight(false);
						$damager->setFlying(false);
					}
				}
			}
		}
	}
}