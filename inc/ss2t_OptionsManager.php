<?php
if ( ! defined('ABSPATH') ) { exit; }
/**
 * OptionsManager
**/

class ss2t_OptionsManager {

  public function getOptionName() {
    return get_class( $this );
  }

  public function getOptionMetaData() {
    return array();
  }

  public function getOptionNames() {
    return array_keys($this->getOptionMetaData());
  }

  protected function deleteSavedOptions() {
    $optionMetaData = $this->getOptionMetaData();
    if ( is_array( $optionMetaData ) ) {
      $options = get_option( $this->getOptionName() );
      if ( ! is_array( $options ) )
        $options = array();
      foreach ( $optionMetaData as $aOptionKey => $aOptionMeta ) {
        if ( isset( $options[$aOptionKey] ) ) {
          unset( $options[$aOptionKey] );
        }
      }
      update_option( $this->getOptionName(), $options );
    }
  }

  public function getPluginDisplayName() {
  }

  public function getPluginDescription() {
  }

  public function getPlanDisplayName() {
  }

  public function getOption( $optionName, $default=null ) {
    $options = get_option( $this->getOptionName() );
    if ( ! is_array( $options ) ) {
      $options = array();
    }
    if ( isset( $options[$optionName] ) ) {
      $retVal = $options[$optionName];
    } elseif ( $default ) {
      $retVal = $default;
    } else {
      $retVal = '';
    }
    return $retVal;
  }

  public function deleteOption( $optionName ) {
    $options = get_option( $this->getOptionName() );
    if ( ! is_array( $options ) ) {
      $options = array();
    }
    if ( isset( $options[$optionName] ) ) {
      unset( $options[$optionName] );
      return update_option( $this->getOptionName(), $options );
    } else {
      return true;
    }
  }

  public function addOption( $optionName, $value ) {
    if (strpos($optionName, 'key') !== false) {
      return $this->updateOption( $optionName, base64_encode($value) );
    } else {
      return $this->updateOption( $optionName, $value );
    }
  }

  public function updateOption( $optionName, $value ) {
    $options = get_option( $this->getOptionName() );
    if ( ! is_array( $options ) )
      $options = array();
    if (strpos($optionName, 'key') !== false) {
      $options[$optionName] = base64_encode($value);
    } else {
      $options[$optionName] = $value;
    }
    return update_option( $this->getOptionName(), $options );
  }

  public function canUserDoRoleOption($optionName) {
    $roleAllowed = $this->getRoleOption($optionName);
    if ('Anyone' == $roleAllowed) {
      return true;
    }
    return $this->isUserRoleEqualOrBetterThan($roleAllowed);
  }

  protected function getOptionValueI18nString($optionValue) {
    switch ($optionValue) {
      case 'true':
        return __('true', '1');
      case 'false':
        return __('false', '0');
      case 'on':
        return __('on', '1');
      case 'off':
        return __('off', '0');
      case 'On':
        return __('On', '1');
      case 'Off':
        return __('Off', '0');
    }
    return $optionValue;
  }

  public function registerSettings() {
    $settingsGroup = get_class($this) . '-settings-group';
    $optionMetaData = $this->getOptionMetaData();
    foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
      register_setting($settingsGroup, $aOptionMeta);
    }
  }

  public function settingsPage() {
  }

}
