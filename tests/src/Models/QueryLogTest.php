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

use \Phramework\Phramework;

/**
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
* @author Xenofon Spafaridis <nohponex@gmail.com>
 */
class QueryLogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var phramework
     */
    private $phramework;

    /**
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //Prepare phramework instance
        $this->phramework = \Phramework\QueryLogJSONAPI\APP\Bootstrap::prepare();
    }

    /**
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @covers Phramework\QueryLogJSONAPI\Models\QueryLog::get
     */
    public function testGet()
    {
        $data = QueryLog::get();

        $this->assertInternalType('array', $data);

        $this->assertInternalType('object', $data[0]);

        $this->assertObjectHasAttribute('id', $data[0]);
        $this->assertObjectHasAttribute('type', $data[0]);
        $this->assertObjectHasAttribute('attributes', $data[0]);

        return $data[0]->id;
    }

    /**
     * @depends testGet
     * @covers Phramework\QueryLogJSONAPI\Models\QueryLog::getById
     */
    public function testGetById($id)
    {
        $data = QueryLog::getById($id);

        var_dump($data);

        $this->assertInternalType('object', $data);
        $this->assertObjectHasAttribute('id', $data);
        $this->assertSame($id, $data->id);
    }
}
