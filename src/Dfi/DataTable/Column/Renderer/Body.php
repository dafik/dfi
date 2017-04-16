<?
namespace Dfi\DataTable\Column\Renderer;

class Body
{
    /**
     * @var string[]
     */
    private $body = [];

    /**
     * @param $body string|string[]
     */
    public function __construct($body)
    {
        if (is_string($body)) {
            $body = [$body];
        }
        $this->body = $body;
    }

    /**
     * @param string|string[]
     * @return \Dfi\DataTable\Column\Renderer\Body
     */
    public static function create($body)
    {
        return new Body($body);
    }

    /**
     * @return \string[]
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param $line
     * @return $this
     */
    public function addLine($line)
    {
        $this->body[] = $line;
        return $this;
    }


    /**
     * @return string
     */
    public function render()
    {
        $body = [];
        foreach ($this->body as $line) {
            $body[] = "\t\t\t\t" . $line;
        }
        return $body;
    }
}