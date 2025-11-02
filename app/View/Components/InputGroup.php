<?php

namespace App\View\Components;

use Illuminate\View\Component;

class InputGroup extends Component
{
    public $name;
    public $label;
    public $type;

    public function __construct($name, $label, $type = 'text')
    {
        $this->name = $name;
        $this->label = $label;
        $this->type = $type;
    }

    public function render()
    {
        return view('components.input-group');
    }
}
