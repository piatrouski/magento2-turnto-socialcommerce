<?php
/**
 * TurnTo_SocialCommerce
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * @copyright  Copyright (c) 2018 TurnTo Networks, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace TurnTo\SocialCommerce\Block\Widget;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use TurnTo\SocialCommerce\Helper\Product;
use TurnTo\SocialCommerce\Model\Data\PinboardConfigFactory;

/**
 * @method getContentType(): string
 * @method getTitle(): string
 * @method getLimit(): string
 * @method getMaxDaysOld(): string
 * @method getMaxCommentsPerBox(): string
 * @method getProgressiveLoading(): string
 */
class Pinboard extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    /**
     * @var PinboardConfigFactory
     */
    protected $pinboardConfigFactory;
    /**
     * @var Product
     */
    protected $productHelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\CatalogWidget\Model\Rule $rule,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        array $data = [],
        Json $json = null,
        PinboardConfigFactory $pinboardConfigFactory = null,
        Product $productHelper = null
    )
    {
        // Call the parent class with the proper arguments based on the availability of a Magento 2.2.x class
        call_user_func_array(
            [__CLASS__, 'parent::__construct'],
            array_slice(
                func_get_args(),
                0,
                // 9 excludes our custom classes, 8 excludes both our classes and the JSON class that doesn't exist
                class_exists('Magento\Framework\Serialize\Serializer\Json') ? 9 : 8
            )
        );

        $this->pinboardConfigFactory = $pinboardConfigFactory ?: ObjectManager::getInstance()->get(
            PinboardConfigFactory::class
        );
        $this->productHelper = $productHelper ?: ObjectManager::getInstance()->get(Product::class);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getProductSkus()
    {
        $productSkus = [];
        if (!empty($this->getConditions()->getConditions())) {
            $productSkus = array_map(
                function ($product) {
                    /** @var \Magento\Catalog\Model\Product $product */
                    return $this->productHelper->turnToSafeEncoding((string)$product->getSku());
                },
                $this->getProductCollection()->getItems()
            );
        }

        return $productSkus;
    }

    /**
     * Creates a TurnTo config block and outputs its html content
     * @return string
     */
    public function getTurnToConfigHtml()
    {
        /** @var \TurnTo\SocialCommerce\Block\TurnToConfig $pinboardBlock */
        try {
            $pinboardBlock = $this->getLayout()->createBlock(
                \TurnTo\SocialCommerce\Block\TurnToConfig::class,
                'turnto.config.pinboard'
            );
        } catch (LocalizedException $e) {
            return '';
        }

        $pinboardBlock->setConfigData($this->pinboardConfigFactory->create(['pinboardBlock' => $this]));

        return $pinboardBlock->toHtml();
    }
}
