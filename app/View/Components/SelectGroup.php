<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SelectGroup extends Component
{
    public $name;
    public $label;
    public $options;

    public function __construct($name, $label, $options = [])
    {
        $this->name = $name;
        $this->label = $label;
        $this->options = $options;
    }

    public function render()
    {
        return view('components.select-group');
    }
}
