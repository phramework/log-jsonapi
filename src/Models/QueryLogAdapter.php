<?php
/**
 * Copyright 2015 Xenofon Spafaridis
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Phramework\QueryLogJSONAPI\Models;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class QueryLogAdapter extends \Phramework\Database\Database
{
    /**
     * @var IAdapter
     */
    protected static $adapter = null;

    protected static $schema = null;

    public static function prepare()
    {
        if (static::$adapter !== null) {
            return;
        }

        $dbSettings = \Phramework\Phramework::getSetting(
            'query-log',
            'database'
        );

        if (is_array($dbSettings)) {
            $dbSettings = (object)$dbSettings;
        }

        $adapterNamespace = $dbSettings->adapter;

        $adapter = new $adapterNamespace((array)$dbSettings);

        if (!($adapter instanceof \Phramework\Database\IAdapter)) {
            throw new \Exception(sprintf(
                'Class "%s" is not implementing \Phramework\Database\IAdapter',
                $adapterNamespace
            ));
        }

        if (isset($dbSettings->schema)) {
            self::$schema = $dbSettings->schema;
        }

        static::setAdapter($adapter);
    }

    /**
     * @return IAdapter
     */
    public static function getAdapter()
    {
        return static::$adapter;
    }

    /**
     * @return string
     */
    public static function getSchema()
    {
        return static::$schema;
    }
}
