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

    public static function getSortable()
    {
        return [
            'id',
            'request_timestamp',
            'response_timestamp',
            'response_status_code',
            'URI',
            'user_id',
            'method'
        ];
    }

    public static function getSort()
    {
        return new Sort(static::getTable(), 'id', false);
    }

    /**
     * Get collection of resources
     * @param Page|null   $page
     * @param Filter|null $filter
     * @param Sort|null   $sort
     * @param Fields|null $fields
     * @param mixed       $additionalParameters Id of user who made the request, `$userId` is required
     * @return Resource[]
     */
    public static function get(
        Page $page = null,
        Filter $filter = null,
        Sort $sort = null,
        Fields $fields = null,
        ...$additionalParameters
    ) {
        SystemLogAdapter::prepare();

        $table = static::$table = SystemLogAdapter::getTable();

        $schema = SystemLogAdapter::getSchema();

        $schema = (
            $schema
            ? sprintf('"%s".', $schema)
            : ''
        );

        //Hack, problem when default table is changed the the configuration
        if ($sort && isset($sort->table)) {
            $sort->table = $table;
        }

        $query = static::handleGet(
            sprintf(
                'SELECT {{fields}}
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
            $fields,
            false
        );

        $records = SystemLogAdapter::executeAndFetchAll($query);

        foreach ($records as &$record) {
            static::prepareRecord($record);
        }

        return static::collection($records);
    }

    /**
     * Helper method, applies directly the required transformations to a database record
     * @param array $record A database record
     * @return null
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
    public static function getRelationshipByQueryLog($queryLogId)
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

        $ids = SystemLogAdapter::executeAndFetchAllArray(
            sprintf(
                'SELECT "id"
                FROM %s"%s"
                WHERE "request_id" = ?',
                $schema,
                $table
            ),
            [$requestId]
        );

        return array_map('strval', $ids);
    }

    /**
     * Get resource's relationships
     * @return object Object with Phramework\JSONAPI\Relationship as values
     */
    public static function getRelationships()
    {
        return (object)[
            'query_log' => new Relationship(
                QueryLog::class,
                Relationship::TYPE_TO_MANY,
                null,
                [QueryLog::class, 'getRelationshipBySystemLog']
            )
        ];
    }
}
