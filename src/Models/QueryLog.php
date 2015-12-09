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

use \Phramework\Database\Database;
use \Phramework\JSONAPI\Relationship;

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
     * Get all entries
     * @return \stdClass[]
     */
    public static function get()
    {
        $records = Database::executeAndFetchAll(
            'SELECT * FROM "query_log"'
        );

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
        $record = Database::executeAndFetch(
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
