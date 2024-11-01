<?php
if ( ! defined('ABSPATH') ) { exit; }
/**
 * LifeCycle
**/

require_once( SS2T_PATH_INC . 'ss2t_InstallIndicator.php' );

class ss2t_LifeCycle extends ss2t_InstallIndicator {

  public function install() {
    $this->saveInstalledVersion();
    $this->markAsInstalled();
    $this->setRandomKey();
    $this->setPostTypes();
    $this->setPro();
    $this->setFormHook();
    $this->setDisplay();
    $this->setStyle();
    $this->setFloating();
    $this->setFloatingPos();
  }

  public function uninstall() {
      $this->deleteSavedOptions();
      $this->markAsUnInstalled();
  }

  public function upgrade() {
    $this->saveInstalledVersion();
  }

  public function activate() {  
    $this->saveInstalledVersion(); 
  }

  public function deactivate() {
    $this->saveInstalledVersion(); 
  }

  public function addActionsAndFilters() {
  }

  protected function requireExtraPluginFiles() {
  }

  protected function getSettingsSlug() {
    return get_class($this) . '_Settings';
  }

  public function addSettingsSubMenuPage() {
    $this->addSettingsSubMenuPageNav();
  }

  protected function addSettingsSubMenuPageNav() {
  }

}
