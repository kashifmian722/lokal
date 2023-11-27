<?php declare(strict_types=1);

namespace Webkul\MPHyperlocal\Core\Content\Bundle;

use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Webkul\MultiVendor\Core\Content\Bundle\SellerDefinition;

class shippingLocationDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'marketplace_hyperlocal_shipping_location';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new Required()),
            (new StringField('location', 'location'))->addFlags(new Required()),
            (new FloatField('longitude', 'longitude'))->addFlags(new Required()),
            (new FloatField('latitude', 'latitude'))->addFlags(new Required()),
            new ManyToOneAssociationField('customer','customer_id', CustomerDefinition::class, 'id', false)
        ]);
    }
}