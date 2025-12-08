<?php
declare(strict_types=1);

namespace HivePHP;

class AssetManager
{
    protected array $css = [];
    protected array $js = [];

    public function css(string $file): void
    {
        if ($file !== '' && preg_match('/^[a-zA-Z0-9_\-\/\.]+$/', $file)) {
            $this->css[] = '/css/' . $file;
        }
    }

    public function js(string $file): void
    {
        if ($file !== '' && preg_match('/^[a-zA-Z0-9_\-\/\.]+$/', $file)) {
            $this->js[] = '/js/' . $file;
        }
    }

    public function renderCss(): string
    {
        $html = "";

        foreach ($this->css as $file) {
            $html .= "<link rel=\"stylesheet\" href=\"{$file}\">\n";
        }

        return $html;
    }

    public function renderJs(): string
    {
        $html = "";

        foreach ($this->js as $file) {
            $html .= "<script src=\"{$file}\"></script>\n";
        }

        return $html;
    }
}
