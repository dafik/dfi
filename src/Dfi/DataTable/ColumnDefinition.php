<?
namespace Dfi\DataTable;

class ColumnDefinition
{
    /**
     * @var \Dfi\DataTable\Column\Renderer
     */
    private $renderer;
    /**
     * @var array
     */
    private $options;

    /**
     * @param \Dfi\DataTable\Column\Renderer $renderer
     * @param array $options
     */
    public function __construct($renderer, $options = [])
    {
        $this->renderer = $renderer;
        $this->options = $options;
    }

    /**
     * @param \Dfi\DataTable\Column\Renderer $renderer
     * @param array $options
     * @return ColumnDefinition
     */
    public static function create($renderer, $options = [])
    {
        return new ColumnDefinition($renderer, $options);
    }

    /**
     * @return string
     */
    public function renderRenderer()
    {
        return $this->renderer->render();
    }

    /**
     * @return \Dfi\DataTable\Column\Template
     */
    public function getTemplate()
    {
        return $this->renderer->getTemplate();
    }

    public function hasTemplate()
    {
        return $this->renderer->getTemplate() !== null;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

}