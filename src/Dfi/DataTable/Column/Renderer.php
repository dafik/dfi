<?
namespace Dfi\DataTable\Column;

use Dfi\DataTable\Column\Renderer\Body;

class Renderer
{
    /**
     * @var Body
     */
    private $body;
    /**
     * @var Template
     */
    private $template;

    public function __construct(Body $body, Template $template = null)
    {
        $this->body = $body;
        $this->template = $template;
    }

    public static function create(Body $body, Template $template = null)
    {
        return new Renderer($body, $template);
    }

    public function render()
    {
        if ($this->body) {
            $lines = ['function (data, type, row, meta) {'];
            $lines = array_merge($lines, $this->body->render());
            $lines [] = "\t\t}";

            return implode("\n", $lines);
        }
        return false;
    }

    /**
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }
}