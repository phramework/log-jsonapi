<?php
/**
 * Copyright 2015-2016 Xenofon Spafaridis
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

use Phramework\Database\Database;
use Phramework\JSONAPI\Fields;
use Phramework\JSONAPI\Filter;
use Phramework\JSONAPI\Page;
use Phramework\JSONAPI\Relationship;
use Phramework\JSONAPI\Sort;
use Phramework\Models\Operator;
use Phramework\Validate\ObjectValidator;
use Phramework\Validate\StringValidator;
use Phramework\Validate\UnsignedIntegerValidator;

/**
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 * @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class SystemLog extends \Phramework\JSONAPI\Model
{
    protected static $type = 'system_log';
    protected static $endpoint = 'system_log';
    protected static $table = 'system_log';



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
        //if ($sort && isset($sort->table)) {
        //    $sort->table = $table;
        //}

        $query = static::handleGet(
            sprintf(
                'SELECT {{fields}}
                FROM %s"%s"
                  {{filter}}
                  {{sort}}
                  {{page}}',
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
     * @return object
     */
    public static function getFilterable()
    {
        return (object) [
            'id' => Operator:: CLASS_COMPARABLE,
            'ip_address' => Operator:: CLASS_COMPARABLE,
            'request_timestamp' => Operator::CLASS_ORDERABLE,
            'request_id' => Operator:: CLASS_COMPARABLE,
            'response_timestamp' => Operator::CLASS_ORDERABLE,
            'response_status_code' => Operator::CLASS_ORDERABLE,
            'flags' => Operator::CLASS_ORDERABLE,
            'URI' => Operator:: CLASS_COMPARABLE | Operator::CLASS_LIKE,
            'user_id' => Operator:: CLASS_COMPARABLE | Operator::CLASS_NULLABLE,
            'method' => Operator:: CLASS_COMPARABLE,
            'exception_class' => Operator:: CLASS_COMPARABLE | Operator::CLASS_NULLABLE,
            'exception' => Operator::CLASS_JSONOBJECT | Operator::CLASS_NULLABLE,
            'errors' => Operator::CLASS_JSONOBJECT | Operator::CLASS_NULLABLE,
            'request_params' => Operator::CLASS_JSONOBJECT | Operator::CLASS_NULLABLE,
            'request_headers' => Operator::CLASS_JSONOBJECT | Operator::CLASS_NULLABLE,
            'response_headers' => Operator::CLASS_JSONOBJECT | Operator::CLASS_NULLABLE,
            'response_body' => Operator::CLASS_JSONOBJECT | Operator::CLASS_NULLABLE,
            'additional_parameters' => Operator::CLASS_JSONOBJECT | Operator::CLASS_NULLABLE,
            'call_trace' => Operator::CLASS_JSONOBJECT | Operator::CLASS_NULLABLE,
        ];
    }

    /**
     * @return ObjectValidator
     */
    public static function getFilterValidationModel()
    {
        return new ObjectValidator((object) [
            'id' => new UnsignedIntegerValidator(),
            'ip_address' => new StringValidator(),
            'request_timestamp' => new UnsignedIntegerValidator(),
            'request_id' => new StringValidator(),
            'response_timestamp' => new UnsignedIntegerValidator(),
            'response_status_code' => new UnsignedIntegerValidator(),
            'flags' => new UnsignedIntegerValidator(),
            'URI' => new StringValidator(),
            'user_id' => new StringValidator(),
            'method' => new StringValidator(),
            'exception_class' => new StringValidator(),
            'exception' => new ObjectValidator(),
            'errors' => new ObjectValidator(),
            'request_params' => new ObjectValidator(),
            'request_headers' => new ObjectValidator(),
            'response_body' => new ObjectValidator(),
            'response_body' => new ObjectValidator(),
            'additional_parameters' => new ObjectValidator(),
            'call_trace' => new ObjectValidator()
        ]);
    }

    /**
     * @return string[]
     */
    public static function getFields()
    {
         return [
            'id',
            'request_timestamp',
            'response_timestamp',
            'response_status_code',
            'URI',
            'user_id',
            'method',
            'request_headers',
            'request_params',
            'response_headers',
            'response_body',
            'response_status_code',
            'flags',
            'additional_parameters',
            'errors',
            'exception',
            'created',
            'request_id',
            'request_body_raw',
            'exception_class',
            'call_trace',
            'ip_address'
        ];
    }

    /**
     * @return string[]
     */
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
        SystemLogAdapter::prepare();
        $table = static::$table = SystemLogAdapter::getTable();

        return new Sort(static::getTable(), 'id', false);
    }

    /**
     * Default is with page limit of 50
     * @return Page
     */
    public static function getDefaultPage()
    {
        return new Page(50);
    }

    /**
     * Default is 5000
     * @return int
     */
    public static function getMaxPageLimit()
    {
        return 5000;
    }

    public static function getDefaultFields()
    {
        return new Fields((object) [
            static::getType() => ['*']
        ]);
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

    /**
     * Return only ids
     * @param  string $queryLogId Foreign key
     * @return string[]
     */
    public static function getRelationshipByQueryLog($queryLogId)
    {
        //Access QueryLog object by this id to get the request_id
        $queryLogObject = QueryLog::getById($queryLogId);

        if (!$queryLogObject) {
            return [];
        }

        $requestId = $queryLogObject->attributes->request_id;

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
     * Helper method, applies directly the required transformations to a database record
     * @param array $record A database record
     * @return array|null
     */
    private static function prepareRecord(&$record)
    {
        if (!$record) {
            return null;
        }

        if (isset($record['request_params'])) {
            $record['request_params'] = json_decode($record['request_params']);
        }

        if (isset($record['request_headers'])) {
            $record['request_headers'] = json_decode($record['request_headers']);
        }

        if (isset($record['additional_parameters'])) {
            $record['additional_parameters'] = json_decode($record['additional_parameters']);
        }

        if (isset($record['call_trace'])) {
            $record['call_trace'] = json_decode($record['call_trace']);
        }

        if (isset($record['response_headers'])) {
            $record['response_headers'] = json_decode($record['response_headers']);
        }

        if (isset($record['errors'])) {
            $record['errors'] = json_decode($record['errors']);
        }
    }
}
