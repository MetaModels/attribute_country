<?php

/**
 * This file is part of MetaModels/attribute_country.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/attribute_country
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_country/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\AttributeCountryBundle\Test\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\Type;
use MetaModels\AttributeCountryBundle\Migration\AllowNullMigration;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MetaModels\AttributeCountryBundle\Migration\AllowNullMigration
 */
class AllowNullMigrationTest extends TestCase
{
    public function testName(): void
    {
        $connection = $this->createMock(Connection::class);
        $migration  = new AllowNullMigration($connection);

        self::assertSame('Allow null values in MetaModels "country" attributes.', $migration->getName());
    }

    public function runConfiguration(): \Generator
    {
        yield 'required tables not exist' => [
            (object) [
                'requiredTablesExist' => false,
                'shouldRun'           => false,
                'attributeConfigured' => false
            ]
        ];

        yield 'attribute select not configured' => [
            (object) [
                'requiredTablesExist' => true,
                'shouldRun'           => false,
                'attributeConfigured' => false
            ]
        ];

        yield 'attribute select is configured' => [
            (object) [
                'requiredTablesExist' => true,
                'shouldRun'           => false,
                'attributeConfigured' => true
            ]
        ];

        yield 'columns migrated' => [
            (object) [
                'requiredTablesExist' => true,
                'shouldRun'           => true,
                'attributeConfigured' => true
            ]
        ];
    }

    /**
     * @dataProvider runConfiguration
     */
    public function testRun(object $configuration): void
    {
        $connection = $this->createMock(Connection::class);
        $manager    = $this
            ->getMockBuilder(AbstractSchemaManager::class)
            ->setConstructorArgs([$connection])
            ->onlyMethods(['tablesExist', 'listTableColumns', 'listTableDetails', 'alterTable'])
            ->getMockForAbstractClass();

        $manager
            ->expects(self::once())
            ->method('tablesExist')
            ->with(['tl_metamodel', 'tl_metamodel_attribute'])
            ->willReturn($configuration->requiredTablesExist);

        $connection
            ->expects($configuration->attributeConfigured ? self::exactly($configuration->shouldRun ? 7 : 2) : self::once())
            ->method('getSchemaManager')
            ->willReturn($manager);

        if ($configuration->requiredTablesExist) {
            $attributes = [
                ['metamodel' => 'mm_table_2', 'attribute' => 'normal'],
                ['metamodel' => 'mm_table_1', 'attribute' => 'camelCase'],
                ['metamodel' => 'mm_table_1', 'attribute' => 'normal'],
                ['metamodel' => 'mm_table_2', 'attribute' => 'camelCase'],
                ['metamodel' => 'mm_table_2', 'attribute' => 'columnnotexist'],
                ['metamodel' => 'mm_table_2', 'attribute' => 'columnNotExist']
            ];
            $result = $this->getMockForAbstractClass(Result::class);
            $result
                ->expects($configuration->shouldRun ? self::exactly(2)  : self::once())
                ->method('fetchAllAssociative')
                ->willReturn($configuration->attributeConfigured ? $attributes : []);

            $queryBuilder = $this->createMock(QueryBuilder::class);
            $queryBuilder
                ->expects($configuration->shouldRun ? self::exactly(2)  : self::once())
                ->method('select')
                ->with('metamodel.tableName AS metamodel', 'attribute.colName AS attribute')
                ->willReturn($queryBuilder);
            $queryBuilder
                ->expects($configuration->shouldRun ? self::exactly(2)  : self::once())
                ->method('from')
                ->with('tl_metamodel_attribute', 'attribute')
                ->willReturn($queryBuilder);
            $queryBuilder
                ->expects($configuration->shouldRun ? self::exactly(2)  : self::once())
                ->method('leftJoin')
                ->with('attribute', 'tl_metamodel', 'metamodel', 'attribute.pid = metamodel.id')
                ->willReturn($queryBuilder);
            $queryBuilder
                ->expects($configuration->shouldRun ? self::exactly(2)  : self::once())
                ->method('where')
                ->with('attribute.type=:type')
                ->willReturn($queryBuilder);
            $queryBuilder
                ->expects($configuration->shouldRun ? self::exactly(2)  : self::once())
                ->method('setParameter')
                ->with('type', 'country')
                ->willReturn($queryBuilder);
            $queryBuilder
                ->expects($configuration->shouldRun ? self::exactly(2)  : self::once())
                ->method('execute')
                ->willReturn($result);

            $connection
                ->expects($configuration->shouldRun ? self::exactly(2)  : self::once())
                ->method('createQueryBuilder')
                ->willReturn($queryBuilder);
        }

        if ($configuration->requiredTablesExist && $configuration->attributeConfigured) {
            $columnDefault = $configuration->shouldRun ? 0 : null;
            $table1Columns = [
                'normal' =>
                    (new Column('normal', new IntegerType()))->setDefault($columnDefault),
                'camelcase' =>
                    (new Column('camelCase', new IntegerType()))->setDefault($columnDefault)
            ];
            $table2Columns = [
                'normal' =>
                    (new Column('normal', new IntegerType()))->setDefault($columnDefault),
                'camelcase' =>
                    (new Column('camelCase', new IntegerType()))->setDefault($columnDefault)
            ];
            if ($configuration->shouldRun) {
                $manager
                    ->method('listTableColumns')
                    ->withConsecutive(['mm_table_2'], ['mm_table_1'], ['mm_table_2'], ['mm_table_1'])
                    ->willReturn($table2Columns, $table1Columns, $table2Columns, $table1Columns);
            } else {
                $manager
                    ->method('listTableColumns')
                    ->withConsecutive(['mm_table_2'], ['mm_table_1'])
                    ->willReturn($table2Columns, $table1Columns);
            }
        }

        $migration = new AllowNullMigration($connection);
        self::assertSame($configuration->shouldRun, $migration->shouldRun());

        if (!$configuration->shouldRun) {
            return;
        }

        $manager
            ->method('listTableDetails')
            ->withConsecutive(['mm_table_2'], ['mm_table_2'], ['mm_table_1'], ['mm_table_1'])
            ->willReturn((new Table('mm_table_2')), (new Table('mm_table_2')), (new Table('mm_table_1')), (new Table('mm_table_1')));

        $migrationResult = $migration->run();
        self::assertTrue($migrationResult->isSuccessful());
        self::assertSame(
            'Adjusted column(s): mm_table_2.normal, mm_table_2.camelCase, mm_table_1.camelCase, mm_table_1.normal',
            $migrationResult->getMessage()
        );
    }

}
