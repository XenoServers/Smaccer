<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer;

use pocketmine\entity\Entity;
use Xenophilicy\Smaccer\entities\{SlapperBat,
  SlapperBlaze,
  SlapperCaveSpider,
  SlapperChicken,
  SlapperCow,
  SlapperCreeper,
  SlapperDonkey,
  SlapperElderGuardian,
  SlapperEnderman,
  SlapperEndermite,
  SlapperEvoker,
  SlapperGhast,
  SlapperGuardian,
  SlapperHorse,
  SlapperHuman,
  SlapperHusk,
  SlapperIronGolem,
  SlapperLavaSlime,
  SlapperLlama,
  SlapperMule,
  SlapperMushroomCow,
  SlapperOcelot,
  SlapperPig,
  SlapperPigZombie,
  SlapperPolarBear,
  SlapperRabbit,
  SlapperSheep,
  SlapperShulker,
  SlapperSilverfish,
  SlapperSkeleton,
  SlapperSkeletonHorse,
  SlapperSlime,
  SlapperSnowman,
  SlapperSpider,
  SlapperSquid,
  SlapperStray,
  SlapperVex,
  SlapperVillager,
  SlapperVindicator,
  SlapperWitch,
  SlapperWither,
  SlapperWitherSkeleton,
  SlapperWolf,
  SlapperZombie,
  SlapperZombieHorse,
  SlapperZombieVillager};
use Xenophilicy\Smaccer\entities\other\{SlapperBoat, SlapperFallingSand, SlapperMinecart, SlapperPrimedTNT};

/**
 * Class EntityManager
 * @package Xenophilicy\BaseSlapper
 */
class EntityManager {
    const ENTITY_TYPES = ["Chicken", "Pig", "Sheep", "Cow", "MushroomCow", "Wolf", "Enderman", "Spider", "Skeleton", "PigZombie", "Creeper", "Slime", "Silverfish", "Villager", "Zombie", "Human", "Bat", "CaveSpider", "LavaSlime", "Ghast", "Ocelot", "Blaze", "ZombieVillager", "Snowman", "Minecart", "FallingSand", "Boat", "PrimedTNT", "Horse", "Donkey", "Mule", "SkeletonHorse", "ZombieHorse", "Witch", "Rabbit", "Stray", "Husk", "WitherSkeleton", "IronGolem", "Snowman", "LavaSlime", "Squid", "ElderGuardian", "Endermite", "Evoker", "Guardian", "PolarBear", "Shulker", "Vex", "Vindicator", "Wither", "Llama"];
    
    const ENTITY_ALIASES = ["MagmaCube" => "LavaSlime", "ZombiePigman" => "PigZombie", "Mooshroom" => "MushroomCow", "Player" => "Human", "VillagerZombie" => "ZombieVillager", "SnowGolem" => "Snowman", "FallingBlock" => "FallingSand", "FakeBlock" => "FallingSand", "VillagerGolem" => "IronGolem", "EGuardian" => "ElderGuardian", "Emite" => "Endermite"];
    
    public static function init(){
        foreach([SlapperCreeper::class, SlapperBat::class, SlapperSheep::class, SlapperPigZombie::class, SlapperGhast::class, SlapperBlaze::class, SlapperIronGolem::class, SlapperSnowman::class, SlapperOcelot::class, SlapperZombieVillager::class, SlapperHuman::class, SlapperCow::class, SlapperZombie::class, SlapperSquid::class, SlapperVillager::class, SlapperSpider::class, SlapperPig::class, SlapperMushroomCow::class, SlapperWolf::class, SlapperLavaSlime::class, SlapperSilverfish::class, SlapperSkeleton::class, SlapperSlime::class, SlapperChicken::class, SlapperEnderman::class, SlapperCaveSpider::class, SlapperBoat::class, SlapperMinecart::class, SlapperMule::class, SlapperWitch::class, SlapperPrimedTNT::class, SlapperHorse::class, SlapperDonkey::class, SlapperSkeletonHorse::class, SlapperZombieHorse::class, SlapperRabbit::class, SlapperStray::class, SlapperHusk::class, SlapperWitherSkeleton::class, SlapperFallingSand::class, SlapperElderGuardian::class, SlapperEndermite::class, SlapperEvoker::class, SlapperGuardian::class, SlapperLlama::class, SlapperPolarBear::class, SlapperShulker::class, SlapperVex::class, SlapperVindicator::class, SlapperWither::class] as $className){
            Entity::registerEntity($className, true);
        }
    }
}