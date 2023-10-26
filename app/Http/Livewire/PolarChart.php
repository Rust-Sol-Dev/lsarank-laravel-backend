<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PolarChart extends Component
{
    /**
     * @var array
     */
    public $names;

    /**
     * @var array
     */
    public $values;

    /**
     * @var array
     */
    public $colors;

    /**
     * @var int
     */
    public $rand;

    /**
     * @var string
     */
    public $keywordName;

    /**
     * Mount the component
     *
     * @param array $names
     * @param array $values
     * @param string $keywordName
     */
    public function mount(array $names, array $values, string $keywordName)
    {
        $this->names = $names;
        $this->values = $values;
        $this->colors = ['#F87171', '#8E44AD', '#1ABC9C', '#E67E22', '#707B7C', '#F0FC03', ' #D98880', '#00EE00', '#97FFFF', '#FFDEAD'];
        $this->keywordName = $keywordName;
        $this->rand = rand(11111, 99999999);
    }

    /**
     * Render the component
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.polar-chart');
    }
}
