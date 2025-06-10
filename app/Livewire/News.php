<?php

namespace App\Livewire;

class News extends BaseNews
{
    public function render()
    {
        $data = $this->getSharedData();
        
        return view('livewire.news', $data)->layout('components.layouts.app.sidebar');
    }
}
