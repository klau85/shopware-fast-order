<?php declare(strict_types=1);

namespace ShopwareFastOrder\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use Shopware\Core\Content\Category\Aggregate\CategoryTranslation\CategoryTranslationDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('core')]
class Migration1733149523AddMenuItem extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1733149523;
    }

    public function update(Connection $connection): void
    {
        $mainCateg = $this->getMainCategory($connection);

        $id = Uuid::randomBytes();
        $connection->insert(CategoryDefinition::ENTITY_NAME, [
            'id' => $id,
            'parent_id' => Uuid::fromHexToBytes($mainCateg['id']),
            'version_id' => Uuid::fromHexToBytes($mainCateg['version_id']),
            'parent_version_id' => Uuid::fromHexToBytes($mainCateg['version_id']),
            'visible' => 1,
            'active' => 1,
            'type' => CategoryDefinition::TYPE_LINK,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
        ]);

        $languages = $this->getLanguages($connection);
        foreach ($languages as $language) {
            $connection->insert(CategoryTranslationDefinition::ENTITY_NAME, [
                'category_id' => $id,
                'category_version_id' => Uuid::fromHexToBytes($mainCateg['version_id']),
                'name' => 'Fast Order',
                'link_type' => 'external',
                'external_link' => '/fast-order',
                'language_id' => Uuid::fromHexToBytes($language['id']),
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)
            ]);
        }
    }

    public function getMainCategory(Connection $connection): array
    {
        $sql = <<<SQL
SELECT LOWER(HEX(id)) as id, LOWER(HEX(version_id)) as version_id
FROM category
WHERE parent_id is null
SQL;

        return $connection->fetchAllAssociative($sql)[0];
    }

    public function getLanguages(Connection $connection): array
    {
        $sql = <<<SQL
SELECT LOWER(HEX(id)) as id
FROM language
SQL;

        return $connection->fetchAllAssociative($sql);
    }
}
