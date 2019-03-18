<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Iways\PayPalInstalments\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'training_category_country'
         */
        $connection = $installer->getConnection();

        $table = $installer->getTable('sales_order');
        $connection->addColumn(
            $table,
            'instalments_fee_amt',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'scale' => 4,
                'precision' => 12,
                'nullable' => true,
                'default' => null,
                'comment' => 'Instalments Fee Amount'
            ]
        );
        $connection->addColumn(
            $table,
            'base_instalments_fee_amt',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'scale' => 4,
                'precision' => 12,
                'nullable' => true,
                'default' => null,
                'comment' => 'Base Instalments Fee Amount'
            ]
        );

        $table = $installer->getTable('quote');
        $connection->addColumn(
            $table,
            'instalments_fee_amt',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'scale' => 4,
                'precision' => 12,
                'nullable' => true,
                'default' => null,
                'comment' => 'Instalments Fee Amount'
            ]
        );
        $connection->addColumn(
            $table,
            'base_instalments_fee_amt',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'scale' => 4,
                'precision' => 12,
                'nullable' => true,
                'default' => null,
                'comment' => 'Base Instalments Fee Amount'
            ]
        );


        $table = $installer->getTable('sales_invoice');
        $connection->addColumn(
            $table,
            'instalments_fee_amt',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'scale' => 4,
                'precision' => 12,
                'nullable' => true,
                'default' => null,
                'comment' => 'Instalments Fee Amount'
            ]
        );
        $connection->addColumn(
            $table,
            'base_instalments_fee_amt',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'scale' => 4,
                'precision' => 12,
                'nullable' => true,
                'default' => null,
                'comment' => 'Base Instalments Fee Amount'
            ]
        );

        $installer->endSetup();
    }
}
