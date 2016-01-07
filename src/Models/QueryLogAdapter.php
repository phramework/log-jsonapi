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

namespace Phramework\LogJSONAPI\Models;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class QueryLogAdapter extends \Phramework\Database\Database
{
    /**
     * @var Phramework\Database\IAdapter
     */
    protected static $adapter = null;

    /**
     * @var string|null
     */
    protected static $schema = null;

    /**
     * @var string
     */
    protected static $table  = 'query_log';

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
                'Class "%s" is not implementing Phramework\Database\IAdapter',
                $adapterNamespace
            ));
        }

        if (isset($dbSettings->schema)) {
            static::$schema = $dbSettings->schema;
        }

        static::$table = (
            isset($dbSettings->table)
            ? $dbSettings->table
            : 'query_log'
        );

        static::setAdapter($adapter);
    }

    /**
     * @return Phramework\Database\IAdapter
     */
    public static function getAdapter()
    {
        return static::$adapter;
    }

    /**
     * @return string|null
     */
    public static function getSchema()
    {
        return static::$schema;
    }

    /**
     * @return string
     */
    public static function getTable()
    {
        return static::$table;
    }
}
