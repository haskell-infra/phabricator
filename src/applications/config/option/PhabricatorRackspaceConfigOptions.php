<?php

final class PhabricatorRackspaceConfigOptions
  extends PhabricatorApplicationConfigOptions {

  public function getName() {
    return pht("Rackspace Cloud");
  }

  public function getDescription() {
    return pht("Configure integration with Rackspace (servers, files, etc).");
  }

  public function getOptions() {
    return array(
      $this->newOption('rackspace-files.username', 'string', null)
        ->setLocked(true)
        ->setDescription(pht('Rackspace username.')),
      $this->newOption('rackspace-files.api-key', 'string', null)
        ->setMasked(true)
        ->setDescription(pht('API key for Rackspace.')),
      $this->newOption('rackspace-files.logging', 'bool', false)
        ->setDescription(pht(
          'Set this to true to enable access logs for all data that the file '.
          'objects acrue.')),
    );
  }

}
