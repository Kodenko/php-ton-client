<?php

declare(strict_types=1);

namespace Extraton\Tests\Integration\TonClient;

use Extraton\TonClient\Abi;
use Extraton\TonClient\Entity\Abi\AbiType;
use Extraton\TonClient\Entity\Net\Aggregation;
use Extraton\TonClient\Entity\Net\Filters;
use Extraton\TonClient\Entity\Net\MessageNode;
use Extraton\TonClient\Entity\Net\OrderBy;
use Extraton\TonClient\Entity\Net\ParamsOfAggregateCollection;
use Extraton\TonClient\Entity\Net\ParamsOfBatchQuery;
use Extraton\TonClient\Entity\Net\ParamsOfQueryCollection;
use Extraton\TonClient\Entity\Net\ParamsOfSubscribeCollection;
use Extraton\TonClient\Entity\Net\ParamsOfWaitForCollection;
use Extraton\TonClient\Entity\Net\ResultOfQueryCollection;
use Extraton\TonClient\Entity\Net\ResultOfQueryCounterparties;
use Extraton\TonClient\Entity\Net\ResultOfQueryTransactionTree;
use Extraton\TonClient\Entity\Net\ResultOfWaitForCollection;
use Extraton\TonClient\Entity\Net\TransactionNode;
use Extraton\TonClient\Handler\Response;

use function dechex;
use function hexdec;

/**
 * Integration tests for Net module
 *
 * @coversDefaultClass \Extraton\TonClient\Net
 */
