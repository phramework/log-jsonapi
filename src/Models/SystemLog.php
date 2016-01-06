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

use \Phramework\JSONAPI\Relationship;
use \Phramework\Models\Operator;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class SystemLog extends \Phramework\JSONAPI\Model
{
    protected static $type = 'system_log';
    protected static $endpoint = 'system_log';
    protected static $table = 'system_log';

    public static function getFilterable()
    {
        return [
            'id' => Operator:: CLASS_COMPARABLE,
            'ip_address' => Operator:: CLASS_COMPARABLE,
            'request_timestamp' => Operator::CLASS_ORDERABLE,
            'request_id' => Operator:: CLASS_COMPARABLE,
            'response_timestamp' => Operator::CLASS_ORDERABLE,
            'response_status_code' => Operator::CLASS_ORDERABLE,
            'flags' => Operator::CLASS_ORDERABLE,
            'URI' => Operator:: CLASS_COMPARABLE,
            'user_id' => Operator:: CLASS_COMPARABLE | Operator::CLASS_NULLABLE,
            'method' => Operator:: CLASS_COMPARABLE,
            'exception' => Operator::CLASS_NULLABLE,
        ];
    }

    public static function getSort()
    {
        return (object)[
            'attributes' => [
                'id',
                'request_timestamp',
                'response_timestamp',
                'response_status_code',
                'URI',
                'user_id',
                'method'
            ],
            'default' => 'id',
            'ascending' => false
        ];
    }

    /**
     * Get all entries
     * @return \stdClass[]
     */
    public static function get($page = null, $filter = null, $sort = null)
    {
        SystemLogAdapter::prepare();

        $table = static::$table = SystemLogAdapter::getTable();

        $schema = SystemLogAdapter::getSchema();

        $schema = (
            $schema
            ? sprintf('"%s".', $schema)
            : ''
        );

        $query = static::handleGet(
            sprintf(
                'SELECT *
                FROM %s"%s"
                  {{filter}}
                  {{sort}}
                  {{pagination}}',
                $schema,
                $table
            ),
            $page,
            $filter,
            $sort,
            false
        );

        $records = SystemLogAdapter::executeAndFetchAll($query);

        foreach ($records as &$record) {
            static::prepareRecord($record);
        }

        return static::collection($records);
    }

    /**
     * Get a single entry by id
     * @param int $id Resource's id
     * @return \stdClass|null
     */
    public static function getById($id, $raw = false)
    {
        SystemLogAdapter::prepare();

        $table = static::$table = SystemLogAdapter::getTable();

        $schema = SystemLogAdapter::getSchema();

        //Include schema if is set at current QuereLog database adapter
        $schema = (
            $schema
            ? sprintf('"%s".', $schema)
            : ''
        );

        $record = SystemLogAdapter::executeAndFetch(
            sprintf(
                'SELECT *
                FROM %s"%s"
                WHERE "id" = ?
                LIMIT 1',
                $schema,
                $table
            ),
            [$id]
        );

        static::prepareRecord($record);

        if ($raw) {
            return (object)$record;
        }

        return static::resource($record);
    }

    /**
     * Helper method, applies directly the required transformations to a database record
     * @param array $record A database record
     */
    private static function prepareRecord(&$record)
    {
        if (!$record) {
            return null;
        }

        $record['request_params'] = json_decode($record['request_params']);
        $record['request_headers'] = json_decode($record['request_headers']);
        $record['additional_parameters'] = json_decode($record['additional_parameters']);
        $record['call_trace'] = json_decode($record['call_trace']);
        $record['response_headers'] = json_decode($record['response_headers']);
        $record['errors'] = json_decode($record['errors']);
    }

    /**
     * Return only ids
     * @param  integer $queryLogId Foreign key
     * @return integer[]
     */
    public static function getRelationshipByQuery_log($queryLogId)
    {
        $queryLogObject = QueryLog::getById($queryLogId, true);

        if (!$queryLogObject) {
            return [];
        }

        $requestId = $queryLogObject->request_id;

        SystemLogAdapter::prepare();

        $table = static::$table = SystemLogAdapter::getTable();

        $schema = SystemLogAdapter::getSchema();

        //Include schema if is set
        $schema = (
            $schema
            ? sprintf('"%s".', $schema)
            : ''
        );

        return SystemLogAdapter::executeAndFetchAllArray(
            sprintf(
                'SELECT "id"
                FROM %s"%s"
                WHERE "request_id" = ?',
                $schema,
                $table
            ),
            [$requestId]
        );
    }

    /**
     * Get resource's relationships
     * @return object Object with Phramework\JSONAPI\Relationship as values
     */
    public static function getRelationships()
    {
        return (object)[
            'query_log' => new Relationship(
                'query_log_id',
                'query_log',
                Relationship::TYPE_TO_MANY,
                QueryLog::class,
                'id'
            ),
        ];
    }
}
