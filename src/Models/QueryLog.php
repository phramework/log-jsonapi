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
class QueryLog extends \Phramework\JSONAPI\Model
{
    protected static $type = 'query_log';
    protected static $endpoint = 'query_log';
    protected static $table = 'query_log';

    /**
     * Get collection of resources
     * @param Page|null   $page
     * @param Filter|null $filter
     * @param Sort|null   $sort
     * @param Fields|null $fields
     * @param mixed       $additionalParameters
     * @return Resource[]
     */
    public static function get(
        Page $page = null,
        Filter $filter = null,
        Sort $sort = null,
        Fields $fields = null,
        ...$additionalParameters
    ) {

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

        $records = QueryLogAdapter::executeAndFetchAll($query);

        foreach ($records as &$record) {
            static::prepareRecord($record);
        }

        return static::collection($records);
    }

    /**
     */
    public static function getFilterable()
    {
        return (object) [
            'duration' => Operator::CLASS_ORDERABLE,
            'created_timestamp' => Operator::CLASS_ORDERABLE,
            'request_id' => Operator::CLASS_COMPARABLE,
            'function' => Operator::CLASS_COMPARABLE,
            'URI' => Operator::CLASS_COMPARABLE | Operator::CLASS_LIKE,
            'user_id' => Operator::CLASS_COMPARABLE | Operator::CLASS_NULLABLE,
            'method' => Operator::CLASS_COMPARABLE,
            'exception' => Operator::CLASS_JSONOBJECT | Operator::CLASS_NULLABLE,
            'exception_class' => Operator::CLASS_COMPARABLE | Operator::CLASS_LIKE | Operator::CLASS_NULLABLE,
            'call_trace' => Operator::CLASS_JSONOBJECT,
            'additional_parameters' => Operator::CLASS_JSONOBJECT,
            'parameters' => Operator::CLASS_JSONOBJECT | Operator::CLASS_NULLABLE,
            'query' => Operator::CLASS_COMPARABLE | Operator::CLASS_LIKE,
        ];
    }

    /**
     * @return ObjectValidator
     */
    public static function getFilterValidationModel()
    {
        return new ObjectValidator((object) [
            'duration' => new UnsignedIntegerValidator(),
            'created_timestamp' => new UnsignedIntegerValidator(),
            'request_id' => new StringValidator(),
            'function' => new StringValidator(),
            'URI' => new StringValidator(),
            'user_id' => new StringValidator(),
            'method' => new StringValidator(),
            'exception' => new ObjectValidator(),
            'exception_class' => new StringValidator(),
            'call_trace' => new ObjectValidator(),
            'additional_parameters' => new ObjectValidator(),
            'parameters' => new ObjectValidator(),
            'query' => new StringValidator(),
        ]);
    }

    /**
     * @return string[]
     */
    public static function getFields()
    {
        return [
            'request_id',
            'query',
            'parameters',
            'start_timestamp',
            'duration',
            'function',
            'URI',
            'additional_parameters',
            'call_trace',
            'user_id',
            'user_uuid',
            'method',
            'exception',
            'exception_class'
        ];
    }

    /**
     * @return string[]
     */
    public static function getSortable()
    {
        return [
            'id',
            'created_timestamp',
            'start_timestamp',
            'duration',
            'request_id',
            'function',
            'URI',
            'user_id',
            'method'
        ];
    }

    /**
     * @return Sort
     */
    public static function getSort()
    {
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


    /**
     * Get resource's relationships
     * @return object Object with Phramework\JSONAPI\Relationship as values
     */
    public static function getRelationships()
    {
        return (object) [
            'system_log' => new Relationship(
                SystemLog::class,
                Relationship::TYPE_TO_MANY,
                null,
                [SystemLog::class, 'getRelationshipByQueryLog']
            )
        ];
    }

    /**
     * Return only ids
     * @param  string $systemLogId Foreign key
     * @return string[]
     */
    public static function getRelationshipBySystemLog($systemLogId)
    {
        //Access system log object by this id to get the request_id
        $systemLogObject = SystemLog::getById($systemLogId);

        if (!$systemLogObject) {
            return [];
        }

        $requestId = $systemLogObject->attributes->request_id;

        QueryLogAdapter::prepare();

        $table = static::$table = QueryLogAdapter::getTable();

        $schema = QueryLogAdapter::getSchema();

        //Include schema if is set at current QueryLog database adapter
        $schema = (
        $schema
            ? sprintf('"%s".', $schema)
            : ''
        );

        $ids = QueryLogAdapter::executeAndFetchAllArray(
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
     * @return array|record
     */
    private static function prepareRecord(&$record)
    {
        if (!$record) {
            return;
        }

        if (isset($record['parameters'])) {
            $record['parameters'] = json_decode($record['parameters']);
        }

        if (isset($record['additional_parameters'])) {
            $record['additional_parameters'] = json_decode($record['additional_parameters']);
        }

        if (isset($record['call_trace'])) {
            $record['call_trace'] = json_decode($record['call_trace']);
        }
    }
}