class NetTest extends AbstractModuleTest
{
    /**
     * @covers ::queryCollection
     */
    public function testQueryCollection(): void
    {
        $query = new ParamsOfQueryCollection(
            'accounts',
            [
                'acc_type',
                'acc_type_name',
                'balance',
                'boc',
                'code',
                'code_hash',
                'data',
                'data_hash',
                'due_payment',
                'id',
                'last_paid',
                'last_trans_lt',
                'library',
                'library_hash',
                'proof',
                'split_depth',
                'state_hash',
                'tick',
                'tock',
                'workchain_id',
            ],
            (new Filters())->add(
                'last_paid',
                Filters::IN,
                [
                    1626994845,
                    1626994874,
                    1626994901,
                    1626994923,
                    1626995009,
                    1626995043,
                    1626995129,
                ]
            ),
            (new OrderBy())->add(
                'last_paid',
                OrderBy::DESC
            ),
            2
        );

        $expected = new ResultOfQueryCollection(
            new Response(
                [
                    'result' => [
                        [
                            'acc_type'      => 1,
                            'acc_type_name' => 'Active',
                            'balance'       => '0x1c8f9985',
                            'boc'           => 'te6ccgECEQEAAsQAAm/ABjnj+rHjU74AcjblZ8fYnvVNPAEU9paoRsuv4l1MIyBSIoTCwwfPzcgAAAACA/AUCQcj5mFTQAIBAJXgn0u2wwDaiPu4oC24Agwi5VSxhWN5YYfDQ53BFcvz7gAAAXrQd3xugARd0XBwzDw3s7GnjkVUVm7//j5S5xc4X6U18WJpTs4z8MACJv8A9KQgIsABkvSg4YrtU1gw9KEHAwEK9KQg9KEEAgPOwAYFADfXaiaGn/6Z/pgGmH64X//DX8NT/8MPwzfDH8MUADf3whZGX//CHnhZ/8I2eFgHwlfCWBZYfl/+T2qkAgEgCggB5P9/Ie1E0CDXScIBjhjT/9M/0wDTD9cL//hr+Gp/+GH4Zvhj+GKOHvQFcPhqcPhrcAGAQPQO8r3XC//4YnD4Y3D4Zn/4YeLTAAGOHYECANcYIPkBAdMAAZTT/wMBkwL4QuIg+GX5EPKoldMAAfJ64tM/AQkAfo4e+EMhuSCfMCD4I4ED6KiCCBt3QKC53pL4Y+CANPI02NMfIcEDIoIQ/////byxk1vyPOAB8AH4R26TMPI83gIBIA4LAgN9eA0MAH2xbiIv8ILdJeAJvaPwlEOB/xxGR6GmA/SAYGORnw5BnQDBnoGfA58Dnyd9uIi8Q54WH5Lj9gG8YSXgB7z/8M8ATbEQyqfwgt0l4Am9ph+j8IpA3SRg4b3wl3XlwMvwAEHw1GHgBv/wzwIBIBAPAOG7yR4cX4QW6OQ+1E0CDXScIBjhjT/9M/0wDTD9cL//hr+Gp/+GH4Zvhj+GKOHvQFcPhqcPhrcAGAQPQO8r3XC//4YnD4Y3D4Zn/4YeLe+Ebyc3H4ZtcN/5XU0dDT/9/RIMIA8uBk+AAg+Gsw8AN/+GeABg3XAi0NcLA6k4ANwhxwDcIdMfId0hwQMighD////9vLGTW/I84AHwAfhHbpMw8jze',
                            'code'          => 'te6ccgECDwEAAjsAAib/APSkICLAAZL0oOGK7VNYMPShBQEBCvSkIPShAgIDzsAEAwA312omhp/+mf6YBph+uF//w1/DU//DD8M3wx/DFAA398IWRl//wh54Wf/CNnhYB8JXwlgWWH5f/k9qpAIBIAgGAeT/fyHtRNAg10nCAY4Y0//TP9MA0w/XC//4a/hqf/hh+Gb4Y/hijh70BXD4anD4a3ABgED0DvK91wv/+GJw+GNw+GZ/+GHi0wABjh2BAgDXGCD5AQHTAAGU0/8DAZMC+ELiIPhl+RDyqJXTAAHyeuLTPwEHAH6OHvhDIbkgnzAg+COBA+iogggbd0Cgud6S+GPggDTyNNjTHyHBAyKCEP////28sZNb8jzgAfAB+EdukzDyPN4CASAMCQIDfXgLCgB9sW4iL/CC3SXgCb2j8JRDgf8cRkehpgP0gGBjkZ8OQZ0AwZ6BnwOfA58nfbiIvEOeFh+S4/YBvGEl4Ae8//DPAE2xEMqn8ILdJeAJvaYfo/CKQN0kYOG98Jd15cDL8ABB8NRh4Ab/8M8CASAODQDhu8keHF+EFujkPtRNAg10nCAY4Y0//TP9MA0w/XC//4a/hqf/hh+Gb4Y/hijh70BXD4anD4a3ABgED0DvK91wv/+GJw+GNw+GZ/+GHi3vhG8nNx+GbXDf+V1NHQ0//f0SDCAPLgZPgAIPhrMPADf/hngAYN1wItDXCwOpOADcIccA3CHTHyHdIcEDIoIQ/////byxk1vyPOAB8AH4R26TMPI83g==',
                            'code_hash'     => '6ceb662c4e5e827079984ce31e8c9230c677f3699de0a10631bfff0c986e2e98',
                            'data'          => 'te6ccgEBAQEATQAAleCfS7bDANqI+7igLbgCDCLlVLGFY3lhh8NDncEVy/PuAAABetB3fG6ABF3RcHDMPDezsaeORVRWbv/+PlLnFzhfpTXxYmlOzjPwwA==',
                            'data_hash'     => '16f1920161eb49f39cc096ee9f1d9ec1846167c191dbd8d39b4316f1b4e46c2c',
                            'due_payment'   => null,
                            'id'            => '0:639e3fab1e353be007236e567c7d89ef54d3c0114f696a846cbafe25d4c23205',
                            'last_paid'     => 1626995129,
                            'last_trans_lt' => '0x80fc0502',
                            'library'       => null,
                            'library_hash'  => null,
                            'proof'         => null,
                            'split_depth'   => null,
                            'state_hash'    => null,
                            'tick'          => null,
                            'tock'          => null,
                            'workchain_id'  => 0,
                        ],
                        [
                            'acc_type'      => 1,
                            'acc_type_name' => 'Active',
                            'balance'       => '0x12705d155',
                            'boc'           => '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',
                            'code'          => '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',
                            'code_hash'     => 'cf7e2a37fd6b66e889447bc9cc6ca315d50f17f5ad4bb3e7e947f4838afc9614',
                            'data'          => 'te6ccgECBAEAAV8AA9WbZSC0tdxftYO/KKADEWjcuNhC8rV/+lrfJ+x3HYzqtQAAAXrQdfq8gAJNspBaWu4v2sHflFABiLRuXGwheVq//S1vk/Y7jsZ1WoAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIAMCAQBDgAjWbTKPUwvRGdZPAKqhIylhmeMD9IyLypvcl3MXJhpskADJgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEADes2e9qxG2oElgTVWtvVgr1xaJOVMpFBPdM5zED6FCegAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAw1XpwzsGWDncXf65zMbeY9FuTyCQ56tztfZs4RFMKRfgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAdbzRWAHeRIcgEOoCXKeQS+yULekX6tnQJNZk1coYLvBjbILGGw',
                            'data_hash'     => '501600ed05199722a514d4c71095cded248966748b72d1a901bd0dd06e5ec6fd',
                            'due_payment'   => null,
                            'id'            => '0:55e9c33b065839dc5dfeb9ccc6de63d16e4f2090e7ab73b5f66ce1114c2917e0',
                            'last_paid'     => 1626995043,
                            'last_trans_lt' => '0x7e7b2683',
                            'library'       => null,
                            'library_hash'  => null,
                            'proof'         => null,
                            'split_depth'   => null,
                            'state_hash'    => null,
                            'tick'          => null,
                            'tock'          => null,
                            'workchain_id'  => 0,
                        ],
                    ],
                ]
            )
        );

        self::assertEquals($expected, $this->net->queryCollection($query));
    }

