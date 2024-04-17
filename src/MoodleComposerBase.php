<?php

namespace bytesparrow;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Installer\PackageEvent;
use Composer\Installers\MoodleInstaller;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use Composer\Util\Filesystem;
use Composer\Plugin\PluginInterface;
use Composer\Composer;
use Composer\IO\IOInterface;

require_once(dirname(__FILE__) . "/helperfunctions.php");

/**
 * Provides static functions for composer script events.
 *
 * @see https://getcomposer.org/doc/articles/scripts.md
 */
class MoodleComposerBase implements PluginInterface {

  /**
   * Handles the pre-install event.
   *
   * @param Event $event The Composer event object.
   */
  public static function preInstall(Event $event) {
    $io = $event->getIO();
    $io->write("------------ preInstall ------------");

    $installerdir = self::getInstallerDir($event);
    #$io->write("Installdir is: $installerdir ------------");
    $extra = $event->getComposer()->getPackage()->getExtra();
    #$io->write("extra -> keepfiles IS:");
    #$io->write(var_export($extra['keepfiles'],1));
  }

  /**
   * Handles the pre-update event.
   *
   * @param Event $event The Composer event object.
   */
  public static function preUpdate(Event $event) {
    $io = $event->getIO();
    $io->write("------------ preUpdate ------------");

    self::copyExtra($event, "backup");
  }

  /**
   * Handles the post-install event.
   *
   * @param Event $event The Composer event object.
   */
  public static function postInstall(Event $event) {
    $io = $event->getIO();
    $io->write("------------ postInstall ------------");

    self::copyExtra($event, "moodle");
  }

  /**
   * Handles the post-update event.
   *
   * @param Event $event The Composer event object.
   */
  public static function postUpdate(Event $event) {
    $io = $event->getIO();
    $io->write("------------ postUpdate ------------");
    self::copyExtra($event, "moodle");
  }

  /**
   * copyExtra
   *
   * Copies files and folders given in composers "extra"["keepfiles"]:array definition from the installation directory to the backup directory.
   *
   * @param \Composer\Script\Event $event The Composer event.
   * @param string $direction backup|moodle copy extra files/folders TO backup OR back TO moodle-dir   
   */
  public static function copyExtra(Event $event, $direction = "backup") {
    $io = $event->getIO();
    $appDir = getcwd();

    $installerdir = self::getInstallerDir($event);

    $extra = $event->getComposer()->getPackage()->getExtra();
    $keepfiles = $extra['keepfiles'];



    $backupdir = $appDir . "/backup";
    //todo make helperfunction
    if (!is_dir($backupdir)) {
      mkdir($backupdir);
    }

    foreach ($keepfiles as $extrafile) {
      if ($direction == "backup") {
        $sourcepath = $installerdir . "/" . $extrafile;
        $targetpath = $backupdir . "/" . $extrafile;
      }
      elseif ($direction == "moodle") {
        $sourcepath = $backupdir . "/" . $extrafile;
        $targetpath = $installerdir . "/" . $extrafile;
      }
      else {
        $io->error("Direction $direction is not valid!!");
      }

      if (file_exists($sourcepath)) {
        $io->write("Copying $sourcepath to $targetpath");
        if (!rcopy($sourcepath, $targetpath)) {
          $io->error("FAILURE copying $sourcepath");
        }
      }
      else {
        $io->write("File $sourcepath not found!");
      }
    }
  }

  /**
   * Retrieves the installer directory from composer.json's extra configuration.
   *
   * @param Event|PackageEvent $event The Composer event object.
   * @return string The installer directory.
   */
  public static function getInstallerDir($event) {
    return "vendor/moodle/moodle";
    $extra = $event->getComposer()->getPackage()->getExtra();
    return $extra['installerdir'] ?? self::INSTALLER_DIR;
  }

  /**
   * Apply plugin modifications to Composer
   *
   * @return void
   */
  public function activate(Composer $composer, IOInterface $io) {
    //$io->write("Thanks for being my friend!!");
    //when installed, you can print something here.
  }

  /**
   * Remove any hooks from Composer
   *
   * This will be called when a plugin is deactivated before being
   * uninstalled, but also before it gets upgraded to a new version
   * so the old one can be deactivated and the new one activated.
   *
   * @return void
   */
  public function deactivate(Composer $composer, IOInterface $io) {
    //$io->write("Why u disable me?!!");
  }

  /**
   * Prepare the plugin to be uninstalled
   *
   * This will be called after deactivate.
   *
   * @return void
   */
  public function uninstall(Composer $composer, IOInterface $io) {
    $io->write("You've successfully uninstalled bytesparrow/moodle-composer-base.");
  }

}
