<?php

namespace App\Livewire;

class Home extends BaseNews
{
        public function render()
    {
        $data = $this->getSharedData();
        
        return view('livewire.home', $data)->layout('components.layouts.app.sidebar');
    }
}