    /**
     * @covers ::waitForCollection
     */
    public function testWaitForCollection(): void
    {
        $query = new ParamsOfWaitForCollection(
            'accounts',
            [
                'acc_type',
                'acc_type_name',
                'balance',
                'boc',
                'code',
                'code_hash',
                'data',
                'data_hash',
                'due_payment',
                'id',
                'last_paid',
                'last_trans_lt',
                'library',
                'library_hash',
                'proof',
                'split_depth',
                'state_hash',
                'tick',
                'tock',
                'workchain_id',
            ],
            (new Filters())->add(
                'last_paid',
                Filters::IN,
                [
                    1626994845,
                    1626994874,
                    1626994901,
                    1626994923,
                    1626995009,
                    1626995043,
                    1626995129,
                ]
            ),
            90_000
        );

        $expected = new ResultOfWaitForCollection(
            new Response(
                [
                    'result' => [
                        'acc_type'      => 1,
                        'acc_type_name' => 'Active',
                        'balance'       => '0x1ce2514f',
                        'boc'           => 'te6ccgECEwEAAtEAAm/ABZt8JB+kfd6E26p9ba3ntTAHLk0vdPEuAfyVkcZ2rnyiJoTRAwfPxOgAAAAB40ZDCQc4lFPTQAYBAWGAAAC9aDmSxAAAAAAADbugQ5GEr7/A0frBSqVoBolcj9xQeKrl/o3bS8ET28xXdElgAgIDzyAFAwEB3gQAA9AgAEHYcjCV9/gaP1gpVK0A0SuR+4oPFVy/0btpeCJ7eYruiSwCJv8A9KQgIsABkvSg4YrtU1gw9KEJBwEK9KQg9KEIAAACASAMCgH+/38h1SDHAZFwjhIggQIA1yHXC/8i+QFTIfkQ8qjiItMf0z81IHBwcO1E0PQEATQggQCA10WY0z8BM9M/ATKWgggbd0Ay4nAjJrmOJCX4I4ED6KgkoLmOF8glAfQAJs8LPyPPCz8izxYgye1UfzIw3t4FXwWZJCLxQAFfCtsw4AsADIA08vBfCgIBIBANAQm8waZuzA4B/nDtRND0BAEyINaAMu1HIm+MI2+MIW+MIO1XXwRwaHWhYH+6lWh4oWAx3u1HbxHXC/+68uBk+AD6QNN/0gAwIcIAIJcwIfgnbxC53vLgZSIiInDIcc8LASLPCgBxz0D4KM8WJM8WI/oCcc9AcPoCcPoCgEDPQPgjzwsfcs9AIMkPABYi+wBfBV8DcGrbMAIBSBIRAOu4iQAnXaiaBBAgEFrovk5gHwAdqPkQICAZ6Bk6DfGAPoCLLfGdquAmDh2o7eJQCB6B3lFa4X/9qOQN4iYAORl/+ToN6j2q/ajkDeJZHoALBBjgMcIGDhnhZ/BBA27oGeFn7jnoMrnizjnoPEAt4jni2T2qjg1QAMrccCHXSSDBII4rIMAAjhwj0HPXIdcLACDAAZbbMF8H2zCW2zBfB9sw4wTZltswXwbbMOME2eAi0x80IHS7II4VMCCCEP////+6IJkwIIIQ/////rrf35bbMF8H2zDgIyHxQAFfBw==',
                        'code'          => 'te6ccgECDQEAAjAAAib/APSkICLAAZL0oOGK7VNYMPShAwEBCvSkIPShAgAAAgEgBgQB/v9/IdUgxwGRcI4SIIECANch1wv/IvkBUyH5EPKo4iLTH9M/NSBwcHDtRND0BAE0IIEAgNdFmNM/ATPTPwEyloIIG3dAMuJwIya5jiQl+COBA+ioJKC5jhfIJQH0ACbPCz8jzws/Is8WIMntVH8yMN7eBV8FmSQi8UABXwrbMOAFAAyANPLwXwoCASAKBwEJvMGmbswIAf5w7UTQ9AQBMiDWgDLtRyJvjCNvjCFvjCDtV18EcGh1oWB/upVoeKFgMd7tR28R1wv/uvLgZPgA+kDTf9IAMCHCACCXMCH4J28Qud7y4GUiIiJwyHHPCwEizwoAcc9A+CjPFiTPFiP6AnHPQHD6AnD6AoBAz0D4I88LH3LPQCDJCQAWIvsAXwVfA3Bq2zACAUgMCwDruIkAJ12omgQQIBBa6L5OYB8AHaj5ECAgGegZOg3xgD6Aiy3xnargJg4dqO3iUAgegd5RWuF//ajkDeImADkZf/k6Deo9qv2o5A3iWR6ACwQY4DHCBg4Z4WfwQQNu6BnhZ+456DK54s456DxALeI54tk9qo4NUADK3HAh10kgwSCOKyDAAI4cI9Bz1yHXCwAgwAGW2zBfB9swltswXwfbMOME2ZbbMF8G2zDjBNngItMfNCB0uyCOFTAgghD/////uiCZMCCCEP////6639+W2zBfB9sw4CMh8UABXwc=',
                        'code_hash'     => '98196905d4f1d250741ab885ac2411e0a547c72486f613d8cb5f302fd9d51c6a',
                        'data'          => 'te6ccgEBBQEAZQABYYAAAL1oOZLEAAAAAAANu6BDkYSvv8DR+sFKpWgGiVyP3FB4quX+jdtLwRPbzFd0SWABAgPPIAQCAQHeAwAD0CAAQdhyMJX3+Bo/WClUrQDRK5H7ig8VXL/Ru2l4Int5iu6JLA==',
                        'data_hash'     => 'de7d64125f5587c9c2dc9ddd5cc3d314f8e422ae1d9a938ddcfd711dd66812ca',
                        'due_payment'   => null,
                        'id'            => '0:59b7c241fa47dde84dbaa7d6dade7b530072e4d2f74f12e01fc9591c676ae7ca',
                        'last_paid'     => 1626994845,
                        'last_trans_lt' => '0x78d190c2',
                        'library'       => null,
                        'library_hash'  => null,
                        'proof'         => null,
                        'split_depth'   => null,
                        'state_hash'    => null,
                        'tick'          => null,
                        'tock'          => null,
                        'workchain_id'  => 0,
                    ],
                ]
            )
        );

        self::assertEquals($expected, $this->net->waitForCollection($query));
    }

