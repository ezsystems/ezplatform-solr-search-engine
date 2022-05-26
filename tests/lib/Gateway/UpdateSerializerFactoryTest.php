<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Gateway;

use eZ\Publish\SPI\Exception\InvalidArgumentException;
use Ibexa\Solr\Gateway\UpdateSerializerFactory;
use Ibexa\Solr\Gateway\UpdateSerializerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\Solr\Gateway\UpdateSerializerFactory
 */
final class UpdateSerializerFactoryTest extends TestCase
{
    private const FORMAT_FOO = 'foo';
    private const FORMAT_BAR = 'bar';

    /**
     * @dataProvider getDataForTestGetSerializer
     *
     * @param array<\Ibexa\Solr\Gateway\UpdateSerializerInterface> $serializers
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testGetSerializer(
        array $serializers,
        UpdateSerializerInterface $expectedSerializer,
        string $format
    ): void {
        $factory = new UpdateSerializerFactory($serializers);

        self::assertSame($expectedSerializer, $factory->getSerializer($format));
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testGetSerializerThrowsInvalidArgumentException(): void
    {
        $fooSerializerMock = $this->createMock(UpdateSerializerInterface::class);
        $fooSerializerMock->method('getSupportedFormat')->willReturn(self::FORMAT_FOO);

        $factory = new UpdateSerializerFactory([$fooSerializerMock]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported Update Serializer format: bar');
        $factory->getSerializer(self::FORMAT_BAR);
    }

    public function getDataForTestGetSerializer(): iterable
    {
        $fooSerializerMock = $this->createMock(UpdateSerializerInterface::class);
        $fooSerializerMock->method('getSupportedFormat')->willReturn(self::FORMAT_FOO);

        $barSerializerMock = $this->createMock(UpdateSerializerInterface::class);
        $barSerializerMock->method('getSupportedFormat')->willReturn(self::FORMAT_BAR);

        $serializers = [$fooSerializerMock, $barSerializerMock];

        yield self::FORMAT_FOO => [$serializers, $fooSerializerMock, self::FORMAT_FOO];
        yield self::FORMAT_BAR => [$serializers, $barSerializerMock, self::FORMAT_BAR];
    }
}
