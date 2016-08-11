<?php

namespace Instante\Bootstrap3Renderer;

use Instante\ExtendedFormMacros\IExtendedFormRenderer;
use /** @noinspection PhpInternalEntityUsedInspection */
    Nette\Bridges\FormsLatte\Runtime;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\InvalidStateException;

/**
 * Created with twitter bootstrap in mind.
 *
 * <code>
 * $form->setRenderer(new Instante\Bootstrap3Renderer\BootstrapRenderer);
 * </code>
 *
 * @author Richard Ejem
 */
class BootstrapRenderer implements IExtendedFormRenderer
{
    /** @var int */
    protected $labelColumns = 2;

    /** @var int */
    protected $inputColumns = 10;

    /** @var bool if true, controls without group go first */
    protected $grouplessRenderedFirst = FALSE;

    /**
     * set to false, if you want to display the field errors also as form errors
     * @var bool
     */
    public $errorsAtInputs = TRUE;

    /** @var Form */
    protected $form;

    /** @var string RenderModeEnum */
    protected $renderMode = RenderModeEnum::VERTICAL;

    public function renderPair(IControl $control)
    {
        // TODO: Implement renderPair() method.
    }

    public function renderGroup(ControlGroup $control)
    {
        // TODO: Implement renderGroup() method.
    }

    public function renderContainer(Container $control)
    {
        // TODO: Implement renderContainer() method.
    }

    public function render(Form $form, $mode = NULL)
    {
        $s = '';
        if (!$mode || $mode === 'begin') {
            //TODO: will have to redirect {form} macro to this routine to set form class
            $s .= $this->renderBegin($form);
        }
        if (!$mode || strtolower($mode) === 'ownerrors') {
            $s .= $this->renderGlobalErrors();

        } elseif ($mode === 'errors') {
            $s .= $this->renderGlobalErrors(FALSE);
        }
        if (!$mode || $mode === 'body') {
            $s .= $this->renderBody();
        }
        if (!$mode || $mode === 'end') {
            $s .= $this->renderEnd();
        }
        return $s;
    }

    /**
     * Sets label (and optionally input) width in bootstrap columns layout
     *
     * @param int $labelColumns
     * @param int|NULL $inputColumns if null, automatically filled to 12-labelColumns
     * @return $this
     */
    public function setLabelColumns($labelColumns, $inputColumns = NULL)
    {
        if ($inputColumns === NULL) {
            $inputColumns = 12 - $labelColumns;
        }
        $this->labelColumns = $labelColumns;
        $this->inputColumns = $inputColumns;
        return $this;
    }

    /**
     * @param string $renderMode RenderModeEnum
     * @return $this
     */
    public function setRenderMode($renderMode)
    {
        RenderModeEnum::assertValidValue($renderMode);
        $this->renderMode = $renderMode;
        return $this;
    }

    /** @return string RenderModeEnum */
    public function getRenderMode()
    {
        return $this->renderMode;
    }

    public function renderBegin(Form $form, array $attrs = [])
    {
        if ($this->form !== $form) {
            $this->form = $form;
        }

        $this->addFormModeClass($form, $attrs);
        /** @noinspection PhpInternalEntityUsedInspection */
        $rendered = Runtime::renderFormBegin($form, $attrs);
        return $rendered;
    }

    public function renderControlErrors(IControl $control)
    {
        //TODO
        return '[ERRORS]';
    }

    public function renderGlobalErrors($ownOnly = TRUE)
    {
        //TODO
        return $ownOnly ? '[OWN]' : '[GLOBAL]';
    }

    public function renderBody()
    {
        //TODO
        return '[BODY]';
    }

    public function renderEnd()
    {
        $this->assertInForm();
        $form = $this->form;
        $this->form = NULL;
        /** @noinspection PhpInternalEntityUsedInspection */
        return Runtime::renderFormEnd($form);
    }

    private function addFormModeClass(Form $form, array &$attrs)
    {
        if ($this->renderMode !== RenderModeEnum::VERTICAL) {
            if (isset($attrs['class'])) {
                $classes = explode(' ', $attrs['class']);
                $pos = array_search('no-' . $this->renderMode, $classes, TRUE);
                if ($pos !== FALSE) {
                    // if .no-form-<mode> class is present, remove it and not include form-<mode> class
                    unset($classes[$pos]);
                } else {
                    // otherwise add form-<mode> class
                    $classes[] = $this->renderMode;
                }
                if (count($classes) === 0) {
                    unset($attrs['class']);
                } else {
                    $attrs['class'] = implode(' ', $classes);
                }
            } else {
                $classes = $form->getElementPrototype()->getAttribute('class');
                if (is_string($classes)) {
                    $classes = explode(' ', $classes);
                } elseif (!is_array($classes)) {
                    $classes = [];
                }
                $classes[] = $this->renderMode;
                $attrs['class'] = implode(' ', $classes);
            }
        }
    }

    protected function assertInForm()
    {
        if ($this->form === NULL) {
            throw new InvalidStateException('No form set, please call renderBegin($form) first.');
        }
    }
}
