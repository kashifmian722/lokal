<?php declare(strict_types=1);

namespace Webkul\MPHyperlocal\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1598958083ShippingLocation extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1598958083;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
        CREATE TABLE IF NOT EXISTS `marketplace_hyperlocal_shipping_location` (
            `id` binary(16) NOT NULL,
            `customer_id` binary(16) NOT NULL,
            `location` VARCHAR(250) NOT NULL,
            `longitude` FLOAT NOT NULL,
            `latitude` FLOAT NOT NULL,
            `shipping_option` tinyint(1) DEFAULT 0,
            `created_at` datetime(3) DEFAULT NULL,
            `updated_at` datetime(3) DEFAULT NULL
        )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
        
        
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
