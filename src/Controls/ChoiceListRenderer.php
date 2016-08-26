<?php

namespace Instante\Bootstrap3Renderer\Controls;

use Instante\Bootstrap3Renderer\BootstrapRenderer;
use Instante\Bootstrap3Renderer\RenderModeEnum;
use Instante\Helpers\SecureCallHelper;
use Nette\Forms\IControl;
use Nette\InvalidStateException;
use Nette\Utils\Html;

class ChoiceListRenderer extends DefaultControlRenderer
{
    /** @var Html item separator */
    public $separator = NULL;

    /** @var string assigns class="?[-inline]" to single control element wrapper */
    protected $wrapperClass;

    /** @var string method name */
    protected $getItemsMethod;


    /**
     * ChoiceListRenderer constructor.
     * @param BootstrapRenderer $bootstrapRenderer
     * @param string $wrapperClass
     * @param string $getItemsMethod
     */
    public function __construct(BootstrapRenderer $bootstrapRenderer, $wrapperClass, $getItemsMethod = 'getItems')
    {
        parent::__construct($bootstrapRenderer);
        $this->wrapperClass = $wrapperClass;
        $this->getItemsMethod = $getItemsMethod;
    }


    /** {@inheritdoc} */
    public function renderSingleChoice(IControl $control, $key)
    {
        $label = $this->renderSingleLabel($control, $key);
        $controlHtml = $this->renderSingleControl($control, $key);
        $label->insert(0, $controlHtml);
        if (($this->bootstrapRenderer->getRenderMode() === RenderModeEnum::INLINE
                && !SecureCallHelper::tryCall($control, 'getOption', 'noRenderInline'))
            || SecureCallHelper::tryCall($control, 'getOption', 'renderInline')
        ) {
            $label->appendAttribute('class', $this->wrapperClass . '-inline');
            $wrapper = $label;
        } else {
            $wrapper = Html::el('div', ['class' => $this->wrapperClass])->addHtml($label);
        }
        return $wrapper;
    }

    /**
     * div.(checkbox|radio){0..n}
     *     label
     *         input[type="checkbox|radio"]
     *         ...Label
     * ||
     * label.(checkbox|radio)-inline{0..n}
     *     input[type="checkbox|radio"]
     *     ...Label
     *
     * @param IControl $control
     * @param bool $renderedDescription
     * @return Html
     */
    public function renderControl(IControl $control, $renderedDescription = FALSE)
    {
        $el = Html::el();
        $first = TRUE;
        foreach ($this->getListItems($control) as $item) {
            if (!$first && $this->separator !== NULL) {
                $el->addHtml($this->separator);
            }
            $first = FALSE;
            $el->addHtml($this->renderSingleChoice($control, $item));
        }
        return $el;
    }

    protected function getListItems(IControl $control)
    {
        if (!method_exists($control, $this->getItemsMethod)) {
            throw new InvalidStateException(sprintf(
                'Control rendered by %s must implement %s method', __CLASS__, $this->getItemsMethod
            ));
        }
        return $control->{$this->getItemsMethod}();
    }

    /**
     * @param IControl $control
     * @param string $key
     * @return Html
     */
    public function renderSingleLabel(IControl $control, $key)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $control->getLabelPart($key);
    }

    /**
     * @param IControl $control
     * @param string $key
     * @return Html
     */
    public function renderSingleControl(IControl $control, $key)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $control->getControlPart($key);
    }
}