    /**
     * @covers ::subscribeCollection
     * @covers ::unsubscribe
     */
    public function testSubscribeCollection(): void
    {
        $minBalanceDelta = 1_000;

        $query = new ParamsOfSubscribeCollection(
            'transactions',
            [
                'id',
                'status',
                'status_name',
                'block_id',
                'account_addr',
                'lt',
                'prev_trans_hash',
                'prev_trans_lt',
                'now',
                'balance_delta',
            ],
            (new Filters())->add(
                'balance_delta',
                Filters::GT,
                '0x' . dechex($minBalanceDelta)
            )
        );

        $result = $this->net->subscribeCollection($query);
        self::assertFalse($result->isFinished());
        self::assertGreaterThan(0, $result->getHandle());

        // Event saver (for manual check event data)
        $saver = $this->eventSaver->getSaver(__METHOD__);

        $counter = 0;
        foreach ($result->getIterator() as $event) {
            // Save event data to dump file (tests/Integration/artifacts/*.txt)
            $saver->send($event->getResult());

            $eventResult = $event->getResult();

            self::assertNotEmpty($eventResult['id']);
            self::assertNotEmpty($eventResult['status']);
            self::assertNotEmpty($eventResult['status_name']);
            self::assertNotEmpty($eventResult['block_id']);
            self::assertNotEmpty($eventResult['account_addr']);
            self::assertNotEmpty($eventResult['lt']);
            self::assertNotEmpty($eventResult['prev_trans_hash']);
            self::assertNotEmpty($eventResult['prev_trans_lt']);
            self::assertNotEmpty($eventResult['now']);
            self::assertNotEmpty($eventResult['balance_delta']);
            self::assertGreaterThan($minBalanceDelta, hexdec($eventResult['balance_delta']));

            if (++$counter > 3) {
                $this->net->unsubscribe($result->getHandle());
                // or call: $result->stop();
            }
        }

        self::assertTrue($result->isFinished());
    }

