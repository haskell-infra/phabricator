<?php

/**
 * Rackspace files storage engine. This engine scales well but is relatively
 * high-latency since data has to be pulled off Cloud Files.
 *
 * @task internal Internals
 */
final class PhabricatorRackspaceFileStorageEngine
  extends PhabricatorFileStorageEngine {


/* -(  Implementation  )----------------------------------------------------- */


  /**
   * This engine identifies as `rackspace-files`.
   */
  public function getEngineIdentifier() {
    return 'rackspace-files';
  }


  /**
   * Writes file data into Rackspace Cloud Files.
   */
  public function writeFile($data, array $params) {
    $container = $this->newCloudFilesAPI();

    // Generate a random name for this file. We add some directories to it
    // (e.g. 'abcdef123456' becomes 'ab/cd/ef123456') to make large numbers of
    // files more browsable with web/debugging tools like the S3 administration
    // tool.
    $seed = Filesystem::readRandomCharacters(20);
    $parts = array(
      substr($seed, 0, 2),
      substr($seed, 2, 2),
      substr($seed, 4),
    );
    $name = 'phabricator/file/'.implode('/', $parts);

    AphrontWriteGuard::willWrite();
    $container->uploadObject($name, $data);

    return $name;
  }


  /**
   * Load a stored blob from Rackspace Cloud Files.
   */
  public function readFile($handle) {
    $obj = $this->newCloudFilesAPI()->getObject($handle);

    $content = $obj->getContent();
    $content->rewind();

    $stream = $content->getStream();
    $body = stream_get_contents($stream);
    fclose($stream);

    return $body;
  }


  /**
   * Delete a blob from Rackspace Cloud Files.
   */
  public function deleteFile($handle) {
    AphrontWriteGuard::willWrite();
    $this->newCloudFilesAPI()->getObject($handle)->delete();
  }


/* -(  Internals  )---------------------------------------------------------- */

  /**
   * Retrieve the region for the Cloud Files container.
   *
   * @task internal
   */
  private function getRegion() {
    $region = PhabricatorEnv::getEnvConfig('storage.rackspace.region');
    if (!$region) {
      throw new PhabricatorFileStorageConfigurationException(
        "No 'storage.rackspace.region' specified!");
    }

    return $region;
  }

  /**
   * Retrieve the API endpoint for the Cloud Files container.
   *
   * @task internal
   */
  private function getEndpoint() {
    $region = $this->getRegion();

    if ($region === 'LON') {
      return "https://lon.identity.api.rackspacecloud.com/v2.0/";
    } else {
      return "https://identity.api.rackspacecloud.com/v2.0/";
    }
  }

  /**
   * Retrieve the Cloud Files container.
   *
   * @task internal
   */
  private function getContainer($rax) {
    $region = $this->getRegion();
    $container = PhabricatorEnv::getEnvConfig('storage.rackspace.container');
    if (!$container) {
      throw new PhabricatorFileStorageConfigurationException(
        "No 'storage.rackspace.container' specified!");
    }

    $obj = $rax->objectStoreService(null, $region)->getContainer($container);
    return $obj;
  }

  /**
   * Create a new Rackspace Cloud Files API object.
   *
   * @task internal
   * @phutil-external-symbol class S3
   */
  private function newCloudFilesAPI() {
    $libroot = dirname(phutil_get_library_root('phabricator'));
    require_once $libroot.'/externals/php-opencloud/vendor/autoload.php';

    $username = PhabricatorEnv::getEnvConfig('rackspace-files.username');
    $api_key  = PhabricatorEnv::getEnvConfig('rackspace-files.api-key');
    $logging  = PhabricatorEnv::getEnvConfig('rackspace-files.logging');

    if (!$username || !$api_key) {
      throw new PhabricatorFileStorageConfigurationException(
        "Specify 'rackspace-files.username' and 'rackspace-files.api-key'!");
    }

    $rax = new \OpenCloud\Rackspace($this->getEndpoint(), array(
      'username' => $username,
      'apiKey'   => $api_key
    ));

    $container = $this->getContainer($rax);

    if ($logging) {
      $container->enableLogging();
    } else {
      $container->disableLogging();
    }

    return $container;
  }

}
