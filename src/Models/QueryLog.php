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
use \Phramework\Model\Operator;

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
                'user_id'
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
        $query = self::handleGet(
            'SELECT * FROM "query_log"
              {{filter}}
              {{sort}}
              {{pagination}}',
            $page,
            $filter,
            $sort,
            false
        );

        QueryLogAdapter::prepare();

        $records = QueryLogAdapter::executeAndFetchAll($query);

        foreach ($records as &$record) {
            $record['parameters'] = json_decode($record['parameters']);
            $record['additional_parameters'] = json_decode($record['additional_parameters']);
            $record['call_trace'] = json_decode($record['call_trace']);
        }

        return self::collection($records);
    }

    /**
     * Get a single entry by id
     * @param int $id Resource's id
     * @return \stdClass|null
     */
    public static function getById($id)
    {
        QueryLogAdapter::prepare();

        $record = QueryLogAdapter::executeAndFetch(
            'SELECT *
            FROM "query_log"
            WHERE "id" = ?
            LIMIT 1',
            [$id]
        );

        $record['parameters'] = json_decode($record['parameters']);
        $record['additional_parameters'] = json_decode($record['additional_parameters']);
        $record['call_trace'] = json_decode($record['call_trace']);

        return self::resource($record);
    }
}
