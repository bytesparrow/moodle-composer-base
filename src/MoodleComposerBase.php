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
    self::copyExtra($event, "backup");
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

        if (!self::rcopy($sourcepath, $targetpath)) {
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

  
  
  
 
  
  
  
  
  
  
  
  
  
  /**********
   * HELPERFUNCTIONS
   */
  
  
  
  /**
   * stolen from  promaty at gmail dot com ¶
   * via https://www.php.net/manual/en/function.copy.php
   * fixed to handle symlinks by bytesparrow
   * */
// removes files and non-empty directories
  public static function rrmdir($dir) {
    if (!file_exists($dir)) { //skip if not exists
      return;
    }  
    if (is_link($dir)) { //symlink
      unlink($dir);
    }
    else if (is_dir($dir)) { //folder
      $files = scandir($dir);
      foreach ($files as $file) {
        if ($file != "." && $file != "..") {
          self::rrmdir("$dir/$file");
        }
      }
      rmdir($dir);
    }
    else { //is file
      unlink($dir);
    }
  }

// copies files and non-empty directories
  public static function rcopy($src, $dst) {
    if (file_exists($dst)) { //clean target
      self::rrmdir($dst);
    }
    if (!file_exists($src)) { //skip if not exists
      return;
    }
    if (is_link($src)) { //symlink
      //copy($src, $dst); copy doesn't work!
      return self::copySymlink($src, $dst);
    }
    elseif (is_dir($src)) { //dir
      mkdir($dst, 0755, true);
      $files = scandir($src);
      foreach ($files as $file) {
        if ($file != "." && $file != "..") {
          self::rcopy("$src/$file", "$dst/$file");
        }
      }
    }
    else { //file
      copy($src, $dst);
    }

    //haha.
    return true;
  }
  
  /**
   * Kopiert einen Symlink von $source nach $destination. 
   * Funktioniert mir relativen und absoluten Symlinks (wie ../../target oder /absolute/path/target)
   * generated by ChatGPT
   * @param type $source
   * @param type $destination
   */
  public static function copySymlink($source, $destination) {
        // Ziel des Symlinks ermitteln
        $linkTarget = readlink($source);

        // Überprüfen, ob das Ziel einen ungültigen relativen Pfad enthält (mit führendem '/')
        if (preg_match('/^\/\.\.\//', $linkTarget)) {
            // Entferne den führenden Schrägstrich, um den relativen Pfad zu korrigieren
            $linkTarget = ltrim($linkTarget, '/');
        } elseif (!preg_match('/^\//', $linkTarget)) {
            // Falls das Ziel relativ ist (ohne führenden '/'), den absoluten Pfad ergänzen
            $linkTarget = realpath(dirname($source) . '/' . $linkTarget);
        }

        // Symlink im Zielverzeichnis erstellen
        return symlink($linkTarget, $destination);
        #echo "Symlink '$destination' erfolgreich erstellt.";
}

}
