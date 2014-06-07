<?php
/**
 * Copyright 2012-2014 Rackspace US, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenCloud\Compute\Resource;

use OpenCloud\Common\PersistentObject;

/**
 * A resource configuration for a server. Each flavor is a unique combination
 * of disk, memory, vCPUs, and network bandwidth.
 */
class Flavor extends PersistentObject
{

    public $status;
    public $updated;
    public $vcpus;
    public $disk;
    public $name;
    public $links;
    public $rxtx_factor;
    public $ram;
    public $id;
    public $swap;

    protected static $json_name = 'flavor';
    protected static $url_resource = 'flavors';

    public function create($params = array())
    {
        return $this->noCreate();
    }

    public function update($params = array())
    {
        return $this->noUpdate();
    }

    public function delete()
    {
        return $this->noDelete();
    }
}
