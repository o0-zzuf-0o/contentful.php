<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Contentful\Delivery\ResourcePool;
use Contentful\Tests\Delivery\Implementation\JsonDecoderClient;
use Contentful\Tests\Delivery\Implementation\MockEntry;
use Contentful\Tests\Delivery\TestCase;

class ResourcePoolTest extends TestCase
{
    public function testGetSetData()
    {
        $resourcePool = new ResourcePool(new JsonDecoderClient(), new ArrayCachePool(), \true);

        $this->assertFalse($resourcePool->has('Entry', 'entryId', ['locale' => 'en-US']));
        $entry = MockEntry::withSys('entryId', [], 'en-US');
        $this->assertTrue($resourcePool->save($entry));

        $this->assertTrue($resourcePool->has('Entry', 'entryId', ['locale' => 'en-US']));
        $this->assertSame($entry, $resourcePool->get('Entry', 'entryId', ['locale' => 'en-US']));
    }

    /**
     * @expectedException        \OutOfBoundsException
     * @expectedExceptionMessage Resource pool could not find a resource with type "Entry", ID "invalidId", and locale "en-US".
     */
    public function testGetInvalidKey()
    {
        $instanceRepository = new ResourcePool(new JsonDecoderClient(), new ArrayCachePool());

        $instanceRepository->get('Entry', 'invalidId', ['locale' => 'en-US']);
    }

    public function testGenerateKey()
    {
        $instanceRepository = new ResourcePool(new JsonDecoderClient(), new ArrayCachePool());

        $key = $instanceRepository->generateKey(
            'Entry',
            'entryId',
            ['locale' => '*']
        );
        $this->assertSame('contentful.DELIVERY.cfexampleapi.master.Entry.entryId.__ALL__', $key);

        $key = $instanceRepository->generateKey(
            'Entry',
            'entryId',
            ['locale' => 'en-US']
        );
        $this->assertSame('contentful.DELIVERY.cfexampleapi.master.Entry.entryId.en_US', $key);

        $key = $instanceRepository->generateKey(
            'Entry',
            'entry-id-._',
            ['locale' => 'en-US']
        );
        $this->assertSame('contentful.DELIVERY.cfexampleapi.master.Entry.entry___45___id___45______46______95___.en_US', $key);
    }
}