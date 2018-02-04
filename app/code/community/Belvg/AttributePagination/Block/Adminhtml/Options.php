<?php

class Tartilas_AttributePagination_Block_Adminhtml_Options extends Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_LIMIT = 10;

    public $collection;
    public $n;
    public $p;
    public $new_url;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tartilas/catalog/product/attribute/options.phtml');
    }

    public function getOptionValues()
    {
        $attributeType = $this->getAttributeObject()->getFrontendInput();
        $defaultValues = $this->getAttributeObject()->getDefaultValue();
        if ($attributeType == 'select' || $attributeType == 'multiselect') {
            $defaultValues = explode(',', $defaultValues);
        } else {
            $defaultValues = array();
        }

        switch ($attributeType) {
            case 'select':
                $inputType = 'radio';
                break;
            case 'multiselect':
                $inputType = 'checkbox';
                break;
            default:
                $inputType = '';
                break;
        }

        $values = $this->getData('option_values');
        if (is_null($values)) {
            $values = array();

            //pagination --begin
            $this->n = $this->getRequest()->getParam('n', self::DEFAULT_LIMIT);
            $this->p = $this->getRequest()->getParam('p', self::DEFAULT_PAGE);
            $optionCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
                ->setAttributeFilter($this->getAttributeObject()->getId())
                ->setPositionOrder('asc', true)
                ->setPageSize($this->n)
                ->setCurPage($this->p)
                ->load();
            $this->collection = $optionCollection;
            $this->new_url = $this->buildNewUrl();
            //pagination --end

            foreach ($optionCollection as $option) {
                $value = array();
                if (in_array($option->getId(), $defaultValues)) {
                    $value['checked'] = 'checked="checked"';
                } else {
                    $value['checked'] = '';
                }

                $value['intype'] = $inputType;
                $value['id'] = $option->getId();
                $value['sort_order'] = $option->getSortOrder();
                foreach ($this->getStores() as $store) {
                    $storeValues = $this->getStoreOptionValues($store->getId());
                    if (isset($storeValues[$option->getId()])) {
                        $value['store'.$store->getId()] = htmlspecialchars($storeValues[$option->getId()]);
                    }
                    else {
                        $value['store'.$store->getId()] = '';
                    }
                }
                $values[] = new Varien_Object($value);
            }
            $this->setData('option_values', $values);
        }

        return $values;
    }

    public function buildNewUrl()
    {
        $current_url = Mage::helper('core/url')->getCurrentUrl();
        $delimeter = '/';
        $arr = explode($delimeter, $current_url);
        $i = 0;
        $new_url = '';
        for ($i; $i < count($arr); $i++) {
            if ($arr[$i] == 'n' || $arr[$i] == 'p') {
                $i++;
            } else {
                $new_url .= $arr[$i] . $delimeter;
            }
        }

        return trim($new_url, $delimeter);
    }
}