    /**
     * @covers ::query
     */
    public function testQuery(): void
    {
        $query = <<<'QUERY'
            query($time: Float) {
                messages(
                    filter: {
                        created_at: {
                            ge: $time
                        }
                    }
                    limit:5
                ){id}
            }
        QUERY;

        $result = $this->net->query(
            $query,
            [
                'time' => time() - 7200
            ]
        );

        self::assertGreaterThan(0, $result->getMessages());
    }

    /**
     * @covers ::findLastShardBlock
     */
    public function testFindLastShardBlock(): void
    {
        $address = $this->dataProvider->getGiverAddress();

        $result = $this->net->findLastShardBlock($address);

        self::assertNotEmpty($result->getBlockId());
    }

    /**
     * is broken?
     * @see https://github.com/tonlabs/TON-SDK/blob/7dcd23d861add85a7ee307f19b82e423f533fea8/ton_client/src/net/tests.rs#L462-L479
     *
     * @covers ::setEndpoints
     * @covers ::fetchEndpoints
     */
    public function testSetEndpointsAndGetEndpoints(): void
    {
        self::markTestSkipped('Broken test.');

        $this->net->setEndpoints(
            [
                'cinet.tonlabs.io',
                'cinet2.tonlabs.io/'
            ]
        );

        $result = $this->net->fetchEndpoints();

        $expected = [
            'https://cinet.tonlabs.io',
            'https://cinet2.tonlabs.io',
        ];

        self::assertEquals($expected, $result->getEndpoints());
    }

    /**
     * @covers ::aggregateCollection
     */
    public function testAggregateCollection(): void
    {
        $aggregation = new Aggregation();
        $aggregation->add('id', Aggregation::COUNT);
        $aggregation->add('balance', Aggregation::AVERAGE);

        $query = new ParamsOfAggregateCollection('accounts');
        $query->setAggregation($aggregation);

        $resultOfAggregateCollection = $this->net->aggregateCollection($query);

        self::assertGreaterThan(0, $resultOfAggregateCollection->getValues()[0]);
        self::assertGreaterThan(0, $resultOfAggregateCollection->getValues()[1]);
    }

