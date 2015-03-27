<?php
/**
 * @Author: pangpond
 * @Date:   2015-03-12 20:43:00
 * @Last Modified by:   pangpond
 * @Last Modified time: 2015-03-27 15:17:58
 */

namespace nextensions\material;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * An extended nav menu for Bootstrap 3 - that offers
 * submenu drilldown
 *
 */
class NavX extends \yii\bootstrap\Nav
{
    /**
     * @var string the class name to render the Dropdown items.
     * Defaults to `\nextensions\material\DropdownX`.
     */
    public $dropdownClass = 'nextensions\material\DropdownX';
    
    /**
     * @var array the dropdown widget options
     */
    public $dropdownOptions = [];
    
    /**
     * @var string the caret indicator to display for dropdowns
     */
    public $dropdownIndicator = '';
    
    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init() {
        if (!class_exists($this->dropdownClass)) {
             throw new InvalidConfigException("The dropdownClass '{$this->dropdownClass}' does not exist or is not accessible.");
        }
        parent::init();
        Html::removeCssClass($this->options, 'nav');
    }
    
    /**
     * @inheritdoc
     */
    public function renderItem($item)
    {
        if (is_string($item)) {
            return $item;
        }
        if (!isset($item['label'])) {
            throw new InvalidConfigException("The 'label' option is required.");
        }
        $label = $this->encodeLabels ? Html::encode($item['label']) : $item['label'];
        $options = ArrayHelper::getValue($item, 'options', []);
        $items = ArrayHelper::getValue($item, 'items');
        $url = ArrayHelper::getValue($item, 'url', '#');
        $linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);

        if (isset($item['active'])) {
            $active = ArrayHelper::remove($item, 'active', false);
        } else {
            $active = $this->isItemActive($item);
        }

        if ($items !== null) {
            // $linkOptions['data-toggle'] = 'dropdown';
            Html::addCssClass($options, 'gui-folder');
            // Html::addCssClass($linkOptions, 'dropdown-toggle');
            $label .= $this->dropdownIndicator;
            if (is_array($items)) {
                if ($this->activateItems) {
                    $items = $this->isChildActive($items, $active);
                }
                $dropdown = $this->dropdownClass;
                $dropdownOptions = ArrayHelper::merge($this->dropdownOptions, [
                    'items' => $items,
                    'encodeLabels' => $this->encodeLabels,
                    'clientOptions' => false,
                    'view' => $this->getView(),
                ]);
                $items = $dropdown::widget($dropdownOptions);
            }
        }

        if ($this->activateItems && $active) {
            Html::addCssClass($options, 'active');
        }

        return Html::tag('li', Html::a($label, $url, $linkOptions) . $items, $options);
    }
    
    /**
     * @inheritdoc
     */
    protected function isChildActive($items, &$active)
    {
        foreach ($items as $i => $child) {
            if (ArrayHelper::remove($items[$i], 'active', false) || $this->isItemActive($child)) {
                Html::addCssClass($items[$i]['options'], 'active');
                if ($this->activateParents) {
                    $active = true;
                }
            }
            if (isset($items[$i]['items']) && is_array($items[$i]['items'])) {
                $childActive = false;
                $items[$i]['items'] = $this->isChildActive($items[$i]['items'], $childActive);
                if ($childActive) {
                    Html::addCssClass($items[$i]['options'], 'active');
                    $active = true;
                }
            }
        }
        return $items;
    }
}
