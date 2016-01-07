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

use \Phramework\JSONAPI\Relationship;
use \Phramework\Models\Operator;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class QueryLog extends \Phramework\JSONAPI\Model
{
    protected static $type = 'query_log';
    protected static $endpoint = 'query_log';
    protected static $table = 'query_log';

    /**
     * @todo add query as CLASS_LIKE
     * @todo add parameters AS json
     * @todo add call_trace AS json
     * @todo add additional_parameters AS json
     */
    public static function getFilterable()
    {
        return [
            'duration' => Operator::CLASS_ORDERABLE,
            'created_timestamp' => Operator::CLASS_ORDERABLE,
            'request_id' => Operator:: CLASS_COMPARABLE,
            'function' => Operator:: CLASS_COMPARABLE,
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
                'created_timestamp',
                'duration',
                'request_id',
                'function',
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
        QueryLogAdapter::prepare();

        $table = static::$table = QueryLogAdapter::getTable();

        $schema = QueryLogAdapter::getSchema();

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

        $records = QueryLogAdapter::executeAndFetchAll($query);

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
        QueryLogAdapter::prepare();

        $table = static::$table = QueryLogAdapter::getTable();

        $schema = QueryLogAdapter::getSchema();

        //Include schema if is set at current QuereLog database adapter
        $schema = (
            $schema
            ? sprintf('"%s".', $schema)
            : ''
        );

        $record = QueryLogAdapter::executeAndFetch(
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
     * Return only ids
     * @param  integer $systemLogId Foreign key
     * @return integer[]
     */
    public static function getRelationshipBySystem_log($systemLogId)
    {
        $systemLogObject = SystemLog::getById($systemLogId, true);

        if (!$systemLogObject) {
            return [];
        }

        $requestId = $systemLogObject->request_id;

        QueryLogAdapter::prepare();

        $table = static::$table = QueryLogAdapter::getTable();

        $schema = QueryLogAdapter::getSchema();

        //Include schema if is set at current QuereLog database adapter
        $schema = (
            $schema
            ? sprintf('"%s".', $schema)
            : ''
        );

        return QueryLogAdapter::executeAndFetchAllArray(
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
     * Helper method, applies directly the required transformations to a database record
     * @param  array $record A database record
     */
    private static function prepareRecord(&$record)
    {
        if (!$record) {
            return;
        }

        $record['parameters'] = json_decode($record['parameters']);
        $record['additional_parameters'] = json_decode($record['additional_parameters']);
        $record['call_trace'] = json_decode($record['call_trace']);
    }

    /**
     * Get resource's relationships
     * @return object Object with Phramework\JSONAPI\Relationship as values
     */
    public static function getRelationships()
    {
        return (object)[
            'system_log' => new Relationship(
                'system_log_id',
                'system_log',
                Relationship::TYPE_TO_MANY,
                SystemLog::class,
                'id'
            ),
        ];
    }
}