    /**
     * @covers ::batchQuery
     */
    public function testBatchQuery(): void
    {
        // Query 1
        $query1 = new ParamsOfQueryCollection('blocks_signatures', ['id']);
        $query1->setLimit(1);

        // Query 2
        $query2 = new ParamsOfWaitForCollection('transactions', ['id', 'now']);
        $filters = new Filters();
        $filters->add('now', Filters::GT, 20);
        $query2->setFilters($filters);

        // Query 3
        $aggregation = new Aggregation();
        $aggregation->add('id', Aggregation::COUNT);
        $aggregation->add('balance', Aggregation::AVERAGE);
        $query3 = new ParamsOfAggregateCollection('accounts');
        $query3->setAggregation($aggregation);

        $query = new ParamsOfBatchQuery();
        $query->add($query1);
        $query->add($query2);
        $query->add($query3);

        $resultOfBatchQuery = $this->net->batchQuery($query);

        self::assertCount(3, $resultOfBatchQuery->getResults());
    }

    /**
     * @covers ::queryCounterparties
     */
    public function testQueryCounterparties(): void
    {
        self::markTestSkipped('Broken test.');

        $account = '-1:7777777777777777777777777777777777777777777777777777777777777777';

        $expected = new ResultOfQueryCounterparties(
            new Response(
                [
                    'result' => [
                        [
                            'counterparty'    => '0:954688c088881241c5c6b248da398aefa2752bd5a97202f41454fdf3671e671c',
                            'last_message_id' => '972e6d570c2a114e09a291435c69d29333e3ad67a7e60445fff517d58d38cefd',
                            'cursor'          => '1614858473/0:954688c088881241c5c6b248da398aefa2752bd5a97202f41454fdf3671e671c',
                        ],
                        $part2 = [
                            'counterparty'    => '0:2bb4a0e8391e7ea8877f4825064924bd41ce110fce97e939d3323999e1efbb13',
                            'last_message_id' => '33ce8939b7cec3018272ecf47381782d502ca7a81e7ff9385803f69a03fced35',
                            'cursor'          => '1610344806/0:2bb4a0e8391e7ea8877f4825064924bd41ce110fce97e939d3323999e1efbb13',
                        ],
                    ]
                ]
            )
        );

        $resultOfQueryCounterparties = $this->net->queryCounterparties(
            $account,
            'counterparty last_message_id cursor',
            5
        );

        self::assertEquals($expected, $resultOfQueryCounterparties);

        $result = $resultOfQueryCounterparties->getResult();
        $last = reset($result);

        $resultOfQueryCounterparties = $this->net->queryCounterparties(
            $account,
            'counterparty last_message_id cursor',
            5,
            $last['cursor']
        );

        $expected = new ResultOfQueryCounterparties(
            new Response(
                [
                    'result' => [
                        $part2,
                    ]
                ]
            )
        );

        self::assertEquals($expected, $resultOfQueryCounterparties);
    }

    /**
     * @covers ::queryTransactionTree
     */
    public function testQueryTransactionTree(): void
    {
        $filters = new Filters();
        $filters->add('msg_type', Filters::EQ, 1);

        $query = new ParamsOfQueryCollection(
            'messages',
            [],
            $filters
        );

        $query->addResultField(
            <<<FIELDS
                id dst dst_transaction {
                    id aborted out_messages {
                        id dst msg_type_name dst_transaction {
                            id aborted out_messages {
                                id dst msg_type_name dst_transaction {
                                    id aborted
                                }
                            }
                        }
                    }
                }
            FIELDS
        );

        $resultOfQueryCollection = $this->net->queryCollection($query);

        $abiRegistry = [
            AbiType::fromArray($this->dataProvider->getHelloAbiArray())
        ];

        foreach ($resultOfQueryCollection->getResult() as $message) {
            $messageId = $message['id'] ?? null;
            if ($messageId === null) {
                continue;
            }

            $resultOfQueryTransactionTree = $this->net->queryTransactionTree(
                $messageId,
                $abiRegistry
            );

            self::assertContainsOnlyInstancesOf(
                MessageNode::class,
                $resultOfQueryTransactionTree->getMessages()
            );

            self::assertContainsOnlyInstancesOf(
                TransactionNode::class,
                $resultOfQueryTransactionTree->getTransactions()
            );
        }
    }
}
