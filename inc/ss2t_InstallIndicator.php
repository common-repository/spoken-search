<?php
if ( ! defined('ABSPATH') ) { exit; }
/**
 * InstallIndicator
**/

require_once( SS2T_PATH_INC . 'ss2t_OptionsManager.php' );

class ss2t_InstallIndicator extends ss2t_OptionsManager {

  const optionInstalled = '_installed';
  const optionVersion = '_version';
  const optionKey = '_key';
  const optionPtArr = 'ptmultiple';
  const optionPro = 'pro';
  const optionFormHook = 'formhook';
  const optionDisplay = 'display';
  const optionStyle = 'style';
  const optionFloating = 'floating';
  const optionFloatingPos = 'floatingpos';

  /**
   * @return bool
   */
  public function isInstalled() {
    return $this->getOption(self::optionInstalled) == true;
  }

  /**
   * @return null
   */
  protected function markAsInstalled() {
    return $this->updateOption(self::optionInstalled, true);
  }

  /**
   * @return bool
   */
  protected function markAsUnInstalled() {
    return $this->deleteOption(self::optionInstalled);
  }

  /**
   * @return string
   */
  protected function setVersionSaved($version) {
    return $this->updateOption(self::optionVersion, $version);
  }

  /**
   * @return null
   */
  protected function getVersionSaved() {
    return $this->getOption(self::optionVersion);
  }

  /**
   * @return null
   */
  protected function generateRandomString($length=18) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
  }

  protected function setRandomKey() {
    return $this->updateOption(self::optionKey, $this->generateRandomString());
  }

  protected function setPostTypes() {
    return $this->updateOption(self::optionPtArr, array( 'any' ));
  }

  protected function setPro() {
    return $this->updateOption(self::optionPro, false);
  }

  protected function setFormHook() {
    return $this->updateOption(self::optionFormHook, false);
  }

  protected function setDisplay() {
    return $this->updateOption(self::optionDisplay, 'Default');
  }

  protected function setStyle() {
    return $this->updateOption(self::optionStyle, 'Dark');
  }

  protected function setFloating() {
    return $this->updateOption(self::optionFloating, 'Off');
  }

  protected function setFloatingPos() {
    return $this->updateOption(self::optionFloatingPos, 'Top');
  }

  /**
   * @return string
   */
  protected function getMainPluginFileName() {
    return basename(dirname(__FILE__)) . 'php';
  }

  /**
   * @return string
   */
  protected function getPluginDir() {
    return dirname(__FILE__);
  }

  /**
   * @return null
   */
  public function getPluginHeaderValue($key) {
    $data = file_get_contents($this->getPluginDir() . DIRECTORY_SEPARATOR . $this->getMainPluginFileName());
    $match = array();
    preg_match('/' . $key . ':\s*(\S+)/', $data, $match);
    if (count($match) >= 1) {
      return $match[1];
    }
    return null;
  }

  /**
   * @return string
   */
  public function getVersion() {
    return $this->getPluginHeaderValue('Version');
  }


  /**
   * @return bool
   */
  public function isInstalledCodeAnUpgrade() {
    return $this->isSavedVersionLessThan($this->getVersion());
  }

  /**
   * @return bool
   */
  public function isSavedVersionLessThan($aVersion) {
    return $this->isVersionLessThan($this->getVersionSaved(), $aVersion);
  }

  /**
   * @return bool
   */
  public function isSavedVersionLessThanEqual($aVersion) {
    return $this->isVersionLessThanEqual($this->getVersionSaved(), $aVersion);
  }

  /**
   * @return bool
   */
  public function isVersionLessThanEqual($version1, $version2) {
    return (version_compare($version1, $version2) <= 0);
  }

  /**
   * @return bool
   */
  public function isVersionLessThan($version1, $version2) {
    return (version_compare($version1, $version2) < 0);
  }

  /**
   * @return string
   */
  protected function saveInstalledVersion() {
    $this->setVersionSaved($this->getVersion());
  }

}
